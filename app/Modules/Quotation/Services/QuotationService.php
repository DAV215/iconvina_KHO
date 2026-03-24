<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Component\Repositories\ComponentRepository;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Material\Repositories\MaterialRepository;
use App\Modules\Quotation\Repositories\QuotationRepository;
use DateTimeImmutable;
use PDOException;

final class QuotationService
{
    private const STATUSES = ['draft', 'pending_approval', 'approved', 'rejected', 'cancelled', 'converted_to_order'];
    private const ITEM_MODES = [
        'estimate' => 'Ước tính / MTO',
        'component' => 'Bán thành phẩm có sẵn',
        'material' => 'Vật tư có sẵn',
        'service' => 'Dịch vụ',
    ];
    private const STATUS_LABELS = [
        'draft' => 'Nháp',
        'pending_approval' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'cancelled' => 'Đã hủy',
        'converted_to_order' => 'Đã chuyển đơn',
    ];
    private const STATUS_BADGES = [
        'draft' => 'secondary',
        'pending_approval' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary',
        'converted_to_order' => 'primary',
    ];
    private const EDITABLE_STATUSES = ['draft', 'rejected'];

    public function __construct(
        private readonly QuotationRepository $repository,
        private readonly CustomerRepository $customerRepository,
        private readonly MaterialRepository $materialRepository,
        private readonly ComponentRepository $componentRepository,
    ) {
    }

    public function list(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $list = $this->repository->search($search, $this->normalizeStatusFilter($status), $page, $perPage);
        $list['items'] = array_map(fn (array $quotation): array => $this->decorateQuotation($quotation), $list['items']);

        return $list;
    }

    public function find(int $id): array
    {
        $quotation = $this->repository->findById($id);
        if ($quotation === null) {
            throw new HttpException('Quotation not found.', 404);
        }

        $quotation = $this->decorateQuotation($quotation);
        $quotation['items'] = $this->decorateItems($this->repository->findItemsByQuotationId($id));
        $quotation['logs'] = $this->decorateLogs($this->repository->logsByQuotationId($id));
        $quotation['workflow'] = $this->workflowMeta((string) ($quotation['status'] ?? 'draft'));
        $quotation['tracking_steps'] = $this->trackingSteps($quotation['logs'], (string) ($quotation['status'] ?? 'draft'));

        return $quotation;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);
        $this->assertUniqueCode($payload['header']['code']);

        $payload['header']['created_at'] = $this->timestamp();
        $payload['header']['updated_at'] = $payload['header']['created_at'];
        $payload['header']['status'] = 'draft';

