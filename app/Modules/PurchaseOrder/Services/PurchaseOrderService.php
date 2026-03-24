<?php

declare(strict_types=1);

namespace App\Modules\PurchaseOrder\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Material\Services\MaterialService;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\PurchaseOrder\Repositories\PurchaseOrderRepository;
use App\Modules\Supplier\Services\SupplierService;
use DateTimeImmutable;
use PDOException;

final class PurchaseOrderService
{
    private const STATUSES = [
        'draft',
        'pending_approval',
        'approved',
        'partially_received',
        'fully_received',
        'pending_stock_in',
        'stocked_in',
        'closed',
        'rejected',
        'cancelled',
    ];

    private const EDITABLE_STATUSES = ['draft', 'rejected'];
    private const RECEIVING_STATUSES = ['approved', 'partially_received'];
    private const EXTRA_COST_STATUSES = ['partially_received', 'fully_received'];
    private const STATUS_LABELS = [
        'draft' => 'Nháp',
        'pending_approval' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'partially_received' => 'Nhận một phần',
        'fully_received' => 'Đã nhận đủ',
        'pending_stock_in' => 'Chờ duyệt nhập kho',
        'stocked_in' => 'Đã nhập kho',
        'closed' => 'Đã đóng',
        'rejected' => 'Từ chối',
        'cancelled' => 'Đã hủy',
    ];
    private const STATUS_BADGES = [
        'draft' => 'secondary',
        'pending_approval' => 'warning',
        'approved' => 'success',
        'partially_received' => 'info',
        'fully_received' => 'success',
        'pending_stock_in' => 'primary',
        'stocked_in' => 'dark',
        'closed' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary',
    ];

    public function __construct(
        private readonly PurchaseOrderRepository $repository,
        private readonly SupplierService $supplierService,
        private readonly MaterialService $materialService,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $list = $this->repository->search(
            $this->normalizeFilters($filters),
            $this->normalizeSort($sort),
            $page,
            $perPage
        );

        foreach ($list['items'] as &$item) {
            $status = (string) ($item['status'] ?? 'draft');
            $item['status_label'] = self::STATUS_LABELS[$status] ?? $status;
            $item['status_badge'] = self::STATUS_BADGES[$status] ?? 'secondary';
            $item = $this->decoratePaymentSummary($item);
        }
        unset($item);

        return $list;
    }

    public function find(int $id): array
    {
        $purchaseOrder = $this->repository->findById($id);
        if ($purchaseOrder === null) {
            throw new HttpException('Không tìm thấy đơn mua hàng.', 404);
        }

        $purchaseOrder['items'] = $this->repository->findItemsByPurchaseOrderId($id);
        $purchaseOrder['receivings'] = $this->normalizeReceivings($this->repository->receivingsByPurchaseOrderId($id));
        $purchaseOrder['extra_costs'] = $this->repository->extraCostsByPurchaseOrderId($id);
        $purchaseOrder['payments'] = $this->decoratePayments($this->paymentRepository->listByPurchaseOrderId($id));
        $purchaseOrder['logs'] = $this->repository->logsByPurchaseOrderId($id);
        $purchaseOrder['stock_transaction'] = $this->repository->findStockTransactionByReference('purchase_order', $id);
        $purchaseOrder['status_label'] = self::STATUS_LABELS[(string) ($purchaseOrder['status'] ?? 'draft')] ?? (string) ($purchaseOrder['status'] ?? 'draft');
        $purchaseOrder['status_badge'] = self::STATUS_BADGES[(string) ($purchaseOrder['status'] ?? 'draft')] ?? 'secondary';
        $purchaseOrder = $this->decoratePaymentSummary($purchaseOrder);
        $purchaseOrder['receiving_summary'] = $this->buildReceivingSummary($purchaseOrder['items'], $purchaseOrder['receivings']);
        $purchaseOrder['extra_cost_total'] = $this->sumExtraCosts($purchaseOrder['extra_costs']);
        $purchaseOrder['tracking_steps'] = $this->trackingSteps((string) ($purchaseOrder['status'] ?? 'draft'));
        $purchaseOrder['available_actions'] = $this->availableActions($purchaseOrder);

        return $purchaseOrder;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data, 'draft');
        $this->assertUniqueCode($payload['header']['code']);
        $timestamp = $this->timestamp();
        $payload['header']['created_at'] = $timestamp;
        $payload['header']['updated_at'] = $timestamp;

