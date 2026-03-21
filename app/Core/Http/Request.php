<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
    ) {
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $rawPath = parse_url($uri, PHP_URL_PATH) ?: '/';

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        $path = $rawPath;
        if ($scriptDir !== '' && $scriptDir !== '.' && str_starts_with($rawPath, $scriptDir)) {
            $path = substr($rawPath, strlen($scriptDir));
        }

        $path = $path === false || $path === '' ? '/' : $path;
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        $body = $_POST;
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($body['_method']) && is_string($body['_method'])) {
            $spoofed = strtoupper(trim($body['_method']));
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofed;
            }
            unset($body['_method']);
        }

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            body: $body,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }
}
