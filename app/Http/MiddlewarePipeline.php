<?php

declare(strict_types=1);

namespace App\Http;

final class MiddlewarePipeline
{
    private array $middlewares = [];

    public function add(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function addMany(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new \InvalidArgumentException(
                    'Every middleware must implement MiddlewareInterface.'
                );
            }

            $this->add($middleware);
        }

        return $this;
    }

    public function process(
        Request $request,
        callable $destination
    ): Response {
        $next = static function (Request $request) use ($destination): Response {
            $response = $destination($request);

            if (!$response instanceof Response) {
                throw new \UnexpectedValueException(
                    'The destination must return an instance of Response.'
                );
            }

            return $response;
        };

        foreach (array_reverse($this->middlewares) as $middleware) {
            $currentMiddleware = $middleware;
            $previousNext = $next;

            $next = static function (Request $request) use (
                $currentMiddleware,
                $previousNext
            ): Response {
                $response = $currentMiddleware->process(
                    $request,
                    $previousNext
                );

                if (!$response instanceof Response) {
                    throw new \UnexpectedValueException(
                        'Middleware must return an instance of Response.'
                    );
                }

                return $response;
            };
        }

        return $next($request);
    }

    public function clear(): void
    {
        $this->middlewares = [];
    }

    public function count(): int
    {
        return count($this->middlewares);
    }
}
