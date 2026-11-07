<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Models\ContactMessage;
use PDO;
use Throwable;

final class AdminContactMessagesController extends Controller
{
    private const STATUSES = [
        'new',
        'in_progress',
        'replied',
        'closed',
    ];

    public function __construct(
        Request $request,
        array $parameters = []
    ) {
        parent::__construct($request, $parameters);
    }

    /**
     * Display contact inbox.
     */
    public function index(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        try {
            $model = new ContactMessage(db());

            $search = trim((string) $this->query('search', ''));

            $status = trim((string) $this->query('status', ''));

            $page = filter_var(
                $this->query('page', 1),
                FILTER_VALIDATE_INT
            );

            $page = $page && $page > 0 ? $page : 1;

            $perPage = 20;

            if (!in_array($status, self::STATUSES, true)) {
                $status = '';
            }

            if (mb_strlen($search, 'UTF-8') > 200) {
                $search = mb_substr(
                    $search,
                    0,
                    200,
                    'UTF-8'
                );
            }

            $filters = [
                'search' => $search,
                'status' => $status,
            ];

            $totalMessages = $model->countAll($filters);

            $totalPages = max(
                1,
                (int) ceil($totalMessages / $perPage)
            );

            $page = min($page, $totalPages);

            $offset = ($page - 1) * $perPage;

            $messages = $model->getAll(
                $filters,
                $perPage,
                $offset
            );

            $statistics = $model->getStatistics();

            return $this->view(
                'pages.admin.contact-messages.index',
                [
                    'messages' => $messages,
                    'filters' => [
                        'search' => $search,
                        'status' => $status,
                    ],
                    'statistics' => $statistics,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total_messages' => $totalMessages,
                        'total_pages' => $totalPages,
                    ],
                ]
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminContactMessagesController] Index Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'دریافت صندوق پیام‌ها با مشکل مواجه شد.'
            );
        }
    }

    /**
     * Display one contact message.
     */
    public function show(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        $messageId = filter_var(
            $this->parameter('id'),
            FILTER_VALIDATE_INT
        );

        if (!$messageId || $messageId <= 0) {
            return Response::notFound(
                'شناسه پیام معتبر نیست.'
            );
        }

        try {
            $model = new ContactMessage(db());

            $message = $model->findById((int) $messageId);

            if ($message === null) {
                return Response::notFound(
                    'پیام موردنظر پیدا نشد.'
                );
            }

            $model->markAsRead((int) $messageId);

            $message = $model->findById((int) $messageId);

            return $this->view(
                'pages.admin.contact-messages.show',
                [
                    'message' => $message,
                ]
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminContactMessagesController] Show Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'نمایش جزئیات پیام با مشکل مواجه شد.'
            );
        }
    }

    /**
     * Update message status.
     */
    public function updateStatus(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        $csrfToken = (string) $this->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token')
            || !verify_csrf_token($csrfToken)
        ) {
            return Response::forbidden(
                'درخواست نامعتبر است. صفحه را دوباره بارگذاری کنید.'
            );
        }

        $messageId = filter_var(
            $this->parameter('id'),
            FILTER_VALIDATE_INT
        );

        if (!$messageId || $messageId <= 0) {
            return $this->error(
                'شناسه پیام معتبر نیست.',
                '/admin/contact-messages'
            );
        }

        $status = trim(
            (string) $this->post('status', '')
        );

        if (!in_array($status, self::STATUSES, true)) {
            return $this->error(
                'وضعیت انتخاب‌شده معتبر نیست.',
                '/admin/contact-messages/' . (int) $messageId
            );
        }

        try {
            $adminId = $this->currentUserId();

            if ($adminId === null) {
                return $this->redirect('/login');
            }

            $model = new ContactMessage(db());

            $updated = $model->updateStatus(
                (int) $messageId,
                $adminId,
                $status
            );

            if (!$updated) {
                return $this->error(
                    'تغییر وضعیت پیام انجام نشد.',
                    '/admin/contact-messages/' . (int) $messageId
                );
            }

            return $this->success(
                'وضعیت پیام با موفقیت تغییر کرد.',
                '/admin/contact-messages/' . (int) $messageId
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminContactMessagesController] Status Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'تغییر وضعیت پیام با مشکل مواجه شد.'
            );
        }
    }

    /**
     * Save internal administrator note.
     */
    public function updateNote(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        $csrfToken = (string) $this->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token')
            || !verify_csrf_token($csrfToken)
        ) {
            return Response::forbidden(
                'درخواست نامعتبر است. صفحه را دوباره بارگذاری کنید.'
            );
        }

        $messageId = filter_var(
            $this->parameter('id'),
            FILTER_VALIDATE_INT
        );

        if (!$messageId || $messageId <= 0) {
            return $this->error(
                'شناسه پیام معتبر نیست.',
                '/admin/contact-messages'
            );
        }

        $note = trim(
            (string) $this->post('admin_note', '')
        );

        if (mb_strlen($note, 'UTF-8') > 10000) {
            return $this->error(
                'یادداشت مدیر بیش از حد طولانی است.',
                '/admin/contact-messages/' . (int) $messageId
            );
        }

        try {
            $adminId = $this->currentUserId();

            if ($adminId === null) {
                return $this->redirect('/login');
            }

            $model = new ContactMessage(db());

            $updated = $model->updateAdminNote(
                (int) $messageId,
                $adminId,
                $note
            );

            if (!$updated) {
                return $this->error(
                    'ثبت یادداشت انجام نشد.',
                    '/admin/contact-messages/' . (int) $messageId
                );
            }

            return $this->success(
                'یادداشت داخلی با موفقیت ذخیره شد.',
                '/admin/contact-messages/' . (int) $messageId
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminContactMessagesController] Note Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'ذخیره یادداشت با مشکل مواجه شد.'
            );
        }
    }

    /**
     * Delete a contact message.
     */
    public function delete(): Response
    {
        $authResponse = $this->requireAuthentication();

        if ($authResponse !== null) {
            return $authResponse;
        }

        $adminResponse = $this->requireAdmin();

        if ($adminResponse !== null) {
            return $adminResponse;
        }

        $methodResponse = $this->requirePost();

        if ($methodResponse !== null) {
            return $methodResponse;
        }

        $csrfToken = (string) $this->post(
            '_csrf_token',
            ''
        );

        if (
            !function_exists('verify_csrf_token')
            || !verify_csrf_token($csrfToken)
        ) {
            return Response::forbidden(
                'درخواست نامعتبر است. صفحه را دوباره بارگذاری کنید.'
            );
        }

        $messageId = filter_var(
            $this->parameter('id'),
            FILTER_VALIDATE_INT
        );

        if (!$messageId || $messageId <= 0) {
            return $this->error(
                'شناسه پیام معتبر نیست.',
                '/admin/contact-messages'
            );
        }

        try {
            $model = new ContactMessage(db());

            $deleted = $model->delete((int) $messageId);

            if (!$deleted) {
                return $this->error(
                    'پیام پیدا نشد یا حذف آن انجام نشد.',
                    '/admin/contact-messages'
                );
            }

            return $this->success(
                'پیام با موفقیت حذف شد.',
                '/admin/contact-messages'
            );

        } catch (Throwable $exception) {
            error_log(
                '[AdminContactMessagesController] Delete Error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'حذف پیام با مشکل مواجه شد.'
            );
        }
    }
}
