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
        $response = $this->router->dispatch($request);
        $response->send();
    }

    private function registerRoutes(): void
    {
        $this->router->get('/', [\App\Modules\Home\Controllers\HomeController::class, 'index']);
        $this->router->get('/health', [\App\Modules\Home\Controllers\HealthController::class, 'show']);

        $this->router->get('/customers', [\App\Modules\Customer\Controllers\CustomerController::class, 'index']);
        $this->router->get('/customers/create', [\App\Modules\Customer\Controllers\CustomerController::class, 'create']);
        $this->router->post('/customers/store', [\App\Modules\Customer\Controllers\CustomerController::class, 'store']);
        $this->router->get('/customers/show', [\App\Modules\Customer\Controllers\CustomerController::class, 'show']);
        $this->router->get('/customers/edit', [\App\Modules\Customer\Controllers\CustomerController::class, 'edit']);
        $this->router->post('/customers/update', [\App\Modules\Customer\Controllers\CustomerController::class, 'update']);
        $this->router->post('/customers/delete', [\App\Modules\Customer\Controllers\CustomerController::class, 'delete']);

        $this->router->get('/materials', [\App\Modules\Material\Controllers\MaterialController::class, 'index']);
        $this->router->get('/materials/create', [\App\Modules\Material\Controllers\MaterialController::class, 'create']);
        $this->router->post('/materials/store', [\App\Modules\Material\Controllers\MaterialController::class, 'store']);
        $this->router->get('/materials/show', [\App\Modules\Material\Controllers\MaterialController::class, 'show']);
        $this->router->get('/materials/edit', [\App\Modules\Material\Controllers\MaterialController::class, 'edit']);
        $this->router->post('/materials/update', [\App\Modules\Material\Controllers\MaterialController::class, 'update']);
        $this->router->post('/materials/delete', [\App\Modules\Material\Controllers\MaterialController::class, 'delete']);

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
        $this->router->post('/quotations/delete', [\App\Modules\Quotation\Controllers\QuotationController::class, 'delete']);

        $this->router->get('/orders', [\App\Modules\Order\Controllers\OrderController::class, 'index']);
        $this->router->get('/orders/create', [\App\Modules\Order\Controllers\OrderController::class, 'create']);
        $this->router->post('/orders/store', [\App\Modules\Order\Controllers\OrderController::class, 'store']);
        $this->router->get('/orders/show', [\App\Modules\Order\Controllers\OrderController::class, 'show']);
        $this->router->get('/orders/edit', [\App\Modules\Order\Controllers\OrderController::class, 'edit']);
        $this->router->post('/orders/update', [\App\Modules\Order\Controllers\OrderController::class, 'update']);
        $this->router->post('/orders/delete', [\App\Modules\Order\Controllers\OrderController::class, 'delete']);

        $this->router->get('/bom', [\App\Modules\Bom\Controllers\BomController::class, 'index']);
        $this->router->get('/bom/create', [\App\Modules\Bom\Controllers\BomController::class, 'create']);
        $this->router->post('/bom/store', [\App\Modules\Bom\Controllers\BomController::class, 'store']);
        $this->router->get('/bom/show', [\App\Modules\Bom\Controllers\BomController::class, 'show']);
        $this->router->get('/bom/edit', [\App\Modules\Bom\Controllers\BomController::class, 'edit']);
        $this->router->post('/bom/update', [\App\Modules\Bom\Controllers\BomController::class, 'update']);
        $this->router->post('/bom/delete', [\App\Modules\Bom\Controllers\BomController::class, 'delete']);

        $this->router->get('/stocks', [\App\Modules\Inventory\Controllers\StockController::class, 'index']);
        $this->router->get('/stocks/create', [\App\Modules\Inventory\Controllers\StockController::class, 'create']);
        $this->router->post('/stocks/store', [\App\Modules\Inventory\Controllers\StockController::class, 'store']);
        $this->router->get('/stocks/show', [\App\Modules\Inventory\Controllers\StockController::class, 'show']);
        $this->router->get('/stocks/edit', [\App\Modules\Inventory\Controllers\StockController::class, 'edit']);
        $this->router->post('/stocks/update', [\App\Modules\Inventory\Controllers\StockController::class, 'update']);
        $this->router->post('/stocks/delete', [\App\Modules\Inventory\Controllers\StockController::class, 'delete']);

        $this->router->get('/api/auth', [\App\Modules\Auth\Controllers\AuthController::class, 'index']);
        $this->router->get('/api/customers', [\App\Modules\Customer\Controllers\CustomerApiController::class, 'index']);
        $this->router->get('/api/quotations', [\App\Modules\Quotation\Controllers\QuotationApiController::class, 'index']);
        $this->router->get('/api/orders', [\App\Modules\Order\Controllers\OrderApiController::class, 'index']);
        $this->router->get('/api/bom', [\App\Modules\Bom\Controllers\BomApiController::class, 'index']);
        $this->router->get('/api/stocks', [\App\Modules\Inventory\Controllers\StockApiController::class, 'index']);
        $this->router->get('/api/production-orders', [\App\Modules\Production\Controllers\ProductionController::class, 'index']);
        $this->router->get('/api/inventory', [\App\Modules\Inventory\Controllers\StockApiController::class, 'index']);
        $this->router->get('/api/accounting', [\App\Modules\Accounting\Controllers\AccountingController::class, 'index']);
    }
}