        return $this->repository->transaction(function () use ($payload): int {
            $quotationId = $this->repository->create($payload['header'], $payload['items']);
            $this->writeLog($quotationId, 'create', null, 'draft', [
                'header' => $payload['header'],
                'items' => $payload['items'],
            ]);

            return $quotationId;
        });
    }

    public function update(int $id, array $data): void
    {
        $quotation = $this->find($id);
        $this->assertEditable((string) ($quotation['status'] ?? 'draft'));
        $payload = $this->normalizePayload($data);
        $payload['header']['status'] = (string) ($quotation['status'] ?? 'draft');
        $payload['header']['code'] = (string) ($quotation['code'] ?? '');

        $payload['header']['updated_at'] = $this->timestamp();
        $oldHeader = $this->extractHeaderForLog($quotation);

        $this->repository->transaction(function () use ($id, $payload, $quotation, $oldHeader): void {
            $this->repository->update($id, $payload['header'], $payload['items']);
            $this->writeLog($id, 'update', (string) ($quotation['status'] ?? 'draft'), (string) ($quotation['status'] ?? 'draft'), [
                'before' => $oldHeader,
                'after' => $payload['header'],
                'items_count' => count($payload['items']),
            ]);
        });
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Quotation cannot be deleted because related records already exist.', 409, [
                    'errors' => [
                        'quotation' => ['Quotation has related ERP records. Delete or unlink them first.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    public function customerOptions(): array
    {
        return $this->customerRepository->options();
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function suggestCode(?string $quoteDate = null, ?string $currentCode = null): string
    {
        if ($currentCode !== null && trim($currentCode) !== '') {
            return strtoupper(trim($currentCode));
        }

        $normalizedDate = $this->normalizeCodeDate($quoteDate);
        $prefix = 'QO' . date('my', strtotime($normalizedDate)) . '-' . date('d', strtotime($normalizedDate)) . '-';
        $latest = $this->repository->latestCodeLike($prefix . '%');
        $nextSequence = 0;

        if (is_string($latest) && preg_match('/^' . preg_quote($prefix, '/') . '(\d{2})$/', $latest, $matches) === 1) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 2, '0', STR_PAD_LEFT);
    }

    public function statusLabels(): array
    {
        return self::STATUS_LABELS;
    }

    public function workflowMeta(string $status): array
    {
        $status = strtolower(trim($status));
        $canEdit = in_array($status, self::EDITABLE_STATUSES, true);

        return [
            'can_edit' => $canEdit,
            'can_submit' => $status === 'draft',
            'can_approve' => $status === 'pending_approval',
            'can_reject' => $status === 'pending_approval',
            'can_cancel' => in_array($status, ['draft', 'pending_approval'], true),
            'can_convert' => $status === 'approved',
        ];
    }

    public function submit(int $id, ?string $remark = null): void
    {
        $this->transition($id, 'draft', 'pending_approval', 'submit', $remark);
    }

    public function approve(int $id, ?string $remark = null): void
    {
        $this->transition($id, 'pending_approval', 'approved', 'approve', $remark);
    }

    public function reject(int $id, ?string $remark = null): void
    {
        $this->transition($id, 'pending_approval', 'rejected', 'reject', $remark);
    }

    public function cancel(int $id, ?string $remark = null): void
    {
        $quotation = $this->find($id);
        $currentStatus = (string) ($quotation['status'] ?? 'draft');

        if (!in_array($currentStatus, ['draft', 'pending_approval'], true)) {
            throw new HttpException('Chỉ có thể hủy báo giá ở trạng thái nháp hoặc chờ duyệt.', 409, [
                'errors' => [
                    'status' => ['Trạng thái hiện tại không cho phép hủy báo giá.'],
                ],
            ]);
        }

        $this->repository->transaction(function () use ($id, $currentStatus, $remark): void {
            $this->repository->updateStatus($id, 'cancelled');
            $this->writeLog($id, 'cancel', $currentStatus, 'cancelled', [], $remark);
        });
    }

    public function itemModes(): array
    {
        return self::ITEM_MODES;
    }

    public function itemPayload(): array
    {
        $materials = [];
        foreach ($this->materialRepository->options() as $material) {
            $materials[(int) $material['id']] = [
                'id' => (int) $material['id'],
                'code' => (string) $material['code'],
                'name' => (string) $material['name'],
                'unit' => (string) ($material['unit'] ?? ''),
                'standard_cost' => number_format((float) ($material['standard_cost'] ?? 0), 2, '.', ''),
                'option_label' => trim((string) $material['code'] . ' - ' . (string) $material['name']),
            ];
        }

        $components = [];
        foreach ($this->componentRepository->options() as $component) {
            $components[(int) $component['id']] = [
                'id' => (int) $component['id'],
                'code' => (string) $component['code'],
                'name' => (string) $component['name'],
                'unit' => (string) ($component['unit'] ?? ''),
                'standard_cost' => number_format((float) ($component['standard_cost'] ?? 0), 2, '.', ''),
                'option_label' => trim((string) $component['code'] . ' - ' . (string) $component['name']),
            ];
        }

        return [
            'materials' => $materials,
            'components' => $components,
        ];
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0 || $this->customerRepository->findById($customerId) === null) {
            $errors['customer_id'][] = 'Selected customer does not exist.';
        }

        $status = strtolower(trim((string) ($data['status'] ?? 'draft')));
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'][] = 'Selected status is invalid.';
        }

        $quoteDate = $this->normalizeDate($data['quote_date'] ?? null, 'quote_date', true, $errors);
        $expiredAt = $this->normalizeDate($data['expired_at'] ?? null, 'expired_at', false, $errors);
        $taxRate = $this->normalizeDecimal($data['tax_amount'] ?? 0, 'tax_amount', true, $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $errors);

        if ($taxRate < 0 || $taxRate > 100) {
            $errors['tax_amount'][] = 'Thuế suất phải từ 0 đến 100%.';
        }

        $code = $this->suggestCode($quoteDate);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $subtotal = 0.0;
        $discountAmount = 0.0;

        foreach ($items as $item) {
            $lineGross = ((float) $item['quantity']) * ((float) $item['unit_price']);
            $subtotal += $lineGross;
            $discountAmount += (float) $item['discount_amount'];
        }

        $taxableAmount = max($subtotal - $discountAmount, 0);
        $taxAmount = round($taxableAmount * $taxRate / 100, 2);
        $totalAmount = $taxableAmount + $taxAmount;

        return [
            'header' => [
                'code' => $code,
                'customer_id' => $customerId,
                'quote_date' => $quoteDate,
                'expired_at' => $expiredAt,
                'status' => $status,
                'subtotal' => $this->formatDecimal($subtotal),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'tax_amount' => $this->formatDecimal($taxAmount),
                'total_amount' => $this->formatDecimal($totalAmount),
                'note' => $this->nullableString($data['note'] ?? null),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Quotation items are invalid.';

            return [];
        }

        $items = [];
        $lineNo = 1;

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $itemMode = strtolower(trim((string) ($rawItem['item_mode'] ?? $rawItem['item_type'] ?? 'estimate')));
            $description = trim((string) ($rawItem['description'] ?? ''));
            $unit = trim((string) ($rawItem['unit'] ?? ''));
            $quantityRaw = $rawItem['quantity'] ?? '';
            $unitPriceRaw = $rawItem['unit_price'] ?? '';
            $discountRaw = $rawItem['discount_amount'] ?? 0;
            $componentId = $this->normalizeOptionalInt($rawItem['component_id'] ?? null);
            $materialId = $this->normalizeOptionalInt($rawItem['material_id'] ?? null);
            $tempCode = $this->nullableString($rawItem['temp_code'] ?? null);
            $specSummary = $this->nullableString($rawItem['spec_summary'] ?? null);

            $isEmptyRow = $itemMode === ''
                && $description === ''
                && $unit === ''
                && trim((string) $quantityRaw) === ''
                && trim((string) $unitPriceRaw) === ''
                && trim((string) $discountRaw) === ''
                && $componentId === null
                && $materialId === null
                && $tempCode === null
                && $specSummary === null;

            if ($isEmptyRow) {
                continue;
            }

            if (!array_key_exists($itemMode, self::ITEM_MODES)) {
                $errors["items.{$index}.item_mode"][] = 'Item mode is invalid.';
            }

            $component = null;
            $material = null;

            if ($itemMode === 'component') {
                $component = $componentId !== null ? $this->componentRepository->findById($componentId) : null;
                if ($component === null) {
                    $errors["items.{$index}.component_id"][] = 'Selected component does not exist.';
                }
                $materialId = null;
                $tempCode = null;
                $description = $description !== '' ? $description : (string) ($component['name'] ?? '');
                $unit = $unit !== '' ? $unit : (string) ($component['unit'] ?? '');
            } elseif ($itemMode === 'material') {
                $material = $materialId !== null ? $this->materialRepository->findById($materialId) : null;
                if ($material === null) {
                    $errors["items.{$index}.material_id"][] = 'Selected material does not exist.';
                }
                $componentId = null;
                $tempCode = null;
                $description = $description !== '' ? $description : (string) ($material['name'] ?? '');
                $unit = $unit !== '' ? $unit : (string) ($material['unit'] ?? '');
            } else {
                $componentId = null;
                $materialId = null;
                if ($itemMode !== 'estimate') {
                    $tempCode = null;
                }
            }

            if ($description === '') {
                $errors["items.{$index}.description"][] = 'Description is required.';
            }

            if ($unit === '') {
                $errors["items.{$index}.unit"][] = 'Unit is required.';
            }

            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", false, $errors);
            $defaultUnitPrice = (float) ($component['standard_cost'] ?? $material['standard_cost'] ?? 0);
            $unitPrice = trim((string) $unitPriceRaw) === ''
                ? round($defaultUnitPrice, 2)
                : $this->normalizeDecimal($unitPriceRaw, "items.{$index}.unit_price", false, $errors);
            $discountAmount = $this->normalizeDecimal($discountRaw, "items.{$index}.discount_amount", true, $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Quantity must be greater than zero.';
            }

            if ($unitPrice < 0) {
                $errors["items.{$index}.unit_price"][] = 'Unit price must be zero or greater.';
            }

            if ($discountAmount < 0) {
                $errors["items.{$index}.discount_amount"][] = 'Discount amount must be zero or greater.';
            }

            $grossAmount = round($quantity * $unitPrice, 2);
            if ($discountAmount > $grossAmount) {
                $errors["items.{$index}.discount_amount"][] = 'Discount amount cannot exceed line amount.';
            }

            $items[] = [
                'line_no' => $lineNo++,
                'item_mode' => $itemMode,
                'item_type' => $itemMode,
                'component_id' => $componentId,
                'material_id' => $materialId,
                'temp_code' => $tempCode,
                'spec_summary' => $specSummary,
                'description' => $description,
                'unit' => $unit,
                'quantity' => $this->formatDecimal($quantity),
                'unit_price' => $this->formatDecimal($unitPrice),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'total_amount' => $this->formatDecimal(max($grossAmount - $discountAmount, 0)),
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'At least one quotation item is required.';
        }

        return $items;
    }

    private function decorateItems(array $items): array
    {
        foreach ($items as &$item) {
            $mode = strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? 'estimate'));
            $item['item_mode'] = $mode;
            $item['item_mode_label'] = self::ITEM_MODES[$mode] ?? $mode;
            $item['is_estimate_item'] = $mode === 'estimate';
            $item['is_component_ready'] = (int) ($item['component_id'] ?? 0) > 0;
            $item['engineering_status_label'] = $mode === 'estimate' && (int) ($item['component_id'] ?? 0) <= 0
                ? 'Chưa chuẩn hóa kỹ thuật'
                : ((int) ($item['component_id'] ?? 0) > 0 ? 'Đã có mã bán thành phẩm' : '-');
            $item['master_code'] = (string) ($item['component_code'] ?? $item['material_code'] ?? $item['temp_code'] ?? '');
        }
        unset($item);

        return $items;
    }

    private function decorateQuotation(array $quotation): array
    {
        $status = strtolower((string) ($quotation['status'] ?? 'draft'));
        $subtotal = (float) ($quotation['subtotal'] ?? 0);
        $discountAmount = (float) ($quotation['discount_amount'] ?? 0);
        $taxAmount = (float) ($quotation['tax_amount'] ?? 0);
        $taxableAmount = max($subtotal - $discountAmount, 0);
        $quotation['status'] = $status;
        $quotation['status_label'] = self::STATUS_LABELS[$status] ?? $status;
        $quotation['status_badge'] = self::STATUS_BADGES[$status] ?? 'secondary';
        $quotation['tax_percent'] = $taxableAmount > 0 ? round(($taxAmount / $taxableAmount) * 100, 2) : 0.0;

        return $quotation;
    }

    private function decorateLogs(array $logs): array
    {
        $actionLabels = [
            'create' => 'Tạo báo giá',
            'update' => 'Cập nhật báo giá',
            'submit' => 'Trình duyệt báo giá',
            'approve' => 'Duyệt báo giá',
            'reject' => 'Từ chối báo giá',
            'cancel' => 'Hủy báo giá',
            'convert_to_order' => 'Chuyển sang đơn bán hàng',
        ];

        foreach ($logs as &$log) {
            $log['action_label'] = $actionLabels[(string) ($log['action'] ?? '')] ?? (string) ($log['action'] ?? '');
            $log['old_status_label'] = self::STATUS_LABELS[(string) ($log['old_status'] ?? '')] ?? ($log['old_status'] ?? null);
            $log['new_status_label'] = self::STATUS_LABELS[(string) ($log['new_status'] ?? '')] ?? ($log['new_status'] ?? null);
            $log['actor_name'] = trim((string) ($log['acted_full_name'] ?? '')) !== ''
                ? (string) $log['acted_full_name']
                : ((string) ($log['acted_username'] ?? '') !== '' ? (string) $log['acted_username'] : ((int) ($log['acted_by'] ?? 0) > 0 ? 'User #' . (int) $log['acted_by'] : 'Hệ thống'));
        }
        unset($log);

        return $logs;
    }

    private function trackingSteps(array $logs, string $currentStatus): array
    {
        $map = [
            1 => ['label' => 'Nháp', 'anchor' => 'quote-info', 'actions' => ['create']],
            2 => ['label' => 'Chờ duyệt', 'anchor' => 'quote-workflow', 'actions' => ['submit']],
            3 => ['label' => 'Đã duyệt', 'anchor' => 'quote-workflow', 'actions' => ['approve']],
            4 => ['label' => 'Hoàn tất / Chuyển đơn', 'anchor' => 'quote-workflow', 'actions' => ['convert_to_order']],
        ];
        $currentStep = $this->trackingStep($currentStatus);
        $cancelledStates = ['rejected', 'cancelled'];
        $lookup = [];

        foreach ($logs as $log) {
            $lookup[(string) ($log['action'] ?? '')] = $log;
        }

        $steps = [];
        foreach ($map as $index => $step) {
            $state = 'pending';
            if (in_array($currentStatus, $cancelledStates, true)) {
                $state = $index < $currentStep ? 'completed' : 'cancelled';
            } elseif ($index < $currentStep) {
                $state = 'completed';
            } elseif ($index === $currentStep) {
                $state = 'current';
            }

            $matchedLog = null;
            foreach ($step['actions'] as $action) {
                if (isset($lookup[$action])) {
                    $matchedLog = $lookup[$action];
                    break;
                }
            }

            $steps[] = [
                'label' => $step['label'],
                'anchor' => $step['anchor'],
                'state' => $state,
                'time' => (string) ($matchedLog['acted_at'] ?? ''),
                'note' => isset($matchedLog['actor_name']) ? 'Bởi ' . $matchedLog['actor_name'] : '',
                'badge' => $state === 'cancelled' && in_array($currentStatus, $cancelledStates, true)
                    ? (self::STATUS_LABELS[$currentStatus] ?? 'Đã dừng')
                    : null,
            ];
        }

        return $steps;
    }

    private function trackingStep(string $status): int
    {
        return match ($status) {
            'pending_approval' => 2,
            'approved' => 3,
            'converted_to_order' => 4,
            default => 1,
        };
    }

    private function transition(int $id, string $expectedStatus, string $nextStatus, string $action, ?string $remark = null): void
    {
        $quotation = $this->find($id);
        $currentStatus = (string) ($quotation['status'] ?? 'draft');

        if ($currentStatus !== $expectedStatus) {
            throw new HttpException('Trạng thái báo giá không hợp lệ cho thao tác này.', 409, [
                'errors' => [
                    'status' => ['Trạng thái hiện tại không cho phép thực hiện thao tác này.'],
                ],
            ]);
        }

        $this->repository->transaction(function () use ($id, $currentStatus, $nextStatus, $action, $remark): void {
            $this->repository->updateStatus($id, $nextStatus);
            $this->writeLog($id, $action, $currentStatus, $nextStatus, [], $remark);
        });
    }

    private function writeLog(int $quotationId, string $action, ?string $oldStatus, ?string $newStatus, array $changedFields = [], ?string $remark = null): void
    {
        $user = auth_user();
        $this->repository->createLog([
            'module' => 'quotation',
            'entity_id' => $quotationId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_fields_json' => $changedFields === [] ? null : json_encode($changedFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'remark' => $this->nullableString($remark),
            'acted_by' => (int) ($user['id'] ?? 0) > 0 ? (int) $user['id'] : null,
            'acted_at' => $this->timestamp(),
            'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);
    }

    private function assertEditable(string $status): void
    {
        if (in_array($status, self::EDITABLE_STATUSES, true)) {
            return;
        }

        throw new HttpException('Chỉ được chỉnh sửa báo giá ở trạng thái nháp hoặc bị từ chối.', 409, [
            'errors' => [
                'status' => ['Trạng thái hiện tại không cho phép chỉnh sửa báo giá.'],
            ],
        ]);
    }

    private function extractHeaderForLog(array $quotation): array
    {
        return [
            'code' => (string) ($quotation['code'] ?? ''),
            'customer_id' => (int) ($quotation['customer_id'] ?? 0),
            'quote_date' => (string) ($quotation['quote_date'] ?? ''),
            'expired_at' => $quotation['expired_at'] ?? null,
            'status' => (string) ($quotation['status'] ?? 'draft'),
            'subtotal' => (string) ($quotation['subtotal'] ?? '0.00'),
            'discount_amount' => (string) ($quotation['discount_amount'] ?? '0.00'),
            'tax_amount' => (string) ($quotation['tax_amount'] ?? '0.00'),
            'total_amount' => (string) ($quotation['total_amount'] ?? '0.00'),
            'note' => $quotation['note'] ?? null,
        ];
    }

    private function normalizeDate(mixed $value, string $field, bool $required, array &$errors): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            if ($required) {
                $errors[$field][] = 'This field is required.';
            }

            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $errors[$field][] = 'Invalid date format.';

            return null;
        }

        return $date->format('Y-m-d');
    }

    private function normalizeDecimal(mixed $value, string $field, bool $nullable, array &$errors): float
    {
        $stringValue = trim((string) ($value ?? ''));

        if ($stringValue === '') {
            if ($nullable) {
                return 0.0;
            }

            $errors[$field][] = 'This field is required.';

            return 0.0;
        }

        if (!is_numeric($stringValue)) {
            $errors[$field][] = 'This field must be numeric.';

            return 0.0;
        }

        return round((float) $stringValue, 2);
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Quotation code already exists.', 422, [
                'errors' => [
                    'code' => ['Quotation code already exists.'],
                ],
            ]);
        }
    }

    private function normalizeStatusFilter(?string $status): ?string
    {
        if ($status === null || trim($status) === '') {
            return null;
        }

        $status = strtolower(trim($status));

        return in_array($status, self::STATUSES, true) ? $status : null;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }

    private function normalizeCodeDate(?string $quoteDate): string
    {
        $quoteDate = trim((string) ($quoteDate ?? ''));
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $quoteDate);
        if ($date === false || $date->format('Y-m-d') !== $quoteDate) {
            return date('Y-m-d');
        }

        return $date->format('Y-m-d');
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}
