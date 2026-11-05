<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

class ProjectStatusHistory
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * دریافت تاریخچه یک پروژه
     */
    public function getByProject(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $sql = "
            SELECT
                h.id,
                h.project_id,
                h.changed_by,
                h.old_status,
                h.new_status,
                h.note,
                h.changed_at,
                u.full_name AS changed_by_name,
                u.email AS changed_by_email
            FROM project_status_history h
            LEFT JOIN users u ON u.id = h.changed_by
            WHERE h.project_id = :project_id
            ORDER BY h.changed_at DESC, h.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'project_id' => $projectId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * دریافت آخرین تغییر وضعیت پروژه
     */
    public function getLatest(int $projectId): ?array
    {
        if ($projectId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                h.*,
                u.full_name AS changed_by_name
            FROM project_status_history h
            LEFT JOIN users u ON u.id = h.changed_by
            WHERE h.project_id = :project_id
            ORDER BY h.changed_at DESC, h.id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'project_id' => $projectId,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * ثبت تغییر وضعیت
     */
    public function create(
        int $projectId,
        ?int $changedBy,
        ?string $oldStatus,
        string $newStatus,
        ?string $note = null
    ): int {
        if ($projectId <= 0) {
            throw new InvalidArgumentException('شناسه پروژه معتبر نیست.');
        }

        $allowedStatuses = [
            'pending',
            'under_review',
            'accepted',
            'rejected',
            'completed',
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            throw new InvalidArgumentException('وضعیت جدید معتبر نیست.');
        }

        if (
            $oldStatus !== null &&
            !in_array($oldStatus, $allowedStatuses, true)
        ) {
            throw new InvalidArgumentException('وضعیت قبلی معتبر نیست.');
        }

        $sql = "
            INSERT INTO project_status_history (
                project_id,
                changed_by,
                old_status,
                new_status,
                note,
                changed_at
            ) VALUES (
                :project_id,
                :changed_by,
                :old_status,
                :new_status,
                :note,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'project_id' => $projectId,
            'changed_by' => $changedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * شمارش تغییرات وضعیت پروژه
     */
    public function countByProject(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM project_status_history
            WHERE project_id = :project_id
        ");

        $stmt->execute([
            'project_id' => $projectId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * دریافت تاریخچه تغییرات توسط مدیر
     */
    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min($limit, 200));
        $offset = max(0, $offset);

        $sql = "
            SELECT
                h.*,
                p.project_code,
                p.project_title,
                u.full_name AS changed_by_name
            FROM project_status_history h
            INNER JOIN projects p ON p.id = h.project_id
            LEFT JOIN users u ON u.id = h.changed_by
            ORDER BY h.changed_at DESC, h.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
