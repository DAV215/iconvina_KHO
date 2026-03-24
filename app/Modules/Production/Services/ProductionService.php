<?php

declare(strict_types=1);

namespace App\Modules\Production\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Bom\Repositories\BomRepository;
use App\Modules\Component\Repositories\ComponentRepository;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Production\Repositories\ProductionRepository;
use DateTimeImmutable;

final class ProductionService
{
    private const STATUSES = ['draft', 'released', 'in_progress', 'paused', 'completed', 'cancelled'];
    private const TASK_STATUSES = ['pending', 'assigned', 'in_progress', 'done', 'cancelled'];
    private const PRIORITY_LABELS = [
        1 => 'Thấp',
        2 => 'Bình thường',
        3 => 'Cao',
        4 => 'Khẩn',
    ];
    private const DEFAULT_TASKS = [
        ['name' => 'Chuẩn bị vật tư', 'weight_percent' => '15.00'],
        ['name' => 'Gia công', 'weight_percent' => '35.00'],
        ['name' => 'Lắp ráp', 'weight_percent' => '25.00'],
        ['name' => 'Kiểm tra', 'weight_percent' => '15.00'],
        ['name' => 'Hoàn tất', 'weight_percent' => '10.00'],
    ];
    private const STATUS_LABELS = [
        'draft' => 'Nháp',
        'released' => 'Đã phát hành',
        'in_progress' => 'Đang sản xuất',
        'paused' => 'Tạm dừng',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
    ];
    private const STATUS_BADGES = [
        'draft' => 'secondary',
        'released' => 'info',
        'in_progress' => 'warning',
        'paused' => 'secondary',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    private const MATERIAL_ISSUE_LABELS = [
        'no_bom' => 'Chưa có BOM active',
        'not_issued' => 'Chưa xuất vật tư',
        'partially_issued' => 'Đã xuất một phần',
        'fully_issued' => 'Đã xuất đủ',
    ];
    private const MATERIAL_ISSUE_BADGES = [
        'no_bom' => 'danger',
        'not_issued' => 'secondary',
        'partially_issued' => 'warning',
        'fully_issued' => 'success',
    ];

    public function __construct(
        private readonly ProductionRepository $repository,
        private readonly OrderRepository $orderRepository,
        private readonly BomRepository $bomRepository,
        private readonly ComponentRepository $componentRepository,
    ) {
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $filters['assigned_to'] = !empty($filters['mine']) ? $this->actorId() : 0;
        $list = $this->repository->search($filters, $page, $perPage);
        $list['items'] = array_map(fn (array $item): array => $this->decorateOrderRow($item), $list['items']);

        return $list;
    }

    public function find(int $id): array
    {
        $productionOrder = $this->repository->findById($id);
        if ($productionOrder === null) {
            throw new HttpException('Không tìm thấy lệnh sản xuất.', 404);
        }

        $productionOrder = $this->decorateOrderRow($productionOrder);
        $productionOrder['tasks'] = $this->decorateTasks($this->repository->findTasksByProductionOrderId($id));
        $productionOrder['stock_receipt'] = $this->repository->findStockReceiptByProductionOrderId($id);
        $productionOrder['stock_receipt_url'] = !empty($productionOrder['stock_receipt']['id'])
            ? app_url('/stocks/show?id=' . (int) $productionOrder['stock_receipt']['id'])
            : null;
        $productionOrder['sales_order_url'] = !empty($productionOrder['sales_order_id'])
            ? app_url('/orders/show?id=' . (int) $productionOrder['sales_order_id'])
            : null;

        $activeBom = $this->resolveActiveBom($productionOrder);
        $productionOrder['active_bom'] = $activeBom;
        $productionOrder['bom_url'] = !empty($activeBom['id'])
            ? app_url('/bom/show?id=' . (int) $activeBom['id'])
            : null;
        $productionOrder['bom_tree_url'] = !empty($activeBom['id'])
            ? app_url('/bom/tree?id=' . (int) $activeBom['id'])
            : null;

        $requirements = $this->buildMaterialRequirements($productionOrder, $activeBom);
        $productionOrder['material_requirements'] = $requirements['items'];
        $productionOrder['material_summary'] = $requirements['summary'];

        $productionOrder['issue_transactions'] = $this->decorateIssueTransactions(
            $this->repository->findIssueTransactionsByProductionOrderId($id)
        );
        $productionOrder['logs'] = $this->repository->logsByProductionOrderId($id);
        $productionOrder['available_actions'] = $this->availableActions($productionOrder);
        $productionOrder['next_step_message'] = $this->buildNextStepMessage($productionOrder);

        return $productionOrder;
    }

    public function createFromSalesOrderItem(int $salesOrderId, int $salesOrderItemId): int
    {
        $order = $this->orderRepository->findById($salesOrderId);
        if ($order === null) {
            throw new HttpException('Không tìm thấy đơn bán hàng.', 404);
        }

        $item = $this->orderRepository->findItemById($salesOrderId, $salesOrderItemId);
        if ($item === null) {
            throw new HttpException('Không tìm thấy dòng đơn bán hàng.', 404);
        }

        $componentId = (int) ($item['component_id'] ?? 0);
        if ($componentId <= 0) {
            throw new HttpException('Chỉ dòng bán thành phẩm mới tạo được lệnh sản xuất.', 409);
        }

        $bom = $this->bomRepository->findActiveByComponentId($componentId);
        if ($bom === null) {
            throw new HttpException('Dòng này chưa có BOM active, không thể tạo lệnh sản xuất.', 409);
        }

        $stockMap = $this->orderRepository->componentStockMap([$componentId]);
        $availableQty = (float) ($stockMap[$componentId] ?? 0);
        $requiredQty = (float) ($item['quantity'] ?? 0);
        $shortageQty = round(max($requiredQty - $availableQty, 0), 2);

        if ($shortageQty <= 0) {
            throw new HttpException('Tồn kho hiện đủ đáp ứng, không cần tạo lệnh sản xuất.', 409);
        }

        if ($this->repository->findActiveBySalesOrderItemId($salesOrderItemId) !== null) {
            throw new HttpException('Dòng này đã có lệnh sản xuất đang hoạt động.', 409);
        }

        $component = $this->componentRepository->findById($componentId);
        $priority = $this->normalizePriority($order['priority'] ?? 2);
        $timestamp = $this->timestamp();

        return $this->repository->transaction(function () use ($order, $item, $component, $bom, $shortageQty, $priority, $salesOrderItemId, $salesOrderId, $timestamp): int {
            $code = $this->generateUniqueCode();
            $productionOrderId = $this->repository->create([
                'code' => $code,
                'sales_order_id' => $salesOrderId,
                'sales_order_item_id' => $salesOrderItemId,
                'component_id' => (int) ($component['id'] ?? 0),
                'bom_id' => (int) ($bom['id'] ?? 0),
                'title' => 'SX cho ' . (string) ($order['code'] ?? 'SO') . ' - dòng ' . (int) ($item['line_no'] ?? 1),
                'status' => 'draft',
                'priority' => $priority,
                'planned_start_at' => $timestamp,
                'planned_end_at' => null,
                'actual_start_at' => null,
                'actual_end_at' => null,
                'planned_qty' => $this->formatDecimal($shortageQty),
                'stock_shortage_qty' => $this->formatDecimal($shortageQty),
                'completed_qty' => '0.00',
                'progress_percent' => '0.00',
                'note' => 'Tạo tự động từ đơn bán ' . (string) ($order['code'] ?? ''),
            ], $this->defaultTasks());

            $this->orderRepository->updateItemEngineering($salesOrderItemId, [
                'fulfillment_status' => 'waiting_production',
            ]);
            $this->orderRepository->updateStatus($salesOrderId, 'waiting_production');
            $this->writeLog($productionOrderId, 'create', null, 'draft', [
                'planned_qty' => $this->formatDecimal($shortageQty),
                'bom_id' => (int) ($bom['id'] ?? 0),
            ], 'Tạo lệnh sản xuất từ đơn bán hàng.');

            return $productionOrderId;
        });
    }

    public function release(int $id): void
    {
        $order = $this->find($id);
        if (($order['status'] ?? '') !== 'draft') {
            throw new HttpException('Chỉ lệnh sản xuất nháp mới được phát hành.', 409);
        }

        $this->assertBomReady($order);

        $this->repository->transaction(function () use ($id, $order): void {
            $this->repository->updateOrder($id, [
                'status' => 'released',
                'planned_start_at' => $order['planned_start_at'] ?? $this->timestamp(),
            ]);

            foreach ($order['tasks'] as $task) {
                $this->repository->updateTask((int) $task['id'], [
                    'status' => (int) ($task['assigned_to'] ?? 0) > 0 ? 'assigned' : 'pending',
                ]);
            }

            $this->writeLog($id, 'release', 'draft', 'released', [], 'Phát hành lệnh sản xuất.');
            $this->refreshOrderFulfillment((int) ($order['sales_order_id'] ?? 0));
        });
    }

    public function issueMaterials(int $id, array $data): array
    {
        $order = $this->find($id);
        if (in_array((string) ($order['status'] ?? ''), ['completed', 'cancelled'], true)) {
            throw new HttpException('Không thể xuất vật tư cho lệnh sản xuất đã hoàn tất hoặc đã hủy.', 409);
        }

        $this->assertBomReady($order);
        if (($order['status'] ?? '') === 'draft') {
            throw new HttpException('Cần phát hành lệnh sản xuất trước khi xuất vật tư.', 409);
        }

        $payload = $this->normalizeIssuePayload($order, $data);
        $issueStatus = $payload['summary_after']['issue_status'];

        $this->repository->transaction(function () use ($id, $order, $payload, $issueStatus): void {
            $transactionId = $this->repository->createStockIssueInTransaction($payload['header'], $payload['items']);
            $this->writeLog($id, 'issue_materials', (string) ($order['status'] ?? 'released'), (string) ($order['status'] ?? 'released'), [
                'stock_transaction_id' => $transactionId,
                'issue_status' => $issueStatus,
                'items' => $payload['log_items'],
            ], $issueStatus === 'fully_issued' ? 'Xuất đủ vật tư cho lệnh sản xuất.' : 'Xuất vật tư cho lệnh sản xuất.');

            if ($issueStatus === 'fully_issued') {
                $this->writeLog($id, 'issue_full', (string) ($order['status'] ?? 'released'), (string) ($order['status'] ?? 'released'), [], 'Đã xuất đủ vật tư theo BOM.');
            } else {
                $this->writeLog($id, 'issue_partial', (string) ($order['status'] ?? 'released'), (string) ($order['status'] ?? 'released'), [], 'Đã xuất một phần vật tư theo BOM.');
            }
        });

        return [
            'message' => $issueStatus === 'fully_issued'
                ? 'Đã tạo phiếu xuất vật tư và xuất đủ theo BOM.'
                : 'Đã tạo phiếu xuất vật tư sản xuất.',
        ];
    }

    public function start(int $id): void
    {
        $order = $this->find($id);
        $status = (string) ($order['status'] ?? 'draft');
        if (!in_array($status, ['released', 'paused'], true)) {
            throw new HttpException('Chỉ lệnh đã phát hành mới được bắt đầu sản xuất.', 409);
        }

        $this->assertBomReady($order);
        if (($order['material_summary']['issue_status'] ?? 'not_issued') === 'not_issued') {
            throw new HttpException('Cần xuất vật tư trước khi bắt đầu sản xuất.', 409);
        }

        $this->repository->transaction(function () use ($id, $order, $status): void {
            $this->repository->updateOrder($id, [
                'status' => 'in_progress',
                'actual_start_at' => $order['actual_start_at'] ?? $this->timestamp(),
            ]);
            $this->writeLog($id, 'start', $status, 'in_progress', [], 'Bắt đầu sản xuất.');
            $this->refreshOrderFulfillment((int) ($order['sales_order_id'] ?? 0));
        });
    }

    public function assignTask(int $productionOrderId, int $taskId, array $data): void
    {
        $order = $this->find($productionOrderId);
        $task = $this->repository->findTaskById($taskId);
        if ($task === null || (int) ($task['production_order_id'] ?? 0) !== $productionOrderId) {
            throw new HttpException('Không tìm thấy công việc sản xuất.', 404);
        }

        $assignedTo = (int) ($data['assigned_to'] ?? 0);
        if ($assignedTo <= 0) {
            throw new HttpException('Vui lòng chọn người phụ trách.', 422, ['errors' => ['assigned_to' => ['Vui lòng chọn người phụ trách.']]]);
        }

        $plannedStart = $this->normalizeDateTime($data['planned_start_at'] ?? null);
        $plannedEnd = $this->normalizeDateTime($data['planned_end_at'] ?? null);

        $this->repository->updateTask($taskId, [
            'assigned_to' => $assignedTo,
            'status' => ($order['status'] ?? '') === 'draft' ? 'pending' : 'assigned',
            'planned_start_at' => $plannedStart,
            'planned_end_at' => $plannedEnd,
            'note' => $this->nullableString($data['note'] ?? $task['note'] ?? null),
        ]);
    }

    public function updateTask(int $productionOrderId, int $taskId, array $data): void
    {
        $productionOrder = $this->find($productionOrderId);
        $task = $this->repository->findTaskById($taskId);
        if ($task === null || (int) ($task['production_order_id'] ?? 0) !== $productionOrderId) {
            throw new HttpException('Không tìm thấy công việc sản xuất.', 404);
        }

        $actorId = $this->actorId();
        $isManager = has_permission('production.assign') || has_permission('production.complete');
        if (!$isManager && (int) ($task['assigned_to'] ?? 0) !== $actorId) {
            throw new HttpException('Bạn không được cập nhật công việc này.', 403);
        }

        $status = strtolower(trim((string) ($data['status'] ?? $task['status'] ?? 'pending')));
        if (!in_array($status, self::TASK_STATUSES, true)) {
            throw new HttpException('Trạng thái công việc không hợp lệ.', 422);
        }

        $this->assertTaskCanProceed($productionOrder, (string) ($task['name'] ?? ''), $status);

        $progress = round((float) ($data['progress_percent'] ?? $task['progress_percent'] ?? 0), 2);
        $progress = max(0, min(100, $progress));
        $update = [
            'status' => $status,
            'progress_percent' => $status === 'done' ? '100.00' : $this->formatDecimal($progress),
            'note' => $this->nullableString($data['note'] ?? $task['note'] ?? null),
        ];

        if (in_array($status, ['in_progress', 'done'], true) && empty($task['actual_start_at'])) {
            $update['actual_start_at'] = $this->timestamp();
        }
        if (in_array($status, ['done', 'cancelled'], true)) {
            $update['actual_end_at'] = $this->timestamp();
        }

        $this->repository->transaction(function () use ($productionOrderId, $taskId, $update): void {
            $this->repository->updateTask($taskId, $update);
            $this->syncProgress($productionOrderId);
        });
    }

    public function complete(int $id, array $data = []): void
    {
        $productionOrder = $this->find($id);
        if (($productionOrder['status'] ?? '') === 'completed') {
            return;
        }

        $this->assertBomReady($productionOrder);
        if (($productionOrder['material_summary']['issue_status'] ?? 'not_issued') !== 'fully_issued') {
            throw new HttpException('Chỉ được hoàn tất khi vật tư đã được xuất đủ theo BOM.', 409);
        }

        $plannedQty = (float) ($productionOrder['planned_qty'] ?? 0);
        $completedQty = round((float) ($data['completed_qty'] ?? $plannedQty), 2);
        if ($completedQty <= 0 || $completedQty < $plannedQty) {
            throw new HttpException('Số lượng hoàn thành phải lớn hơn hoặc bằng số lượng kế hoạch.', 422);
        }

        if ($this->repository->findStockReceiptByProductionOrderId($id) !== null) {
            throw new HttpException('Lệnh sản xuất này đã nhập kho thành phẩm.', 409);
        }

        $componentCost = (float) ($productionOrder['component_standard_cost'] ?? 0);
        $salesOrderId = (int) ($productionOrder['sales_order_id'] ?? 0);
        $salesOrderItemId = (int) ($productionOrder['sales_order_item_id'] ?? 0);
        $fromStatus = (string) ($productionOrder['status'] ?? 'in_progress');

        $this->repository->transaction(function () use ($id, $productionOrder, $completedQty, $componentCost, $salesOrderId, $salesOrderItemId, $fromStatus): void {
            $this->repository->updateOrder($id, [
                'status' => 'completed',
                'completed_qty' => $this->formatDecimal($completedQty),
                'progress_percent' => '100.00',
                'actual_start_at' => $productionOrder['actual_start_at'] ?? $this->timestamp(),
                'actual_end_at' => $this->timestamp(),
            ]);

            foreach ($productionOrder['tasks'] as $task) {
                if (($task['status'] ?? '') !== 'done') {
                    $this->repository->updateTask((int) $task['id'], [
                        'status' => 'done',
                        'progress_percent' => '100.00',
                        'actual_start_at' => $task['actual_start_at'] ?? $this->timestamp(),
                        'actual_end_at' => $this->timestamp(),
                    ]);
                }
            }

            $receiptId = $this->repository->createStockReceipt([
                'txn_no' => $this->generateReceiptNo($id),
                'txn_type' => 'receipt',
                'ref_type' => 'production_order',
                'ref_id' => $id,
                'txn_date' => date('Y-m-d'),
                'note' => 'Nhập kho từ lệnh sản xuất ' . (string) ($productionOrder['code'] ?? ''),
            ], [[
                'item_kind' => 'component',
                'material_id' => null,
                'component_id' => (int) ($productionOrder['component_id'] ?? 0),
                'quantity' => $this->formatDecimal($completedQty),
                'unit_cost' => $this->formatDecimal($componentCost),
                'line_total' => $this->formatDecimal(round($completedQty * $componentCost, 2)),
            ]]);

            $this->writeLog($id, 'complete', $fromStatus, 'completed', [
                'completed_qty' => $this->formatDecimal($completedQty),
                'stock_receipt_id' => $receiptId,
            ], 'Hoàn tất lệnh sản xuất và nhập kho thành phẩm.');

            if ($salesOrderItemId > 0) {
                $this->orderRepository->updateItemEngineering($salesOrderItemId, [
                    'fulfillment_status' => 'ready',
                ]);
            }

            if ($salesOrderId > 0) {
                $this->refreshOrderFulfillment($salesOrderId);
            }
        });
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function taskStatuses(): array
    {
        return self::TASK_STATUSES;
    }

    public function userOptions(): array
    {
        return $this->repository->userOptions();
    }

    private function decorateOrderRow(array $order): array
    {
        $status = (string) ($order['status'] ?? 'draft');
        $order['priority_label'] = self::PRIORITY_LABELS[$this->normalizePriority($order['priority'] ?? 2)] ?? 'Bình thường';
        $order['progress_percent'] = number_format((float) ($order['progress_percent'] ?? 0), 2, '.', '');
        $order['status_label'] = self::STATUS_LABELS[$status] ?? 'Nháp';
        $order['status_badge'] = self::STATUS_BADGES[$status] ?? 'secondary';
        $order['bom_status_label'] = !empty($order['bom_id']) ? 'Đã có BOM' : 'Chưa có BOM';

        return $order;
    }

    private function decorateTasks(array $tasks): array
    {
        foreach ($tasks as &$task) {
            $task['assigned_name'] = trim((string) ($task['assigned_full_name'] ?? '')) ?: ((string) ($task['assigned_username'] ?? 'Chưa phân công'));
            $task['status_label'] = match ((string) ($task['status'] ?? 'pending')) {
                'assigned' => 'Đã phân công',
                'in_progress' => 'Đang làm',
                'done' => 'Hoàn tất',
                'cancelled' => 'Đã hủy',
                default => 'Chờ xử lý',
            };
            $task['status_badge'] = match ((string) ($task['status'] ?? 'pending')) {
                'assigned' => 'info',
                'in_progress' => 'warning',
                'done' => 'success',
                'cancelled' => 'danger',
                default => 'secondary',
            };
        }
        unset($task);

        return $tasks;
    }

    private function decorateIssueTransactions(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['detail_url'] = app_url('/stocks/show?id=' . (int) ($row['id'] ?? 0));
        }
        unset($row);

        return $rows;
    }

    private function resolveActiveBom(array $productionOrder): ?array
    {
        $componentId = (int) ($productionOrder['component_id'] ?? 0);
        if ($componentId <= 0) {
            return null;
        }

        $bom = $this->bomRepository->findActiveByComponentId($componentId);
        if ($bom !== null) {
            return $bom;
        }

        $fallbackBomId = (int) ($productionOrder['bom_id'] ?? 0);

        return $fallbackBomId > 0 ? $this->bomRepository->findById($fallbackBomId) : null;
    }

    private function buildMaterialRequirements(array $productionOrder, ?array $activeBom): array
    {
        if ($activeBom === null || (int) ($activeBom['id'] ?? 0) <= 0 || (int) ($activeBom['is_active'] ?? 0) !== 1) {
            return [
                'items' => [],
                'summary' => $this->materialSummaryTemplate('no_bom'),
            ];
        }

        $bomItems = $this->repository->findBomItemsByBomId((int) $activeBom['id']);
        if ($bomItems === []) {
            return [
                'items' => [],
                'summary' => $this->materialSummaryTemplate('no_bom'),
            ];
        }

        $materialIds = [];
        $componentIds = [];
        foreach ($bomItems as $bomItem) {
            if (($bomItem['item_kind'] ?? '') === 'material' && (int) ($bomItem['material_id'] ?? 0) > 0) {
                $materialIds[] = (int) $bomItem['material_id'];
            }
            if (($bomItem['item_kind'] ?? '') === 'component' && (int) ($bomItem['component_id'] ?? 0) > 0) {
                $componentIds[] = (int) $bomItem['component_id'];
            }
        }

        $materialStockMap = $this->repository->materialStockMap($materialIds);
        $componentStockMap = $this->repository->componentStockMap($componentIds);
        $issuedMap = $this->issueMapByItem($this->repository->findIssueTransactionItemsByProductionOrderId((int) $productionOrder['id']));
        $plannedQty = round((float) ($productionOrder['planned_qty'] ?? 0), 2);

        $items = [];
        $summary = $this->materialSummaryTemplate('not_issued');
        foreach ($bomItems as $bomItem) {
            $itemKind = (string) ($bomItem['item_kind'] ?? 'material');
            $itemId = $itemKind === 'material'
                ? (int) ($bomItem['material_id'] ?? 0)
                : (int) ($bomItem['component_id'] ?? 0);
            if ($itemId <= 0) {
                continue;
            }

            $rowKey = $itemKind . ':' . $itemId;
            $bomQty = round((float) ($bomItem['quantity'] ?? 0), 2);
            $requiredQty = round($plannedQty * $bomQty, 2);
            $currentStock = $itemKind === 'material'
                ? round((float) ($materialStockMap[$itemId] ?? 0), 2)
                : round((float) ($componentStockMap[$itemId] ?? 0), 2);
            $issuedQty = round((float) ($issuedMap[$rowKey] ?? 0), 2);
            $remainingIssueQty = round(max($requiredQty - $issuedQty, 0), 2);
            $availableForOrderQty = round($currentStock + $issuedQty, 2);
            $shortageQty = round(max($requiredQty - $availableForOrderQty, 0), 2);

            $items[] = [
                'row_key' => $rowKey,
                'item_kind' => $itemKind,
                'item_kind_label' => $itemKind === 'material' ? 'Vật tư' : 'Bán thành phẩm',
                'item_id' => $itemId,
                'code' => (string) ($itemKind === 'material' ? ($bomItem['material_code'] ?? '') : ($bomItem['component_code'] ?? '')),
                'name' => (string) ($itemKind === 'material' ? ($bomItem['material_name'] ?? '') : ($bomItem['component_name'] ?? '')),
                'unit' => (string) ($itemKind === 'material' ? ($bomItem['material_unit'] ?? '') : ($bomItem['component_unit'] ?? '')),
                'bom_qty' => $this->formatDecimal($bomQty),
                'required_qty' => $this->formatDecimal($requiredQty),
                'issued_qty' => $this->formatDecimal($issuedQty),
                'remaining_issue_qty' => $this->formatDecimal($remainingIssueQty),
                'current_stock' => $this->formatDecimal($currentStock),
                'shortage_qty' => $this->formatDecimal($shortageQty),
                'standard_cost' => $this->formatDecimal((float) ($itemKind === 'material' ? ($bomItem['material_standard_cost'] ?? 0) : ($bomItem['component_standard_cost'] ?? 0))),
                'is_enough' => $shortageQty <= 0,
                'status_label' => $shortageQty <= 0 ? 'Đủ' : 'Thiếu',
                'status_badge' => $shortageQty <= 0 ? 'success' : 'danger',
            ];

            $summary['line_count']++;
            $summary['required_total'] += $requiredQty;
            $summary['issued_total'] += $issuedQty;
            $summary['remaining_total'] += $remainingIssueQty;
            if ($shortageQty > 0) {
                $summary['shortage_line_count']++;
            }
        }

        $summary['issue_status'] = $this->resolveIssueStatus($summary['required_total'], $summary['issued_total']);
        $summary['issue_status_label'] = self::MATERIAL_ISSUE_LABELS[$summary['issue_status']] ?? $summary['issue_status'];
        $summary['issue_status_badge'] = self::MATERIAL_ISSUE_BADGES[$summary['issue_status']] ?? 'secondary';
        $summary['required_total'] = $this->formatDecimal($summary['required_total']);
        $summary['issued_total'] = $this->formatDecimal($summary['issued_total']);
        $summary['remaining_total'] = $this->formatDecimal($summary['remaining_total']);

        return [
            'items' => $items,
            'summary' => $summary,
        ];
    }

    private function materialSummaryTemplate(string $issueStatus): array
    {
        return [
            'line_count' => 0,
            'shortage_line_count' => 0,
            'required_total' => '0.00',
            'issued_total' => '0.00',
            'remaining_total' => '0.00',
            'issue_status' => $issueStatus,
            'issue_status_label' => self::MATERIAL_ISSUE_LABELS[$issueStatus] ?? $issueStatus,
            'issue_status_badge' => self::MATERIAL_ISSUE_BADGES[$issueStatus] ?? 'secondary',
        ];
    }

    private function resolveIssueStatus(float $requiredTotal, float $issuedTotal): string
    {
        if ($requiredTotal <= 0) {
            return 'not_issued';
        }
        if ($issuedTotal <= 0) {
            return 'not_issued';
        }
        if (round($issuedTotal, 2) >= round($requiredTotal, 2)) {
            return 'fully_issued';
        }

        return 'partially_issued';
    }

    private function issueMapByItem(array $issueItems): array
    {
        $map = [];
        foreach ($issueItems as $issueItem) {
            $itemKind = (string) ($issueItem['item_kind'] ?? 'material');
            $itemId = $itemKind === 'material'
                ? (int) ($issueItem['material_id'] ?? 0)
                : (int) ($issueItem['component_id'] ?? 0);
            if ($itemId <= 0) {
                continue;
            }

            $key = $itemKind . ':' . $itemId;
            $map[$key] = round(($map[$key] ?? 0) + (float) ($issueItem['quantity'] ?? 0), 2);
        }

        return $map;
    }

    private function normalizeIssuePayload(array $order, array $data): array
    {
        $requirements = $order['material_requirements'] ?? [];
        $requirementMap = [];
        foreach ($requirements as $requirement) {
            $requirementMap[(string) $requirement['row_key']] = $requirement;
        }

        $rawItems = $data['items'] ?? [];
        if (!is_array($rawItems)) {
            throw new HttpException('Danh sách xuất vật tư không hợp lệ.', 422);
        }

        $items = [];
        $logItems = [];
        $issuedTotal = 0.0;

        foreach ($rawItems as $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $rowKey = trim((string) ($rawItem['row_key'] ?? ''));
            $quantityValue = trim((string) ($rawItem['issue_qty'] ?? ''));
            if ($rowKey === '' || $quantityValue === '') {
                continue;
            }

            if (!isset($requirementMap[$rowKey])) {
                throw new HttpException('Chỉ được xuất các vật tư thuộc BOM của lệnh sản xuất.', 422);
            }

            if (!is_numeric($quantityValue)) {
                throw new HttpException('Số lượng xuất phải là số.', 422);
            }

            $issueQty = round((float) $quantityValue, 2);
            if ($issueQty <= 0) {
                continue;
            }

            $requirement = $requirementMap[$rowKey];
            $remainingIssueQty = (float) ($requirement['remaining_issue_qty'] ?? 0);
            $currentStock = (float) ($requirement['current_stock'] ?? 0);
            if ($issueQty > $remainingIssueQty) {
                throw new HttpException('Số lượng xuất vượt nhu cầu còn lại của BOM.', 422);
            }
            if ($issueQty > $currentStock) {
                throw new HttpException('Số lượng xuất vượt tồn kho khả dụng.', 422);
            }

            $itemKind = (string) ($requirement['item_kind'] ?? 'material');
            $itemId = (int) ($requirement['item_id'] ?? 0);
            $unitCost = round((float) ($requirement['standard_cost'] ?? 0), 2);

            $items[] = [
                'item_kind' => $itemKind,
                'material_id' => $itemKind === 'material' ? $itemId : null,
                'component_id' => $itemKind === 'component' ? $itemId : null,
                'quantity' => $this->formatDecimal($issueQty),
                'unit_cost' => $this->formatDecimal($unitCost),
                'line_total' => $this->formatDecimal($issueQty * $unitCost),
            ];
            $logItems[] = [
                'row_key' => $rowKey,
                'code' => (string) ($requirement['code'] ?? ''),
                'quantity' => $this->formatDecimal($issueQty),
            ];
            $issuedTotal += $issueQty;
        }

        if ($items === []) {
            throw new HttpException('Vui lòng nhập ít nhất một dòng xuất vật tư hợp lệ.', 422);
        }

        $txnDate = trim((string) ($data['txn_date'] ?? date('Y-m-d')));
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $txnDate);
        if ($date === false || $date->format('Y-m-d') !== $txnDate) {
            throw new HttpException('Ngày xuất kho không hợp lệ.', 422);
        }

        $summaryAfter = $order['material_summary'] ?? $this->materialSummaryTemplate('not_issued');
        $requiredTotal = (float) ($summaryAfter['required_total'] ?? 0);
        $alreadyIssued = (float) ($summaryAfter['issued_total'] ?? 0);
        $afterIssued = round($alreadyIssued + $issuedTotal, 2);
        $summaryAfter['issue_status'] = $this->resolveIssueStatus($requiredTotal, $afterIssued);

        return [
            'header' => [
                'txn_no' => $this->generateIssueNo((int) ($order['id'] ?? 0)),
                'txn_type' => 'issue',
                'ref_type' => 'production_order_issue',
                'ref_id' => (int) ($order['id'] ?? 0),
                'txn_date' => $txnDate,
                'note' => $this->nullableString($data['note'] ?? ('Xuất vật tư cho lệnh sản xuất ' . (string) ($order['code'] ?? ''))),
            ],
            'items' => $items,
            'log_items' => $logItems,
            'summary_after' => $summaryAfter,
        ];
    }

