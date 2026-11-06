<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Models\User;
use App\Services\MailService;
use App\Services\PasswordResetService;
use PDOException;
use RuntimeException;
use Throwable;

final class ForgotPasswordController extends Controller
{
    public function showForgotForm(): Response
    {
        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        return $this->view('pages.auth.forgot-password');
    }

    public function sendResetLink(): Response
    {
        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        $csrfToken = (string) $this->post('_csrf_token', '');

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            $_SESSION['errors'] = [
                'درخواست نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/forgot-password');
        }

        $email = trim((string) $this->post('email', ''));

        $_SESSION['old'] = [
            'email' => $email,
        ];

        if (
            $email === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $_SESSION['errors'] = [
                'لطفاً یک آدرس ایمیل معتبر وارد کنید.',
            ];

            return Response::redirect('/forgot-password');
        }

        try {
            $db = db();

            $userModel = new User($db);
            $user = $userModel->findByEmail($email);

            if (
                $user &&
                ($user['account_status'] ?? '') === 'active' &&
                !empty($user['email_verified_at'])
            ) {
                $resetService = new PasswordResetService($db);

                // توکن قبلی تا ارسال موفق ایمیل حفظ می‌شود.
                $token = $resetService->issueToken(
                    (int) $user['id'],
                    30,
                    false
                );

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

                $resetUrl = $appUrl
                    . '/reset-password?token='
                    . rawurlencode($token);

                $safeName = htmlspecialchars(
                    (string) $user['full_name'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );

                $safeResetUrl = htmlspecialchars(
                    $resetUrl,
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

                            <h3>بازیابی رمز عبور</h3>

                            <p style="line-height:2;color:#e8dff0;">
                                سلام ' . $safeName . '،
                            </p>

                            <p style="line-height:2;color:#e8dff0;">
                                برای تنظیم رمز عبور جدید، روی دکمه زیر کلیک کنید.
                            </p>

                            <p style="text-align:center;margin:30px 0;">
                                <a
                                    href="' . $safeResetUrl . '"
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
                                    تنظیم رمز عبور جدید
                                </a>
                            </p>

                            <p style="line-height:2;color:#c7b8d5;">
                                این لینک تا ۳۰ دقیقه اعتبار دارد.
                                اگر شما درخواست بازیابی رمز عبور نداده‌اید،
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
                    . "برای بازیابی رمز عبور از لینک زیر استفاده کنید:\n"
                    . $resetUrl
                    . "\n\nاین لینک تا ۳۰ دقیقه اعتبار دارد.\n"
                    . "MRM Technology Team";

                $mailService = new MailService();

                $sent = $mailService->send(
                    $email,
                    'بازیابی رمز عبور | MRM Technology Team',
                    $htmlBody,
                    $plainBody
                );

                if ($sent !== true) {
                    throw new RuntimeException(
                        'Password reset email delivery failed.'
                    );
                }

                // فقط پس از ارسال موفق ایمیل، توکن‌های قبلی حذف می‌شوند.
                try {
                    $resetService->cleanupOtherTokens(
                        (int) $user['id'],
                        $token
                    );
                } catch (Throwable $cleanupException) {
                    error_log(
                        '[ForgotPasswordController] Token cleanup failed: '
                        . $cleanupException->getMessage()
                    );
                }
            }

            unset(
                $_SESSION['old'],
                $_SESSION['errors']
            );

            $_SESSION['flash_message'] =
                'اگر این ایمیل متعلق به یک حساب فعال باشد، لینک بازیابی رمز عبور برای آن ارسال خواهد شد.';

            return Response::redirect('/forgot-password');

        } catch (Throwable $exception) {
            error_log(
                '[ForgotPasswordController] Password recovery request failed: '
                . $exception->getMessage()
            );

            unset($_SESSION['old']);

            $_SESSION['flash_message'] =
                'اگر این ایمیل متعلق به یک حساب فعال باشد، لینک بازیابی رمز عبور برای آن ارسال خواهد شد.';

            return Response::redirect('/forgot-password');
        }
    }

    public function showResetForm(): Response
    {
        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        $token = trim(
            (string) $this->query('token', '')
        );

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            $_SESSION['errors'] = [
                'لینک بازیابی رمز عبور نامعتبر است.',
            ];

            return Response::redirect('/forgot-password');
        }

        return $this->view(
            'pages.auth.reset-password',
            [
                'token' => $token,
            ]
        );
    }

    public function resetPassword(): Response
    {
        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        $csrfToken = (string) $this->post('_csrf_token', '');

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            $_SESSION['errors'] = [
                'درخواست نامعتبر یا منقضی شده است. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/forgot-password');
        }

        $token = trim(
            (string) $this->post('token', '')
        );

        $password = (string) $this->post(
            'password',
            ''
        );

        $passwordConfirmation = (string) $this->post(
            'password_confirmation',
            ''
        );

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            $_SESSION['errors'] = [
                'لینک بازیابی رمز عبور نامعتبر است.',
            ];

            return Response::redirect('/forgot-password');
        }

        if (
            strlen($password) < 8 ||
            strlen($password) > 128 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            $_SESSION['errors'] = [
                'رمز عبور باید بین ۸ تا ۱۲۸ نویسه باشد و شامل حروف بزرگ انگلیسی، حروف کوچک انگلیسی و عدد باشد.',
            ];

            return Response::redirect(
                '/reset-password?token=' . rawurlencode($token)
            );
        }

        if ($password !== $passwordConfirmation) {
            $_SESSION['errors'] = [
                'تکرار رمز عبور مطابقت ندارد.',
            ];

            return Response::redirect(
                '/reset-password?token=' . rawurlencode($token)
            );
        }

        try {
            $resetService = new PasswordResetService(db());

            $reset = $resetService->resetPassword(
                $token,
                $password
            );

            if (!$reset) {
                $_SESSION['errors'] = [
                    'لینک بازیابی نامعتبر یا منقضی شده است. دوباره درخواست دهید.',
                ];

                return Response::redirect('/forgot-password');
            }

            unset(
                $_SESSION['errors'],
                $_SESSION['old']
            );

            $_SESSION['flash_message'] =
                'رمز عبور شما با موفقیت تغییر کرد. اکنون می‌توانید وارد حساب خود شوید.';

            return Response::redirect('/login');

        } catch (PDOException $exception) {
            error_log(
                '[ForgotPasswordController] Database error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ذخیره رمز عبور با مشکل مواجه شد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect(
                '/reset-password?token=' . rawurlencode($token)
            );

        } catch (RuntimeException $exception) {
            error_log(
                '[ForgotPasswordController] Password reset service error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'تغییر رمز عبور انجام نشد. ممکن است لینک منقضی شده باشد. دوباره درخواست بازیابی دهید.',
            ];

            return Response::redirect('/forgot-password');

        } catch (Throwable $exception) {
            error_log(
                '[ForgotPasswordController] Unexpected reset error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'خطای غیرمنتظره‌ای رخ داد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/forgot-password');
        }
    }
}
