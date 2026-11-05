<?php

declare(strict_types=1);

namespace App\Http;

use App\Services\FlashMessageService;

abstract class Controller
{
    protected Request $request;

    protected array $parameters = [];

    protected ?FlashMessageService $flash = null;

    public function __construct(
        Request $request,
        array $parameters = []
    ) {
        $this->request = $request;
        $this->parameters = $parameters;
    }

    protected function view(
        string $view,
        array $data = [],
        int $statusCode = 200
    ): Response {
        $viewPath = dirname(__DIR__, 2)
            . '/resources/views/'
            . str_replace('.', '/', $view)
            . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException(
                'View not found: ' . $view
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $viewPath;
            $content = ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        return Response::html($content, $statusCode);
    }

    protected function json(
        array $data,
        int $statusCode = 200
    ): Response {
        return Response::json($data, $statusCode);
    }

    protected function redirect(
        string $location,
        int $statusCode = 302
    ): Response {
        return Response::redirect($location, $statusCode);
    }

    protected function back(
        string $fallback = '/'
    ): Response {
        $referer = $this->request->header('Referer');

        if (
            $referer !== null
            && $referer !== ''
        ) {
            $refererPath = parse_url(
                $referer,
                PHP_URL_PATH
            );

            $refererHost = parse_url(
                $referer,
                PHP_URL_HOST
            );

            $appHost = parse_url(
                defined('APP_URL') ? APP_URL : '',
                PHP_URL_HOST
            );

            if (
                is_string($refererPath)
                && (
                    $refererHost === null
                    || $appHost === null
                    || strcasecmp($refererHost, $appHost) === 0
                )
            ) {
                return $this->redirect($refererPath);
            }
        }

        return $this->redirect($fallback);
    }

    protected function success(
        string $message,
        string $location = '/'
    ): Response {
        $this->flash()->success($message);

        return $this->redirect($location);
    }

    protected function error(
        string $message,
        string $location = '/'
    ): Response {
        $this->flash()->error($message);

        return $this->redirect($location);
    }

    protected function warning(
        string $message,
        string $location = '/'
    ): Response {
        $this->flash()->warning($message);

        return $this->redirect($location);
    }

    protected function info(
        string $message,
        string $location = '/'
    ): Response {
        $this->flash()->info($message);

        return $this->redirect($location);
    }

    protected function flash(): FlashMessageService
    {
        if ($this->flash === null) {
            $this->flash = new FlashMessageService();
        }

        return $this->flash;
    }

    protected function parameter(
        string $name,
        mixed $default = null
    ): mixed {
        return $this->parameters[$name] ?? $default;
    }

    protected function input(
        string $name,
        mixed $default = null
    ): mixed {
        return $this->request->input($name, $default);
    }

    protected function query(
        string $name,
        mixed $default = null
    ): mixed {
        return $this->request->query($name, $default);
    }

    protected function post(
        string $name,
        mixed $default = null
    ): mixed {
        return $this->request->post($name, $default);
    }

    protected function requirePost(): ?Response
    {
        if (!$this->request->isMethod('POST')) {
            return Response::text(
                'Method Not Allowed',
                405
            )->setHeader('Allow', 'POST');
        }

        return null;
    }

    protected function requireJson(): ?Response
    {
        if (!$this->request->isJsonRequest()) {
            return Response::json(
                [
                    'success' => false,
                    'message' => 'درخواست باید از نوع JSON باشد.',
                ],
                415
            );
        }

        return null;
    }

    protected function validateCsrf(): ?Response
    {
        if (
            !function_exists('verify_csrf_token')
            || !verify_csrf_token(
                (string) $this->request->input('_csrf_token', '')
            )
        ) {
            return Response::json(
                [
                    'success' => false,
                    'message' => 'درخواست نامعتبر یا منقضی شده است.',
                ],
                419
            );
        }

        return null;
    }

    protected function currentUserId(): ?int
    {
        if (
            !isset($_SESSION['user']['id'])
            || !is_numeric($_SESSION['user']['id'])
        ) {
            return null;
        }

        $id = (int) $_SESSION['user']['id'];

        return $id > 0 ? $id : null;
    }

    protected function isAuthenticated(): bool
    {
        return $this->currentUserId() !== null
            && !empty($_SESSION['logged_in']);
    }

    protected function isAdmin(): bool
    {
        return $this->isAuthenticated()
            && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    protected function requireAuthentication(): ?Response
    {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        return null;
    }

    protected function requireAdmin(): ?Response
    {
        if (!$this->isAdmin()) {
            return Response::forbidden();
        }

        return null;
    }
}
