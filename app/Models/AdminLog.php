<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class AdminLog
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $adminId,
        string $action,
        ?string $description = null
    ): int {
        if ($adminId <= 0) {
            throw new InvalidArgumentException('Invalid admin ID.');
        }

        $action = trim($action);
        $description = $description !== null ? trim($description) : null;

        if ($action === '' || mb_strlen($action) > 150) {
            throw new InvalidArgumentException('Invalid action.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO admin_logs (admin_id, action, description)
             SELECT id, :action, :description
             FROM users
             WHERE id = :admin_id
               AND role = "admin"
               AND account_status = "active"'
        );

        $stmt->execute([
            'admin_id' => $adminId,
            'action' => $action,
            'description' => $description,
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new InvalidArgumentException(
                'Active administrator not found.'
            );
        }

        return (int) $this->db->lastInsertId();
    }

    public function getByAdmin(
        int $adminId,
        int $limit = 50,
        int $offset = 0
    ): array {
        if ($adminId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            'SELECT
                id,
                admin_id,
                action,
                description,
                created_at
             FROM admin_logs
             WHERE admin_id = :admin_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(
        int $limit = 50,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            'SELECT
                al.id,
                al.admin_id,
                u.full_name AS admin_name,
                u.email AS admin_email,
                al.action,
                al.description,
                al.created_at
             FROM admin_logs al
             INNER JOIN users u ON u.id = al.admin_id
             ORDER BY al.created_at DESC, al.id DESC
             LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        return (int) $this->db
            ->query('SELECT COUNT(*) FROM admin_logs')
            ->fetchColumn();
    }

    public function countByAdmin(int $adminId): int
    {
        if ($adminId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM admin_logs
             WHERE admin_id = :admin_id'
        );

        $stmt->execute(['admin_id' => $adminId]);

        return (int) $stmt->fetchColumn();
    }
}
