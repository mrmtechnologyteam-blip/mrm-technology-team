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
    /**
     * نمایش فرم درخواست بازیابی رمز عبور
     */
    public function showForgotForm(): Response
    {
        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        return $this->view('pages.auth.forgot-password');
    }

    /**
     * ارسال لینک بازیابی رمز عبور
     */
    public function sendResetLink(): Response
    {
        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        $email = trim(
            (string) $this->post('email', '')
        );

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

            /*
             * برای جلوگیری از شناسایی ایمیل‌های ثبت‌شده،
             * در همه حالت‌ها پیام یکسان نمایش داده می‌شود.
             */
            if (
                $user &&
                ($user['account_status'] ?? '') === 'active' &&
                !empty($user['email_verified_at'])
            ) {
                $resetService = new PasswordResetService($db);

                $token = $resetService->issueToken(
                    (int) $user['id'],
                    30
                );

                $appUrl = rtrim(
                    (string) app_env('APP_URL', ''),
                    '/'
                );

                if ($appUrl === '') {
                    throw new RuntimeException(
                        'آدرس اصلی وب‌سایت در تنظیمات تعریف نشده است.'
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
                                    href="' . htmlspecialchars(
                                        $resetUrl,
                                        ENT_QUOTES | ENT_SUBSTITUTE,
                                        'UTF-8'
                                    ) . '"
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

                $mailService->send(
                    $email,
                    'بازیابی رمز عبور | MRM Technology Team',
                    $htmlBody,
                    $plainBody
                );
            }

            unset(
                $_SESSION['old'],
                $_SESSION['errors']
            );

            $_SESSION['flash_message'] =
                'اگر این ایمیل متعلق به یک حساب فعال باشد، لینک بازیابی رمز عبور برای آن ارسال خواهد شد.';

            return Response::redirect('/forgot-password');

        } catch (PDOException $exception) {

            error_log(
                '[ForgotPasswordController] Database error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'در حال حاضر پردازش درخواست امکان‌پذیر نیست. لطفاً بعداً تلاش کنید.',
            ];

            return Response::redirect('/forgot-password');

        } catch (RuntimeException $exception) {

            error_log(
                '[ForgotPasswordController] Service error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'ارسال درخواست با مشکل مواجه شد. لطفاً تنظیمات سرویس ایمیل را بررسی کنید.',
            ];

            return Response::redirect('/forgot-password');

        } catch (Throwable $exception) {

            error_log(
                '[ForgotPasswordController] Unexpected error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'خطای غیرمنتظره‌ای رخ داد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect('/forgot-password');
        }
    }

    /**
     * نمایش فرم تعیین رمز عبور جدید
     */
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

    /**
     * ذخیره رمز عبور جدید
     */
    public function resetPassword(): Response
    {
        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        if ($this->isAuthenticated()) {
            return Response::redirect('/dashboard');
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

            $_SESSION['errors'] = [
                'رمز عبور باید حداقل ۸ نویسه داشته باشد و شامل حروف بزرگ، حروف کوچک و عدد باشد.',
            ];

            return Response::redirect(
                '/reset-password?token=' . rawurlencode($token)
            );

        } catch (Throwable $exception) {

            error_log(
                '[ForgotPasswordController] Reset error: '
                . $exception->getMessage()
            );

            $_SESSION['errors'] = [
                'خطای غیرمنتظره‌ای رخ داد. لطفاً دوباره تلاش کنید.',
            ];

            return Response::redirect(
                '/forgot-password'
            );
        }
    }
}
