<?php

declare(strict_types=1);

use App\Core\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Application(
    basePath: dirname(__DIR__),
);

$app->bootstrap();
$app->run();