    private function availableActions(array $productionOrder): array
    {
        $status = (string) ($productionOrder['status'] ?? 'draft');
        $issueStatus = (string) ($productionOrder['material_summary']['issue_status'] ?? 'not_issued');
        $hasBom = $issueStatus !== 'no_bom';

        return [
            'can_release' => production_permission('update') && $status === 'draft' && $hasBom,
            'can_issue' => production_permission('issue') && in_array($status, ['released', 'in_progress', 'paused'], true) && $hasBom,
            'can_start' => production_permission('start') && in_array($status, ['released', 'paused'], true) && in_array($issueStatus, ['partially_issued', 'fully_issued'], true),
            'can_complete' => production_permission('complete') && in_array($status, ['released', 'in_progress', 'paused'], true) && $issueStatus === 'fully_issued',
            'can_view_log' => production_permission('view_log'),
        ];
    }

    private function buildNextStepMessage(array $productionOrder): ?string
    {
        $materialStatus = (string) ($productionOrder['material_summary']['issue_status'] ?? 'not_issued');
        if ($materialStatus === 'no_bom') {
            return 'Lệnh sản xuất chưa có BOM active. Cần tạo hoặc kích hoạt BOM trước khi phát hành và xuất vật tư.';
        }
        if (($productionOrder['status'] ?? '') === 'draft') {
            return 'Kiểm tra BOM và phát hành lệnh sản xuất trước khi cấp vật tư cho xưởng.';
        }
        if ($materialStatus === 'not_issued') {
            return 'Chưa xuất vật tư cho lệnh này. Kho cần tạo phiếu xuất vật tư trước khi bắt đầu gia công hoặc lắp ráp.';
        }
        if ($materialStatus === 'partially_issued' && ($productionOrder['status'] ?? '') !== 'completed') {
            return 'Lệnh đã xuất vật tư một phần. Tiếp tục xuất các dòng còn thiếu để hoàn tất theo BOM.';
        }
        if ((string) ($productionOrder['status'] ?? '') !== 'completed') {
            return 'Vật tư đã sẵn sàng. Có thể bắt đầu hoặc tiếp tục sản xuất.';
        }

        $hasReceipt = !empty($productionOrder['stock_receipt']['id']);
        if (!$hasReceipt) {
            return 'Đã hoàn tất sản xuất nhưng chưa thấy phiếu nhập kho thành phẩm. Cần kiểm tra lại giao dịch kho.';
        }

        return match ((string) ($productionOrder['sales_order_status'] ?? '')) {
            'ready_to_deliver' => 'Đã nhập kho thành phẩm. Bước tiếp theo là giao hàng trên đơn bán.',
            'waiting_production' => 'Đã nhập kho cho dòng này. Đơn bán vẫn còn dòng khác chờ sản xuất.',
            'delivered', 'closed' => 'Đã nhập kho thành phẩm và đơn bán đã hoàn tất.',
            default => 'Đã nhập kho thành phẩm. Kiểm tra đơn bán để xử lý bước tiếp theo.',
        };
    }

