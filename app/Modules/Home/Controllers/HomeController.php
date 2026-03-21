<?php

declare(strict_types=1);

namespace App\Modules\Home\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class HomeController
{
    public function index(Request $request): Response
    {
        unset($request);

        ob_start();
        require base_path('app/Modules/Home/Views/home.php');
        $content = (string) ob_get_clean();

        return Response::html($content);
    }
}
