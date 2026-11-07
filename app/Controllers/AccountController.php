<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Models\User;
use PDOException;
use RuntimeException;
use Throwable;

final class AccountController extends Controller
{
    public function __construct(
        Request $request,
        array $parameters = []
    ) {
        parent::__construct($request, $parameters);
    }

    /**
     * Display account profile.
     */
    public function profile(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        try {
            $userModel = new User(db());

            $user = $userModel->findById(
                (int) $this->currentUserId()
            );

            if (
                !$user ||
                ($user['account_status'] ?? '') !== 'active' ||
                empty($user['email_verified_at'])
            ) {
                $_SESSION = [];

                return Response::redirect('/login');
            }

            return $this->view(
                'pages.account.profile',
                [
                    'user' => $user,
                    'isAdmin' => $this->isAdmin(),
                ]
            );

        } catch (PDOException $exception) {
            error_log(
                '[AccountController] Profile database error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'دریافت اطلاعات حساب کاربری با مشکل مواجه شد.'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AccountController] Profile error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'خطایی هنگام دریافت اطلاعات حساب رخ داد.'
            );
        }
    }

    /**
     * Update account profile.
     */
    public function updateProfile(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        $csrfToken = (string) $this->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            return $this->error(
                'درخواست نامعتبر یا منقضی شده است.',
                '/account'
            );
        }

        $fullName = trim(
            (string) $this->post('full_name', '')
        );

        $whatsappNumber = trim(
            (string) $this->post('whatsapp_number', '')
        );

        $country = trim(
            (string) $this->post('country', '')
        );

        try {
            $userModel = new User(db());

            $updated = $userModel->updateProfile(
                (int) $this->currentUserId(),
                $fullName,
                $whatsappNumber,
                $country !== '' ? $country : null
            );

            $_SESSION['user_name'] = $fullName;

            return $this->success(
                $updated
                    ? 'اطلاعات حساب کاربری با موفقیت به‌روزرسانی شد.'
                    : 'اطلاعات حساب کاربری تغییری نکرد.',
                '/account'
            );

        } catch (RuntimeException $exception) {
            return $this->error(
                $exception->getMessage(),
                '/account'
            );

        } catch (PDOException $exception) {
            error_log(
                '[AccountController] Profile update database error: ' .
                $exception->getMessage()
            );

            return $this->error(
                'به‌روزرسانی اطلاعات با مشکل مواجه شد.',
                '/account'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AccountController] Profile update error: ' .
                $exception->getMessage()
            );

            return $this->error(
                'خطای غیرمنتظره‌ای رخ داد.',
                '/account'
            );
        }
    }

    /**
     * Change account password.
     */
    public function changePassword(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        $csrfToken = (string) $this->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token') ||
            !verify_csrf_token($csrfToken)
        ) {
            return $this->error(
                'درخواست نامعتبر یا منقضی شده است.',
                '/account'
            );
        }

        $currentPassword = (string) $this->post(
            'current_password',
            ''
        );

        $newPassword = (string) $this->post(
            'new_password',
            ''
        );

        $confirmPassword = (string) $this->post(
            'confirm_password',
            ''
        );

        if (
            $currentPassword === '' ||
            $newPassword === '' ||
            $confirmPassword === ''
        ) {
            return $this->error(
                'تمام فیلدهای رمز عبور الزامی هستند.',
                '/account'
            );
        }

        if (strlen($newPassword) < 8) {
            return $this->error(
                'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
                '/account'
            );
        }

        if ($newPassword !== $confirmPassword) {
            return $this->error(
                'رمز عبور جدید و تکرار آن یکسان نیستند.',
                '/account'
            );
        }

        if ($currentPassword === $newPassword) {
            return $this->error(
                'رمز عبور جدید باید با رمز عبور فعلی متفاوت باشد.',
                '/account'
            );
        }

        try {
            $userModel = new User(db());

            $user = $userModel->findById(
                (int) $this->currentUserId()
            );

            if (!$user) {
                return Response::redirect('/login');
            }

            if (
                !$userModel->verifyPassword(
                    $user,
                    $currentPassword
                )
            ) {
                return $this->error(
                    'رمز عبور فعلی اشتباه است.',
                    '/account'
                );
            }

            $userModel->updatePassword(
                (int) $this->currentUserId(),
                $newPassword
            );

            return $this->success(
                'رمز عبور حساب کاربری با موفقیت تغییر کرد.',
                '/account'
            );

        } catch (RuntimeException $exception) {
            return $this->error(
                $exception->getMessage(),
                '/account'
            );

        } catch (PDOException $exception) {
            error_log(
                '[AccountController] Password database error: ' .
                $exception->getMessage()
            );

            return $this->error(
                'تغییر رمز عبور با مشکل مواجه شد.',
                '/account'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AccountController] Password change error: ' .
                $exception->getMessage()
            );

            return $this->error(
                'خطای غیرمنتظره‌ای هنگام تغییر رمز عبور رخ داد.',
                '/account'
            );
        }
    }
}
