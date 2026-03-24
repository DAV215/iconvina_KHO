<?php

declare(strict_types=1);

namespace App\Modules\ServiceOrder\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Order\Services\OrderService;
use App\Modules\ServiceOrder\Repositories\ServiceOrderRepository;
use App\Modules\User\Repositories\UserRepository;

final class ServiceOrderService
{
    private const STATUSES = ['draft', 'assigned', 'in_progress', 'completed', 'closed', 'cancelled'];
    private const STATUS_LABELS = [
        'draft' => 'Nháp',
        'assigned' => 'Đã giao việc',
        'in_progress' => 'Đang thực hiện',
        'completed' => 'Hoàn thành',
        'closed' => 'Đã đóng',
        'cancelled' => 'Đã hủy',
    ];
    private const STATUS_BADGES = [
        'draft' => 'secondary',
        'assigned' => 'info',
        'in_progress' => 'warning',
        'completed' => 'success',
        'closed' => 'dark',
        'cancelled' => 'danger',
    ];
    private const PRIORITY_LABELS = [
        1 => 'Thấp',
        2 => 'Bình thường',
        3 => 'Cao',
        4 => 'Khẩn',
    ];

    public function __construct(
        private readonly ServiceOrderRepository $repository,
        private readonly OrderRepository $orderRepository,
        private readonly UserRepository $userRepository,
        private readonly OrderService $orderService,
    ) {
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $filters['assigned_to'] = !empty($filters['mine']) ? $this->actorId() : 0;
        $list = $this->repository->search($filters, $page, $perPage);
        $list['items'] = array_map(fn (array $item): array => $this->decorateRow($item), $list['items']);

        return $list;
    }

    public function find(int $id): array
    {
        $serviceOrder = $this->repository->findById($id);
        if ($serviceOrder === null) {
            throw new HttpException('Không tìm thấy lệnh dịch vụ.', 404);
        }

        $serviceOrder = $this->decorateRow($serviceOrder);
        if (!$this->canView($serviceOrder)) {
            throw new HttpException('Bạn không được xem lệnh dịch vụ này.', 403);
        }
        $serviceOrder['logs'] = $this->repository->logsByServiceOrderId($id);
        $serviceOrder['available_actions'] = $this->availableActions($serviceOrder);

        return $serviceOrder;
    }

    public function ensureForSalesOrder(int $salesOrderId): array
    {
        $order = $this->orderRepository->findById($salesOrderId);
        if ($order === null) {
            throw new HttpException('Không tìm thấy đơn bán hàng.', 404);
        }

        $createdIds = [];
        foreach ($this->orderRepository->findItemsByOrderId($salesOrderId) as $item) {
            if (strtolower((string) ($item['item_mode'] ?? $item['item_type'] ?? '')) !== 'service') {
                continue;
            }

            if ($this->repository->findActiveBySalesOrderItemId((int) ($item['id'] ?? 0)) !== null) {
                continue;
            }

            $createdIds[] = $this->repository->transaction(function () use ($order, $item): int {
                return $this->createFromOrderItem($order, $item);
            });
        }

        if ($createdIds !== []) {
            $this->orderService->syncFulfillmentState($salesOrderId);
        }

        return $createdIds;
    }

    public function assign(int $id, array $data): void
    {
        $serviceOrder = $this->find($id);
        if (in_array((string) ($serviceOrder['status'] ?? 'draft'), ['completed', 'closed', 'cancelled'], true)) {
            throw new HttpException('Không thể giao việc cho lệnh dịch vụ đã kết thúc.', 409);
        }

        $assignedTo = (int) ($data['assigned_to'] ?? 0);
        if ($assignedTo <= 0 || !$this->userRepository->activeUserExists($assignedTo)) {
            throw new HttpException('Vui lòng chọn người phụ trách hợp lệ.', 422, [
                'errors' => ['assigned_to' => ['Vui lòng chọn người phụ trách hợp lệ.']],
            ]);
        }

        $update = [
            'assigned_to' => $assignedTo,
            'status' => 'assigned',
            'planned_start_at' => $this->nullableDateTime($data['planned_start_at'] ?? ($serviceOrder['planned_start_at'] ?? null)),
            'planned_end_at' => $this->nullableDateTime($data['planned_end_at'] ?? ($serviceOrder['planned_end_at'] ?? null)),
            'internal_note' => $this->nullableString($data['internal_note'] ?? ($serviceOrder['internal_note'] ?? null)),
            'updated_at' => $this->timestamp(),
        ];

        $this->repository->transaction(function () use ($id, $serviceOrder, $update, $assignedTo): void {
            $this->repository->updateOrder($id, $update);
            $this->writeLog($id, 'assign', (string) ($serviceOrder['status'] ?? 'draft'), 'assigned', [
                'assigned_to' => $assignedTo,
            ], 'Đã giao việc dịch vụ.');
            $this->orderService->syncFulfillmentState((int) ($serviceOrder['sales_order_id'] ?? 0));
        });
    }

