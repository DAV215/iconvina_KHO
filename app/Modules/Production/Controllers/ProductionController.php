<?php

declare(strict_types=1);

namespace App\Modules\Production\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Production\Services\ProductionService;

final class ProductionController extends Controller
{
    public function __construct(private readonly ProductionService $service)
    {
    }

    public function index(Request $request)
    {
        if (!has_permission('production.view') && !has_permission('production.update')) {
            $this->authorize('production.view');
        }
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'mine' => (string) $request->query('mine', '') === '1',
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/production-orders', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'mine' => $filters['mine'] ? '1' : '',
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Production/Views/index.php', [
            'pageTitle' => 'Lệnh sản xuất',
            'activeSidebar' => 'production-orders',
            'filters' => $filters,
            'productionOrders' => $list['items'],
            'statuses' => $this->service->statuses(),
            'pagination' => $pagination,
        ]);
    }

    public function show(Request $request)
    {
        if (!has_permission('production.view') && !has_permission('production.update')) {
            $this->authorize('production.view');
        }
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Production/Views/show.php', [
            'pageTitle' => 'Chi tiết lệnh sản xuất',
            'activeSidebar' => 'production-orders',
            'productionOrder' => $this->service->find($id),
            'taskStatuses' => $this->service->taskStatuses(),
            'users' => $this->service->userOptions(),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function createFromSalesOrder(Request $request)
    {
        $this->authorize('production.create');
        $salesOrderId = (int) $request->query('id', 0);
        $itemId = (int) $request->query('item_id', 0);
        $productionOrderId = $this->service->createFromSalesOrderItem($salesOrderId, $itemId);
        session_flash('success', 'Đã tạo lệnh sản xuất cho phần thiếu hụt.');

        return $this->redirect(app_url('/production-orders/show?id=' . $productionOrderId));
    }

    public function release(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_production_permission('update');

        return $this->runActionWithRedirect($id, function () use ($id): void {
            $this->service->release($id);
            session_flash('success', 'Đã phát hành lệnh sản xuất.');
        });
    }

    public function issueMaterials(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_production_permission('issue');

        return $this->runActionWithRedirect($id, function () use ($id, $request): void {
            $result = $this->service->issueMaterials($id, $request->all());
            session_flash('success', $result['message']);
        });
    }

    public function start(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_production_permission('start');

        return $this->runActionWithRedirect($id, function () use ($id): void {
            $this->service->start($id);
            session_flash('success', 'Đã bắt đầu sản xuất.');
        });
    }

    public function assignTask(Request $request)
    {
        $this->authorize('production.assign');
        $productionOrderId = (int) $request->query('id', 0);
        $taskId = (int) $request->query('task_id', 0);
        $this->service->assignTask($productionOrderId, $taskId, $request->all());
        session_flash('success', 'Đã phân công công việc.');

        return $this->redirect(app_url('/production-orders/show?id=' . $productionOrderId));
    }

    public function updateTask(Request $request)
    {
        $productionOrderId = (int) $request->query('id', 0);
        $taskId = (int) $request->query('task_id', 0);
        $this->service->updateTask($productionOrderId, $taskId, $request->all());
        session_flash('success', 'Đã cập nhật tiến độ công việc.');

        return $this->redirect(app_url('/production-orders/show?id=' . $productionOrderId));
    }

    public function complete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_production_permission('complete');

        return $this->runActionWithRedirect($id, function () use ($id, $request): void {
            $this->service->complete($id, $request->all());
            session_flash('success', 'Đã hoàn tất lệnh sản xuất và nhập kho thành phẩm.');
        });
    }

    private function runActionWithRedirect(int $id, callable $callback)
    {
        try {
            $callback();
        } catch (HttpException|ValidationException $exception) {
            session_flash('error', $exception->getMessage());
        }

        return $this->redirect(app_url('/production-orders/show?id=' . $id));
    }
}
