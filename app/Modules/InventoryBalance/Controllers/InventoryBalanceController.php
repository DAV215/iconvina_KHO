<?php

declare(strict_types=1);

namespace App\Modules\InventoryBalance\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\InventoryBalance\Services\InventoryBalanceService;

final class InventoryBalanceController extends Controller
{
    public function __construct(private readonly InventoryBalanceService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('stock.view');

        $paging = $this->paginationParams($request);
        $filters = [
            'item_type' => (string) $request->query('item_type', ''),
            'code' => (string) $request->query('code', ''),
            'name' => (string) $request->query('name', ''),
            'category_id' => (string) $request->query('category_id', ''),
            'stock_status' => (string) $request->query('stock_status', ''),
            'is_active' => (string) $request->query('is_active', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort', 'code'),
            'dir' => (string) $request->query('dir', 'asc'),
        ];
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/inventory/balance', [
            'item_type' => $list['filters']['item_type'] ?? '',
            'code' => $list['filters']['code'] ?? '',
            'name' => $list['filters']['name'] ?? '',
            'category_id' => $list['filters']['category_id'] ?? '',
            'stock_status' => $list['filters']['stock_status'] ?? '',
            'is_active' => $list['filters']['is_active'] ?? '',
            'sort' => $list['sort']['by'] ?? 'code',
            'dir' => $list['sort']['dir'] ?? 'asc',
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/InventoryBalance/Views/index.php', [
            'pageTitle' => 'Xem tồn kho',
            'pageEyebrow' => 'Kho / Tồn kho',
            'activeSidebar' => 'inventory-balance',
            'filters' => $list['filters'],
            'sort' => $list['sort'],
            'itemTypes' => $this->service->itemTypes(),
            'stockStatuses' => $this->service->stockStatusOptions(),
            'categoryOptions' => $this->service->materialCategoryOptions(),
            'balances' => $list['items'],
            'pagination' => $pagination,
        ]);
    }
}
