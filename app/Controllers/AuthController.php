<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Services\AuthService;
use App\Services\SessionAuthService;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Throwable;

final class AuthController extends Controller
{
    /**
     * Display login page.
     */
    public function showLogin(): Response
    {
        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect(
                ($_SESSION['user_role'] ?? '') === 'admin'
                    ? '/admin/dashboard'
                    : '/dashboard'
            );
        }

        return $this->view('pages.auth.login');
    }

    /**
     * Process user login.
     */
    public function login(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect('/dashboard');
        }

        $csrfToken = (string) $this->request->post(
            '_csrf_token',
            ''
        );

        $csrfValid = function_exists('verify_csrf_token')
            && verify_csrf_token($csrfToken);

        if (!$csrfValid) {
            $_SESSION['errors'] = [
                'درخواست نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.'
            ];

            return Response::redirect('/login');
        }

        $email = trim(
            (string) $this->request->post('email', '')
        );

        $password = (string) $this->request->post(
            'password',
            ''
        );

        $_SESSION['old'] = [
            'email' => $email,
        ];

        try {
            $authService = new AuthService(db());

            $sessionAuth = new SessionAuthService(
                $authService
            );

            $user = $sessionAuth->login(
                $email,
                $password
            );

            unset(
                $_SESSION['old'],
                $_SESSION['errors'],
                $_SESSION['flash_message']
            );

            $_SESSION['flash_message'] =
                'با موفقیت وارد حساب خود شدید.';

            if (($user['role'] ?? '') === 'admin') {
                return Response::redirect(
                    '/admin/dashboard'
                );
            }

            return Response::redirect('/dashboard');

        } catch (PDOException $exception) {
            error_log(
                '[AuthController] Database error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارتباط با پایگاه داده برقرار نشد. لطفاً بعداً تلاش کنید.',
            ];

            return Response::redirect('/login');

        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            $_SESSION['errors'] = [
                $exception->getMessage(),
            ];

            return Response::redirect('/login');

        } catch (Throwable $exception) {
            error_log(
                '[AuthController] Login error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'خطایی هنگام ورود رخ داد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/login');
        }
    }

    /**
     * Logout user.
     */
    public function logout(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        $csrfToken = (string) $this->request->post(
            '_csrf_token',
            ''
        );

        $csrfValid = function_exists('verify_csrf_token')
            && verify_csrf_token($csrfToken);

        if (!$csrfValid) {
            return Response::text(
                'درخواست نامعتبر است. لطفاً صفحه را تازه‌سازی کنید.',
                419
            );
        }

        try {
            $authService = new AuthService(db());

            $sessionAuth = new SessionAuthService(
                $authService
            );

            $sessionAuth->logout();

            return Response::redirect('/');

        } catch (Throwable $exception) {
            error_log(
                '[AuthController] Logout error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'خروج از حساب با مشکل مواجه شد.'
            );
        }
    }
}
