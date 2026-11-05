<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;
use RuntimeException;

final class Project
{
    private PDO $db;

    private const STATUSES = [
        'pending',
        'under_review',
        'accepted',
        'rejected',
        'completed'
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Generate a unique project code.
     */
    private function generateProjectCode(): string
    {
        return 'MRM-' . date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(5)));
    }

    /**
     * Create a project.
     */
    public function create(array $data): int
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $categoryId = (int) ($data['category_id'] ?? 0);
        $title = trim((string) ($data['project_title'] ?? ''));

        if ($userId <= 0 || $categoryId <= 0 || $title === '') {
            throw new InvalidArgumentException(
                'Invalid project information.'
            );
        }

        if (mb_strlen($title, 'UTF-8') > 200) {
            throw new InvalidArgumentException(
                'Project title is too long.'
            );
        }

        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $userStmt = $this->db->prepare(
                "SELECT id
                 FROM users
                 WHERE id = :id
                   AND role = 'customer'
                   AND account_status = 'active'
                   AND email_verified_at IS NOT NULL
                 LIMIT 1"
            );

            $userStmt->execute(['id' => $userId]);

            if (!$userStmt->fetch()) {
                throw new RuntimeException(
                    'Customer account is not active or verified.'
                );
            }

            $categoryStmt = $this->db->prepare(
                "SELECT id
                 FROM project_categories
                 WHERE id = :id
                   AND is_active = 1
                 LIMIT 1"
            );

            $categoryStmt->execute([
                'id' => $categoryId
            ]);

            if (!$categoryStmt->fetch()) {
                throw new RuntimeException(
                    'Project category is unavailable.'
                );
            }

            $budget = $data['budget'] ?? null;

            if ($budget !== null && $budget !== '') {
                if (!is_numeric($budget) || (float) $budget < 0) {
                    throw new InvalidArgumentException(
                        'Invalid project budget.'
                    );
                }
            } else {
                $budget = null;
            }

            $deliveryDate = $data['expected_delivery_date'] ?? null;

            if ($deliveryDate !== null && $deliveryDate !== '') {
                if (!is_string($deliveryDate)) {
                    throw new InvalidArgumentException(
                        'Invalid delivery date.'
                    );
                }

                $date = \DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $deliveryDate
                );

                if (!$date || $date->format('Y-m-d') !== $deliveryDate) {
                    throw new InvalidArgumentException(
                        'Invalid delivery date.'
                    );
                }
            } else {
                $deliveryDate = null;
            }

            $projectCode = $this->generateProjectCode();

            $stmt = $this->db->prepare(
                "INSERT INTO projects (
                    project_code,
                    user_id,
                    category_id,
                    project_title,
                    project_description,
                    budget,
                    budget_currency,
                    expected_delivery_date,
                    status
                ) VALUES (
                    :project_code,
                    :user_id,
                    :category_id,
                    :project_title,
                    :project_description,
                    :budget,
                    :budget_currency,
                    :expected_delivery_date,
                    'pending'
                )"
            );

            $stmt->execute([
                'project_code' => $projectCode,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'project_title' => $title,
                'project_description' =>
                    $data['project_description'] ?? null,
                'budget' => $budget,
                'budget_currency' =>
                    $data['budget_currency'] ?? null,
                'expected_delivery_date' => $deliveryDate
            ]);

            $projectId = (int) $this->db->lastInsertId();

            $history = $this->db->prepare(
                "INSERT INTO project_status_history (
                    project_id,
                    changed_by,
                    old_status,
                    new_status,
                    note
                ) VALUES (
                    :project_id,
                    :changed_by,
                    NULL,
                    'pending',
                    'Project submitted'
                )"
            );

            $history->execute([
                'project_id' => $projectId,
                'changed_by' => $userId
            ]);

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return $projectId;

        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Find project by ID.
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT
                p.*,
                u.full_name AS customer_name,
                u.email AS customer_email,
                c.name AS category_name
             FROM projects p
             INNER JOIN users u ON u.id = p.user_id
             INNER JOIN project_categories c ON c.id = p.category_id
             WHERE p.id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Find project by unique code.
     */
    public function findByCode(string $code): ?array
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM projects
             WHERE project_code = :code
             LIMIT 1"
        );

        $stmt->execute(['code' => $code]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Get customer projects.
     */
    public function getByUser(
        int $userId,
        int $limit = 20,
        int $offset = 0
    ): array {
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid user ID.');
        }

        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            "SELECT
                p.*,
                c.name AS category_name
             FROM projects p
             INNER JOIN project_categories c
                ON c.id = p.category_id
             WHERE p.user_id = :user_id
             ORDER BY p.submitted_at DESC
             LIMIT :limit OFFSET :offset"
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get all projects for administration.
     */
    public function getAll(
        ?string $status = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $sql = "
            SELECT
                p.*,
                u.full_name AS customer_name,
                u.email AS customer_email,
                c.name AS category_name
            FROM projects p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN project_categories c ON c.id = p.category_id
        ";

        if ($status !== null) {
            if (!in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException(
                    'Invalid project status.'
                );
            }

            $sql .= " WHERE p.status = :status";
        }

        $sql .= " ORDER BY p.submitted_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Update project status.
     */
    public function updateStatus(
        int $projectId,
        int $adminId,
        string $newStatus,
        ?string $note = null
    ): bool {
        if (
            $projectId <= 0 ||
            $adminId <= 0 ||
            !in_array($newStatus, self::STATUSES, true)
        ) {
            throw new InvalidArgumentException(
                'Invalid status update information.'
            );
        }

        if ($note !== null) {
            $note = trim($note);

            if (mb_strlen($note, 'UTF-8') > 2000) {
                throw new InvalidArgumentException(
                    'Status note is too long.'
                );
            }

            if ($note === '') {
                $note = null;
            }
        }

        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            // Verify active and email-verified administrator.
            $admin = $this->db->prepare(
                "SELECT id
                 FROM users
                 WHERE id = :id
                   AND role = 'admin'
                   AND account_status = 'active'
                   AND email_verified_at IS NOT NULL
                 LIMIT 1"
            );

            $admin->execute(['id' => $adminId]);

            if (!$admin->fetch()) {
                throw new RuntimeException(
                    'Unauthorized administrator.'
                );
            }

            $project = $this->db->prepare(
                "SELECT status
                 FROM projects
                 WHERE id = :id
                 FOR UPDATE"
            );

            $project->execute(['id' => $projectId]);

            $current = $project->fetch();

            if (!$current) {
                throw new RuntimeException(
                    'Project not found.'
                );
            }

            $oldStatus = $current['status'];

            if ($oldStatus !== $newStatus) {
                $update = $this->db->prepare(
                    "UPDATE projects
                     SET status = :status
                     WHERE id = :id"
                );

                $update->execute([
                    'status' => $newStatus,
                    'id' => $projectId
                ]);

                $history = $this->db->prepare(
                    "INSERT INTO project_status_history (
                        project_id,
                        changed_by,
                        old_status,
                        new_status,
                        note
                    ) VALUES (
                        :project_id,
                        :changed_by,
                        :old_status,
                        :new_status,
                        :note
                    )"
                );

                $history->execute([
                    'project_id' => $projectId,
                    'changed_by' => $adminId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'note' => $note
                ]);
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
     * Count all projects.
     */
    public function count(?string $status = null): int
    {
        if ($status !== null) {
            if (!in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException(
                    'Invalid project status.'
                );
            }

            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM projects WHERE status = :status"
            );

            $stmt->execute(['status' => $status]);

        } else {
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM projects"
            );
        }

        return (int) $stmt->fetchColumn();
    }
}
