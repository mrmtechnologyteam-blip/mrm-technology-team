<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\RegistrationController;
use App\Controllers\EmailVerificationController;
use App\Controllers\DashboardController;
use App\Controllers\AdminDashboardController;
use App\Controllers\ForgotPasswordController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| Home Page
|--------------------------------------------------------------------------
*/

$router->get(
    '/',
    function (Request $request, array $parameters): Response {
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
    }
);

/*
|--------------------------------------------------------------------------
| Customer Dashboard
|--------------------------------------------------------------------------
*/

$router->get(
    '/dashboard',
    function (Request $request, array $parameters): Response {
        $controller = new DashboardController(
            $request,
            $parameters
        );

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/dashboard',
    function (Request $request, array $parameters): Response {
        $controller = new AdminDashboardController(
            $request,
            $parameters
        );

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login page
$router->get(
    '/login',
    function (Request $request, array $parameters): Response {
        $controller = new AuthController(
            $request,
            $parameters
        );

        return $controller->showLogin();
    }
);

// Process login
$router->post(
    '/login',
    function (Request $request, array $parameters): Response {
        $controller = new AuthController(
            $request,
            $parameters
        );

        return $controller->login();
    }
);

// Registration page
$router->get(
    '/register',
    function (Request $request, array $parameters): Response {
        $controller = new RegistrationController(
            $request,
            $parameters
        );

        return $controller->showRegister();
    }
);

// Process registration
$router->post(
    '/register',
    function (Request $request, array $parameters): Response {
        $controller = new RegistrationController(
            $request,
            $parameters
        );

        return $controller->register();
    }
);

// Email verification information page
$router->get(
    '/verification-required',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController(
            $request,
            $parameters
        );

        return $controller->showRequired();
    }
);

// Verify email using token
$router->get(
    '/verify-email',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController(
            $request,
            $parameters
        );

        return $controller->verify();
    }
);

/*
|--------------------------------------------------------------------------
| Password Recovery
|--------------------------------------------------------------------------
*/

// Forgot password page
$router->get(
    '/forgot-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController(
            $request,
            $parameters
        );

        return $controller->showForgotForm();
    }
);

// Send password reset link
$router->post(
    '/forgot-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController(
            $request,
            $parameters
        );

        return $controller->sendResetLink();
    }
);

// Reset password page
$router->get(
    '/reset-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController(
            $request,
            $parameters
        );

        return $controller->showResetForm();
    }
);

// Save new password
$router->post(
    '/reset-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController(
            $request,
            $parameters
        );

        return $controller->resetPassword();
    }
);

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

$router->post(
    '/logout',
    function (Request $request, array $parameters): Response {
        $controller = new AuthController(
            $request,
            $parameters
        );

        return $controller->logout();
    }
);

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/

$router->get(
    '/health',
    function (Request $request, array $parameters): Response {
        return Response::json([
            'success' => true,
            'application' => APP_NAME,
            'status' => 'healthy',
            'timestamp' => date(DATE_ATOM),
        ]);
    }
);

return $router;
