<?php

declare(strict_types=1);

namespace App\Modules\ServiceOrder\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\ServiceOrder\Services\ServiceOrderService;

final class ServiceOrderController extends Controller
{
    public function __construct(private readonly ServiceOrderService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'mine' => (string) $request->query('mine', '') === '1',
        ];
        if (!has_permission('service_order.view') && !has_permission('service_order.update')) {
            $filters['mine'] = true;
        }
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/service-orders', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'mine' => $filters['mine'] ? '1' : '',
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/ServiceOrder/Views/index.php', [
            'pageTitle' => 'Lệnh dịch vụ',
            'activeSidebar' => 'service-orders',
            'filters' => $filters,
            'serviceOrders' => $list['items'],
            'statuses' => $this->service->statuses(),
            'pagination' => $pagination,
        ]);
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/ServiceOrder/Views/show.php', [
            'pageTitle' => 'Chi tiết lệnh dịch vụ',
            'activeSidebar' => 'service-orders',
            'serviceOrder' => $this->service->find($id),
            'users' => $this->service->userOptions(),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function assign(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_service_order_permission('assign');

        return $this->runActionWithRedirect($id, function () use ($id, $request): void {
            $this->service->assign($id, $request->all());
            session_flash('success', 'Đã giao việc dịch vụ.');
        });
    }

    public function start(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->runActionWithRedirect($id, function () use ($id): void {
            $this->service->start($id);
            session_flash('success', 'Đã bắt đầu thực hiện dịch vụ.');
        });
    }

    public function complete(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->runActionWithRedirect($id, function () use ($id): void {
            $this->service->complete($id);
            session_flash('success', 'Đã hoàn thành lệnh dịch vụ.');
        });
    }

    public function cancel(Request $request)
    {
        $id = (int) $request->query('id', 0);
        require_service_order_permission('cancel');

        return $this->runActionWithRedirect($id, function () use ($id): void {
            $this->service->cancel($id);
            session_flash('success', 'Đã hủy lệnh dịch vụ.');
        });
    }

    private function runActionWithRedirect(int $id, callable $callback)
    {
        try {
            $callback();
        } catch (HttpException $exception) {
            session_flash('error', $exception->getMessage());
        }

        return $this->redirect(app_url('/service-orders/show?id=' . $id));
    }
}
