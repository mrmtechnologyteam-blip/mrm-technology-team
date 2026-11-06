<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ProjectQuestionnaireService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function saveAnswers(
        int $projectId,
        int $userId,
        array $answers
    ): int {
        if ($projectId <= 0 || $userId <= 0) {
            throw new InvalidArgumentException(
                'Invalid project or user ID.'
            );
        }

        if (count($answers) > 500) {
            throw new InvalidArgumentException(
                'Too many answers submitted.'
            );
        }

        // جلوگیری از شروع تراکنش تودرتو
        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $projectStmt = $this->db->prepare(
                'SELECT id, category_id, status
                 FROM projects
                 WHERE id = :project_id
                   AND user_id = :user_id
                 LIMIT 1
                 FOR UPDATE'
            );

            $projectStmt->execute([
                'project_id' => $projectId,
                'user_id' => $userId,
            ]);

            $project = $projectStmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                throw new RuntimeException(
                    'Project not found or access denied.'
                );
            }

            if ($project['status'] !== 'pending') {
                throw new RuntimeException(
                    'Answers can only be changed while the project is pending.'
                );
            }

            $questionStmt = $this->db->prepare(
                'SELECT
                    id,
                    category_id,
                    question_text,
                    question_type,
                    options_json,
                    parent_question_id,
                    condition_value,
                    is_required
                 FROM project_questions
                 WHERE is_active = 1
                   AND (
                       category_id = :category_id
                       OR category_id IS NULL
                   )
                 ORDER BY
                    section_number ASC,
                    question_number ASC,
                    id ASC'
            );

            $questionStmt->execute([
                'category_id' => (int) $project['category_id'],
            ]);

            $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

            $questionMap = [];

            foreach ($questions as $question) {
                $questionMap[(int) $question['id']] = $question;
            }

            $normalizedAnswers = [];

            foreach ($answers as $questionId => $answer) {
                if (
                    !is_int($questionId) &&
                    !(
                        is_string($questionId) &&
                        ctype_digit($questionId)
                    )
                ) {
                    throw new InvalidArgumentException(
                        'Invalid question ID.'
                    );
                }

                $questionId = (int) $questionId;

                if (
                    $questionId <= 0 ||
                    !isset($questionMap[$questionId])
                ) {
                    throw new InvalidArgumentException(
                        'A submitted question is invalid or inactive.'
                    );
                }

                if (array_key_exists($questionId, $normalizedAnswers)) {
                    throw new InvalidArgumentException(
                        'Duplicate question answer.'
                    );
                }

                $normalizedAnswers[$questionId] = $answer;
            }

            // دریافت پاسخ‌های قبلی
            $existingStmt = $this->db->prepare(
                'SELECT question_id, answer_value
                 FROM project_answers
                 WHERE project_id = :project_id'
            );

            $existingStmt->execute([
                'project_id' => $projectId,
            ]);

            $finalAnswers = [];

            foreach (
                $existingStmt->fetchAll(PDO::FETCH_ASSOC)
                as $row
            ) {
                $questionId = (int) $row['question_id'];

                if (isset($questionMap[$questionId])) {
                    $finalAnswers[$questionId] =
                        $this->decodeStoredAnswer(
                            $row['answer_value']
                        );
                }
            }

            // اعتبارسنجی و اعمال پاسخ‌های جدید
            foreach ($normalizedAnswers as $questionId => $answer) {
                $validated = $this->validateAnswer(
                    $questionMap[$questionId],
                    $answer
                );

                if ($validated === null) {
                    unset($finalAnswers[$questionId]);
                } else {
                    $finalAnswers[$questionId] = $validated;
                }
            }

            // جلوگیری از ثبت پاسخ برای پرسش مخفی
            foreach ($normalizedAnswers as $questionId => $answer) {
                if (
                    !$this->isQuestionVisible(
                        $questionMap[$questionId],
                        $finalAnswers,
                        $questionMap
                    )
                ) {
                    throw new InvalidArgumentException(
                        'An answer was submitted for a hidden question.'
                    );
                }
            }

            // حذف پاسخ پرسش‌هایی که مخفی شده‌اند
            do {
                $changed = false;

                foreach ($finalAnswers as $questionId => $answer) {
                    if (!isset($questionMap[$questionId])) {
                        unset($finalAnswers[$questionId]);
                        $changed = true;
                        continue;
                    }

                    if (
                        !$this->isQuestionVisible(
                            $questionMap[$questionId],
                            $finalAnswers,
                            $questionMap
                        )
                    ) {
                        unset($finalAnswers[$questionId]);
                        $changed = true;
                    }
                }
            } while ($changed);

            // بررسی تمام پرسش‌های اجباری قابل نمایش
            foreach ($questionMap as $questionId => $question) {
                if ((int) $question['is_required'] !== 1) {
                    continue;
                }

                if (
                    !$this->isQuestionVisible(
                        $question,
                        $finalAnswers,
                        $questionMap
                    )
                ) {
                    continue;
                }

                if (
                    !array_key_exists($questionId, $finalAnswers) ||
                    $this->isEmptyAnswer($finalAnswers[$questionId])
                ) {
                    throw new InvalidArgumentException(
                        'Please answer all required questions.'
                    );
                }
            }

            // حذف پاسخ‌های قبلی
            $deleteStmt = $this->db->prepare(
                'DELETE FROM project_answers
                 WHERE project_id = :project_id'
            );

            $deleteStmt->execute([
                'project_id' => $projectId,
            ]);

            // ذخیره پاسخ‌های نهایی
            $insertStmt = $this->db->prepare(
                'INSERT INTO project_answers
                    (project_id, question_id, answer_value)
                 VALUES
                    (:project_id, :question_id, :answer_value)'
            );

            $savedCount = 0;

            foreach ($finalAnswers as $questionId => $answer) {
                $insertStmt->execute([
                    'project_id' => $projectId,
                    'question_id' => $questionId,
                    'answer_value' => json_encode(
                        $answer,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES |
                        JSON_THROW_ON_ERROR
                    ),
                ]);

                $savedCount++;
            }

            // فقط مالک تراکنش اجازه commit دارد
            if ($ownsTransaction) {
                $this->db->commit();
            }

            return $savedCount;

        } catch (Throwable $exception) {
            // فقط تراکنشی که خود سرویس آغاز کرده لغو می‌شود
            if (
                $ownsTransaction &&
                $this->db->inTransaction()
            ) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    private function validateAnswer(
        array $question,
        mixed $answer
    ): mixed {
        if ($answer === null || $answer === '') {
            return null;
        }

        $type = (string) $question['question_type'];

        $options = [];

        if ($question['options_json'] !== null) {
            $options = json_decode(
                $question['options_json'],
                true
            );

            if (!is_array($options)) {
                throw new RuntimeException(
                    'Invalid options configuration for a question.'
                );
            }
        }

        switch ($type) {
            case 'text':
            case 'textarea':
                if (!is_string($answer) && !is_numeric($answer)) {
                    throw new InvalidArgumentException(
                        'Invalid text answer.'
                    );
                }

                $answer = trim((string) $answer);

                if ($answer === '') {
                    return null;
                }

                if (mb_strlen($answer) > 10000) {
                    throw new InvalidArgumentException(
                        'Answer is too long.'
                    );
                }

                return $answer;

            case 'number':
                if (
                    (!is_string($answer) && !is_numeric($answer)) ||
                    !is_numeric($answer) ||
                    !is_finite((float) $answer)
                ) {
                    throw new InvalidArgumentException(
                        'Invalid number answer.'
                    );
                }

                return (string) $answer;

            case 'date':
                if (!is_string($answer)) {
                    throw new InvalidArgumentException(
                        'Invalid date answer.'
                    );
                }

                $date = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $answer
                );

                if (
                    !$date ||
                    $date->format('Y-m-d') !== $answer
                ) {
                    throw new InvalidArgumentException(
                        'Invalid date format.'
                    );
                }

                return $answer;

            case 'radio':
            case 'select':
                if (!is_string($answer) && !is_numeric($answer)) {
                    throw new InvalidArgumentException(
                        'Invalid selected option.'
                    );
                }

                $answer = (string) $answer;

                if ($answer === '') {
                    return null;
                }

                if (!in_array($answer, $options, true)) {
                    throw new InvalidArgumentException(
                        'Selected option is not allowed.'
                    );
                }

                return $answer;

            case 'checkbox':
                if (!is_array($answer)) {
                    throw new InvalidArgumentException(
                        'Invalid checkbox answer.'
                    );
                }

                if (count($answer) > count($options)) {
                    throw new InvalidArgumentException(
                        'Too many selected options.'
                    );
                }

                $selected = [];

                foreach ($answer as $value) {
                    if (
                        !is_string($value) &&
                        !is_numeric($value)
                    ) {
                        throw new InvalidArgumentException(
                            'Invalid checkbox option.'
                        );
                    }

                    $value = (string) $value;

                    if (!in_array($value, $options, true)) {
                        throw new InvalidArgumentException(
                            'A selected checkbox option is not allowed.'
                        );
                    }

                    if (!in_array($value, $selected, true)) {
                        $selected[] = $value;
                    }
                }

                return $selected === [] ? null : $selected;

            default:
                throw new InvalidArgumentException(
                    'Unsupported question type.'
                );
        }
    }

    private function isQuestionVisible(
        array $question,
        array $answers,
        array $questionMap,
        array $visited = []
    ): bool {
        $questionId = (int) $question['id'];

        if (isset($visited[$questionId])) {
            return false;
        }

        $visited[$questionId] = true;

        if ($question['parent_question_id'] === null) {
            return true;
        }

        $parentId = (int) $question['parent_question_id'];

        if (
            !isset($questionMap[$parentId]) ||
            !array_key_exists($parentId, $answers)
        ) {
            return false;
        }

        $parent = $questionMap[$parentId];

        if (
            !$this->isQuestionVisible(
                $parent,
                $answers,
                $questionMap,
                $visited
            )
        ) {
            return false;
        }

        $parentAnswer = $answers[$parentId];
        $conditionValue = (string) $question['condition_value'];

        if (is_array($parentAnswer)) {
            return in_array(
                $conditionValue,
                $parentAnswer,
                true
            );
        }

        return (string) $parentAnswer === $conditionValue;
    }

    private function isEmptyAnswer(mixed $answer): bool
    {
        if (is_array($answer)) {
            return $answer === [];
        }

        return $answer === null ||
            trim((string) $answer) === '';
    }

    private function decodeStoredAnswer(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_decode(
                $value,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return $value;
        }
    }
}
