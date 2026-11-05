<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class ProjectStatusHistory
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

    public function getByProject(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, u.full_name AS changed_by_name
             FROM project_status_history h
             INNER JOIN users u ON u.id = h.changed_by
             WHERE h.project_id = :project_id
             ORDER BY h.changed_at DESC, h.id DESC"
        );

        $stmt->execute(['project_id' => $projectId]);

        return $stmt->fetchAll();
    }

    public function getLatest(int $projectId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, u.full_name AS changed_by_name
             FROM project_status_history h
             INNER JOIN users u ON u.id = h.changed_by
             WHERE h.project_id = :project_id
             ORDER BY h.changed_at DESC, h.id DESC
             LIMIT 1"
        );

        $stmt->execute(['project_id' => $projectId]);

        return $stmt->fetch() ?: null;
    }

    public function create(
        int $projectId,
        int $changedBy,
        ?string $oldStatus,
        string $newStatus,
        ?string $note = null
    ): int {
        if ($projectId <= 0 || $changedBy <= 0) {
            throw new InvalidArgumentException('Invalid IDs.');
        }

        if (!in_array($newStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid new status.');
        }

        if (
            $oldStatus !== null &&
            !in_array($oldStatus, self::STATUSES, true)
        ) {
            throw new InvalidArgumentException('Invalid old status.');
        }

        $stmt = $this->db->prepare(
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

        $stmt->execute([
            'project_id' => $projectId,
            'changed_by' => $changedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function countByProject(int $projectId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM project_status_history
             WHERE project_id = :project_id"
        );

        $stmt->execute(['project_id' => $projectId]);

        return (int) $stmt->fetchColumn();
    }

    public function getAll(
        int $limit = 50,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 200));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            "SELECT
                h.*,
                p.project_code,
                p.project_title,
                u.full_name AS changed_by_name
             FROM project_status_history h
             INNER JOIN projects p ON p.id = h.project_id
             INNER JOIN users u ON u.id = h.changed_by
             ORDER BY h.changed_at DESC, h.id DESC
             LIMIT :limit OFFSET :offset"
        );

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
