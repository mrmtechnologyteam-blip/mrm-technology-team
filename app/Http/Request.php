<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\BadRequestException;

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

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
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

        return preg_match(
            '/^application\/(?:[\w.+-]+\+)?json(?:\s*;|$)/i',
            $contentType
        ) === 1;
    }

    public function jsonBody(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $raw = file_get_contents('php://input');

        if ($raw === false) {
            throw new BadRequestException(
                'Unable to read request body.'
            );
        }

        if (trim($raw) === '') {
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
        } catch (\JsonException $exception) {
            throw new BadRequestException(
                'Invalid JSON request body.'
            );
        }

        if (!is_array($decoded)) {
            throw new BadRequestException(
                'JSON request body must be an object or array.'
            );
        }

        $this->json = $decoded;

        return $this->json;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(
            str_replace('-', '_', $name)
        );

        if (strtolower($name) === 'content-type') {
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