    private function syncProgress(int $productionOrderId): void
    {
        $productionOrder = $this->find($productionOrderId);
        $tasks = $productionOrder['tasks'];
        $weightedProgress = 0.0;
        $hasStarted = false;

        foreach ($tasks as $task) {
            $weight = (float) ($task['weight_percent'] ?? 0);
            $progress = (float) ($task['progress_percent'] ?? 0);
            $weightedProgress += ($weight * $progress) / 100;
            if (in_array((string) ($task['status'] ?? ''), ['in_progress', 'done'], true)) {
                $hasStarted = true;
            }
        }

        $status = (string) ($productionOrder['status'] ?? 'draft');
        if (!in_array($status, ['completed', 'cancelled'], true)) {
            if ($hasStarted && $status !== 'draft') {
                $status = 'in_progress';
            } elseif ($status !== 'draft') {
                $status = 'released';
            }
        }

        $this->repository->updateOrder($productionOrderId, [
            'status' => $status,
            'progress_percent' => $this->formatDecimal(min($weightedProgress, 100)),
            'actual_start_at' => $hasStarted ? ($productionOrder['actual_start_at'] ?? $this->timestamp()) : $productionOrder['actual_start_at'],
        ]);

        $salesOrderId = (int) ($productionOrder['sales_order_id'] ?? 0);
        if ($salesOrderId > 0) {
            $this->refreshOrderFulfillment($salesOrderId);
        }
    }

