<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Controller;
use App\Http\Response;
use App\Models\Project;
use App\Services\ProjectQuestionnaireService;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class ProjectRequestController extends Controller
{
    /**
     * نمایش فرم ثبت درخواست پروژه
     */
    public function create(): Response
    {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        if ($this->isAdmin()) {
            return $this->redirect('/admin/dashboard');
        }

        try {
            $db = db();

            $userStmt = $db->prepare(
                "SELECT id
                 FROM users
                 WHERE id = :id
                   AND role = 'customer'
                   AND account_status = 'active'
                   AND email_verified_at IS NOT NULL
                 LIMIT 1"
            );

            $userStmt->execute([
                'id' => $this->currentUserId(),
            ]);

            if (!$userStmt->fetchColumn()) {
                return $this->redirect('/verification-required');
            }

            $categoryStmt = $db->query(
                "SELECT id, name, description
                 FROM project_categories
                 WHERE is_active = 1
                 ORDER BY id ASC"
            );

            $categories = $categoryStmt->fetchAll();

            $questionStmt = $db->query(
                "SELECT
                    id,
                    category_id,
                    section_number,
                    question_number,
                    question_text,
                    question_type,
                    options_json,
                    parent_question_id,
                    condition_value,
                    is_required
                 FROM project_questions
                 WHERE is_active = 1
                 ORDER BY
                    section_number ASC,
                    question_number ASC,
                    id ASC"
            );

            $questions = $questionStmt->fetchAll();

            $groupedQuestions = [];

            foreach ($questions as $question) {
                $categoryKey = $question['category_id'] === null
                    ? 'general'
                    : (string) $question['category_id'];

                $sectionNumber = (int) $question['section_number'];

                $question['id'] = (int) $question['id'];

                $question['parent_question_id'] =
                    $question['parent_question_id'] === null
                        ? null
                        : (int) $question['parent_question_id'];

                $question['is_required'] =
                    (bool) $question['is_required'];

                $question['options'] = [];

                if ($question['options_json'] !== null) {
                    $options = json_decode(
                        $question['options_json'],
                        true
                    );

                    if (is_array($options)) {
                        $question['options'] = $options;
                    }
                }

                $groupedQuestions[$categoryKey][$sectionNumber][] =
                    $question;
            }

            return $this->view(
                'pages.projects.create',
                [
                    'categories' => $categories,
                    'questions' => $groupedQuestions,
                    'csrfToken' => function_exists('csrf_token')
                        ? csrf_token()
                        : '',
                ]
            );

        } catch (Throwable $exception) {
            error_log(
                '[ProjectRequestController::create] ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'نمایش فرم ثبت پروژه با مشکل مواجه شد.'
            );
        }
    }

    /**
     * ثبت درخواست جدید پروژه
     */
    public function store(): Response
    {
        if (!$this->request->isMethod('POST')) {
            return Response::text('Method Not Allowed', 405)
                ->setHeader('Allow', 'POST');
        }

        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }

        if ($this->isAdmin()) {
            return $this->redirect('/admin/dashboard');
        }

        $csrfResponse = $this->validateCsrf();

        if ($csrfResponse !== null) {
            return $csrfResponse;
        }

        $userId = $this->currentUserId();

        $db = null;
        $transactionStarted = false;

        try {
            $db = db();

            $userStmt = $db->prepare(
                "SELECT id
                 FROM users
                 WHERE id = :id
                   AND role = 'customer'
                   AND account_status = 'active'
                   AND email_verified_at IS NOT NULL
                 LIMIT 1"
            );

            $userStmt->execute([
                'id' => $userId,
            ]);

            if (!$userStmt->fetchColumn()) {
                return $this->redirect('/verification-required');
            }

            $categoryId = filter_var(
                $this->input('category_id'),
                FILTER_VALIDATE_INT
            );

            $title = trim(
                (string) $this->input('project_title', '')
            );

            $description = trim(
                (string) $this->input('project_description', '')
            );

            $budget = $this->input('budget');

            $currency = trim(
                (string) $this->input('budget_currency', 'USD')
            );

            $deliveryDate = $this->input(
                'expected_delivery_date'
            );

            $answers = $this->input('answers', []);

            if (!is_array($answers)) {
                throw new InvalidArgumentException(
                    'ساختار پاسخ‌های پرسش‌نامه معتبر نیست.'
                );
            }

            if (!$categoryId || $categoryId <= 0) {
                throw new InvalidArgumentException(
                    'لطفاً نوع پروژه را انتخاب کنید.'
                );
            }

            if (
                $title === '' ||
                mb_strlen($title, 'UTF-8') > 200
            ) {
                throw new InvalidArgumentException(
                    'عنوان پروژه الزامی است و نباید بیشتر از ۲۰۰ حرف باشد.'
                );
            }

            if (mb_strlen($description, 'UTF-8') > 10000) {
                throw new InvalidArgumentException(
                    'توضیحات پروژه بیش از حد طولانی است.'
                );
            }

            if (
                $currency !== '' &&
                !in_array($currency, ['USD', 'AFN', 'EUR'], true)
            ) {
                throw new InvalidArgumentException(
                    'واحد پول انتخاب‌شده معتبر نیست.'
                );
            }

            if ($budget === '') {
                $budget = null;
            }

            if ($budget !== null) {
                if (
                    !is_numeric($budget) ||
                    !is_finite((float) $budget) ||
                    (float) $budget < 0
                ) {
                    throw new InvalidArgumentException(
                        'مبلغ بودجه معتبر نیست.'
                    );
                }

                $budget = (string) $budget;
            }

            if ($deliveryDate === '') {
                $deliveryDate = null;
            }

            if ($deliveryDate !== null) {
                if (!is_string($deliveryDate)) {
                    throw new InvalidArgumentException(
                        'تاریخ تحویل معتبر نیست.'
                    );
                }

                $date = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $deliveryDate
                );

                if (
                    !$date ||
                    $date->format('Y-m-d') !== $deliveryDate
                ) {
                    throw new InvalidArgumentException(
                        'فرمت تاریخ تحویل معتبر نیست.'
                    );
                }
            }

            // بررسی فعال‌بودن دسته‌بندی
            $categoryCheck = $db->prepare(
                "SELECT id
                 FROM project_categories
                 WHERE id = :id
                   AND is_active = 1
                 LIMIT 1"
            );

            $categoryCheck->execute([
                'id' => $categoryId,
            ]);

            if (!$categoryCheck->fetchColumn()) {
                throw new InvalidArgumentException(
                    'دسته‌بندی انتخاب‌شده معتبر یا فعال نیست.'
                );
            }

            /*
             * شروع تراکنش مشترک:
             * ایجاد پروژه و ذخیره پاسخ‌ها با هم انجام می‌شوند.
             */
            if ($db->inTransaction()) {
                throw new RuntimeException(
                    'یک تراکنش غیرمنتظره از قبل فعال است.'
                );
            }

            $db->beginTransaction();
            $transactionStarted = true;

            // ایجاد پروژه
            $projectModel = new Project($db);

            $projectId = $projectModel->create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'project_title' => $title,
                'project_description' => $description,
                'budget' => $budget,
                'budget_currency' => $currency !== ''
                    ? $currency
                    : null,
                'expected_delivery_date' => $deliveryDate,
            ]);

            // ذخیره و اعتبارسنجی پرسش‌نامه
            $questionnaire = new ProjectQuestionnaireService($db);

            $questionnaire->saveAnswers(
                $projectId,
                $userId,
                $answers
            );

            // تأیید نهایی هر دو عملیات
            $db->commit();
            $transactionStarted = false;

            $this->flash()->success(
                'درخواست پروژه شما با موفقیت ثبت شد.'
            );

            return $this->redirect('/dashboard');

        } catch (InvalidArgumentException $exception) {
            if (
                $transactionStarted &&
                $db instanceof PDO &&
                $db->inTransaction()
            ) {
                $db->rollBack();
            }

            $this->flash()->error(
                $exception->getMessage()
            );

            return $this->redirect('/projects/create');

        } catch (RuntimeException $exception) {
            if (
                $transactionStarted &&
                $db instanceof PDO &&
                $db->inTransaction()
            ) {
                $db->rollBack();
            }

            error_log(
                '[ProjectRequestController::store] ' .
                $exception->getMessage()
            );

            $this->flash()->error(
                'ثبت پروژه انجام نشد. لطفاً اطلاعات و دسترسی حساب خود را بررسی کنید.'
            );

            return $this->redirect('/projects/create');

        } catch (PDOException $exception) {
            if (
                $transactionStarted &&
                $db instanceof PDO &&
                $db->inTransaction()
            ) {
                $db->rollBack();
            }

            error_log(
                '[ProjectRequestController::store] Database error: ' .
                $exception->getMessage()
            );

            $this->flash()->error(
                'خطا در ذخیره اطلاعات. لطفاً دوباره تلاش کنید.'
            );

            return $this->redirect('/projects/create');

        } catch (Throwable $exception) {
            if (
                $transactionStarted &&
                $db instanceof PDO &&
                $db->inTransaction()
            ) {
                $db->rollBack();
            }

            error_log(
                '[ProjectRequestController::store] Unexpected error: ' .
                $exception->getMessage()
            );

            return Response::serverError(
                'در ثبت درخواست پروژه خطای غیرمنتظره‌ای رخ داد.'
            );
        }
    }
}
