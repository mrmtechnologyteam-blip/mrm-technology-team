<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Services\EmailVerificationService;
use PDOException;
use RuntimeException;
use Throwable;

final class EmailVerificationController extends Controller
{
    /**
     * نمایش صفحه راهنمای تأیید ایمیل
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
     * تأیید ایمیل با استفاده از توکن
     */
    public function verify(): Response
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

            if (
                isset($_SESSION['pending_verification']['token']) &&
                is_string($_SESSION['pending_verification']['token']) &&
                hash_equals(
                    $_SESSION['pending_verification']['token'],
                    $token
                )
            ) {
                unset($_SESSION['pending_verification']);
            }

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

        } catch (RuntimeException $exception) {

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

        } catch (Throwable $exception) {

            error_log(
                '[EmailVerificationController] Unexpected error: ' .
                $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'خطای غیرمنتظره‌ای رخ داد. لطفاً بعداً تلاش کنید.'
            ];

            return Response::redirect(
                '/verification-required'
            );
        }
    }
}
