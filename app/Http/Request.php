<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    private array $query;
    private array $body;
    private array $files;
    private array $server;
    private ?array $json = null;

    public function __construct(
        array $query,
        array $body,
        array $files,
        array $server
    ) {
        $this->query = $query;
        $this->body = $body;
        $this->files = $files;
        $this->server = $server;
    }

    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER
        );
    }

    public function method(): string
    {
        return strtoupper(
            (string) ($this->server['REQUEST_METHOD'] ?? 'GET')
        );
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function input(
        string $key,
        mixed $default = null
    ): mixed {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    public function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->query[$key] ?? $default;
    }

    public function post(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->body[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    public function all(): array
    {
        if ($this->isJsonRequest()) {
            return $this->jsonBody();
        }

        return $this->body;
    }

    public function queryAll(): array
    {
        return $this->query;
    }

    public function postAll(): array
    {
        return $this->body;
    }

    public function filesAll(): array
    {
        return $this->files;
    }

    public function isJsonRequest(): bool
    {
        $contentType = strtolower(
            trim((string) (
                $this->server['CONTENT_TYPE'] ??
                $this->server['HTTP_CONTENT_TYPE'] ??
                ''
            ))
        );

        return str_contains($contentType, 'application/json');
    }

    public function jsonBody(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            $this->json = [];
            return $this->json;
        }

        try {
            $decoded = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            $this->json = [];
            return $this->json;
        }

        $this->json = is_array($decoded) ? $decoded : [];

        return $this->json;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(
            str_replace('-', '_', $name)
        );

        if ($name === 'Content-Type') {
            $key = 'CONTENT_TYPE';
        }

        $value = $this->server[$key] ?? null;

        return is_string($value) ? trim($value) : null;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');

        if (
            $authorization === null ||
            !preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)
        ) {
            return null;
        }

        return trim($matches[1]);
    }

    public function ip(): ?string
    {
        $ip = $this->server['REMOTE_ADDR'] ?? null;

        if (
            !is_string($ip) ||
            filter_var($ip, FILTER_VALIDATE_IP) === false
        ) {
            return null;
        }

        return $ip;
    }

    public function userAgent(): ?string
    {
        $agent = $this->server['HTTP_USER_AGENT'] ?? null;

        if (!is_string($agent)) {
            return null;
        }

        return substr($agent, 0, 500);
    }

    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');

        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return '/' . ltrim($path, '/');
    }

    public function wantsJson(): bool
    {
        $accept = strtolower(
            (string) ($this->server['HTTP_ACCEPT'] ?? '')
        );

        return str_contains($accept, 'application/json');
    }
}
