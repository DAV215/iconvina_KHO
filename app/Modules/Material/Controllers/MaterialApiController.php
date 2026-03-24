<?php

declare(strict_types=1);

namespace App\Modules\Material\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Material\Services\MaterialService;

final class MaterialApiController extends Controller
{
    public function __construct(private readonly MaterialService $service)
    {
    }

    public function quickCreate(Request $request): array
    {
        $this->authorize('material.create');
        try {
            $validated = $this->validate($request->all(), [
                'code' => 'nullable|string|max:30',
                'name' => 'required|string|max:190',
                'category_id' => 'nullable|numeric',
                'unit' => 'required|string|max:50',
                'standard_cost' => 'required|numeric',
                'specification' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:65535',
            ]);

            if (trim((string) ($validated['code'] ?? '')) === '') {
                $validated['code'] = $this->service->suggestQuickCode();
            }

            $validated['min_stock'] = 0;
            $validated['is_active'] = 1;
            $validated['image_path'] = null;

            $materialId = $this->service->create($validated);
            $material = $this->service->find($materialId);

            return [
                'message' => 'Tạo nhanh vật tư thành công.',
                'data' => [
                    'id' => (int) $material['id'],
                    'code' => (string) $material['code'],
                    'name' => (string) $material['name'],
                    'option_label' => trim((string) $material['code'] . ' - ' . (string) $material['name']),
                    'unit' => (string) $material['unit'],
                    'standard_cost' => number_format((float) ($material['standard_cost'] ?? 0), 2, '.', ''),
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
                ],
            ];
        } catch (ValidationException $exception) {
            throw new HttpException('Dữ liệu tạo nhanh vật tư không hợp lệ.', 422, [
                'errors' => $exception->context()['errors'] ?? [],
            ]);
        }
    }
}