        return $this->repository->transaction(function () use ($payload): int {
            $purchaseOrderId = $this->repository->create($payload['header'], $payload['items']);
            $this->writeLog($purchaseOrderId, 'create', null, 'draft', $payload['header'], 'Tạo đơn mua hàng.');

            return $purchaseOrderId;
        });
    }

    public function update(int $id, array $data): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');

        if (!in_array($status, self::EDITABLE_STATUSES, true)) {
            throw new HttpException('Chỉ có thể chỉnh sửa PO ở trạng thái draft hoặc rejected.', 409);
        }

        $payload = $this->normalizePayload($data, $status);
        if ((string) $purchaseOrder['code'] !== $payload['header']['code']) {
            $this->assertUniqueCode($payload['header']['code']);
        }

        $payload['header']['updated_at'] = $this->timestamp();

        $this->repository->transaction(function () use ($id, $payload, $purchaseOrder, $status): void {
            $this->repository->update($id, $payload['header'], $payload['items']);
            $this->writeLog($id, 'update', $status, $status, $this->changedFields($purchaseOrder, $payload['header']), 'Cập nhật đơn mua hàng.');
        });
    }

    public function submit(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $fromStatus = (string) ($purchaseOrder['status'] ?? 'draft');
        if (!in_array($fromStatus, ['draft', 'rejected'], true)) {
            throw new HttpException('Chỉ có thể submit PO từ draft hoặc rejected.', 409);
        }

        $this->transition($id, 'submit', $fromStatus, 'pending_approval', [], $remark ?: 'Trình duyệt đơn mua hàng.');
    }

    public function approve(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $fromStatus = (string) ($purchaseOrder['status'] ?? 'draft');
        if ($fromStatus !== 'pending_approval') {
            throw new HttpException('Chỉ có thể duyệt đơn mua hàng ở trạng thái chờ duyệt.', 409);
        }

        $this->transition($id, 'approve', $fromStatus, 'approved', [], $remark ?: 'Duyệt đơn mua hàng.');
    }

    public function reject(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $fromStatus = (string) ($purchaseOrder['status'] ?? 'draft');
        if ($fromStatus !== 'pending_approval') {
            throw new HttpException('Chỉ có thể từ chối đơn mua hàng ở trạng thái chờ duyệt.', 409);
        }

        $this->transition($id, 'reject', $fromStatus, 'rejected', [], $remark ?: 'Từ chối đơn mua hàng.');
    }

    public function cancel(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $fromStatus = (string) ($purchaseOrder['status'] ?? 'draft');
        if (!in_array($fromStatus, ['draft', 'pending_approval'], true)) {
            throw new HttpException('Chỉ có thể cancel PO trước khi approved.', 409);
        }

        $this->transition($id, 'cancel', $fromStatus, 'cancelled', [], $remark ?: 'Hủy đơn mua hàng.');
    }

    public function receive(int $id, array $data, string $mode): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');
        if (!in_array($status, self::RECEIVING_STATUSES, true)) {
            throw new HttpException('Chỉ PO approved hoặc partially received mới được nhận hàng.', 409);
        }

        $summary = $purchaseOrder['receiving_summary'];
        $receivingItems = $this->normalizeReceivingItems($purchaseOrder['items'], $summary, $data['items'] ?? [], $mode);
        $nextStatus = $this->isFullyReceived($receivingItems, $summary) ? 'fully_received' : 'partially_received';
        $errors = [];
        $receivedAt = $this->normalizeDate($data['received_at'] ?? date('Y-m-d'), 'received_at', true, $errors);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $this->repository->transaction(function () use ($id, $status, $nextStatus, $receivingItems, $data, $receivedAt): void {
            $this->repository->createReceiving([
                'purchase_order_id' => $id,
                'receive_type' => $nextStatus === 'fully_received' ? 'full' : 'partial',
                'received_at' => $receivedAt . ' 00:00:00',
                'items_json' => json_encode(array_values($receivingItems), JSON_UNESCAPED_UNICODE),
                'remark' => $this->nullableString($data['remark'] ?? null),
                'acted_by' => $this->actorId(),
                'created_at' => $this->timestamp(),
            ]);
            $this->repository->updateStatus($id, $nextStatus, $this->timestamp());
            $this->writeLog($id, $nextStatus === 'fully_received' ? 'receive_full' : 'receive_partial', $status, $nextStatus, ['items' => array_values($receivingItems)], (string) ($data['remark'] ?? 'Đã ghi nhận nhận hàng.'));
        });
    }

    public function addExtraCost(int $id, array $data): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');
        if (!in_array($status, self::EXTRA_COST_STATUSES, true)) {
            throw new HttpException('Chỉ được thêm extra cost trong giai đoạn receiving.', 409);
        }

        $label = trim((string) ($data['label'] ?? ''));
        $amount = trim((string) ($data['amount'] ?? ''));
        $errors = [];
        if ($label === '') {
            $errors['label'][] = 'Label là bắt buộc.';
        }
        if (!is_numeric($amount) || (float) $amount <= 0) {
            $errors['amount'][] = 'Amount phải lớn hơn 0.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $this->repository->transaction(function () use ($id, $label, $amount, $data, $status): void {
            $this->repository->createExtraCost([
                'purchase_order_id' => $id,
                'cost_type' => strtolower(trim((string) ($data['cost_type'] ?? 'extra_cost'))) ?: 'extra_cost',
                'label' => $label,
                'amount' => $this->formatDecimal((float) $amount),
                'remark' => $this->nullableString($data['remark'] ?? null),
                'acted_by' => $this->actorId(),
                'created_at' => $this->timestamp(),
            ]);
            $this->writeLog($id, 'add_extra_cost', $status, $status, ['label' => $label, 'amount' => $this->formatDecimal((float) $amount)], (string) ($data['remark'] ?? 'Đã thêm chi phí phát sinh.'));
        });
    }

    public function submitStockIn(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');
        if ($status !== 'fully_received') {
            throw new HttpException('Chỉ PO đã nhận đủ mới được submit stock in.', 409);
        }

        $this->transition($id, 'submit_stock_in', $status, 'pending_stock_in', [], $remark ?: 'Trình duyệt nhập kho.');
    }

    public function approveStockIn(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');
        if ($status !== 'pending_stock_in') {
            throw new HttpException('Chỉ đơn mua hàng chờ duyệt nhập kho mới được duyệt nhập kho.', 409);
        }

        if ($purchaseOrder['stock_transaction'] !== null) {
            $this->transition($id, 'stock_in_approve', $status, 'stocked_in', [
                'stock_transaction_id' => (int) $purchaseOrder['stock_transaction']['id'],
            ], $remark ?: 'Duyệt phiếu nhập kho liên kết.');

            return;
        }

        $stockPayload = $this->buildStockPayload($purchaseOrder);
        $this->repository->transaction(function () use ($id, $status, $stockPayload, $remark): void {
            $stockTransactionId = $this->repository->createStockTransactionInTransaction($stockPayload['header'], $stockPayload['items']);
            $this->repository->updateStatus($id, 'stocked_in', $this->timestamp());
            $this->writeLog($id, 'stock_in_approve', $status, 'stocked_in', [
                'stock_transaction_id' => $stockTransactionId,
            ], $remark ?: 'Duyệt và tạo phiếu nhập kho.');
        });
    }

    public function close(int $id, ?string $remark = null): void
    {
        $purchaseOrder = $this->find($id);
        $status = (string) ($purchaseOrder['status'] ?? 'draft');
        if ($status !== 'stocked_in') {
            throw new HttpException('Chỉ đơn mua hàng đã nhập kho mới được đóng.', 409);
        }

        $this->transition($id, 'close', $status, 'closed', [], $remark ?: 'Đóng đơn mua hàng.');
    }

    public function delete(int $id): void
    {
        $purchaseOrder = $this->find($id);
        if ($this->repository->hasLinkedStockTransactions($id)) {
            throw new HttpException('Không thể xóa đơn mua hàng đã liên kết phiếu nhập kho.', 409);
        }

        try {
            $this->repository->transaction(function () use ($id, $purchaseOrder): void {
                $this->repository->delete($id);
                $this->writeLog($id, 'delete', (string) ($purchaseOrder['status'] ?? 'draft'), null, [], 'Xóa đơn mua hàng.');
            });
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Không thể xóa đơn mua hàng do đã phát sinh liên kết dữ liệu.', 409);
            }

            throw $exception;
        }
    }

    public function materialOptions(): array
    {
        return $this->repository->materialOptions();
    }

    public function supplierPayload(): array
    {
        $payload = [];

        foreach ($this->supplierService->options() as $supplier) {
            $payload[(int) $supplier['id']] = [
                'id' => (int) $supplier['id'],
                'code' => (string) $supplier['code'],
                'name' => (string) $supplier['name'],
                'contact_name' => (string) ($supplier['contact_name'] ?? ''),
                'phone' => (string) ($supplier['phone'] ?? ''),
                'email' => (string) ($supplier['email'] ?? ''),
                'tax_code' => (string) ($supplier['tax_code'] ?? ''),
                'address' => (string) ($supplier['address'] ?? ''),
                'note' => (string) ($supplier['note'] ?? ''),
                'option_label' => trim((string) $supplier['code'] . ' - ' . (string) $supplier['name']),
                'search_text' => mb_strtolower(implode(' ', array_filter([
                    (string) $supplier['code'],
                    (string) $supplier['name'],
                    (string) ($supplier['contact_name'] ?? ''),
                    (string) ($supplier['phone'] ?? ''),
                    (string) ($supplier['email'] ?? ''),
                ]))),
            ];
        }

        return $payload;
    }

    public function materialPayload(): array
    {
        $payload = [];

        foreach ($this->repository->materialOptions() as $material) {
            $payload[(int) $material['id']] = [
                'id' => (int) $material['id'],
                'code' => (string) $material['code'],
                'name' => (string) $material['name'],
                'unit' => (string) $material['unit'],
                'standard_cost' => number_format((float) $material['standard_cost'], 2, '.', ''),
                'option_label' => trim((string) $material['code'] . ' - ' . (string) $material['name']),
                'category_id' => $material['category_id'] !== null ? (int) $material['category_id'] : null,
                'category_name' => (string) ($material['category_name'] ?? ''),
                'category_code' => (string) ($material['category_code'] ?? ''),
                'color' => (string) ($material['color'] ?? ''),
                'specification' => (string) ($material['specification'] ?? ''),
                'description' => (string) ($material['description'] ?? ''),
                'search_text' => mb_strtolower(implode(' ', array_filter([
                    (string) $material['code'],
                    (string) $material['name'],
                    (string) ($material['category_code'] ?? ''),
                    (string) ($material['category_name'] ?? ''),
                    (string) ($material['color'] ?? ''),
                    (string) ($material['specification'] ?? ''),
                ]))),
            ];
        }

        return $payload;
    }

    public function materialCategoryOptions(): array
    {
        return $this->materialService->categoryOptions();
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function statusLabels(): array
    {
        return self::STATUS_LABELS;
    }

    public function sortOptions(): array
    {
        return [
            'order_date' => 'Ngày đặt',
            'expected_date' => 'Ngày dự kiến',
            'code' => 'Mã đơn',
            'supplier_name' => 'Nhà cung cấp',
            'status' => 'Trạng thái',
            'total_amount' => 'Tổng tiền',
            'updated_at' => 'Cập nhật',
        ];
    }

    public function suggestCode(?string $orderDate = null, ?int $ignoreId = null): string
    {
        $errors = [];
        $resolvedDate = $this->normalizeDate($orderDate ?: date('Y-m-d'), 'order_date', true, $errors);
        $date = new DateTimeImmutable($resolvedDate ?? date('Y-m-d'));
        $prefix = 'PO' . $date->format('my') . '-' . $date->format('d') . '-';
        $sequence = 0;

        foreach ($this->repository->dailyCodes($date->format('Y-m-d'), $ignoreId) as $row) {
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            if (!str_starts_with($code, $prefix)) {
                continue;
            }

            $matched = [];
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{2,})$/', $code, $matched) === 1) {
                $sequence = max($sequence, (int) $matched[1] + 1);
            }
        }

        return $prefix . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    private function transition(int $id, string $action, string $fromStatus, ?string $toStatus, array $changedFields = [], string $remark = ''): void
    {
        $this->repository->transaction(function () use ($id, $action, $fromStatus, $toStatus, $changedFields, $remark): void {
            if ($toStatus !== null) {
                $this->repository->updateStatus($id, $toStatus, $this->timestamp());
            }
            $this->writeLog($id, $action, $fromStatus, $toStatus, $changedFields, $remark);
        });
    }

    private function normalizePayload(array $data, string $status): array
    {
        $errors = [];
        $orderDate = $this->normalizeDate($data['order_date'] ?? null, 'order_date', true, $errors);
        $expectedDate = $this->normalizeDate($data['expected_date'] ?? null, 'expected_date', false, $errors);
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if ($code === '') {
            $code = $this->suggestCode($orderDate);
        }
        if (preg_match('/^PO\d{4}-\d{2}-\d{2}$/', $code) !== 1) {
            $errors['code'][] = 'Mã đơn mua hàng phải theo định dạng POMMYY-NGÀY-STT.';
        }

        $supplierId = $this->normalizeOptionalInt($data['supplier_id'] ?? null);
        $supplier = null;
        if ($supplierId !== null) {
            try {
                $supplier = $this->supplierService->find($supplierId);
            } catch (HttpException) {
                $errors['supplier_name'][] = 'Nhà cung cấp được chọn không tồn tại.';
            }
        }

        $supplierName = trim((string) ($data['supplier_name'] ?? ''));
        if ($supplierName === '' && is_array($supplier)) {
            $supplierName = trim((string) ($supplier['name'] ?? ''));
        }
        if ($supplierName === '') {
            $errors['supplier_name'][] = 'Vui lòng nhập tên nhà cung cấp.';
        }

        $taxPercent = $this->normalizeDecimal($data['tax_percent'] ?? 0, 'tax_percent', true, $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $errors);

        if ($taxPercent < 0) {
            $errors['tax_percent'][] = 'Thuế phần trăm phải lớn hơn hoặc bằng 0.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $subtotal = 0.0;
        $discountAmount = 0.0;
        $netAmount = 0.0;

        foreach ($items as $item) {
            $lineGross = ((float) $item['quantity']) * ((float) $item['unit_price']);
            $subtotal += $lineGross;
            $discountAmount += (float) $item['discount_amount'];
            $netAmount += (float) $item['total_amount'];
        }
        $taxAmount = round($netAmount * ($taxPercent / 100), 2);

        return [
            'header' => [
                'code' => $code,
                'supplier_name' => $supplierName,
                'supplier_contact' => $this->nullableString($data['supplier_contact'] ?? ($supplier['contact_name'] ?? null)),
                'supplier_phone' => $this->nullableString($data['supplier_phone'] ?? ($supplier['phone'] ?? null)),
                'supplier_email' => $this->nullableString($data['supplier_email'] ?? ($supplier['email'] ?? null)),
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'status' => $status,
                'subtotal' => $this->formatDecimal($subtotal),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'tax_amount' => $this->formatDecimal($taxAmount),
                'total_amount' => $this->formatDecimal($netAmount + $taxAmount),
                'paid_amount' => $this->formatDecimal(0),
                'payment_status' => 'unpaid',
                'note' => $this->nullableString($data['note'] ?? null),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Dòng vật tư không hợp lệ.';

            return [];
        }

        $items = [];
        $seenMaterialIds = [];

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $materialId = $this->normalizeOptionalInt($rawItem['material_id'] ?? null);
            $description = trim((string) ($rawItem['description'] ?? ''));
            $unit = trim((string) ($rawItem['unit'] ?? ''));
            $quantityRaw = $rawItem['quantity'] ?? '';
            $unitPriceRaw = $rawItem['unit_price'] ?? '';
            $discountRaw = $rawItem['discount_amount'] ?? 0;

            $isEmptyRow = $materialId === null
                && $description === ''
                && $unit === ''
                && trim((string) $quantityRaw) === ''
                && trim((string) $unitPriceRaw) === ''
                && trim((string) $discountRaw) === '';

            if ($isEmptyRow) {
                continue;
            }

            if ($materialId === null) {
                $errors["items.{$index}.material_id"][] = 'Vui lòng chọn vật tư.';
            }

            $material = $materialId !== null ? $this->repository->findMaterialById($materialId) : null;
            if ($materialId !== null && ($material === null || (int) ($material['is_active'] ?? 0) !== 1)) {
                $errors["items.{$index}.material_id"][] = 'Vật tư được chọn không tồn tại.';
            }
            if ($materialId !== null) {
                if (isset($seenMaterialIds[$materialId])) {
                    $errors["items.{$index}.material_id"][] = 'Vật tư đã tồn tại ở dòng khác.';
                } else {
                    $seenMaterialIds[$materialId] = true;
                }
            }

            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", false, $errors);
            $unitPrice = $this->normalizeDecimal($unitPriceRaw, "items.{$index}.unit_price", false, $errors);
            $discountAmount = $this->normalizeDecimal($discountRaw, "items.{$index}.discount_amount", true, $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Số lượng phải lớn hơn 0.';
            }

            if ($unitPrice < 0) {
                $errors["items.{$index}.unit_price"][] = 'Đơn giá phải lớn hơn hoặc bằng 0.';
            }

            if ($discountAmount < 0) {
                $errors["items.{$index}.discount_amount"][] = 'Chiết khấu phải lớn hơn hoặc bằng 0.';
            }

            $lineGross = round($quantity * $unitPrice, 2);
            if ($discountAmount > $lineGross) {
                $errors["items.{$index}.discount_amount"][] = 'Chiết khấu dòng không được vượt quá thành tiền.';
            }

            $resolvedDescription = $description !== '' ? $description : (string) ($material['name'] ?? '');
            $resolvedUnit = $unit !== '' ? $unit : (string) ($material['unit'] ?? '');

            if ($resolvedDescription === '') {
                $errors["items.{$index}.description"][] = 'Mô tả dòng là bắt buộc.';
            }

            if ($resolvedUnit === '') {
                $errors["items.{$index}.unit"][] = 'Đơn vị tính là bắt buộc.';
            }

            $items[] = [
                'material_id' => $materialId,
                'description' => $resolvedDescription,
                'unit' => $resolvedUnit,
                'quantity' => $this->formatDecimal($quantity),
                'unit_price' => $this->formatDecimal($unitPrice),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'total_amount' => $this->formatDecimal(max($lineGross - $discountAmount, 0)),
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'Đơn mua hàng phải có ít nhất một dòng vật tư.';
        }

        return $items;
    }

    private function normalizeReceivingItems(array $items, array $summary, mixed $rawItems, string $mode): array
    {
        $receivingItems = [];

        foreach ($items as $index => $item) {
            $orderedQty = (float) ($item['quantity'] ?? 0);
            $receivedQty = (float) ($summary[$index]['received_quantity'] ?? 0);
            $remainingQty = max($orderedQty - $receivedQty, 0);

            if ($remainingQty <= 0) {
                continue;
            }

            $requestedQty = $mode === 'full'
                ? $remainingQty
                : round((float) ($rawItems[$index]['receive_quantity'] ?? 0), 2);

            if ($requestedQty <= 0 && $mode === 'partial') {
                continue;
            }

            if ($requestedQty > $remainingQty) {
                throw new ValidationException([
                    "items.{$index}.receive_quantity" => ['Số lượng nhận vượt quá số lượng còn lại.'],
                ]);
            }

            $receivingItems[] = [
                'item_index' => $index,
                'material_id' => $item['material_id'] !== null ? (int) $item['material_id'] : null,
                'description' => (string) ($item['description'] ?? ''),
                'ordered_quantity' => $this->formatDecimal($orderedQty),
                'receive_quantity' => $this->formatDecimal($requestedQty),
            ];
        }

        if ($receivingItems === []) {
            throw new ValidationException([
                'items' => ['Vui lòng nhập ít nhất một dòng nhận hàng hợp lệ.'],
            ]);
        }

        return $receivingItems;
    }

    private function buildReceivingSummary(array $items, array $receivings): array
    {
        $summary = [];

        foreach ($items as $index => $item) {
            $summary[$index] = [
                'ordered_quantity' => (float) ($item['quantity'] ?? 0),
                'received_quantity' => 0.0,
                'remaining_quantity' => (float) ($item['quantity'] ?? 0),
            ];
        }

        foreach ($receivings as $receiving) {
            foreach (($receiving['items'] ?? []) as $row) {
                $index = (int) ($row['item_index'] ?? -1);
                if (!isset($summary[$index])) {
                    continue;
                }

                $summary[$index]['received_quantity'] += (float) ($row['receive_quantity'] ?? 0);
                $summary[$index]['remaining_quantity'] = max(
                    $summary[$index]['ordered_quantity'] - $summary[$index]['received_quantity'],
                    0
                );
            }
        }

        return $summary;
    }

    private function normalizeReceivings(array $rows): array
    {
        foreach ($rows as &$row) {
            $decoded = json_decode((string) ($row['items_json'] ?? '[]'), true);
            $row['items'] = is_array($decoded) ? $decoded : [];
        }
        unset($row);

        return $rows;
    }

    private function availableActions(array $purchaseOrder): array
    {
        $status = (string) ($purchaseOrder['status'] ?? 'draft');

        return [
            'can_edit' => po_permission('update') && in_array($status, self::EDITABLE_STATUSES, true),
            'can_submit' => po_permission('submit') && in_array($status, ['draft', 'rejected'], true),
            'can_approve' => po_permission('approve') && $status === 'pending_approval',
            'can_reject' => po_permission('reject') && $status === 'pending_approval',
            'can_cancel' => po_permission('cancel') && in_array($status, ['draft', 'pending_approval'], true),
            'can_receive_partial' => po_permission('receive') && po_permission('receive_partial') && in_array($status, self::RECEIVING_STATUSES, true),
            'can_receive_full' => po_permission('receive') && po_permission('receive_full') && in_array($status, self::RECEIVING_STATUSES, true),
            'can_add_extra_cost' => po_permission('add_extra_cost') && in_array($status, self::EXTRA_COST_STATUSES, true),
            'can_submit_stock_in' => po_permission('submit_stock_in') && $status === 'fully_received',
            'can_stock_in_approve' => po_permission('stock_in_approve') && $status === 'pending_stock_in',
            'can_close' => po_permission('close') && $status === 'stocked_in',
            'can_create_payment' => has_permission('payment.create') && !in_array($status, ['draft', 'rejected', 'cancelled'], true),
            'can_view_payments' => has_permission('payment.view') || has_permission('payment.create') || has_permission('payment.confirm'),
            'can_view_log' => po_permission('view_log'),
        ];
    }

    private function decoratePaymentSummary(array $purchaseOrder): array
    {
        $totalAmount = round((float) ($purchaseOrder['total_amount'] ?? 0), 2);
        $paidAmount = round((float) ($purchaseOrder['paid_amount'] ?? 0), 2);
        $remainingAmount = round(max($totalAmount - $paidAmount, 0), 2);
        $paymentStatus = strtolower(trim((string) ($purchaseOrder['payment_status'] ?? 'unpaid')));

        $purchaseOrder['paid_amount'] = $paidAmount;
        $purchaseOrder['remaining_amount'] = $remainingAmount;
        $purchaseOrder['payment_status'] = in_array($paymentStatus, ['unpaid', 'partially_paid', 'paid'], true) ? $paymentStatus : 'unpaid';
        $purchaseOrder['payment_status_label'] = match ($purchaseOrder['payment_status']) {
            'paid' => 'Đã thanh toán',
            'partially_paid' => 'Thanh toán một phần',
            default => 'Chưa thanh toán',
        };
        $purchaseOrder['payment_status_badge'] = match ($purchaseOrder['payment_status']) {
            'paid' => 'success',
            'partially_paid' => 'warning',
            default => 'secondary',
        };

        return $purchaseOrder;
    }

    private function decoratePayments(array $payments): array
    {
        foreach ($payments as &$payment) {
            $status = (string) ($payment['status'] ?? 'draft');
            $payment['status_label'] = $status === 'confirmed' ? 'Confirmed' : 'Draft';
            $payment['status_badge'] = $status === 'confirmed' ? 'success' : 'secondary';
            $payment['payment_method_label'] = match ((string) ($payment['payment_method'] ?? 'other')) {
                'cash' => 'Cash',
                'bank_transfer' => 'Bank transfer',
                'card' => 'Card',
                default => 'Other',
            };
            $payment['confirmed_by_display'] = trim((string) ($payment['confirmed_by_name'] ?? '')) ?: ((string) ($payment['confirmed_by_username'] ?? '-'));
            $payment['can_confirm'] = $status === 'draft' && has_permission('payment.confirm');
        }
        unset($payment);

        return $payments;
    }

    private function trackingSteps(string $status): array
    {
        $stepStates = [
            1 => 'pending',
            2 => 'pending',
            3 => 'pending',
            4 => 'pending',
            5 => 'pending',
            6 => 'pending',
        ];
        $map = [
            'draft' => 1,
            'pending_approval' => 2,
            'approved' => 3,
            'partially_received' => 4,
            'fully_received' => 4,
            'pending_stock_in' => 5,
            'stocked_in' => 5,
            'closed' => 6,
        ];

        if (isset($map[$status])) {
            $currentStep = $map[$status];
            foreach ($stepStates as $step => &$state) {
                $state = $step < $currentStep ? 'completed' : ($step === $currentStep ? 'current' : 'pending');
            }
            unset($state);
        }

        if ($status === 'closed') {
            foreach ($stepStates as &$state) {
                $state = 'completed';
            }
            unset($state);
        }

        if ($status === 'rejected') {
            $stepStates[2] = 'cancelled';
        }
        if ($status === 'cancelled') {
            $stepStates[1] = 'completed';
            $stepStates[2] = 'cancelled';
        }

        return [
            ['label' => 'Nháp', 'state' => $stepStates[1], 'icon' => 'file-earmark-text'],
            ['label' => 'Chờ duyệt', 'state' => $stepStates[2], 'icon' => 'hourglass-split', 'badge' => in_array($status, ['rejected', 'cancelled'], true) ? (self::STATUS_LABELS[$status] ?? $status) : null],
            ['label' => 'Đã duyệt', 'state' => $stepStates[3], 'icon' => 'patch-check'],
            ['label' => 'Nhận hàng', 'state' => $stepStates[4], 'icon' => 'truck'],
            ['label' => 'Nhập kho', 'state' => $stepStates[5], 'icon' => 'box-arrow-in-down'],
            ['label' => 'Đã đóng', 'state' => $stepStates[6], 'icon' => 'flag'],
        ];
    }

    private function buildStockPayload(array $purchaseOrder): array
    {
        $items = [];

        foreach (($purchaseOrder['items'] ?? []) as $item) {
            $quantity = round((float) ($item['quantity'] ?? 0), 2);
            if ($quantity <= 0) {
                continue;
            }

            $unitCost = round((float) ($item['unit_price'] ?? 0), 2);
            $items[] = [
                'item_kind' => 'material',
                'material_id' => $item['material_id'] !== null ? (int) $item['material_id'] : null,
                'component_id' => null,
                'quantity' => $this->formatDecimal($quantity),
                'unit_cost' => $this->formatDecimal($unitCost),
                'line_total' => $this->formatDecimal($quantity * $unitCost),
            ];
        }

        if ($items === []) {
            throw new HttpException('PO không có dòng vật tư hợp lệ để nhập kho.', 409);
        }

        $poId = (int) ($purchaseOrder['id'] ?? 0);
        $poCode = preg_replace('/[^A-Z0-9\-]/', '', strtoupper((string) ($purchaseOrder['code'] ?? 'PO')));
        $txnNo = 'POIN-' . str_pad((string) $poId, 6, '0', STR_PAD_LEFT);
        if ($poCode !== '') {
            $txnNo = substr($poCode, 0, 32) . '-IN';
        }

        return [
            'header' => [
                'txn_no' => $txnNo,
                'txn_type' => 'import',
                'ref_type' => 'purchase_order',
                'ref_id' => $poId > 0 ? $poId : null,
                'txn_date' => date('Y-m-d'),
                'note' => 'Auto stock-in from PO ' . ((string) ($purchaseOrder['code'] ?? $poId)),
            ],
            'items' => $items,
        ];
    }

    private function writeLog(int $purchaseOrderId, string $action, ?string $oldStatus, ?string $newStatus, array $changedFields = [], ?string $remark = null): void
    {
        $this->repository->createLog([
            'module' => 'purchase_order',
            'entity_id' => $purchaseOrderId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_fields_json' => $changedFields === [] ? null : json_encode($changedFields, JSON_UNESCAPED_UNICODE),
            'remark' => $this->nullableString($remark),
            'acted_by' => $this->actorId(),
            'acted_at' => $this->timestamp(),
            'ip_address' => $this->nullableString($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $this->nullableString($_SERVER['HTTP_USER_AGENT'] ?? null),
        ]);
    }

    private function changedFields(array $current, array $next): array
    {
        $changes = [];

        foreach ($next as $field => $value) {
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $from = $current[$field] ?? null;
            if ((string) $from === (string) $value) {
                continue;
            }

            $changes[$field] = [
                'from' => $from,
                'to' => $value,
            ];
        }

        return $changes;
    }

    private function isFullyReceived(array $receivingItems, array $summary): bool
    {
        $receivedByIndex = [];
        foreach ($receivingItems as $row) {
            $index = (int) ($row['item_index'] ?? -1);
            if ($index < 0) {
                continue;
            }

            $receivedByIndex[$index] = ($receivedByIndex[$index] ?? 0.0) + (float) ($row['receive_quantity'] ?? 0);
        }

        foreach ($summary as $index => $row) {
            $remaining = (float) ($row['remaining_quantity'] ?? 0);
            $newlyReceived = (float) ($receivedByIndex[$index] ?? 0);
            if (round(max($remaining - $newlyReceived, 0), 2) > 0) {
                return false;
            }
        }

        return true;
    }

    private function sumExtraCosts(array $rows): float
    {
        $total = 0.0;
        foreach ($rows as $row) {
            $total += (float) ($row['amount'] ?? 0);
        }

        return round($total, 2);
    }

    private function normalizeFilters(array $filters): array
    {
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        $errors = [];

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => $status,
            'date_from' => $this->normalizeDate($filters['date_from'] ?? null, 'date_from', false, $errors),
            'date_to' => $this->normalizeDate($filters['date_to'] ?? null, 'date_to', false, $errors),
        ];
    }

    private function normalizeSort(array $sort): array
    {
        $allowed = ['order_date', 'expected_date', 'code', 'supplier_name', 'status', 'total_amount', 'updated_at'];
        $by = strtolower(trim((string) ($sort['by'] ?? 'order_date')));
        $dir = strtolower(trim((string) ($sort['dir'] ?? 'desc')));

        return [
            'by' => in_array($by, $allowed, true) ? $by : 'order_date',
            'dir' => $dir === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function normalizeDate(mixed $value, string $field, bool $required, array &$errors): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            if ($required) {
                $errors[$field][] = 'Trường này là bắt buộc.';
            }

            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $errors[$field][] = 'Định dạng ngày không hợp lệ.';

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

            $errors[$field][] = 'Trường này là bắt buộc.';

            return 0.0;
        }

        if (!is_numeric($stringValue)) {
            $errors[$field][] = 'Giá trị phải là số.';

            return 0.0;
        }

        return round((float) $stringValue, 2);
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $stringValue = trim((string) ($value ?? ''));
        if ($stringValue === '') {
            return null;
        }

        if (!is_numeric($stringValue)) {
            return null;
        }

        return (int) $stringValue;
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã đơn mua hàng đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã đơn mua hàng đã tồn tại.'],
                ],
            ]);
        }
    }

    private function actorId(): ?int
    {
        $user = auth_user();
        if (!is_array($user)) {
            return null;
        }

        $id = (int) ($user['id'] ?? 0);

        return $id > 0 ? $id : null;
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

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}
