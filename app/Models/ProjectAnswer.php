<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class ProjectAnswer
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * دریافت تمام پاسخ‌های یک پروژه
     */
    public function getByProject(int $projectId): array
    {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('Invalid project ID.');
        }

        $stmt = $this->db->prepare(
            "SELECT
                pa.id,
                pa.project_id,
                pa.question_id,
                pa.answer_value,
                pq.question_text,
                pq.question_type,
                pq.section_number,
                pq.question_number
             FROM project_answers pa
             INNER JOIN project_questions pq
                ON pq.id = pa.question_id
             WHERE pa.project_id = :project_id
             ORDER BY
                pq.section_number ASC,
                pq.question_number ASC"
        );

        $stmt->execute([
            'project_id' => $projectId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * دریافت پاسخ یک سؤال مشخص
     */
    public function find(int $projectId, int $questionId): ?array
    {
        if ($projectId <= 0 || $questionId <= 0) {
            throw new InvalidArgumentException('Invalid ID.');
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_answers
             WHERE project_id = :project_id
               AND question_id = :question_id
             LIMIT 1"
        );

        $stmt->execute([
            'project_id' => $projectId,
            'question_id' => $questionId
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * ثبت یا به‌روزرسانی پاسخ
     */
    public function save(
        int $projectId,
        int $questionId,
        mixed $answer
    ): bool {
        if ($projectId <= 0 || $questionId <= 0) {
            throw new InvalidArgumentException('Invalid project or question ID.');
        }

        $answerValue = $this->encodeAnswer($answer);

        $stmt = $this->db->prepare(
            "INSERT INTO project_answers (
                project_id,
                question_id,
                answer_value
            ) VALUES (
                :project_id,
                :question_id,
                :answer_value
            )
            ON DUPLICATE KEY UPDATE
                answer_value = VALUES(answer_value)"
        );

        return $stmt->execute([
            'project_id' => $projectId,
            'question_id' => $questionId,
            'answer_value' => $answerValue
        ]);
    }

    /**
     * ثبت چند پاسخ به‌صورت یک‌جا
     */
    public function saveMany(int $projectId, array $answers): bool
    {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('Invalid project ID.');
        }

        if (empty($answers)) {
            return true;
        }

        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            foreach ($answers as $questionId => $answer) {
                if (
                    !is_numeric($questionId) ||
                    (int) $questionId <= 0
                ) {
                    throw new InvalidArgumentException(
                        'Invalid question ID.'
                    );
                }

                $this->save(
                    $projectId,
                    (int) $questionId,
                    $answer
                );
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return true;

        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * حذف پاسخ یک سؤال
     */
    public function delete(
        int $projectId,
        int $questionId
    ): bool {
        if ($projectId <= 0 || $questionId <= 0) {
            throw new InvalidArgumentException('Invalid ID.');
        }

        $stmt = $this->db->prepare(
            "DELETE FROM project_answers
             WHERE project_id = :project_id
               AND question_id = :question_id"
        );

        $stmt->execute([
            'project_id' => $projectId,
            'question_id' => $questionId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * حذف تمام پاسخ‌های یک پروژه
     */
    public function deleteByProject(int $projectId): bool
    {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('Invalid project ID.');
        }

        $stmt = $this->db->prepare(
            "DELETE FROM project_answers
             WHERE project_id = :project_id"
        );

        $stmt->execute([
            'project_id' => $projectId
        ]);

        return true;
    }

    /**
     * تبدیل پاسخ به مقدار قابل ذخیره در دیتابیس
     */
    private function encodeAnswer(mixed $answer): string
    {
        if (is_array($answer)) {
            return json_encode(
                $answer,
                JSON_UNESCAPED_UNICODE |
                JSON_THROW_ON_ERROR
            );
        }

        if (is_bool($answer)) {
            return $answer ? '1' : '0';
        }

        if ($answer === null) {
            return '';
        }

        if (!is_scalar($answer)) {
            throw new InvalidArgumentException(
                'Invalid answer value.'
            );
        }

        return (string) $answer;
    }
}
