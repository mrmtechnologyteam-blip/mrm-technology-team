<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\RequestSizeMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;

final class Kernel
{
    private Router $router;

    private MiddlewarePipeline $pipeline;

    public function __construct(
        ?Router $router = null,
        ?MiddlewarePipeline $pipeline = null
    ) {
        $this->router = $router ?? new Router();

        if ($pipeline !== null) {
            $this->pipeline = $pipeline;
        } else {
            $this->pipeline = new MiddlewarePipeline();

            $this->pipeline
                ->add(new RequestSizeMiddleware())
                ->add(new CsrfMiddleware())
                ->add(new SecurityHeadersMiddleware());
        }
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function pipeline(): MiddlewarePipeline
    {
        return $this->pipeline;
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->pipeline->process(
                $request,
                fn (Request $request): Response =>
                    $this->router->dispatch($request)
            );
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[Kernel Error] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            if (defined('APP_DEBUG') && APP_DEBUG === true) {
                return Response::serverError(
                    'خطا: ' . $exception->getMessage()
                );
            }

            return Response::serverError();
        }
    }

    public function run(): void
    {
        try {
            $request = Request::capture();
            $response = $this->handle($request);
            $response->send();
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[Kernel Fatal Error] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store');

                if (defined('APP_DEBUG') && APP_DEBUG === true) {
                    echo '<h1>500</h1><pre>'
                        . htmlspecialchars(
                            $exception->getMessage(),
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        )
                        . '</pre>';
                } else {
                    echo '<h1>500</h1>';
                    echo '<p>خطای داخلی سرور رخ داده است.</p>';
                }
            }
        }
    }
}
