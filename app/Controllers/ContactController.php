<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Models\ContactMessage;
use InvalidArgumentException;
use PDOException;
use Throwable;

final class ContactController extends Controller
{
    /**
     * Display contact form.
     */
    public function show(): Response
    {
        $user = [
            'full_name' => '',
            'email' => '',
            'whatsapp_number' => '',
        ];

        if ($this->isAuthenticated()) {
            $user['full_name'] = (string) (
                $_SESSION['user_name'] ?? ''
            );

            $user['email'] = (string) (
                $_SESSION['user_email'] ?? ''
            );
        }

        return $this->view(
            'pages.contact.index',
            [
                'user' => $user,
                'csrfToken' => function_exists('csrf_token')
                    ? csrf_token()
                    : '',
            ]
        );
    }

    /**
     * Store a new contact message.
     */
    public function store(): Response
    {
        $methodError = $this->requirePost();

        if ($methodError !== null) {
            return $methodError;
        }

        $csrfResponse = $this->validateCsrf();

        if ($csrfResponse !== null) {
            return $this->error(
                'درخواست نامعتبر یا منقضی شده است.',
                '/contact'
            );
        }

        $fullName = trim(
            (string) $this->post('full_name', '')
        );

        $email = trim(
            (string) $this->post('email', '')
        );

        $whatsapp = trim(
            (string) $this->post('whatsapp_number', '')
        );

        $subject = trim(
            (string) $this->post('subject', '')
        );

        $message = trim(
            (string) $this->post('message', '')
        );

        $userId = $this->currentUserId();

        try {
            $contactModel = new ContactMessage(db());

            $messageId = $contactModel->create([
                'user_id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'whatsapp_number' => $whatsapp,
                'subject' => $subject,
                'message' => $message,
            ]);

            if ($messageId <= 0) {
                throw new \RuntimeException(
                    'ذخیره پیام انجام نشد.'
                );
            }

            return $this->success(
                'پیام شما با موفقیت ثبت شد. از تماس شما سپاسگزاریم.',
                '/contact'
            );

        } catch (InvalidArgumentException $exception) {
            return $this->error(
                $exception->getMessage(),
                '/contact'
            );

        } catch (PDOException $exception) {
            error_log(
                '[ContactController::store] Database error: ' .
                $exception->getMessage()
            );

            return $this->error(
                'در ذخیره پیام شما مشکلی رخ داد. لطفاً دوباره تلاش کنید.',
                '/contact'
            );

        } catch (Throwable $exception) {
            error_log(
                '[ContactController::store] Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'در ثبت پیام شما خطای غیرمنتظره‌ای رخ داد.'
            );
        }
    }
}
