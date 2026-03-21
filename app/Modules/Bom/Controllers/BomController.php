<?php

declare(strict_types=1);

namespace App\Modules\Bom\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Bom\Services\BomService;

final class BomController extends Controller
{
    public function __construct(private readonly BomService $service)
    {
    }

    public function index(Request $request)
    {
        $componentId = (int) $request->query('component_id', 0);
        $version = (string) $request->query('version', '');

        return $this->view('app/Modules/Bom/Views/index.php', [
            'pageTitle' => 'BOM',
            'pageEyebrow' => 'Quản lý BOM',
            'activeSidebar' => 'bom',
            'componentId' => $componentId,
            'version' => $version,
            'components' => $this->service->componentOptions(),
            'boms' => $this->service->list($componentId > 0 ? $componentId : null, $version),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Thêm BOM', app_url('/bom/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $bomId = $this->service->create($validated);
            session_flash('success', 'Tạo BOM thành công.');

            return $this->redirect(app_url('/bom/show?id=' . $bomId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm BOM', app_url('/bom/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Bom/Views/show.php', [
            'pageTitle' => 'Chi tiết BOM',
            'pageEyebrow' => 'Hồ sơ BOM',
            'activeSidebar' => 'bom',
            'bom' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $bom = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa BOM', app_url('/bom/update?id=' . $id), $bom);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật BOM thành công.');

            return $this->redirect(app_url('/bom/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa BOM', app_url('/bom/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa BOM thành công.');

        return $this->redirect(app_url('/bom'));
    }

    private function renderForm(string $title, string $action, array $bom = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Bom/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý BOM',
            'activeSidebar' => 'bom',
            'formAction' => $action,
            'bom' => $bom,
            'components' => $this->service->componentOptions(),
            'materials' => $this->service->materialOptions(),
            'childComponents' => $this->service->componentOptions(),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'component_id' => 'required|numeric',
            'version' => 'required|string|max:50',
            'is_active' => 'nullable|numeric',
        ];
    }
}
