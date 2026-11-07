<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\RegistrationController;
use App\Controllers\EmailVerificationController;
use App\Controllers\DashboardController;
use App\Controllers\AdminDashboardController;
use App\Controllers\AdminProjectsController;
use App\Controllers\ForgotPasswordController;
use App\Controllers\ProjectRequestController;
use App\Controllers\ProjectDetailsController;
use App\Controllers\AccountController;
use App\Controllers\ContactController;
use App\Controllers\AdminContactMessagesController;
use App\Controllers\AdminSystemSettingsController;
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
        $controller = new DashboardController($request, $parameters);

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Account Profile
|--------------------------------------------------------------------------
*/

$router->get(
    '/account',
    function (Request $request, array $parameters): Response {
        $controller = new AccountController($request, $parameters);

        return $controller->profile();
    }
);

/*
|--------------------------------------------------------------------------
| Update Account Profile
|--------------------------------------------------------------------------
*/

$router->post(
    '/account/profile',
    function (Request $request, array $parameters): Response {
        $controller = new AccountController($request, $parameters);

        return $controller->updateProfile();
    }
);

/*
|--------------------------------------------------------------------------
| Change Account Password
|--------------------------------------------------------------------------
*/

$router->post(
    '/account/password',
    function (Request $request, array $parameters): Response {
        $controller = new AccountController($request, $parameters);

        return $controller->changePassword();
    }
);

/*
|--------------------------------------------------------------------------
| Project Request
|--------------------------------------------------------------------------
*/

$router->get(
    '/projects/create',
    function (Request $request, array $parameters): Response {
        $controller = new ProjectRequestController($request, $parameters);

        return $controller->create();
    }
);

$router->post(
    '/projects',
    function (Request $request, array $parameters): Response {
        $controller = new ProjectRequestController($request, $parameters);

        return $controller->store();
    }
);

/*
|--------------------------------------------------------------------------
| Project Details
|--------------------------------------------------------------------------
*/

$router->get(
    '/projects/{id}',
    function (Request $request, array $parameters): Response {
        $controller = new ProjectDetailsController($request, $parameters);

        return $controller->show();
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
        $controller = new AdminDashboardController($request, $parameters);

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Admin Projects Management
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/projects',
    function (Request $request, array $parameters): Response {
        $controller = new AdminProjectsController($request, $parameters);

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Admin Project Status Update
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/projects/{id}/status',
    function (Request $request, array $parameters): Response {
        $controller = new AdminProjectsController($request, $parameters);

        return $controller->updateStatus();
    }
);

/*
|--------------------------------------------------------------------------
| Contact Page
|--------------------------------------------------------------------------
*/

$router->get(
    '/contact',
    function (Request $request, array $parameters): Response {
        $controller = new ContactController($request, $parameters);

        return $controller->show();
    }
);

/*
|--------------------------------------------------------------------------
| Submit Contact Message
|--------------------------------------------------------------------------
*/

$router->post(
    '/contact',
    function (Request $request, array $parameters): Response {
        $controller = new ContactController($request, $parameters);

        return $controller->store();
    }
);

/*
|--------------------------------------------------------------------------
| Admin Contact Messages
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/contact-messages',
    function (Request $request, array $parameters): Response {
        $controller = new AdminContactMessagesController(
            $request,
            $parameters
        );

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Admin Contact Message Details
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/contact-messages/{id}',
    function (Request $request, array $parameters): Response {
        $controller = new AdminContactMessagesController(
            $request,
            $parameters
        );

        return $controller->show();
    }
);

/*
|--------------------------------------------------------------------------
| Update Contact Message Status
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/contact-messages/{id}/status',
    function (Request $request, array $parameters): Response {
        $controller = new AdminContactMessagesController(
            $request,
            $parameters
        );

        return $controller->updateStatus();
    }
);

/*
|--------------------------------------------------------------------------
| Update Contact Message Note
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/contact-messages/{id}/note',
    function (Request $request, array $parameters): Response {
        $controller = new AdminContactMessagesController(
            $request,
            $parameters
        );

        return $controller->updateNote();
    }
);

/*
|--------------------------------------------------------------------------
| Delete Contact Message
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/contact-messages/{id}/delete',
    function (Request $request, array $parameters): Response {
        $controller = new AdminContactMessagesController(
            $request,
            $parameters
        );

        return $controller->delete();
    }
);

/*
|--------------------------------------------------------------------------
| Admin System Settings
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/settings',
    function (Request $request, array $parameters): Response {
        $controller = new AdminSystemSettingsController(
            $request,
            $parameters
        );

        return $controller->index();
    }
);

/*
|--------------------------------------------------------------------------
| Update Admin System Settings
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/settings/update',
    function (Request $request, array $parameters): Response {
        $controller = new AdminSystemSettingsController(
            $request,
            $parameters
        );

        return $controller->update();
    }
);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$router->get(
    '/login',
    function (Request $request, array $parameters): Response {
        $controller = new AuthController($request, $parameters);

        return $controller->showLogin();
    }
);

$router->post(
    '/login',
    function (Request $request, array $parameters): Response {
        $controller = new AuthController($request, $parameters);

        return $controller->login();
    }
);

$router->get(
    '/register',
    function (Request $request, array $parameters): Response {
        $controller = new RegistrationController($request, $parameters);

        return $controller->showRegister();
    }
);

$router->post(
    '/register',
    function (Request $request, array $parameters): Response {
        $controller = new RegistrationController($request, $parameters);

        return $controller->register();
    }
);

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

$router->get(
    '/verification-required',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController($request, $parameters);

        return $controller->showRequired();
    }
);

$router->post(
    '/resend-verification',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController($request, $parameters);

        return $controller->resendVerification();
    }
);

$router->get(
    '/verify-email',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController($request, $parameters);

        return $controller->showConfirmation();
    }
);

$router->post(
    '/verify-email',
    function (Request $request, array $parameters): Response {
        $controller = new EmailVerificationController($request, $parameters);

        return $controller->verify();
    }
);

/*
|--------------------------------------------------------------------------
| Password Recovery
|--------------------------------------------------------------------------
*/

$router->get(
    '/forgot-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController($request, $parameters);

        return $controller->showForgotForm();
    }
);

$router->post(
    '/forgot-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController($request, $parameters);

        return $controller->sendResetLink();
    }
);

$router->get(
    '/reset-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController($request, $parameters);

        return $controller->showResetForm();
    }
);

$router->post(
    '/reset-password',
    function (Request $request, array $parameters): Response {
        $controller = new ForgotPasswordController($request, $parameters);

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
        $controller = new AuthController($request, $parameters);

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
