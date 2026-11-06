<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Services\EmailVerificationService;
use PDOException;
use Throwable;

final class EmailVerificationController extends Controller
{
    /**
     * Display email verification instructions.
     */
    public function showRequired(): Response
    {
        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect('/dashboard');
        }

        return $this->view(
            'pages.auth.verification-required'
        );
    }

    /**
     * Display the email verification confirmation page.
     */
    public function showConfirmation(): Response
    {
        $token = trim(
            (string) $this->request->query('token', '')
        );

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            $_SESSION['errors'] = [
                'لینک تأیید ایمیل نامعتبر است.'
            ];

            return Response::redirect(
                '/verification-required'
            );
        }

        return $this->view(
            'pages.auth.verify-email',
            ['token' => $token]
        );
    }

    /**
     * Verify email through a CSRF-protected POST request.
     */
    public function verify(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
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

            return Response::redirect(
                '/verification-required'
            );
        }

        // Validate verification token.
        $token = trim(
            (string) $this->request->post('token', '')
        );

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            $_SESSION['errors'] = [
                'لینک تأیید ایمیل نامعتبر است.'
            ];

            return Response::redirect(
                '/verification-required'
            );
        }

        try {
            $verificationService = new EmailVerificationService(
                db()
            );

            $verified = $verificationService->verify($token);

            if (!$verified) {
                $_SESSION['errors'] = [
                    'لینک تأیید ایمیل نامعتبر یا منقضی شده است.'
                ];

                return Response::redirect(
                    '/verification-required'
                );
            }

            unset($_SESSION['pending_verification']);
            unset($_SESSION['errors']);

            $_SESSION['flash_message'] =
                'ایمیل شما با موفقیت تأیید شد. اکنون می‌توانید وارد حساب خود شوید.';

            return Response::redirect('/login');

        } catch (PDOException $exception) {
            error_log(
                '[EmailVerificationController] Database error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارتباط با پایگاه داده برقرار نشد. لطفاً بعداً تلاش کنید.'
            ];

            return Response::redirect(
                '/verification-required'
            );

        } catch (Throwable $exception) {
            error_log(
                '[EmailVerificationController] Verification error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'تأیید ایمیل با مشکل مواجه شد. لطفاً دوباره تلاش کنید.'
            ];

            return Response::redirect(
                '/verification-required'
            );
        }
    }
}
