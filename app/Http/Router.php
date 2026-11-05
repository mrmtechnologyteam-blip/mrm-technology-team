<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];

    private array $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    public function get(string $path, callable $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): self
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable $handler): self
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable $handler): self
    {
        return $this->add('DELETE', $path, $handler);
    }

    public function options(string $path, callable $handler): self
    {
        return $this->add('OPTIONS', $path, $handler);
    }

    public function add(
        string $method,
        string $path,
        callable $handler
    ): self {
        $method = strtoupper(trim($method));

        if (!in_array($method, $this->allowedMethods, true)) {
            throw new \InvalidArgumentException(
                'Unsupported HTTP method: ' . $method
            );
        }

        $path = $this->normalizePath($path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $this->compilePath($path),
            'handler' => $handler,
        ];

        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method());
        $path = $this->normalizePath($request->path());

        $allowedMethods = [];
        $matchedRoute = null;
        $matchedParameters = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $allowedMethods[] = $route['method'];

            $methodMatches = $route['method'] === $method
                || ($method === 'HEAD' && $route['method'] === 'GET');

            if (!$methodMatches) {
                continue;
            }

            $matchedRoute = $route;

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $matchedParameters[$key] = rawurldecode($value);
                }
            }

            break;
        }

        if ($matchedRoute === null) {
            if ($allowedMethods !== []) {
                $allowedMethods = array_unique($allowedMethods);

                if (in_array('GET', $allowedMethods, true)) {
                    $allowedMethods[] = 'HEAD';
                }

                $allowedMethods[] = 'OPTIONS';

                return Response::text(
                    'Method Not Allowed',
                    405
                )->setHeader(
                    'Allow',
                    implode(', ', array_unique($allowedMethods))
                );
            }

            return Response::notFound();
        }

        /*
         * Exceptions are intentionally allowed to bubble up
         * to Kernel::handle() and ExceptionHandler.
         */

        $response = ($matchedRoute['handler'])(
            $request,
            $matchedParameters
        );

        if (!$response instanceof Response) {
            throw new \UnexpectedValueException(
                'Route handlers must return an instance of Response.'
            );
        }

        return $response;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        $path = parse_url($path, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        $path = '/' . trim($path, '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    private function compilePath(string $path): string
    {
        if ($path === '/') {
            return '#^/$#u';
        }

        $segments = explode('/', trim($path, '/'));
        $compiledSegments = [];

        foreach ($segments as $segment) {
            if (
                preg_match(
                    '/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/',
                    $segment,
                    $matches
                )
            ) {
                $parameterName = $matches[1];

                $compiledSegments[] =
                    '(?P<' . $parameterName . '>[^/]+)';

                continue;
            }

            $compiledSegments[] = preg_quote($segment, '#');
        }

        return '#^/' . implode('/', $compiledSegments) . '$#u';
    }

    public function getRoutes(): array
    {
        return array_map(
            static function (array $route): array {
                return [
                    'method' => $route['method'],
                    'path' => $route['path'],
                ];
            },
            $this->routes
        );
    }

    public function clear(): void
    {
        $this->routes = [];
    }
}
