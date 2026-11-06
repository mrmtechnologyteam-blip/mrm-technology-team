<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\MailService;
use PDOException;
use RuntimeException;
use Throwable;

final class EmailVerificationController extends Controller
{
    public function showRequired(): Response
    {
        if (!empty($_SESSION['logged_in'])) {
            return Response::redirect('/dashboard');
        }

        return $this->view('pages.auth.verification-required');
    }

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

            return Response::redirect('/verification-required');
        }

        return $this->view(
            'pages.auth.verify-email',
            ['token' => $token]
        );
    }

    public function resendVerification(): Response
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

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            $_SESSION['errors'] = [
                'درخواست نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.'
            ];

            return Response::redirect('/verification-required');
        }

        $pending = $_SESSION['pending_verification'] ?? null;

        if (
            !is_array($pending) ||
            empty($pending['user_id']) ||
            empty($pending['email'])
        ) {
            $_SESSION['errors'] = [
                'اطلاعات ثبت‌نام پیدا نشد. لطفاً دوباره ثبت‌نام کنید.'
            ];

            return Response::redirect('/register');
        }

        $userId = (int) $pending['user_id'];
        $email = trim((string) $pending['email']);

        if (
            $userId <= 0 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $_SESSION['errors'] = [
                'اطلاعات حساب کاربری معتبر نیست.'
            ];

            return Response::redirect('/verification-required');
        }

        try {
            $db = db();

            $userModel = new User($db);
            $user = $userModel->findById($userId);

            if (
                !$user ||
                strtolower((string) $user['email']) !== strtolower($email)
            ) {
                throw new RuntimeException(
                    'اطلاعات حساب کاربری مطابقت ندارد.'
                );
            }

            if (!empty($user['email_verified_at'])) {
                unset($_SESSION['pending_verification']);

                $_SESSION['flash_message'] =
                    'ایمیل این حساب قبلاً تأیید شده است. اکنون می‌توانید وارد شوید.';

                return Response::redirect('/login');
            }

            if (($user['account_status'] ?? '') !== 'pending') {
                throw new RuntimeException(
                    'وضعیت حساب برای تأیید ایمیل معتبر نیست.'
                );
            }

            $appUrl = rtrim(
                (string) app_env('APP_URL', ''),
                '/'
            );

            if (
                $appUrl === '' ||
                !filter_var($appUrl, FILTER_VALIDATE_URL) ||
                !in_array(
                    strtolower((string) parse_url($appUrl, PHP_URL_SCHEME)),
                    ['http', 'https'],
                    true
                )
            ) {
                throw new RuntimeException(
                    'APP_URL configuration is invalid.'
                );
            }

            $verificationService = new EmailVerificationService($db);

            // توکن قبلی تا زمان ارسال موفق ایمیل حفظ می‌شود.
            $token = $verificationService->issueToken(
                $userId,
                60,
                false
            );

            $verificationUrl = $appUrl
                . '/verify-email?token='
                . rawurlencode($token);

            $safeName = htmlspecialchars(
                (string) $user['full_name'],
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $safeUrl = htmlspecialchars(
                $verificationUrl,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $htmlBody = '
                <!DOCTYPE html>
                <html lang="fa" dir="rtl">
                <head>
                    <meta charset="UTF-8">
                </head>
                <body style="
                    margin:0;
                    padding:30px;
                    background:#05000D;
                    color:#ffffff;
                    font-family:Arial,sans-serif;
                    direction:rtl;
                    text-align:right;
                ">
                    <div style="
                        max-width:600px;
                        margin:auto;
                        padding:30px;
                        background:#140D23;
                        border:1px solid #49305d;
                        border-radius:15px;
                    ">
                        <h2 style="color:#D45CFF;">
                            MRM Technology Team
                        </h2>

                        <h3>تأیید آدرس ایمیل</h3>

                        <p style="line-height:2;color:#e8dff0;">
                            سلام ' . $safeName . '،
                        </p>

                        <p style="line-height:2;color:#e8dff0;">
                            برای فعال‌سازی حساب کاربری خود،
                            روی دکمه زیر کلیک کنید.
                        </p>

                        <p style="text-align:center;margin:30px 0;">
                            <a
                                href="' . $safeUrl . '"
                                style="
                                    display:inline-block;
                                    padding:14px 25px;
                                    background:#9B20FF;
                                    color:#ffffff;
                                    text-decoration:none;
                                    border-radius:8px;
                                    font-weight:bold;
                                "
                            >
                                تأیید ایمیل
                            </a>
                        </p>

                        <p style="line-height:2;color:#c7b8d5;">
                            این لینک تا ۶۰ دقیقه اعتبار دارد.
                            اگر شما درخواست ثبت‌نام نداده‌اید،
                            این پیام را نادیده بگیرید.
                        </p>

                        <hr style="border:0;border-top:1px solid #352044;">

                        <p style="color:#9b8ca8;font-size:12px;">
                            MRM Technology Team
                        </p>
                    </div>
                </body>
                </html>
            ';

            $plainBody =
                "سلام {$user['full_name']}\n\n"
                . "برای تأیید ایمیل خود از لینک زیر استفاده کنید:\n"
                . $verificationUrl
                . "\n\nاین لینک تا ۶۰ دقیقه اعتبار دارد.\n"
                . "MRM Technology Team";

            $mailService = new MailService();

            $sent = $mailService->send(
                $email,
                'تأیید ایمیل | MRM Technology Team',
                $htmlBody,
                $plainBody
            );

            if ($sent !== true) {
                throw new RuntimeException(
                    'Verification email delivery failed.'
                );
            }

            // فقط پس از ارسال موفق، توکن‌های قبلی حذف می‌شوند.
            try {
                $verificationService->cleanupOtherTokens(
                    $userId,
                    $token
                );
            } catch (Throwable $cleanupException) {
                error_log(
                    '[EmailVerificationController] Token cleanup failed: '
                    . $cleanupException->getMessage()
                );
            }

            $_SESSION['pending_verification']['token'] = $token;

            $_SESSION['flash_message'] =
                'لینک تأیید ایمیل با موفقیت ارسال شد. صندوق ورودی و پوشه Spam را بررسی کنید.';

            return Response::redirect('/verification-required');

        } catch (Throwable $exception) {
            error_log(
                '[EmailVerificationController] Resend failed: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارسال ایمیل تأیید انجام نشد. تنظیمات سرویس ایمیل را بررسی کنید.'
            ];

            return Response::redirect('/verification-required');
        }
    }

    public function verify(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        $csrfToken = (string) $this->request->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            $_SESSION['errors'] = [
                'درخواست نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.'
            ];

            return Response::redirect('/verification-required');
        }

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

            return Response::redirect('/verification-required');
        }

        try {
            $verificationService = new EmailVerificationService(db());

            $verified = $verificationService->verify($token);

            if (!$verified) {
                $_SESSION['errors'] = [
                    'لینک تأیید ایمیل نامعتبر یا منقضی شده است.'
                ];

                return Response::redirect('/verification-required');
            }

            unset(
                $_SESSION['pending_verification'],
                $_SESSION['errors']
            );

            $_SESSION['flash_message'] =
                'ایمیل شما با موفقیت تأیید شد. اکنون می‌توانید وارد حساب خود شوید.';

            return Response::redirect('/login');

        } catch (PDOException $exception) {
            error_log(
                '[EmailVerificationController] Database error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارتباط با پایگاه داده برقرار نشد. لطفاً بعداً تلاش کنید.'
            ];

            return Response::redirect('/verification-required');

        } catch (Throwable $exception) {
            error_log(
                '[EmailVerificationController] Verification error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'تأیید ایمیل با مشکل مواجه شد. لطفاً دوباره تلاش کنید.'
            ];

            return Response::redirect('/verification-required');
        }
    }
}