    private function refreshOrderFulfillment(int $salesOrderId): void
    {
        if ($salesOrderId <= 0) {
            return;
        }

        $order = $this->orderRepository->findById($salesOrderId);
        if ($order === null) {
            return;
        }

        $items = $this->orderRepository->findItemsByOrderId($salesOrderId);
        if ($items === []) {
            return;
        }

        $componentIds = [];
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = (int) $item['id'];
            $componentId = (int) ($item['component_id'] ?? 0);
            if ($componentId > 0) {
                $componentIds[] = $componentId;
            }
        }

        $stockMap = $this->orderRepository->componentStockMap(array_values(array_unique($componentIds)));
        $productionMap = [];
        foreach ($this->repository->findLatestBySalesOrderItemIds($itemIds) as $productionOrder) {
            $productionMap[(int) $productionOrder['sales_order_item_id']] = $productionOrder;
        }

        $allReady = true;
        $allDelivered = true;
        $hasProduction = false;

        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $componentId = (int) ($item['component_id'] ?? 0);
            $status = (string) ($item['fulfillment_status'] ?? 'pending');

            if ($componentId > 0) {
                $available = (float) ($stockMap[$componentId] ?? 0);
                $required = (float) ($item['quantity'] ?? 0);
                $shortage = round(max($required - $available, 0), 2);
                $productionOrder = $productionMap[$itemId] ?? null;

                if ($shortage <= 0) {
                    $status = 'ready_from_stock';
                } elseif ($productionOrder !== null) {
                    $hasProduction = true;
                    $status = match ((string) ($productionOrder['status'] ?? 'draft')) {
                        'completed' => 'ready',
                        'released', 'in_progress', 'paused' => 'in_production',
                        default => 'waiting_production',
                    };
                } else {
                    $status = 'waiting_production';
                }
            } elseif (($item['item_mode'] ?? '') === 'service') {
                $status = 'ready';
            }