    public function start(int $id): void
    {
        $serviceOrder = $this->find($id);
        $this->assertCanOperate($serviceOrder);

        if ((int) ($serviceOrder['assigned_to'] ?? 0) <= 0) {
            throw new HttpException('Cần giao người phụ trách trước khi bắt đầu.', 409);
        }

        $status = (string) ($serviceOrder['status'] ?? 'draft');
        if (!in_array($status, ['draft', 'assigned'], true)) {
            throw new HttpException('Lệnh dịch vụ hiện không thể bắt đầu.', 409);
        }

        $this->repository->transaction(function () use ($id, $serviceOrder, $status): void {
            $this->repository->updateOrder($id, [
                'status' => 'in_progress',
                'planned_start_at' => $serviceOrder['planned_start_at'] ?? $this->timestamp(),
                'actual_start_at' => $serviceOrder['actual_start_at'] ?? $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
            $this->writeLog($id, 'start', $status, 'in_progress', [], 'Bắt đầu thực hiện dịch vụ.');
            $this->orderService->syncFulfillmentState((int) ($serviceOrder['sales_order_id'] ?? 0));
        });
    }

    public function complete(int $id): void
    {
        $serviceOrder = $this->find($id);
        $this->assertCanOperate($serviceOrder);

        if ((int) ($serviceOrder['assigned_to'] ?? 0) <= 0) {
            throw new HttpException('Cần giao người phụ trách trước khi hoàn thành.', 409);
        }

        $status = (string) ($serviceOrder['status'] ?? 'draft');
        if (!in_array($status, ['assigned', 'in_progress'], true)) {
            throw new HttpException('Lệnh dịch vụ hiện không thể hoàn thành.', 409);
        }

        $this->repository->transaction(function () use ($id, $serviceOrder, $status): void {
            $this->repository->updateOrder($id, [
                'status' => 'completed',
                'actual_start_at' => $serviceOrder['actual_start_at'] ?? $this->timestamp(),
                'actual_end_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
            $this->writeLog($id, 'complete', $status, 'completed', [], 'Đã hoàn thành dịch vụ.');
            $this->orderRepository->updateItemEngineering((int) ($serviceOrder['sales_order_item_id'] ?? 0), [
                'fulfillment_status' => 'ready',
            ]);
            $this->orderService->syncFulfillmentState((int) ($serviceOrder['sales_order_id'] ?? 0));
        });
    }

    public function cancel(int $id): void
    {
        $serviceOrder = $this->find($id);
        if (in_array((string) ($serviceOrder['status'] ?? 'draft'), ['completed', 'closed', 'cancelled'], true)) {
            throw new HttpException('Không thể hủy lệnh dịch vụ đã kết thúc.', 409);
        }

        $this->repository->transaction(function () use ($id, $serviceOrder): void {
            $this->repository->updateOrder($id, [
                'status' => 'cancelled',
                'updated_at' => $this->timestamp(),
            ]);
            $this->writeLog($id, 'cancel', (string) ($serviceOrder['status'] ?? 'draft'), 'cancelled', [], 'Đã hủy lệnh dịch vụ.');
            $this->orderRepository->updateItemEngineering((int) ($serviceOrder['sales_order_item_id'] ?? 0), [
                'fulfillment_status' => 'waiting_service',
            ]);
            $this->orderService->syncFulfillmentState((int) ($serviceOrder['sales_order_id'] ?? 0));
        });
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function userOptions(): array
    {
        return $this->repository->userOptions();
    }

    private function createFromOrderItem(array $order, array $item): int
    {
        $serviceOrderId = $this->repository->create([
            'code' => $this->generateUniqueCode(),
            'sales_order_id' => (int) ($order['id'] ?? 0),
            'sales_order_item_id' => (int) ($item['id'] ?? 0),
            'title' => 'DV cho ' . (string) ($order['code'] ?? 'SO') . ' - dòng ' . (int) ($item['line_no'] ?? 1),
            'service_name' => trim((string) ($item['description'] ?? 'Dịch vụ')),
            'work_description' => $this->nullableString($item['description'] ?? null),
            'quantity' => $this->formatDecimal((float) ($item['quantity'] ?? 0)),
            'assigned_to' => null,
            'priority' => $this->normalizePriority($order['priority'] ?? 2),
            'status' => 'draft',
            'planned_start_at' => $this->nullableDateTime(($order['order_date'] ?? '') !== '' ? (string) $order['order_date'] . ' 08:00:00' : null),
            'planned_end_at' => $this->nullableDateTime(($order['due_date'] ?? '') !== '' ? (string) $order['due_date'] . ' 17:00:00' : null),
            'actual_start_at' => null,
            'actual_end_at' => null,
            'internal_note' => 'Tạo tự động từ đơn bán ' . (string) ($order['code'] ?? ''),
            'created_by' => $this->actorId(),
            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),
        ]);

        $this->writeLog($serviceOrderId, 'create', null, 'draft', [
            'sales_order_item_id' => (int) ($item['id'] ?? 0),
        ], 'Tạo lệnh dịch vụ từ đơn bán hàng.');
        $this->orderRepository->updateItemEngineering((int) ($item['id'] ?? 0), [
            'fulfillment_status' => 'waiting_service',
        ]);

        return $serviceOrderId;
    }

    private function decorateRow(array $serviceOrder): array
    {
        $status = (string) ($serviceOrder['status'] ?? 'draft');
        $priority = $this->normalizePriority($serviceOrder['priority'] ?? 2);
        $serviceOrder['status_label'] = self::STATUS_LABELS[$status] ?? $status;
        $serviceOrder['status_badge'] = self::STATUS_BADGES[$status] ?? 'secondary';
        $serviceOrder['priority_label'] = self::PRIORITY_LABELS[$priority] ?? 'Bình thường';
        $serviceOrder['assigned_name'] = trim((string) ($serviceOrder['assigned_full_name'] ?? '')) ?: ((string) ($serviceOrder['assigned_username'] ?? 'Chưa phân công'));
        $serviceOrder['created_by_name'] = trim((string) ($serviceOrder['created_by_full_name'] ?? '')) ?: ((string) ($serviceOrder['created_by_username'] ?? '-'));
        $serviceOrder['sales_order_url'] = !empty($serviceOrder['sales_order_id']) ? app_url('/orders/show?id=' . (int) $serviceOrder['sales_order_id']) : null;
        $serviceOrder['status_warning'] = (int) ($serviceOrder['assigned_to'] ?? 0) <= 0 && in_array($status, ['draft', 'assigned'], true)
            ? 'Chưa giao nhân sự xử lý cho lệnh dịch vụ này.'
            : null;

        return $serviceOrder;
    }

    private function availableActions(array $serviceOrder): array
    {
        $status = (string) ($serviceOrder['status'] ?? 'draft');
        $isAssignedUser = (int) ($serviceOrder['assigned_to'] ?? 0) > 0 && (int) ($serviceOrder['assigned_to'] ?? 0) === $this->actorId();

        return [
            'can_assign' => service_order_permission('assign') && in_array($status, ['draft', 'assigned'], true),
            'can_start' => (service_order_permission('start') || $isAssignedUser) && in_array($status, ['draft', 'assigned'], true) && (int) ($serviceOrder['assigned_to'] ?? 0) > 0,
            'can_complete' => (service_order_permission('complete') || $isAssignedUser) && in_array($status, ['assigned', 'in_progress'], true) && (int) ($serviceOrder['assigned_to'] ?? 0) > 0,
            'can_cancel' => service_order_permission('cancel') && !in_array($status, ['completed', 'closed', 'cancelled'], true),
            'can_view_log' => service_order_permission('view_log'),
        ];
    }

    private function canView(array $serviceOrder): bool
    {
        if (has_permission('service_order.view') || has_permission('service_order.update') || service_order_permission('assign') || service_order_permission('complete')) {
            return true;
        }

        return (int) ($serviceOrder['assigned_to'] ?? 0) === $this->actorId();
    }

    private function assertCanOperate(array $serviceOrder): void
    {
        if (service_order_permission('start') || service_order_permission('complete') || service_order_permission('assign')) {
            return;
        }

        if ((int) ($serviceOrder['assigned_to'] ?? 0) === $this->actorId()) {
            return;
        }

        throw new HttpException('Bạn không được cập nhật lệnh dịch vụ này.', 403);
    }

    private function writeLog(int $serviceOrderId, string $action, ?string $oldStatus, ?string $newStatus, array $changedFields = [], ?string $remark = null): void
    {
        $this->repository->createLog([
            'service_order_id' => $serviceOrderId,
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
        $baseCode = 'SVO-' . date('ymdHis');
        $code = $baseCode;
        $suffix = 1;
        while ($this->repository->findByCode($code) !== null) {
            $tail = '-' . $suffix;
            $code = substr($baseCode, 0, max(1, 30 - strlen($tail))) . $tail;
            $suffix++;
        }

        return $code;
    }

    private function normalizePriority(mixed $value): int
    {
        if (is_numeric($value)) {
            return min(max((int) $value, 1), 4);
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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableDateTime(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
