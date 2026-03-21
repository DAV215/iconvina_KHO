<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Throwable;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $path, array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        try {
            $handler = $this->routes[$request->method()][$request->path()] ?? null;

            if ($handler === null) {
                if ($this->expectsJson($request)) {
                    return Response::json([
                        'message' => 'Không tìm thấy đường dẫn.',
                        'path' => $request->path(),
                    ], 404);
                }

                session_flash('error', 'Không tìm thấy đường dẫn.');

                return Response::redirect($this->backLocation());
            }

            [$controllerClass, $method] = $handler;
            $controller = $this->container->get($controllerClass);
            $result = $controller->{$method}($request);

            if ($result instanceof Response) {
                return $result;
            }

            return Response::json($result);
        } catch (HttpException $exception) {
            if ($this->expectsJson($request)) {
                return Response::json([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->context()['errors'] ?? null,
                ], $exception->status());
            }

            session_flash('error', $exception->getMessage());

            return Response::redirect($this->backLocation());
        } catch (Throwable $exception) {
            if ($this->expectsJson($request)) {
                return Response::json([
                    'message' => 'Đã xảy ra lỗi hệ thống.',
                    'error' => $exception->getMessage(),
                ], 500);
            }

            session_flash('error', 'Đã xảy ra lỗi hệ thống.');

            return Response::redirect($this->backLocation());
        }
    }

    private function expectsJson(Request $request): bool
    {
        return str_starts_with($request->path(), '/api');
    }

    private function backLocation(): string
    {
        $fallback = app_url('/');
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        if (!is_string($referer) || trim($referer) === '') {
            return $fallback;
        }

        $refererPath = (string) parse_url($referer, PHP_URL_PATH);
        $refererQuery = (string) parse_url($referer, PHP_URL_QUERY);
        $currentPath = (string) parse_url($requestUri, PHP_URL_PATH);
        $currentQuery = (string) parse_url($requestUri, PHP_URL_QUERY);

        if ($refererPath === $currentPath && $refererQuery === $currentQuery) {
            return $fallback;
        }

        return $referer;
    }
}