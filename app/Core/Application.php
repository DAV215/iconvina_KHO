<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Config\Repository as ConfigRepository;
use App\Core\Database\Connection;
use App\Core\Database\Migrations\Migrator;
use App\Core\Http\Request;
use App\Core\Http\Response;

final class Application
{
    private static string $basePath;

    private readonly Container $container;

    private readonly Router $router;

    public function __construct(string $basePath)
    {
        self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->container = new Container();
        $this->router = new Router($this->container);
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function bootstrap(): void
    {
        Env::load(base_path('.env'));

        $appConfig = require config_path('app.php');
        $dbConfig = require config_path('database.php');

        date_default_timezone_set($appConfig['timezone']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->container->singleton('config', fn (): array => [
            'app' => $appConfig,
            'database' => $dbConfig,
        ]);
        $this->container->singleton(ConfigRepository::class, fn (Container $container): ConfigRepository => new ConfigRepository(
            $container->get('config')
        ));

        $this->container->singleton(Connection::class, fn (Container $container): Connection => new Connection(
            $container->get(ConfigRepository::class)->get('database')
        ));

        $this->container->singleton(Migrator::class, fn (Container $container): Migrator => new Migrator(
            $container->get(Connection::class),
            base_path('database/migrations')
        ));

        $this->registerRoutes();
    }

    public function run(): void
    {
        $request = Request::capture();

        if ($this->shouldRequireLogin($request) && auth_user() === null) {
            if (str_starts_with($request->path(), '/api')) {
                Response::json([
                    'message' => 'Unauthorized.',
                ], 401)->send();

                return;
            }

            require_login();
        }

        $response = $this->router->dispatch($request);
        $response->send();
    }

    private function registerRoutes(): void
    {
        $this->router->get('/login', [\App\Modules\Auth\Controllers\AuthController::class, 'showLogin']);
        $this->router->post('/login', [\App\Modules\Auth\Controllers\AuthController::class, 'login']);
        $this->router->post('/logout', [\App\Modules\Auth\Controllers\AuthController::class, 'logout']);

        $this->router->get('/', [\App\Modules\Home\Controllers\HomeController::class, 'index']);
        $this->router->get('/health', [\App\Modules\Home\Controllers\HealthController::class, 'show']);

        $this->router->get('/customers', [\App\Modules\Customer\Controllers\CustomerController::class, 'index']);
        $this->router->get('/customers/create', [\App\Modules\Customer\Controllers\CustomerController::class, 'create']);
        $this->router->post('/customers/store', [\App\Modules\Customer\Controllers\CustomerController::class, 'store']);
        $this->router->get('/customers/show', [\App\Modules\Customer\Controllers\CustomerController::class, 'show']);
        $this->router->get('/customers/edit', [\App\Modules\Customer\Controllers\CustomerController::class, 'edit']);
        $this->router->post('/customers/update', [\App\Modules\Customer\Controllers\CustomerController::class, 'update']);
        $this->router->post('/customers/delete', [\App\Modules\Customer\Controllers\CustomerController::class, 'delete']);

        $this->router->get('/suppliers', [\App\Modules\Supplier\Controllers\SupplierController::class, 'index']);
        $this->router->get('/suppliers/create', [\App\Modules\Supplier\Controllers\SupplierController::class, 'create']);
        $this->router->post('/suppliers/store', [\App\Modules\Supplier\Controllers\SupplierController::class, 'store']);
        $this->router->get('/suppliers/show', [\App\Modules\Supplier\Controllers\SupplierController::class, 'show']);
        $this->router->get('/suppliers/edit', [\App\Modules\Supplier\Controllers\SupplierController::class, 'edit']);
        $this->router->post('/suppliers/update', [\App\Modules\Supplier\Controllers\SupplierController::class, 'update']);
        $this->router->post('/suppliers/delete', [\App\Modules\Supplier\Controllers\SupplierController::class, 'delete']);

        $this->router->get('/companies', [\App\Modules\Company\Controllers\CompanyController::class, 'index']);
        $this->router->get('/companies/create', [\App\Modules\Company\Controllers\CompanyController::class, 'create']);
        $this->router->post('/companies/store', [\App\Modules\Company\Controllers\CompanyController::class, 'store']);
        $this->router->get('/companies/show', [\App\Modules\Company\Controllers\CompanyController::class, 'show']);
        $this->router->get('/companies/edit', [\App\Modules\Company\Controllers\CompanyController::class, 'edit']);
        $this->router->post('/companies/update', [\App\Modules\Company\Controllers\CompanyController::class, 'update']);
        $this->router->post('/companies/disable', [\App\Modules\Company\Controllers\CompanyController::class, 'disable']);
        $this->router->post('/companies/delete', [\App\Modules\Company\Controllers\CompanyController::class, 'delete']);

        $this->router->get('/branches', [\App\Modules\Branch\Controllers\BranchController::class, 'index']);
        $this->router->get('/branches/create', [\App\Modules\Branch\Controllers\BranchController::class, 'create']);
        $this->router->post('/branches/store', [\App\Modules\Branch\Controllers\BranchController::class, 'store']);
        $this->router->get('/branches/show', [\App\Modules\Branch\Controllers\BranchController::class, 'show']);
        $this->router->get('/branches/edit', [\App\Modules\Branch\Controllers\BranchController::class, 'edit']);
        $this->router->post('/branches/update', [\App\Modules\Branch\Controllers\BranchController::class, 'update']);
        $this->router->post('/branches/disable', [\App\Modules\Branch\Controllers\BranchController::class, 'disable']);
        $this->router->post('/branches/delete', [\App\Modules\Branch\Controllers\BranchController::class, 'delete']);

        $this->router->get('/departments', [\App\Modules\Department\Controllers\DepartmentController::class, 'index']);
        $this->router->get('/departments/create', [\App\Modules\Department\Controllers\DepartmentController::class, 'create']);
        $this->router->post('/departments/store', [\App\Modules\Department\Controllers\DepartmentController::class, 'store']);
        $this->router->get('/departments/show', [\App\Modules\Department\Controllers\DepartmentController::class, 'show']);
        $this->router->get('/departments/edit', [\App\Modules\Department\Controllers\DepartmentController::class, 'edit']);
        $this->router->post('/departments/update', [\App\Modules\Department\Controllers\DepartmentController::class, 'update']);
        $this->router->post('/departments/disable', [\App\Modules\Department\Controllers\DepartmentController::class, 'disable']);
        $this->router->post('/departments/delete', [\App\Modules\Department\Controllers\DepartmentController::class, 'delete']);

        $this->router->get('/positions', [\App\Modules\Position\Controllers\PositionController::class, 'index']);
        $this->router->get('/positions/create', [\App\Modules\Position\Controllers\PositionController::class, 'create']);
        $this->router->post('/positions/store', [\App\Modules\Position\Controllers\PositionController::class, 'store']);
        $this->router->get('/positions/show', [\App\Modules\Position\Controllers\PositionController::class, 'show']);
        $this->router->get('/positions/edit', [\App\Modules\Position\Controllers\PositionController::class, 'edit']);
        $this->router->post('/positions/update', [\App\Modules\Position\Controllers\PositionController::class, 'update']);
        $this->router->post('/positions/disable', [\App\Modules\Position\Controllers\PositionController::class, 'disable']);
        $this->router->post('/positions/delete', [\App\Modules\Position\Controllers\PositionController::class, 'delete']);

        $this->router->get('/materials', [\App\Modules\Material\Controllers\MaterialController::class, 'index']);
        $this->router->get('/materials/create', [\App\Modules\Material\Controllers\MaterialController::class, 'create']);
        $this->router->get('/materials/duplicate', [\App\Modules\Material\Controllers\MaterialController::class, 'duplicate']);
        $this->router->post('/materials/store', [\App\Modules\Material\Controllers\MaterialController::class, 'store']);
        $this->router->get('/materials/show', [\App\Modules\Material\Controllers\MaterialController::class, 'show']);
        $this->router->get('/materials/edit', [\App\Modules\Material\Controllers\MaterialController::class, 'edit']);
        $this->router->post('/materials/update', [\App\Modules\Material\Controllers\MaterialController::class, 'update']);
        $this->router->post('/materials/delete', [\App\Modules\Material\Controllers\MaterialController::class, 'delete']);

        $this->router->get('/material-categories', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'index']);
        $this->router->get('/material-categories/create', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'create']);
        $this->router->post('/material-categories/store', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'store']);
        $this->router->get('/material-categories/show', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'show']);
        $this->router->get('/material-categories/edit', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'edit']);
        $this->router->post('/material-categories/update', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'update']);
        $this->router->post('/material-categories/delete', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryController::class, 'delete']);

        $this->router->get('/components', [\App\Modules\Component\Controllers\ComponentController::class, 'index']);
        $this->router->get('/components/create', [\App\Modules\Component\Controllers\ComponentController::class, 'create']);
        $this->router->post('/components/store', [\App\Modules\Component\Controllers\ComponentController::class, 'store']);
        $this->router->get('/components/show', [\App\Modules\Component\Controllers\ComponentController::class, 'show']);
        $this->router->get('/components/edit', [\App\Modules\Component\Controllers\ComponentController::class, 'edit']);
        $this->router->post('/components/update', [\App\Modules\Component\Controllers\ComponentController::class, 'update']);
        $this->router->post('/components/delete', [\App\Modules\Component\Controllers\ComponentController::class, 'delete']);

        $this->router->get('/quotations', [\App\Modules\Quotation\Controllers\QuotationController::class, 'index']);
        $this->router->get('/quotations/create', [\App\Modules\Quotation\Controllers\QuotationController::class, 'create']);
        $this->router->post('/quotations/store', [\App\Modules\Quotation\Controllers\QuotationController::class, 'store']);
        $this->router->get('/quotations/show', [\App\Modules\Quotation\Controllers\QuotationController::class, 'show']);
        $this->router->get('/quotations/edit', [\App\Modules\Quotation\Controllers\QuotationController::class, 'edit']);
        $this->router->post('/quotations/update', [\App\Modules\Quotation\Controllers\QuotationController::class, 'update']);
        $this->router->post('/quotations/submit', [\App\Modules\Quotation\Controllers\QuotationController::class, 'submit']);
        $this->router->post('/quotations/approve', [\App\Modules\Quotation\Controllers\QuotationController::class, 'approve']);
        $this->router->post('/quotations/reject', [\App\Modules\Quotation\Controllers\QuotationController::class, 'reject']);
        $this->router->post('/quotations/cancel', [\App\Modules\Quotation\Controllers\QuotationController::class, 'cancel']);
        $this->router->post('/quotations/delete', [\App\Modules\Quotation\Controllers\QuotationController::class, 'delete']);

        $this->router->get('/orders', [\App\Modules\Order\Controllers\OrderController::class, 'index']);
        $this->router->get('/orders/create', [\App\Modules\Order\Controllers\OrderController::class, 'create']);
        $this->router->post('/orders/store', [\App\Modules\Order\Controllers\OrderController::class, 'store']);
        $this->router->get('/orders/show', [\App\Modules\Order\Controllers\OrderController::class, 'show']);
        $this->router->get('/orders/edit', [\App\Modules\Order\Controllers\OrderController::class, 'edit']);
        $this->router->post('/orders/update', [\App\Modules\Order\Controllers\OrderController::class, 'update']);
        $this->router->post('/orders/approve', [\App\Modules\Order\Controllers\OrderController::class, 'approve']);
        $this->router->post('/orders/confirm', [\App\Modules\Order\Controllers\OrderController::class, 'confirm']);
        $this->router->post('/orders/mark-ready', [\App\Modules\Order\Controllers\OrderController::class, 'markReadyToDeliver']);
        $this->router->post('/orders/create-delivery', [\App\Modules\Order\Controllers\OrderController::class, 'createDelivery']);
        $this->router->post('/orders/confirm-delivery', [\App\Modules\Order\Controllers\OrderController::class, 'confirmDelivery']);
        $this->router->post('/orders/cancel-delivery', [\App\Modules\Order\Controllers\OrderController::class, 'cancelDelivery']);
        $this->router->post('/orders/create-component', [\App\Modules\Order\Controllers\OrderController::class, 'createComponent']);
        $this->router->post('/orders/delete', [\App\Modules\Order\Controllers\OrderController::class, 'delete']);

        $this->router->get('/purchase-orders', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'index']);
        $this->router->get('/purchase-orders/create', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'create']);
        $this->router->post('/purchase-orders/store', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'store']);
        $this->router->get('/purchase-orders/show', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'show']);
        $this->router->get('/purchase-orders/edit', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'edit']);
        $this->router->post('/purchase-orders/update', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'update']);
        $this->router->post('/purchase-orders/submit', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'submit']);
        $this->router->post('/purchase-orders/approve', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'approve']);
        $this->router->post('/purchase-orders/reject', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'reject']);
        $this->router->post('/purchase-orders/cancel', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'cancel']);
        $this->router->post('/purchase-orders/receive', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'receive']);
        $this->router->post('/purchase-orders/add-extra-cost', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'addExtraCost']);
        $this->router->post('/purchase-orders/submit-stock-in', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'submitStockIn']);
        $this->router->post('/purchase-orders/approve-stock-in', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'approveStockIn']);
        $this->router->post('/purchase-orders/close', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'close']);
        $this->router->post('/purchase-orders/delete', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderController::class, 'delete']);

        $this->router->get('/users', [\App\Modules\User\Controllers\UserController::class, 'index']);
        $this->router->get('/users/create', [\App\Modules\User\Controllers\UserController::class, 'create']);
        $this->router->post('/users/store', [\App\Modules\User\Controllers\UserController::class, 'store']);
        $this->router->get('/users/show', [\App\Modules\User\Controllers\UserController::class, 'show']);
        $this->router->get('/users/edit', [\App\Modules\User\Controllers\UserController::class, 'edit']);
        $this->router->post('/users/update', [\App\Modules\User\Controllers\UserController::class, 'update']);
        $this->router->post('/users/disable', [\App\Modules\User\Controllers\UserController::class, 'disable']);
        $this->router->post('/users/delete', [\App\Modules\User\Controllers\UserController::class, 'delete']);

        $this->router->get('/roles', [\App\Modules\Role\Controllers\RoleController::class, 'index']);
        $this->router->get('/roles/create', [\App\Modules\Role\Controllers\RoleController::class, 'create']);
        $this->router->post('/roles/store', [\App\Modules\Role\Controllers\RoleController::class, 'store']);
        $this->router->get('/roles/edit', [\App\Modules\Role\Controllers\RoleController::class, 'edit']);
        $this->router->post('/roles/update', [\App\Modules\Role\Controllers\RoleController::class, 'update']);
        $this->router->post('/roles/disable', [\App\Modules\Role\Controllers\RoleController::class, 'disable']);
        $this->router->post('/roles/delete', [\App\Modules\Role\Controllers\RoleController::class, 'delete']);
        $this->router->get('/roles/permissions', [\App\Modules\Permission\Controllers\PermissionController::class, 'edit']);
        $this->router->post('/roles/permissions', [\App\Modules\Permission\Controllers\PermissionController::class, 'update']);

        $this->router->get('/bom', [\App\Modules\Bom\Controllers\BomController::class, 'index']);
        $this->router->get('/bom/create', [\App\Modules\Bom\Controllers\BomController::class, 'create']);
        $this->router->post('/bom/store', [\App\Modules\Bom\Controllers\BomController::class, 'store']);
        $this->router->get('/bom/show', [\App\Modules\Bom\Controllers\BomController::class, 'show']);
        $this->router->get('/bom/tree', [\App\Modules\Bom\Controllers\BomController::class, 'tree']);
        $this->router->get('/bom/edit', [\App\Modules\Bom\Controllers\BomController::class, 'edit']);
        $this->router->post('/bom/update', [\App\Modules\Bom\Controllers\BomController::class, 'update']);
        $this->router->post('/bom/delete', [\App\Modules\Bom\Controllers\BomController::class, 'delete']);

        $this->router->get('/production-orders', [\App\Modules\Production\Controllers\ProductionController::class, 'index']);
        $this->router->get('/production-orders/show', [\App\Modules\Production\Controllers\ProductionController::class, 'show']);
        $this->router->post('/production-orders/create-from-sales-order', [\App\Modules\Production\Controllers\ProductionController::class, 'createFromSalesOrder']);
        $this->router->post('/production-orders/release', [\App\Modules\Production\Controllers\ProductionController::class, 'release']);
        $this->router->post('/production-orders/issue-materials', [\App\Modules\Production\Controllers\ProductionController::class, 'issueMaterials']);
        $this->router->post('/production-orders/start', [\App\Modules\Production\Controllers\ProductionController::class, 'start']);
        $this->router->post('/production-orders/assign-task', [\App\Modules\Production\Controllers\ProductionController::class, 'assignTask']);
        $this->router->post('/production-orders/update-task', [\App\Modules\Production\Controllers\ProductionController::class, 'updateTask']);
        $this->router->post('/production-orders/complete', [\App\Modules\Production\Controllers\ProductionController::class, 'complete']);

        $this->router->get('/service-orders', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'index']);
        $this->router->get('/service-orders/show', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'show']);
        $this->router->post('/service-orders/assign', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'assign']);
        $this->router->post('/service-orders/start', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'start']);
        $this->router->post('/service-orders/complete', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'complete']);
        $this->router->post('/service-orders/cancel', [\App\Modules\ServiceOrder\Controllers\ServiceOrderController::class, 'cancel']);

        $this->router->post('/payments/store-receipt', [\App\Modules\Payment\Controllers\PaymentController::class, 'storeReceipt']);
        $this->router->post('/payments/store-voucher', [\App\Modules\Payment\Controllers\PaymentController::class, 'storeVoucher']);
        $this->router->post('/payments/confirm', [\App\Modules\Payment\Controllers\PaymentController::class, 'confirm']);

        $this->router->get('/stocks', [\App\Modules\Inventory\Controllers\StockController::class, 'index']);
        $this->router->get('/stocks/create', [\App\Modules\Inventory\Controllers\StockController::class, 'create']);
        $this->router->post('/stocks/store', [\App\Modules\Inventory\Controllers\StockController::class, 'store']);
        $this->router->get('/stocks/show', [\App\Modules\Inventory\Controllers\StockController::class, 'show']);
        $this->router->get('/stocks/edit', [\App\Modules\Inventory\Controllers\StockController::class, 'edit']);
        $this->router->post('/stocks/update', [\App\Modules\Inventory\Controllers\StockController::class, 'update']);
        $this->router->post('/stocks/delete', [\App\Modules\Inventory\Controllers\StockController::class, 'delete']);
        $this->router->get('/inventory/balance', [\App\Modules\InventoryBalance\Controllers\InventoryBalanceController::class, 'index']);

        $this->router->get('/api/auth', [\App\Modules\Auth\Controllers\AuthController::class, 'index']);
        $this->router->get('/api/customers', [\App\Modules\Customer\Controllers\CustomerApiController::class, 'index']);
        $this->router->get('/api/branches', [\App\Modules\Branch\Controllers\BranchApiController::class, 'index']);
        $this->router->get('/api/departments', [\App\Modules\Department\Controllers\DepartmentApiController::class, 'index']);
        $this->router->get('/api/positions', [\App\Modules\Position\Controllers\PositionApiController::class, 'index']);
        $this->router->get('/api/suppliers/options', [\App\Modules\Supplier\Controllers\SupplierApiController::class, 'options']);
        $this->router->get('/api/suppliers/search', [\App\Modules\Supplier\Controllers\SupplierApiController::class, 'search']);
        $this->router->post('/api/suppliers/quick-create', [\App\Modules\Supplier\Controllers\SupplierApiController::class, 'quickCreate']);
        $this->router->get('/api/suppliers', [\App\Modules\Supplier\Controllers\SupplierApiController::class, 'index']);
        $this->router->post('/api/materials/quick-create', [\App\Modules\Material\Controllers\MaterialApiController::class, 'quickCreate']);
        $this->router->get('/api/material-categories', [\App\Modules\MaterialCategory\Controllers\MaterialCategoryApiController::class, 'index']);
        $this->router->get('/api/quotations', [\App\Modules\Quotation\Controllers\QuotationApiController::class, 'index']);
        $this->router->get('/api/orders', [\App\Modules\Order\Controllers\OrderApiController::class, 'index']);
        $this->router->get('/api/purchase-orders', [\App\Modules\PurchaseOrder\Controllers\PurchaseOrderApiController::class, 'index']);
        $this->router->get('/api/bom', [\App\Modules\Bom\Controllers\BomApiController::class, 'index']);
        $this->router->get('/api/stocks', [\App\Modules\Inventory\Controllers\StockApiController::class, 'index']);
        $this->router->get('/api/production-orders', [\App\Modules\Production\Controllers\ProductionApiController::class, 'index']);
        $this->router->get('/api/inventory', [\App\Modules\Inventory\Controllers\StockApiController::class, 'index']);
        $this->router->get('/api/accounting', [\App\Modules\Accounting\Controllers\AccountingController::class, 'index']);
    }

    private function shouldRequireLogin(Request $request): bool
    {
        $publicPaths = [
            '/login',
            '/health',
            '/api/auth',
        ];

        return !in_array($request->path(), $publicPaths, true);
    }
}
