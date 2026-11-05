<?php

declare(strict_types=1);

namespace App\Http;

final class UrlGenerator
{
    private string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $baseUrl = $baseUrl
            ?? (defined('APP_URL') ? APP_URL : '');

        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function to(string $path = '/'): string
    {
        $path = trim($path);

        if ($path === '') {
            $path = '/';
        }

        if (
            preg_match('/[\r\n]/', $path)
            || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path)
            || str_starts_with($path, '//')
        ) {
            throw new \InvalidArgumentException(
                'Invalid URL path.'
            );
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return $this->baseUrl . $path;
    }

    public function asset(string $path): string
    {
        return $this->to('/assets/' . ltrim($path, '/'));
    }

    public function route(
        string $path,
        array $parameters = []
    ): string {
        foreach ($parameters as $name => $value) {
            $placeholder = '{' . $name . '}';

            if (!str_contains($path, $placeholder)) {
                continue;
            }

            $path = str_replace(
                $placeholder,
                rawurlencode((string) $value),
                $path
            );
        }

        if (preg_match('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', $path)) {
            throw new \InvalidArgumentException(
                'Missing route parameter.'
            );
        }

        return $this->to($path);
    }

    public function current(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (!is_string($uri)) {
            return $this->to('/');
        }

        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            $path = '/';
        }

        return $this->to($path);
    }

    public function isCurrent(string $path): bool
    {
        $currentPath = parse_url(
            $this->current(),
            PHP_URL_PATH
        );

        $targetPath = parse_url(
            $this->to($path),
            PHP_URL_PATH
        );

        return $currentPath === $targetPath;
    }
}