            $this->orderRepository->updateItemEngineering($itemId, [
                'fulfillment_status' => $status,
            ]);

            if (!in_array($status, ['ready_from_stock', 'ready', 'delivered'], true)) {
                $allReady = false;
            }
            if ($status !== 'delivered') {
                $allDelivered = false;
            }
            if (in_array($status, ['waiting_production', 'in_production'], true)) {
                $hasProduction = true;
            }
        }

        $orderStatus = (string) ($order['status'] ?? 'draft');
        if (!in_array($orderStatus, ['draft', 'cancelled', 'closed'], true)) {
            if ($allDelivered) {
                $orderStatus = 'delivered';
            } elseif ($allReady) {
                $orderStatus = 'ready_to_deliver';
            } elseif ($hasProduction) {
                $orderStatus = 'waiting_production';
            } else {
                $orderStatus = 'waiting_stock';
            }
            $this->orderRepository->updateStatus($salesOrderId, $orderStatus);
        }
    }

    private function defaultTasks(): array
    {
        return array_map(
            static fn (array $task): array => [
                'name' => $task['name'],
                'assigned_to' => null,
                'status' => 'pending',
                'planned_start_at' => null,
                'planned_end_at' => null,
                'actual_start_at' => null,
                'actual_end_at' => null,
                'weight_percent' => $task['weight_percent'],
                'progress_percent' => '0.00',
                'note' => null,
            ],
            self::DEFAULT_TASKS
        );
    }

    private function assertBomReady(array $productionOrder): void
    {
        $summary = $productionOrder['material_summary'] ?? $this->materialSummaryTemplate('no_bom');
        if (($summary['issue_status'] ?? 'no_bom') === 'no_bom' || (int) ($summary['line_count'] ?? 0) <= 0) {
            throw new HttpException('Lệnh sản xuất chưa có BOM active hợp lệ, không thể tiếp tục.', 409);
        }
    }

    private function assertTaskCanProceed(array $productionOrder, string $taskName, string $status): void
    {
        if (!in_array($status, ['in_progress', 'done'], true)) {
            return;
        }

        $normalizedName = mb_strtolower(trim($taskName));
        if (!in_array($normalizedName, ['gia công', 'lắp ráp', 'kiem tra', 'kiểm tra', 'hoàn tất', 'hoan tat'], true)) {
            return;
        }

        $issueStatus = (string) ($productionOrder['material_summary']['issue_status'] ?? 'not_issued');
        if ($issueStatus === 'not_issued') {
            throw new HttpException('Cần xuất vật tư trước khi thực hiện công đoạn sản xuất này.', 409);
        }
    }

    private function writeLog(int $productionOrderId, string $action, ?string $oldStatus, ?string $newStatus, array $changedFields = [], ?string $remark = null): void
    {
        $this->repository->createLog([
            'production_order_id' => $productionOrderId,
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

    private function generateUniqueCode(): string
    {
        $baseCode = 'PO-' . date('ymdHis');
        $code = $baseCode;
        $suffix = 1;
        while ($this->repository->findByCode($code) !== null) {
            $tail = '-' . $suffix;
            $code = substr($baseCode, 0, max(1, 30 - strlen($tail))) . $tail;
            $suffix++;
        }

        return $code;
    }

    private function generateReceiptNo(int $productionOrderId): string
    {
        return 'REC-PO-' . str_pad((string) $productionOrderId, 6, '0', STR_PAD_LEFT);
    }

    private function generateIssueNo(int $productionOrderId): string
    {
        return 'ISS-PO-' . str_pad((string) $productionOrderId, 6, '0', STR_PAD_LEFT) . '-' . date('His');
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        throw new HttpException('Định dạng ngày giờ không hợp lệ.', 422);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function normalizePriority(mixed $value): int
    {
        if (is_numeric($value)) {
            $priority = (int) $value;

            return min(max($priority, 1), 4);
        }

        return match (strtolower(trim((string) $value))) {
            'low' => 1,
            'high' => 3,
            'urgent' => 4,
            default => 2,
        };
    }

    private function actorId(): int
    {
        return (int) (auth_user()['id'] ?? auth_user()['user_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}
