<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Supplier\Services\SupplierService;

final class SupplierApiController extends Controller
{
    public function __construct(private readonly SupplierService $service)
    {
    }

    public function options(Request $request): array
    {
        $this->authorize('supplier.view');
        unset($request);

        $suppliers = [];
        foreach ($this->service->options() as $supplier) {
            $suppliers[] = [
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

        return [
            'module' => 'Supplier',
            'data' => $suppliers,
        ];
    }

    public function search(Request $request): array
    {
        $this->authorize('supplier.view');
        $query = mb_strtolower(trim((string) $request->query('q', '')));
        $suppliers = [];

        foreach ($this->service->options() as $supplier) {
            $payload = [
                'id' => (int) $supplier['id'],
                'code' => (string) $supplier['code'],
                'name' => (string) $supplier['name'],
                'contact_name' => (string) ($supplier['contact_name'] ?? ''),
                'phone' => (string) ($supplier['phone'] ?? ''),
                'email' => (string) ($supplier['email'] ?? ''),
                'tax_code' => (string) ($supplier['tax_code'] ?? ''),
                'address' => (string) ($supplier['address'] ?? ''),
                'note' => (string) ($supplier['note'] ?? ''),
            ];
            $payload['option_label'] = trim($payload['code'] . ' - ' . $payload['name']);
            $payload['search_text'] = mb_strtolower(implode(' ', array_filter([
                $payload['code'],
                $payload['name'],
                $payload['contact_name'],
                $payload['phone'],
                $payload['email'],
            ])));

            if ($query !== '' && !str_contains($payload['search_text'], $query) && !str_contains(mb_strtolower($payload['option_label']), $query)) {
                continue;
            }

            $suppliers[] = $payload;

            if (count($suppliers) >= 20) {
                break;
            }
        }

        return [
            'module' => 'Supplier',
            'query' => [
                'q' => $query,
            ],
            'data' => $suppliers,
        ];
    }

    public function index(Request $request): array
    {
        $this->authorize('supplier.view');
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];

        return [
            'module' => 'Supplier',
            'query' => [
                'filters' => $filters,
                'sort' => $sort,
            ],
            'data' => $this->service->list($filters, $sort),
        ];
    }

    public function quickCreate(Request $request): array
    {
        $this->authorize('supplier.create');
        $validated = $this->validate($request->all(), [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'contact_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'tax_code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:65535',
            'note' => 'nullable|string|max:65535',
        ]);
        $validated['is_active'] = 1;

        $supplierId = $this->service->create($validated);
        $supplier = $this->service->find($supplierId);

        return [
            'message' => 'Tạo nhanh nhà cung cấp thành công.',
            'data' => [
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
            ],
        ];
    }
}
