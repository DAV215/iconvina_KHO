<?php

declare(strict_types=1);

namespace App\Modules\Material\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Material\Services\MaterialService;

final class MaterialController extends Controller
{
    public function __construct(private readonly MaterialService $service)
    {
    }

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');

        return $this->view('app/Modules/Material/Views/index.php', [
            'pageTitle' => 'Nguyên vật liệu',
            'pageEyebrow' => 'Quản lý nguyên vật liệu',
            'activeSidebar' => 'materials',
            'search' => $search,
            'materials' => $this->service->list($search),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Thêm nguyên vật liệu', app_url('/materials/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $id = $this->service->create($validated);
            session_flash('success', 'Tạo nguyên vật liệu thành công.');

            return $this->redirect(app_url('/materials/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm nguyên vật liệu', app_url('/materials/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Material/Views/show.php', [
            'pageTitle' => 'Chi tiết nguyên vật liệu',
            'pageEyebrow' => 'Hồ sơ nguyên vật liệu',
            'activeSidebar' => 'materials',
            'material' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $material = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa nguyên vật liệu', app_url('/materials/update?id=' . $id), $material);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật nguyên vật liệu thành công.');

            return $this->redirect(app_url('/materials/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa nguyên vật liệu', app_url('/materials/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa nguyên vật liệu thành công.');

        return $this->redirect(app_url('/materials'));
    }

    private function renderForm(string $title, string $action, array $material = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Material/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý nguyên vật liệu',
            'activeSidebar' => 'materials',
            'formAction' => $action,
            'material' => $material,
            'categoryOptions' => $this->service->categoryOptions(),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'category_id' => 'nullable|numeric',
            'unit' => 'required|string|max:50',
            'specification' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'image_path' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:65535',
            'standard_cost' => 'required|numeric',
            'min_stock' => 'nullable|numeric',
        ];
    }
}