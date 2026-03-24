<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Bom\Repositories\BomRepository;
use App\Modules\Component\Repositories\ComponentRepository;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Material\Repositories\MaterialRepository;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\Production\Repositories\ProductionRepository;
use App\Modules\Quotation\Repositories\QuotationRepository;
use App\Modules\ServiceOrder\Repositories\ServiceOrderRepository;
use DateTimeImmutable;
use PDOException;

final class OrderService
{
    private const STATUSES = ['draft', 'confirmed', 'waiting_stock', 'waiting_production', 'ready_to_deliver', 'partially_delivered', 'delivered', 'closed', 'cancelled'];
    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];
    private const ITEM_MODES = [
        'estimate' => 'Ước tính / MTO',
        'component' => 'Bán thành phẩm',
        'material' => 'Vật tư',
        'service' => 'Dịch vụ',
    ];

    public function __construct(
        private readonly OrderRepository $repository,
        private readonly CustomerRepository $customerRepository,
        private readonly QuotationRepository $quotationRepository,
        private readonly MaterialRepository $materialRepository,
        private readonly ComponentRepository $componentRepository,
        private readonly BomRepository $bomRepository,
        private readonly ProductionRepository $productionRepository,
        private readonly ServiceOrderRepository $serviceOrderRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function list(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $list = $this->repository->search($search, $this->normalizeStatusFilter($status), $page, $perPage);
        $list['items'] = array_map(fn (array $item): array => $this->decorateOrderSummary($item), $list['items']);

        return $list;
    }

    public function find(int $id): array
    {
        $this->refreshFulfillmentState($id);
        $order = $this->repository->findById($id);
        if ($order === null) {
            throw new HttpException('Order not found.', 404);
        }

        $order = $this->decorateOrderSummary($order);
        $order['items'] = $this->decorateItems($this->repository->findItemsByOrderId($id), (string) ($order['status'] ?? 'draft'));
        $order['deliveries'] = $this->decorateDeliveries($this->repository->findDeliveriesByOrderId($id));
        $order['payments'] = $this->decoratePayments($this->paymentRepository->listBySalesOrderId($id));
        $order['logs'] = $this->decorateLogs($this->repository->logsByOrderId($id));
        $order['can_mark_ready'] = in_array((string) ($order['status'] ?? ''), ['confirmed', 'waiting_stock', 'waiting_production'], true)
            && $this->canMarkReadyToDeliver($order['items']);
        $order['can_create_delivery'] = in_array((string) ($order['status'] ?? ''), ['confirmed', 'waiting_stock', 'waiting_production', 'ready_to_deliver', 'partially_delivered'], true)
            && $this->hasDeliverableReadyQty($order['items']);
        $order['can_create_payment'] = has_permission('payment.create') && !in_array((string) ($order['status'] ?? ''), ['draft', 'cancelled'], true);
        $order['can_view_payments'] = has_permission('payment.view') || has_permission('payment.create') || has_permission('payment.confirm');

        return $order;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);
        $this->assertUniqueCode($payload['header']['code']);

        return $this->repository->create($payload['header'], $payload['items']);
    }

    public function syncFulfillmentState(int $orderId): void
    {
        $this->refreshFulfillmentState($orderId);
    }

    public function update(int $id, array $data): void
    {
        $order = $this->find($id);
        $payload = $this->normalizePayload($data);

        if ($order['code'] !== $payload['header']['code']) {
            $this->assertUniqueCode($payload['header']['code']);
        }

        $this->repository->update($id, $payload['header'], $payload['items']);
    }

    public function approve(int $id): void
    {
        $order = $this->find($id);
        $currentStatus = (string) ($order['status'] ?? 'draft');

        if ($currentStatus !== 'draft') {
            throw new HttpException('Chỉ có thể duyệt đơn bán hàng đang ở trạng thái nháp.', 409, [
                'errors' => [
                    'order' => ['Đơn bán hàng hiện không ở trạng thái có thể duyệt.'],
                ],
            ]);
        }

        $this->repository->updateStatus($id, 'confirmed');
        $this->refreshFulfillmentState($id);
    }

    public function markReadyToDeliver(int $id): void
    {
        $order = $this->find($id);
        if (!$this->canMarkReadyToDeliver($order['items'])) {
            throw new HttpException('Đơn bán chưa đủ điều kiện để chuyển sang sẵn sàng giao.', 409);
        }

        $previousStatus = (string) ($order['status'] ?? 'confirmed');
        $this->repository->transaction(function () use ($id, $previousStatus): void {
            $this->repository->updateStatus($id, 'ready_to_deliver');
            $this->writeOrderLog($id, 'mark_ready_to_deliver', $previousStatus, 'ready_to_deliver', [
                'remark' => 'Đơn bán đã đủ hàng và sẵn sàng giao.',
            ]);
        });
    }

    public function createDelivery(int $orderId, array $data): int
    {
        $order = $this->find($orderId);
        if (!in_array((string) ($order['status'] ?? ''), ['confirmed', 'waiting_stock', 'waiting_production', 'ready_to_deliver', 'partially_delivered'], true)) {
            throw new HttpException('Đơn bán hiện chưa thể tạo phiếu giao.', 409);
        }

        $payload = $this->normalizeDeliveryPayload($order, $data);
        $this->assertUniqueDeliveryCode($payload['header']['code']);

        return $this->repository->transaction(function () use ($orderId, $order, $payload): int {
            $deliveryId = $this->repository->createDelivery($payload['header'], $payload['items']);
            $this->writeOrderLog($orderId, 'create_delivery', (string) ($order['status'] ?? ''), (string) ($order['status'] ?? ''), [
                'changed_fields' => [
                    'delivery_code' => $payload['header']['code'],
                    'item_count' => count($payload['items']),
                ],
                'remark' => 'Tạo phiếu giao hàng nháp.',
            ]);

            return $deliveryId;
        });
    }

    public function confirmDelivery(int $orderId, int $deliveryId): void
    {
        $order = $this->find($orderId);
        $delivery = $this->repository->findDeliveryById($orderId, $deliveryId);
        if ($delivery === null) {
            throw new HttpException('Không tìm thấy phiếu giao hàng.', 404);
        }
        if ((string) ($delivery['status'] ?? 'draft') !== 'draft') {
            throw new HttpException('Chỉ phiếu giao nháp mới được xác nhận.', 409);
        }

        $deliveryItems = $this->repository->findDeliveryItemsByDeliveryId($deliveryId);
        if ($deliveryItems === []) {
            throw new HttpException('Phiếu giao hàng chưa có dòng giao.', 409);
        }

        $validatedItems = $this->validateDeliveryConfirmation($order, $deliveryItems);
        $previousStatus = (string) ($order['status'] ?? 'ready_to_deliver');

        $this->repository->transaction(function () use ($orderId, $deliveryId, $delivery, $validatedItems, $previousStatus): void {
            $stockTransactionId = $this->repository->createStockIssue([
                'txn_no' => $this->generateDeliveryIssueNo(),
                'txn_type' => 'issue',
                'ref_type' => 'sales_delivery',
                'ref_id' => $deliveryId,
                'txn_date' => $this->timestamp(),
                'note' => 'Xuất kho giao hàng cho phiếu ' . (string) ($delivery['code'] ?? ''),
            ], array_map(static fn (array $item): array => [
                'item_kind' => $item['item_kind'],
                'material_id' => $item['material_id'],
                'component_id' => $item['component_id'],
                'quantity' => $item['delivery_qty'],
                'unit_cost' => $item['unit_cost'],
                'line_total' => $item['line_total'],
            ], $validatedItems));

            $this->repository->updateDelivery($deliveryId, [
                'status' => 'confirmed',
                'stock_transaction_id' => $stockTransactionId,
                'confirmed_by' => $this->actorId(),
                'confirmed_at' => $this->timestamp(),
            ]);

            $this->refreshFulfillmentState($orderId);
            $updatedOrder = $this->repository->findById($orderId);
            $newStatus = (string) ($updatedOrder['status'] ?? $previousStatus);
            $action = $newStatus === 'delivered' ? 'confirm_delivery_full' : 'confirm_delivery_partial';
            $remark = $newStatus === 'delivered'
                ? 'Xác nhận giao đủ đơn hàng.'
                : 'Xác nhận giao hàng một phần.';
            $this->writeOrderLog($orderId, $action, $previousStatus, $newStatus, [
                'changed_fields' => [
                    'delivery_id' => $deliveryId,
                    'stock_transaction_id' => $stockTransactionId,
                ],
                'remark' => $remark,
            ]);
        });
    }

    public function cancelDelivery(int $orderId, int $deliveryId): void
    {
        $order = $this->find($orderId);
        $delivery = $this->repository->findDeliveryById($orderId, $deliveryId);
        if ($delivery === null) {
            throw new HttpException('Không tìm thấy phiếu giao hàng.', 404);
        }
        if ((string) ($delivery['status'] ?? 'draft') !== 'draft') {
            throw new HttpException('Chỉ phiếu giao nháp mới được hủy.', 409);
        }

        $this->repository->transaction(function () use ($orderId, $delivery, $deliveryId, $order): void {
            $this->repository->updateDelivery($deliveryId, [
                'status' => 'cancelled',
            ]);
            $this->writeOrderLog($orderId, 'cancel_delivery', (string) ($order['status'] ?? ''), (string) ($order['status'] ?? ''), [
                'changed_fields' => [
                    'delivery_id' => $deliveryId,
                    'delivery_code' => (string) ($delivery['code'] ?? ''),
                ],
                'remark' => 'Hủy phiếu giao hàng nháp.',
            ]);
        });
    }

    public function createComponentFromEstimateItem(int $orderId, int $itemId): int
    {
        $order = $this->find($orderId);
        $item = $this->repository->findItemById($orderId, $itemId);

        if ($item === null) {
            throw new HttpException('Không tìm thấy dòng đơn bán hàng.', 404);
        }

        if (!$this->isEstimateItem($item)) {
            throw new HttpException('Chỉ dòng estimate mới được tạo mã bán thành phẩm.', 409);
        }

        if ((int) ($item['component_id'] ?? 0) > 0) {
            throw new HttpException('Dòng này đã có mã bán thành phẩm.', 409);
        }

        if (!$this->canStandardizeEngineering((string) ($order['status'] ?? 'draft'))) {
            throw new HttpException('Chỉ được chuẩn hóa kỹ thuật sau khi đơn bán đã xác nhận.', 409);
        }

        $componentPayload = $this->buildComponentPayload($order, $item);

        return $this->repository->transaction(function () use ($item, $componentPayload): int {
            $componentId = $this->componentRepository->create($componentPayload);
            $this->repository->updateItemEngineering((int) $item['id'], [
                'item_mode' => 'component',
                'item_type' => 'component',
                'component_id' => $componentId,
                'temp_code' => $componentPayload['code'],
            ]);

            $quotationItemId = (int) ($item['quotation_item_id'] ?? 0);
            if ($quotationItemId > 0) {
                $this->repository->updateLinkedQuotationItemEngineering($quotationItemId, [
                    'item_mode' => 'component',
                    'item_type' => 'component',
                    'component_id' => $componentId,
                    'temp_code' => $componentPayload['code'],
                ]);
            }

            return $componentId;
        });
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Order cannot be deleted because related records already exist.', 409, [
                    'errors' => [
                        'order' => ['Order has related ERP records. Delete or unlink them first.'],
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

    public function quotationOptions(): array
    {
        return $this->quotationRepository->options();
    }

    public function quotationPayload(): array
    {
        $quotations = $this->quotationRepository->options();
        $quotationIds = array_map(static fn (array $quotation): int => (int) $quotation['id'], $quotations);
        $items = $this->quotationRepository->findItemsByQuotationIds($quotationIds);
        $groupedItems = [];

        foreach ($this->decorateQuotationItems($items) as $item) {
            $quotationId = (int) $item['quotation_id'];
            $groupedItems[$quotationId][] = [
                'quotation_item_id' => (int) ($item['id'] ?? 0),
                'item_mode' => (string) ($item['item_mode'] ?? 'estimate'),
                'item_type' => (string) ($item['item_type'] ?? $item['item_mode'] ?? 'estimate'),
                'component_id' => $item['component_id'] ?? null,
                'material_id' => $item['material_id'] ?? null,
                'temp_code' => (string) ($item['temp_code'] ?? ''),
                'spec_summary' => (string) ($item['spec_summary'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
                'unit' => (string) ($item['unit'] ?? ''),
                'quantity' => number_format((float) ($item['quantity'] ?? 0), 2, '.', ''),
                'unit_price' => number_format((float) ($item['unit_price'] ?? 0), 2, '.', ''),
                'discount_amount' => number_format((float) ($item['discount_amount'] ?? 0), 2, '.', ''),
            ];
        }

        $payload = [];
        foreach ($quotations as $quotation) {
            $quotationId = (int) $quotation['id'];
            $payload[$quotationId] = [
                'id' => $quotationId,
                'code' => (string) $quotation['code'],
                'customer_id' => (int) $quotation['customer_id'],
                'discount_amount' => number_format((float) $quotation['discount_amount'], 2, '.', ''),
                'tax_amount' => number_format((float) $quotation['tax_amount'], 2, '.', ''),
                'items' => $groupedItems[$quotationId] ?? [],
            ];
        }

        return $payload;
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

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function suggestCode(?string $orderDate = null, ?string $currentCode = null): string
    {
        if ($currentCode !== null && trim($currentCode) !== '') {
            return strtoupper(trim($currentCode));
        }

        $resolvedDate = $this->normalizeCodeDate($orderDate);
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $resolvedDate) ?: new DateTimeImmutable();
        $prefix = 'SO' . $date->format('myd') . '-';
        $latest = $this->repository->latestOrderCodeLike($prefix);
        $sequence = 0;

        if ($latest !== null && preg_match('/^' . preg_quote($prefix, '/') . '(\d{2})$/', (string) ($latest['code'] ?? ''), $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    public function priorities(): array
    {
        return self::PRIORITIES;
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $orderDate = $this->normalizeDate($data['order_date'] ?? null, 'order_date', true, $errors);
        $code = $this->suggestCode($orderDate, (string) ($data['code'] ?? ''));

        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0 || $this->customerRepository->findById($customerId) === null) {
            $errors['customer_id'][] = 'Selected customer does not exist.';
        }

        $quotationId = $this->normalizeOptionalInt($data['quotation_id'] ?? null);
        if ($quotationId !== null) {
            $quotation = $this->quotationRepository->findById($quotationId);
            if ($quotation === null) {
                $errors['quotation_id'][] = 'Selected quotation does not exist.';
            } elseif ((int) $quotation['customer_id'] !== $customerId) {
                $errors['quotation_id'][] = 'Selected quotation does not belong to the chosen customer.';
            }
        }

        $status = strtolower(trim((string) ($data['status'] ?? '')));
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'][] = 'Selected status is invalid.';
        }

        $priority = strtolower(trim((string) ($data['priority'] ?? '')));
        if (!in_array($priority, self::PRIORITIES, true)) {
            $errors['priority'][] = 'Selected priority is invalid.';
        }

        $dueDate = $this->normalizeDate($data['due_date'] ?? null, 'due_date', false, $errors);
        $discountAmount = $this->normalizeDecimal($data['discount_amount'] ?? 0, 'discount_amount', true, $errors);
        $taxAmount = $this->normalizeDecimal($data['tax_amount'] ?? 0, 'tax_amount', true, $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $quotationId, $errors);

        if ($discountAmount < 0) {
            $errors['discount_amount'][] = 'Discount amount must be zero or greater.';
        }

        if ($taxAmount < 0) {
            $errors['tax_amount'][] = 'Tax amount must be zero or greater.';
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $lineGross = ((float) $item['quantity']) * ((float) $item['unit_price']);
            $subtotal += $lineGross - (float) $item['discount_amount'];
        }

        if ($discountAmount > $subtotal) {
            $errors['discount_amount'][] = 'Discount amount cannot exceed subtotal.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'header' => [
                'code' => $code,
                'customer_id' => $customerId,
                'quotation_id' => $quotationId,
                'order_date' => $orderDate,
                'due_date' => $dueDate,
                'status' => $status,
                'priority' => $this->mapPriorityToStorage($priority),
                'subtotal' => $this->formatDecimal($subtotal),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'tax_amount' => $this->formatDecimal($taxAmount),
                'total_amount' => $this->formatDecimal($totalAmount),
                'paid_amount' => $this->formatDecimal(0),
                'payment_status' => 'unpaid',
                'note' => $this->nullableString($data['note'] ?? null),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, ?int $quotationId, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Order items are invalid.';

            return [];
        }

        $items = [];

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
            $quotationItemId = $this->normalizeOptionalInt($rawItem['quotation_item_id'] ?? null);
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
                && $quotationItemId === null
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

            if ($quotationItemId !== null && $quotationId === null) {
                $quotationItemId = null;
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
                'quotation_item_id' => $quotationItemId,
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
            $errors['items'][] = 'At least one order item is required.';
        }

        return $items;
    }

    private function decorateItems(array $items, string $orderStatus): array
    {
        $componentIds = [];
        $materialIds = [];
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = (int) ($item['id'] ?? 0);
            $componentId = (int) ($item['component_id'] ?? 0);
            $materialId = (int) ($item['material_id'] ?? 0);
            if ($componentId > 0) {
                $componentIds[] = $componentId;
            }
            if ($materialId > 0) {
                $materialIds[] = $materialId;
            }
        }

        $stockMap = $this->repository->componentStockMap(array_values(array_unique($componentIds)));
        $materialStockMap = $this->repository->materialStockMap(array_values(array_unique($materialIds)));
        $deliveredMap = $this->repository->deliveryItemTotalsByOrderId((int) ($items[0]['sales_order_id'] ?? 0));
        $productionMap = [];
        foreach ($this->productionRepository->findLatestBySalesOrderItemIds(array_values(array_filter($itemIds))) as $productionOrder) {
            $productionMap[(int) ($productionOrder['sales_order_item_id'] ?? 0)] = $productionOrder;
        }
        $serviceOrderMap = [];
        foreach ($this->serviceOrderRepository->findLatestBySalesOrderItemIds(array_values(array_filter($itemIds))) as $serviceOrder) {
            $serviceOrderMap[(int) ($serviceOrder['sales_order_item_id'] ?? 0)] = $serviceOrder;
        }

        foreach ($items as &$item) {
            $mode = strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? 'estimate'));
            $componentReady = $this->isComponentReady($item);
            $activeBomId = (int) ($item['active_bom_id'] ?? 0);
            $componentId = (int) ($item['component_id'] ?? 0);
            $materialId = (int) ($item['material_id'] ?? 0);
            $orderedQty = (float) ($item['quantity'] ?? 0);
            $deliveredQty = round((float) ($deliveredMap[(int) ($item['id'] ?? 0)] ?? 0), 2);
            $remainingQty = round(max($orderedQty - $deliveredQty, 0), 2);
            $availableQty = $componentId > 0
                ? (float) ($stockMap[$componentId] ?? 0)
                : ($materialId > 0 ? (float) ($materialStockMap[$materialId] ?? 0) : 0.0);
            $readyQty = round(min($availableQty, $remainingQty > 0 ? $remainingQty : $orderedQty), 2);
            $requiredQty = (float) ($item['quantity'] ?? 0);
            $shortageQty = ($componentId > 0 || $materialId > 0) ? round(max($remainingQty - $availableQty, 0), 2) : 0.0;
            $productionOrder = $productionMap[(int) ($item['id'] ?? 0)] ?? null;
            $productionStatus = (string) ($productionOrder['status'] ?? '');
            $serviceOrder = $serviceOrderMap[(int) ($item['id'] ?? 0)] ?? null;
            $serviceOrderStatus = (string) ($serviceOrder['status'] ?? '');
            $fulfillmentStatus = (string) ($item['fulfillment_status'] ?? 'pending');

            $item['item_mode'] = $mode;
            $item['item_mode_label'] = self::ITEM_MODES[$mode] ?? $mode;
            $item['is_estimate_item'] = $this->isEstimateItem($item);
            $item['is_component_ready'] = $componentReady;
            $item['can_create_component'] = $this->canCreateComponentFromEstimate($item, $orderStatus);
            $item['can_create_bom'] = $this->canCreateBom($item);
            $item['can_create_production_order'] = $this->canCreateProductionOrder($item, $orderStatus);
            $item['engineering_status_label'] = $componentReady
                ? 'Đã chuẩn hóa kỹ thuật'
                : ($this->isEstimateItem($item) ? 'Chưa chuẩn hóa kỹ thuật' : '-');
            $item['engineering_status_badge'] = $componentReady ? 'is-active' : ($this->isEstimateItem($item) ? 'is-pending' : 'is-inactive');
            $item['master_code'] = (string) ($item['component_code'] ?? $item['material_code'] ?? $item['temp_code'] ?? '');
            $item['component_detail_url'] = $componentReady ? app_url('/components/show?id=' . (int) $item['component_id']) : null;
            $item['bom_create_url'] = $componentReady && $activeBomId <= 0 ? app_url('/bom/create?component_id=' . (int) $item['component_id'] . '&version=R01') : null;
            $item['bom_show_url'] = $activeBomId > 0 ? app_url('/bom/show?id=' . $activeBomId) : null;
            $item['available_qty'] = $availableQty;
            $item['ordered_qty'] = $orderedQty;
            $item['ready_qty'] = $readyQty;
            $item['delivered_qty'] = $deliveredQty;
            $item['remaining_qty'] = $remainingQty;
            $item['shortage_qty'] = $shortageQty;
            $item['production_order_id'] = $productionOrder['id'] ?? null;
            $item['production_order_code'] = $productionOrder['code'] ?? null;
            $item['production_order_url'] = !empty($productionOrder['id']) ? app_url('/production-orders/show?id=' . (int) $productionOrder['id']) : null;
            $item['service_order_id'] = $serviceOrder['id'] ?? null;
            $item['service_order_code'] = $serviceOrder['code'] ?? null;
            $item['service_order_url'] = !empty($serviceOrder['id']) ? app_url('/service-orders/show?id=' . (int) $serviceOrder['id']) : null;
            $item['service_order_status'] = $serviceOrderStatus;
            $item['service_order_status_label'] = $this->serviceOrderStatusLabel($serviceOrderStatus);
            $item['service_order_status_badge'] = $this->serviceOrderStatusBadge($serviceOrderStatus);
            $item['service_order_assigned_to'] = $serviceOrder['assigned_to'] ?? null;
            $item['service_order_assigned_name'] = trim((string) ($serviceOrder['assigned_full_name'] ?? '')) ?: ((string) ($serviceOrder['assigned_username'] ?? ''));
            $isAssignedServiceUser = (int) ($serviceOrder['assigned_to'] ?? 0) > 0 && (int) ($serviceOrder['assigned_to'] ?? 0) === $this->actorId();
            $item['can_view_service_order'] = !empty($serviceOrder['id']);
            $item['can_start_service_order'] = !empty($serviceOrder['id'])
                && (service_order_permission('start') || $isAssignedServiceUser)
                && in_array($serviceOrderStatus, ['draft', 'assigned'], true)
                && (int) ($serviceOrder['assigned_to'] ?? 0) > 0;
            $item['can_complete_service_order'] = !empty($serviceOrder['id'])
                && (service_order_permission('complete') || $isAssignedServiceUser)
                && in_array($serviceOrderStatus, ['assigned', 'in_progress'], true)
                && (int) ($serviceOrder['assigned_to'] ?? 0) > 0;
            $item['requires_delivery'] = $this->requiresDelivery($item);
            $item['can_create_delivery'] = $item['requires_delivery'] && $remainingQty > 0 && $readyQty > 0;

            if ($deliveredQty >= $orderedQty && $orderedQty > 0) {
                $fulfillmentStatus = 'delivered';
            } elseif ($deliveredQty > 0) {
                $fulfillmentStatus = 'partially_delivered';
            } elseif ($productionStatus !== '') {
                $fulfillmentStatus = match ($productionStatus) {
                    'completed' => 'ready',
                    'released', 'in_progress', 'paused' => 'in_production',
                    default => 'waiting_production',
                };
            } elseif (($componentId > 0 || $materialId > 0) && $shortageQty <= 0) {
                $fulfillmentStatus = 'ready';
            } elseif ($componentId > 0 && $shortageQty > 0) {
                $fulfillmentStatus = 'waiting_production';
            } elseif ($materialId > 0 && $shortageQty > 0) {
                $fulfillmentStatus = 'pending';
            } elseif ($mode === 'service') {
                $fulfillmentStatus = match ($serviceOrderStatus) {
                    'completed', 'closed' => 'ready',
                    'in_progress' => 'in_service',
                    default => 'waiting_service',
                };
            }

            $item['fulfillment_status'] = $fulfillmentStatus;
            $item['fulfillment_status_label'] = $this->fulfillmentStatusLabel($fulfillmentStatus);
            $item['fulfillment_badge'] = $this->fulfillmentStatusBadge($fulfillmentStatus);
            $item['stock_status_label'] = match (true) {
                $fulfillmentStatus === 'delivered' => 'Đã giao đủ',
                $fulfillmentStatus === 'partially_delivered' => 'Đã giao một phần',
                ($componentId > 0 || $materialId > 0) && $shortageQty > 0 => 'Chưa đủ hàng để giao',
                ($componentId > 0 || $materialId > 0) => 'Đã sẵn sàng giao',
                $mode === 'service' => match ($serviceOrderStatus) {
                    'completed', 'closed' => 'Đã hoàn thành dịch vụ',
                    'in_progress' => 'Nhân sự đang thực hiện dịch vụ',
                    'assigned' => 'Đã giao việc dịch vụ',
                    default => 'Chờ giao việc dịch vụ',
                },
                default => 'Chờ chuẩn hóa kỹ thuật',
            };
            $item['stock_status_badge'] = ($componentId > 0 || $materialId > 0)
                ? ($shortageQty > 0 ? 'warning' : 'success')
                : ($mode === 'service'
                    ? match ($serviceOrderStatus) {
                        'completed', 'closed' => 'success',
                        'in_progress' => 'warning',
                        'assigned' => 'info',
                        default => 'secondary',
                    }
                    : 'info');
            $item['can_create_production_order'] = $this->canCreateProductionOrder($item, $orderStatus)
                && $shortageQty > 0
                && $productionStatus === '';
            $item['production_block_reason'] = !$componentReady
                ? 'Chưa có mã bán thành phẩm'
                : ($activeBomId <= 0 ? 'Chưa có BOM active' : ($shortageQty <= 0 ? 'Tồn kho đã đủ' : ($productionStatus !== '' ? 'Đã có lệnh sản xuất' : '')));
        }
        unset($item);

        return $items;
    }

    private function decorateQuotationItems(array $items): array
    {
        foreach ($items as &$item) {
            $mode = strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? 'estimate'));
            $item['item_mode'] = $mode;
        }
        unset($item);

        return $items;
    }

    private function isEstimateItem(array $item): bool
    {
        return strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? '')) === 'estimate';
    }

    private function isComponentReady(array $item): bool
    {
        return (int) ($item['component_id'] ?? 0) > 0;
    }

    private function canCreateBom(array $item): bool
    {
        return $this->isComponentReady($item) && (int) ($item['active_bom_id'] ?? 0) <= 0;
    }

    private function canCreateProductionOrder(array $item, string $orderStatus): bool
    {
        return $this->isComponentReady($item)
            && (int) ($item['active_bom_id'] ?? 0) > 0
            && $this->canStandardizeEngineering($orderStatus);
    }

    private function canCreateComponentFromEstimate(array $item, string $orderStatus): bool
    {
        return $this->isEstimateItem($item)
            && !$this->isComponentReady($item)
            && $this->canStandardizeEngineering($orderStatus);
    }

    private function canStandardizeEngineering(string $orderStatus): bool
    {
        return in_array(strtolower(trim($orderStatus)), ['confirmed', 'waiting_stock', 'waiting_production', 'ready_to_deliver', 'partially_delivered', 'delivered', 'closed'], true);
    }

    private function buildComponentPayload(array $order, array $item): array
    {
        $baseCode = trim((string) ($item['temp_code'] ?? ''));
        if ($baseCode === '') {
            $baseCode = $this->generateComponentCode((string) ($order['code'] ?? 'SO'), (int) ($item['line_no'] ?? 1));
        }

        return [
            'code' => $this->resolveUniqueComponentCode($baseCode),
            'name' => trim((string) ($item['description'] ?? '')) ?: 'Bán thành phẩm từ đơn hàng',
            'unit' => trim((string) ($item['unit'] ?? '')) ?: 'pcs',
            'component_type' => 'semi_finished',
            'standard_cost' => $this->formatDecimal((float) ($item['unit_price'] ?? 0)),
            'image_path' => null,
            'is_active' => 1,
        ];
    }

    private function generateComponentCode(string $orderCode, int $lineNo): string
    {
        $sanitizedOrderCode = strtoupper((string) preg_replace('/[^A-Z0-9]+/', '-', $orderCode));
        $sanitizedOrderCode = trim($sanitizedOrderCode, '-');
        if ($sanitizedOrderCode === '') {
            $sanitizedOrderCode = 'SO';
        }

        return substr('CF-' . $sanitizedOrderCode . '-' . str_pad((string) max(1, $lineNo), 2, '0', STR_PAD_LEFT), 0, 30);
    }

    private function resolveUniqueComponentCode(string $baseCode): string
    {
        $code = strtoupper(trim($baseCode));
        if ($code === '') {
            $code = 'CF-' . date('ymdHis');
        }

        $resolved = substr($code, 0, 30);
        $suffix = 1;

        while ($this->componentRepository->findByCode($resolved) !== null) {
            $tail = '-' . $suffix;
            $resolved = substr($code, 0, max(1, 30 - strlen($tail))) . $tail;
            $suffix++;
        }

        return $resolved;
    }

    private function decorateOrderSummary(array $order): array
    {
        $priorityKey = $this->mapPriorityFromStorage($order['priority'] ?? 2);
        $order['priority'] = $priorityKey;
        $order['priority_label'] = match ($priorityKey) {
            'low' => 'Thấp',
            'high' => 'Cao',
            'urgent' => 'Khẩn',
            default => 'Bình thường',
        };
        $order['status_label'] = match ((string) ($order['status'] ?? 'draft')) {
            'confirmed' => 'Đã xác nhận',
            'waiting_stock' => 'Chờ kiểm tồn',
            'waiting_production' => 'Chờ sản xuất',
            'ready_to_deliver' => 'Sẵn sàng giao',
            'partially_delivered' => 'Giao một phần',
            'delivered' => 'Đã giao',
            'closed' => 'Đã đóng',
            'cancelled' => 'Đã hủy',
            default => 'Nháp',
        };
        $totalAmount = round((float) ($order['total_amount'] ?? 0), 2);
        $paidAmount = round((float) ($order['paid_amount'] ?? 0), 2);
        $remainingAmount = round(max($totalAmount - $paidAmount, 0), 2);
        $paymentStatus = strtolower(trim((string) ($order['payment_status'] ?? 'unpaid')));
        $order['paid_amount'] = $paidAmount;
        $order['remaining_amount'] = $remainingAmount;
        $order['payment_status'] = in_array($paymentStatus, ['unpaid', 'partially_paid', 'paid'], true) ? $paymentStatus : 'unpaid';
        $order['payment_status_label'] = match ($order['payment_status']) {
            'paid' => 'Đã thanh toán',
            'partially_paid' => 'Thanh toán một phần',
            default => 'Chưa thanh toán',
        };
        $order['payment_status_badge'] = match ($order['payment_status']) {
            'paid' => 'success',
            'partially_paid' => 'warning',
            default => 'secondary',
        };

        return $order;
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

    private function fulfillmentStatusLabel(string $status): string
    {
        return match ($status) {
            'ready_from_stock', 'ready' => 'Sẵn sàng giao',
            'waiting_production' => 'Chờ sản xuất',
            'in_production' => 'Đang sản xuất',
            'waiting_service' => 'Chờ thực hiện dịch vụ',
            'in_service' => 'Đang thực hiện dịch vụ',
            'partially_delivered' => 'Đã giao một phần',
            'delivered' => 'Đã giao',
            default => 'Chờ xử lý',
        };
    }

    private function fulfillmentStatusBadge(string $status): string
    {
        return match ($status) {
            'ready_from_stock', 'ready' => 'success',
            'partially_delivered' => 'warning',
            'waiting_production' => 'warning',
            'in_production' => 'info',
            'waiting_service' => 'secondary',
            'in_service' => 'warning',
            'delivered' => 'primary',
            default => 'secondary',
        };
    }

    private function refreshFulfillmentState(int $orderId): void
    {
        $order = $this->repository->findById($orderId);
        if ($order === null) {
            return;
        }

        if (in_array((string) ($order['status'] ?? ''), ['draft', 'cancelled', 'closed'], true)) {
            return;
        }

        $items = $this->repository->findItemsByOrderId($orderId);
        if ($items === []) {
            return;
        }

        $componentIds = [];
        $materialIds = [];
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = (int) ($item['id'] ?? 0);
            $componentId = (int) ($item['component_id'] ?? 0);
            $materialId = (int) ($item['material_id'] ?? 0);
            if ($componentId > 0) {
                $componentIds[] = $componentId;
            }
            if ($materialId > 0) {
                $materialIds[] = $materialId;
            }
        }

        $stockMap = $this->repository->componentStockMap(array_values(array_unique($componentIds)));
        $materialStockMap = $this->repository->materialStockMap(array_values(array_unique($materialIds)));
        $deliveredMap = $this->repository->deliveryItemTotalsByOrderId($orderId);
        $productionMap = [];
        foreach ($this->productionRepository->findLatestBySalesOrderItemIds(array_values(array_filter($itemIds))) as $productionOrder) {
            $productionMap[(int) ($productionOrder['sales_order_item_id'] ?? 0)] = $productionOrder;
        }
        $serviceOrderMap = [];
        foreach ($this->serviceOrderRepository->findLatestBySalesOrderItemIds(array_values(array_filter($itemIds))) as $serviceOrder) {
            $serviceOrderMap[(int) ($serviceOrder['sales_order_item_id'] ?? 0)] = $serviceOrder;
        }

        $orderStatus = 'ready_to_deliver';
        $allDelivered = true;
        $hasPartialDelivery = false;
        $hasDeliverableItem = false;
        $hasPendingNonDeliveryWork = false;

        foreach ($items as $item) {
            $itemId = (int) ($item['id'] ?? 0);
            $componentId = (int) ($item['component_id'] ?? 0);
            $materialId = (int) ($item['material_id'] ?? 0);
            $requiredQty = (float) ($item['quantity'] ?? 0);
            $deliveredQty = round((float) ($deliveredMap[$itemId] ?? 0), 2);
            $remainingQty = round(max($requiredQty - $deliveredQty, 0), 2);
            $availableQty = $componentId > 0
                ? (float) ($stockMap[$componentId] ?? 0)
                : ($materialId > 0 ? (float) ($materialStockMap[$materialId] ?? 0) : 0.0);
            $shortageQty = round(max($remainingQty - $availableQty, 0), 2);
            $productionOrder = $productionMap[$itemId] ?? null;
            $serviceOrder = $serviceOrderMap[$itemId] ?? null;
            $fulfillmentStatus = (string) ($item['fulfillment_status'] ?? 'pending');

            if ($deliveredQty >= $requiredQty && $requiredQty > 0) {
                $fulfillmentStatus = 'delivered';
            } elseif ($deliveredQty > 0) {
                $fulfillmentStatus = 'partially_delivered';
            } elseif ($componentId > 0) {
                if ($shortageQty <= 0) {
                    $fulfillmentStatus = 'ready';
                } elseif ($productionOrder !== null) {
                    $fulfillmentStatus = match ((string) ($productionOrder['status'] ?? 'draft')) {
                        'completed' => 'ready',
                        'released', 'in_progress', 'paused' => 'in_production',
                        default => 'waiting_production',
                    };
                } else {
                    $fulfillmentStatus = 'waiting_production';
                }
            } elseif ($materialId > 0) {
                $fulfillmentStatus = $shortageQty <= 0 ? 'ready' : 'pending';
            } elseif (($item['item_mode'] ?? '') === 'service') {
                $serviceOrderStatus = (string) ($serviceOrder['status'] ?? '');
                $fulfillmentStatus = match ($serviceOrderStatus) {
                    'completed', 'closed' => 'ready',
                    'in_progress' => 'in_service',
                    default => 'waiting_service',
                };
            }

            $this->repository->updateItemEngineering($itemId, ['fulfillment_status' => $fulfillmentStatus]);

            if (!$this->requiresDelivery($item)) {
                if ($fulfillmentStatus !== 'ready') {
                    $allDelivered = false;
                    $hasPendingNonDeliveryWork = true;
                    $orderStatus = 'waiting_production';
                }
                continue;
            }

            $hasDeliverableItem = true;

            if ($fulfillmentStatus === 'delivered') {
                continue;
            }

            $allDelivered = false;
            if ($fulfillmentStatus === 'partially_delivered') {
                $hasPartialDelivery = true;
                $orderStatus = 'partially_delivered';
                continue;
            }
            if (in_array($fulfillmentStatus, ['waiting_production', 'in_production'], true)) {
                $orderStatus = 'waiting_production';
            } elseif (!in_array($fulfillmentStatus, ['ready'], true) && $orderStatus !== 'waiting_production' && !$hasPartialDelivery) {
                $orderStatus = 'waiting_stock';
            }
        }

        if (!$hasDeliverableItem && $hasPendingNonDeliveryWork) {
            $orderStatus = 'waiting_production';
        } elseif (!$hasDeliverableItem || $allDelivered) {
            $orderStatus = 'delivered';
        } elseif ($hasPartialDelivery && $orderStatus !== 'waiting_production') {
            $orderStatus = 'partially_delivered';
        }

        $this->repository->updateStatus($orderId, $orderStatus);
    }

    private function mapPriorityToStorage(string $priority): int
    {
        return match ($priority) {
            'low' => 1,
            'high' => 3,
            'urgent' => 4,
            default => 2,
        };
    }

    private function mapPriorityFromStorage(mixed $priority): string
    {
        $priority = (int) $priority;

        return match ($priority) {
            1 => 'low',
            3 => 'high',
            4 => 'urgent',
            default => 'normal',
        };
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

    private function normalizeDeliveryPayload(array $order, array $data): array
    {
        $errors = [];
        $orderItems = $order['items'] ?? [];
        if ($orderItems === []) {
            throw new HttpException('Đơn bán chưa có dòng hàng để giao.', 409);
        }

        $deliveryDate = $this->normalizeDate($data['delivery_date'] ?? date('Y-m-d'), 'delivery_date', true, $errors);
        $shippingCost = $this->normalizeDecimal($data['shipping_cost'] ?? 0, 'shipping_cost', true, $errors);
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if ($code === '') {
            $code = $this->generateDeliveryCode($deliveryDate ?? date('Y-m-d'));
        }

        $requestedItems = $data['deliveries'] ?? [];
        if (!is_array($requestedItems)) {
            $requestedItems = [];
        }

        $itemsById = [];
        foreach ($orderItems as $item) {
            $itemsById[(int) ($item['id'] ?? 0)] = $item;
        }

        $deliveryItems = [];
        foreach ($requestedItems as $itemId => $row) {
            $salesOrderItemId = (int) $itemId;
            $orderItem = $itemsById[$salesOrderItemId] ?? null;
            if ($orderItem === null || !is_array($row)) {
                continue;
            }

            $qty = $this->normalizeDecimal($row['delivery_qty'] ?? '', 'deliveries.' . $salesOrderItemId . '.delivery_qty', true, $errors);
            if ($qty <= 0) {
                continue;
            }

            $readyQty = (float) ($orderItem['ready_qty'] ?? 0);
            $remainingQty = (float) ($orderItem['remaining_qty'] ?? 0);
            $itemKind = (int) ($orderItem['component_id'] ?? 0) > 0 ? 'component' : ((int) ($orderItem['material_id'] ?? 0) > 0 ? 'material' : 'service');

            if ($itemKind === 'service') {
                $errors['deliveries.' . $salesOrderItemId . '.delivery_qty'][] = 'Dịch vụ không tạo phiếu xuất kho.';
                continue;
            }

            if ($qty > $readyQty) {
                $errors['deliveries.' . $salesOrderItemId . '.delivery_qty'][] = 'Số lượng giao vượt quá số lượng sẵn sàng giao.';
            }

            if ($qty > $remainingQty) {
                $errors['deliveries.' . $salesOrderItemId . '.delivery_qty'][] = 'Số lượng giao vượt quá số lượng còn lại.';
            }

            $unitCost = (float) ($orderItem['unit_price'] ?? 0);
            $deliveryItems[] = [
                'sales_order_item_id' => $salesOrderItemId,
                'item_kind' => $itemKind,
                'component_id' => $itemKind === 'component' ? (int) ($orderItem['component_id'] ?? 0) : null,
                'material_id' => $itemKind === 'material' ? (int) ($orderItem['material_id'] ?? 0) : null,
                'ordered_qty' => $this->formatDecimal((float) ($orderItem['ordered_qty'] ?? 0)),
                'ready_qty' => $this->formatDecimal($readyQty),
                'delivery_qty' => $this->formatDecimal($qty),
                'remaining_qty' => $this->formatDecimal(max($remainingQty - $qty, 0)),
                'unit_cost' => $this->formatDecimal($unitCost),
                'line_total' => $this->formatDecimal(round($qty * $unitCost, 2)),
            ];
        }

        if ($deliveryItems === []) {
            $errors['deliveries'][] = 'Vui lòng nhập ít nhất một dòng giao hàng.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'header' => [
                'sales_order_id' => (int) ($order['id'] ?? 0),
                'code' => $code,
                'status' => 'draft',
                'delivery_date' => $deliveryDate,
                'shipping_cost' => $this->formatDecimal(max($shippingCost, 0)),
                'note' => $this->nullableString($data['note'] ?? null),
                'stock_transaction_id' => null,
                'created_by' => $this->actorId(),
                'confirmed_by' => null,
                'confirmed_at' => null,
            ],
            'items' => $deliveryItems,
        ];
    }

    private function validateDeliveryConfirmation(array $order, array $deliveryItems): array
    {
        $orderItems = [];
        foreach ($order['items'] as $item) {
            $orderItems[(int) ($item['id'] ?? 0)] = $item;
        }

        $componentIds = [];
        $materialIds = [];
        foreach ($deliveryItems as $item) {
            if ((int) ($item['component_id'] ?? 0) > 0) {
                $componentIds[] = (int) $item['component_id'];
            }
            if ((int) ($item['material_id'] ?? 0) > 0) {
                $materialIds[] = (int) $item['material_id'];
            }
        }

        $componentStockMap = $this->repository->componentStockMap($componentIds);
        $materialStockMap = $this->repository->materialStockMap($materialIds);
        $validated = [];

        foreach ($deliveryItems as $item) {
            $salesOrderItemId = (int) ($item['sales_order_item_id'] ?? 0);
            $orderItem = $orderItems[$salesOrderItemId] ?? null;
            if ($orderItem === null) {
                throw new HttpException('Phiếu giao có dòng không còn thuộc đơn bán.', 409);
            }

            $itemKind = (string) ($item['item_kind'] ?? '');
            $deliveryQty = round((float) ($item['delivery_qty'] ?? 0), 2);
            $remainingQty = round((float) ($orderItem['remaining_qty'] ?? 0), 2);

            $availableQty = $itemKind === 'component'
                ? (float) ($componentStockMap[(int) ($item['component_id'] ?? 0)] ?? 0)
                : (float) ($materialStockMap[(int) ($item['material_id'] ?? 0)] ?? 0);

            if ($deliveryQty <= 0) {
                throw new HttpException('Phiếu giao có dòng số lượng không hợp lệ.', 409);
            }
            if ($deliveryQty > $remainingQty) {
                throw new HttpException('Số lượng giao vượt quá số lượng còn lại của đơn bán.', 409);
            }
            if ($deliveryQty > $availableQty) {
                throw new HttpException('Không đủ tồn kho để xác nhận giao hàng.', 409);
            }

            $unitCost = (float) ($orderItem['unit_price'] ?? 0);
            $validated[] = [
                'item_kind' => $itemKind,
                'material_id' => $itemKind === 'material' ? (int) ($item['material_id'] ?? 0) : null,
                'component_id' => $itemKind === 'component' ? (int) ($item['component_id'] ?? 0) : null,
                'delivery_qty' => $this->formatDecimal($deliveryQty),
                'unit_cost' => $this->formatDecimal($unitCost),
                'line_total' => $this->formatDecimal(round($deliveryQty * $unitCost, 2)),
            ];
        }

        return $validated;
    }

    private function canMarkReadyToDeliver(array $items): bool
    {
        if ($items === []) {
            return false;
        }

        $hasDeliverableItem = false;
        foreach ($items as $item) {
            if (!$this->requiresDelivery($item)) {
                continue;
            }

            $hasDeliverableItem = true;
            $status = (string) ($item['fulfillment_status'] ?? 'pending');
            if (!in_array($status, ['ready', 'delivered', 'partially_delivered'], true)) {
                return false;
            }
        }

        return $hasDeliverableItem;
    }

    private function hasDeliverableReadyQty(array $items): bool
    {
        foreach ($items as $item) {
            if (!$this->requiresDelivery($item)) {
                continue;
            }

            if ((float) ($item['ready_qty'] ?? 0) > 0 && (float) ($item['remaining_qty'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    private function requiresDelivery(array $item): bool
    {
        return strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? '')) !== 'service';
    }

    private function serviceOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'assigned' => 'Đã giao việc',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'closed' => 'Đã đóng',
            'cancelled' => 'Đã hủy',
            default => 'Nháp',
        };
    }

    private function serviceOrderStatusBadge(string $status): string
    {
        return match ($status) {
            'assigned' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'closed' => 'dark',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    private function decorateDeliveries(array $deliveries): array
    {
        foreach ($deliveries as &$delivery) {
            $status = (string) ($delivery['status'] ?? 'draft');
            $delivery['status_label'] = match ($status) {
                'confirmed' => 'Đã xác nhận',
                'cancelled' => 'Đã hủy',
                default => 'Nháp',
            };
            $delivery['status_badge'] = match ($status) {
                'confirmed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary',
            };
            $delivery['created_by_name'] = trim((string) ($delivery['created_by_full_name'] ?? '')) ?: (string) ($delivery['created_by_username'] ?? '-');
            $delivery['confirmed_by_name'] = trim((string) ($delivery['confirmed_by_full_name'] ?? '')) ?: (string) ($delivery['confirmed_by_username'] ?? '-');
            $delivery['stock_transaction_url'] = !empty($delivery['stock_transaction_id'])
                ? app_url('/stocks/show?id=' . (int) $delivery['stock_transaction_id'])
                : null;
        }
        unset($delivery);

        return $deliveries;
    }

    private function decorateLogs(array $logs): array
    {
        foreach ($logs as &$log) {
            $log['actor_name'] = trim((string) ($log['full_name'] ?? '')) ?: (string) ($log['username'] ?? 'Hệ thống');
            $log['action_label'] = match ((string) ($log['action'] ?? '')) {
                'payment_created' => 'Tạo payment',
                'payment_confirmed' => 'Xác nhận payment',
                'mark_ready_to_deliver' => 'Đánh dấu sẵn sàng giao',
                'create_delivery' => 'Tạo phiếu giao',
                'confirm_delivery_partial' => 'Xác nhận giao một phần',
                'confirm_delivery_full' => 'Xác nhận giao đủ',
                'cancel_delivery' => 'Hủy phiếu giao',
                default => (string) ($log['action'] ?? ''),
            };
        }
        unset($log);

        return $logs;
    }

    private function writeOrderLog(int $orderId, string $action, ?string $oldStatus, ?string $newStatus, array $meta = []): void
    {
        $request = $_SERVER;
        $this->repository->createLog([
            'module' => 'sales_order',
            'entity_id' => $orderId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_fields_json' => !empty($meta['changed_fields']) ? json_encode($meta['changed_fields'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'remark' => $this->nullableString($meta['remark'] ?? null),
            'acted_by' => $this->actorId(),
            'acted_at' => $this->timestamp(),
            'ip_address' => $request['REMOTE_ADDR'] ?? null,
            'user_agent' => isset($request['HTTP_USER_AGENT']) ? substr((string) $request['HTTP_USER_AGENT'], 0, 255) : null,
        ]);
    }

    private function generateDeliveryCode(string $deliveryDate): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $deliveryDate) ?: new DateTimeImmutable();
        $prefix = 'DO' . $date->format('my');
        $latest = $this->repository->latestDeliveryCodeLike($prefix);
        $sequence = 1;

        if ($latest !== null && preg_match('/(\d+)$/', (string) ($latest['code'] ?? ''), $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function generateDeliveryIssueNo(): string
    {
        return 'ISS-' . date('ymdHis');
    }

    private function assertUniqueDeliveryCode(string $code): void
    {
        if ($this->repository->findDeliveryByCode($code) !== null) {
            throw new HttpException('Mã phiếu giao đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã phiếu giao đã tồn tại.'],
                ],
            ]);
        }
    }

    private function actorId(): ?int
    {
        $actorId = (int) ($_SESSION['user_id'] ?? 0);

        return $actorId > 0 ? $actorId : null;
    }

    private function timestamp(): string
    {
        return (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Order code already exists.', 422, [
                'errors' => [
                    'code' => ['Order code already exists.'],
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

    private function normalizeCodeDate(?string $orderDate): string
    {
        $orderDate = trim((string) ($orderDate ?? ''));
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $orderDate);
        if ($date === false || $date->format('Y-m-d') !== $orderDate) {
            return date('Y-m-d');
        }

        return $date->format('Y-m-d');
    }
}
