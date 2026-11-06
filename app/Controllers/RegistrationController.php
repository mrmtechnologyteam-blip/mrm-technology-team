<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Services\AuthService;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Throwable;

final class RegistrationController extends Controller
{
    /**
     * Display registration page.
     */
    public function showRegister(): Response
    {
        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect('/dashboard');
        }

        return $this->view('pages.auth.register');
    }

    /**
     * Process customer registration.
     */
    public function register(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect('/dashboard');
        }

        // Validate CSRF token.
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

            return Response::redirect('/register');
        }

        $data = [
            'full_name' => trim(
                (string) $this->request->post('full_name', '')
            ),
            'email' => trim(
                (string) $this->request->post('email', '')
            ),
            'whatsapp_number' => trim(
                (string) $this->request->post('whatsapp_number', '')
            ),
            'country' => trim(
                (string) $this->request->post('country', '')
            ),
            'password' => (string) $this->request->post('password', ''),
            'password_confirmation' => (string) $this->request->post(
                'password_confirmation',
                ''
            ),
        ];

        $_SESSION['old'] = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'whatsapp_number' => $data['whatsapp_number'],
            'country' => $data['country'],
        ];

        try {
            if (
                $data['password'] !==
                $data['password_confirmation']
            ) {
                throw new InvalidArgumentException(
                    'تکرار رمز عبور مطابقت ندارد.'
                );
            }

            $authService = new AuthService(db());

            $result = $authService->register($data);

            unset(
                $_SESSION['old'],
                $_SESSION['errors']
            );

            $_SESSION['pending_verification'] = [
                'user_id' => $result['user_id'],
                'email' => $result['email'],
                'token' => $result['verification_token'],
                'created_at' => time(),
            ];

            $_SESSION['flash_message'] =
                'حساب شما ساخته شد. برای فعال‌سازی باید ایمیل خود را تأیید کنید.';

            return Response::redirect(
                '/verification-required'
            );

        } catch (PDOException $exception) {
            error_log(
                '[RegistrationController] Database error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارتباط با پایگاه داده برقرار نشد. لطفاً بعداً تلاش کنید.',
            ];

            return Response::redirect('/register');

        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            $_SESSION['errors'] = [
                $exception->getMessage(),
            ];

            return Response::redirect('/register');

        } catch (Throwable $exception) {
            error_log(
                '[RegistrationController] Registration error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ثبت‌نام با خطا مواجه شد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/register');
        }
    }
}
