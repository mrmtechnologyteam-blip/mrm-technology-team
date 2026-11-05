<?php

declare(strict_types=1);

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| Home Page
|--------------------------------------------------------------------------
*/

$router->get('/', function (Request $request, array $parameters): Response {
    $viewPath = BASE_PATH . '/resources/views/pages/home.php';

    if (!is_file($viewPath)) {
        return Response::serverError(
            'صفحه اصلی هنوز آماده نشده است.'
        );
    }

    ob_start();

    try {
        require $viewPath;
        $content = ob_get_clean();
    } catch (Throwable $exception) {
        ob_end_clean();
        throw $exception;
    }

    return Response::html($content);
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/

$router->get('/health', function (Request $request, array $parameters): Response {
    return Response::json([
        'success' => true,
        'application' => APP_NAME,
        'status' => 'healthy',
        'timestamp' => date(DATE_ATOM),
    ]);
});

return $router;
