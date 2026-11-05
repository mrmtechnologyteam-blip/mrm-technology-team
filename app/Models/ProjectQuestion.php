<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;
use RuntimeException;

final class ProjectQuestion
{
    private PDO $db;

    private const QUESTION_TYPES = [
        'text',
        'textarea',
        'radio',
        'checkbox',
        'select',
        'date',
        'number'
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get questionnaire for a project category.
     * Includes global and category-specific questions.
     */
    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare(
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
               AND (
                    category_id = :category_id
                    OR category_id IS NULL
               )
             ORDER BY
                section_number ASC,
                question_number ASC,
                id ASC"
        );

        $stmt->execute([
            'category_id' => $categoryId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Find question by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_questions
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Get all questions for administration.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT
                q.*,
                c.name AS category_name
             FROM project_questions q
             LEFT JOIN project_categories c
                ON c.id = q.category_id
             ORDER BY
                q.category_id ASC,
                q.section_number ASC,
                q.question_number ASC"
        );

        return $stmt->fetchAll();
    }

    /**
     * Create a question.
     */
    public function create(array $data): int
    {
        $this->validateQuestion($data);

        $options = $this->encodeOptions(
            $data['options'] ?? null
        );

        $stmt = $this->db->prepare(
            "INSERT INTO project_questions (
                category_id,
                section_number,
                question_number,
                question_text,
                question_type,
                options_json,
                parent_question_id,
                condition_value,
                is_required,
                is_active
            ) VALUES (
                :category_id,
                :section_number,
                :question_number,
                :question_text,
                :question_type,
                :options_json,
                :parent_question_id,
                :condition_value,
                :is_required,
                1
            )"
        );

        $stmt->execute([
            'category_id' => $data['category_id'] ?? null,
            'section_number' => $data['section_number'],
            'question_number' => $data['question_number'],
            'question_text' => trim($data['question_text']),
            'question_type' => $data['question_type'],
            'options_json' => $options,
            'parent_question_id' => $data['parent_question_id'] ?? null,
            'condition_value' => $data['condition_value'] ?? null,
            'is_required' => !empty($data['is_required']) ? 1 : 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing question.
     */
    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Invalid question ID.'
            );
        }

        $this->validateQuestion($data);

        $options = $this->encodeOptions(
            $data['options'] ?? null
        );

        $stmt = $this->db->prepare(
            "UPDATE project_questions
             SET
                category_id = :category_id,
                section_number = :section_number,
                question_number = :question_number,
                question_text = :question_text,
                question_type = :question_type,
                options_json = :options_json,
                parent_question_id = :parent_question_id,
                condition_value = :condition_value,
                is_required = :is_required
             WHERE id = :id"
        );

        $stmt->execute([
            'category_id' => $data['category_id'] ?? null,
            'section_number' => $data['section_number'],
            'question_number' => $data['question_number'],
            'question_text' => trim($data['question_text']),
            'question_type' => $data['question_type'],
            'options_json' => $options,
            'parent_question_id' => $data['parent_question_id'] ?? null,
            'condition_value' => $data['condition_value'] ?? null,
            'is_required' => !empty($data['is_required']) ? 1 : 0,
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Activate or deactivate a question.
     */
    public function setActive(int $id, bool $active): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE project_questions
             SET is_active = :active
             WHERE id = :id"
        );

        $stmt->execute([
            'active' => $active ? 1 : 0,
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Validate question data.
     */
    private function validateQuestion(array $data): void
    {
        $required = [
            'section_number',
            'question_number',
            'question_text',
            'question_type'
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException(
                    "Missing required field: {$field}"
                );
            }
        }

        if (
            !is_numeric($data['section_number']) ||
            (int) $data['section_number'] < 1 ||
            (int) $data['section_number'] > 255
        ) {
            throw new InvalidArgumentException(
                'Invalid section number.'
            );
        }

        if (
            !is_numeric($data['question_number']) ||
            (int) $data['question_number'] < 1 ||
            (int) $data['question_number'] > 255
        ) {
            throw new InvalidArgumentException(
                'Invalid question number.'
            );
        }

        if (
            !is_string($data['question_text']) ||
            trim($data['question_text']) === ''
        ) {
            throw new InvalidArgumentException(
                'Question text is required.'
            );
        }

        if (!in_array(
            $data['question_type'],
            self::QUESTION_TYPES,
            true
        )) {
            throw new InvalidArgumentException(
                'Invalid question type.'
            );
        }

        if (
            isset($data['parent_question_id']) &&
            $data['parent_question_id'] !== null &&
            (
                !is_numeric($data['parent_question_id']) ||
                (int) $data['parent_question_id'] <= 0
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid parent question ID.'
            );
        }

        if (
            isset($data['category_id']) &&
            $data['category_id'] !== null &&
            (
                !is_numeric($data['category_id']) ||
                (int) $data['category_id'] <= 0
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid category ID.'
            );
        }

        if (
            isset($data['parent_question_id']) &&
            (int) $data['parent_question_id'] > 0 &&
            isset($data['id']) &&
            (int) $data['id'] === (int) $data['parent_question_id']
        ) {
            throw new InvalidArgumentException(
                'A question cannot be its own parent.'
            );
        }
    }

    /**
     * Encode question options as JSON.
     */
    private function encodeOptions(mixed $options): ?string
    {
        if ($options === null || $options === []) {
            return null;
        }

        if (!is_array($options)) {
            throw new InvalidArgumentException(
                'Question options must be an array.'
            );
        }

        $json = json_encode(
            $options,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );

        return $json;
    }
}
