<?php

declare(strict_types=1);

namespace App\Http;

final class Kernel
{
    private Router $router;

    public function __construct(?Router $router = null)
    {
        $this->router = $router ?? new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (\Throwable $exception) {
            error_log(
                sprintf(
                    '[Kernel Error] %s in %s:%d',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                )
            );

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
            error_log(
                sprintf(
                    '[Kernel Fatal Error] %s in %s:%d',
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                )
            );

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store');
            }

            if (
                defined('APP_DEBUG')
                && APP_DEBUG === true
                && !headers_sent()
            ) {
                echo '<h1>500</h1><pre>'
                    . htmlspecialchars(
                        $exception->getMessage(),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    )
                    . '</pre>';
            } elseif (!headers_sent()) {
                echo '<h1>500</h1>';
                echo '<p>خطای داخلی سرور رخ داده است.</p>';
            }
        }
    }
}
