<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    public function __construct(
        private readonly string $content,
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(
            content: json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}',
            status: $status,
            headers: ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self(
            content: translate_html($content),
            status: $status,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
        );
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self(
            content: '',
            status: $status,
            headers: ['Location' => $location],
        );
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}