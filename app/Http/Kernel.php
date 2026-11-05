<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\RequestSizeMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Throwable;

final class Kernel
{
    private Router $router;

    private MiddlewarePipeline $pipeline;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->pipeline = new MiddlewarePipeline();

        $this->pipeline->addMany([
            new RequestSizeMiddleware(),
            new RateLimitMiddleware(new RateLimiter()),
            new CsrfMiddleware(),
            new SecurityHeadersMiddleware(),
        ]);
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->pipeline->process(
                $request,
                fn (Request $request): Response =>
                    $this->router->dispatch($request)
            );
        } catch (Throwable $exception) {
            error_log(
                sprintf(
                    '[Kernel] %s in %s:%d',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                )
            );

            if (defined('APP_DEBUG') && APP_DEBUG === true) {
                return Response::json([
                    'success' => false,
                    'error' => 'internal_server_error',
                    'message' => $exception->getMessage(),
                ], 500);
            }

            return Response::json([
                'success' => false,
                'error' => 'internal_server_error',
                'message' => 'خطای داخلی سرور رخ داده است.',
            ], 500);
        }
    }

    public function run(): void
    {
        $request = Request::capture();

        $response = $this->handle($request);

        $response->send();
    }

    public function router(): Router
    {
        return $this->router;
    }
}
