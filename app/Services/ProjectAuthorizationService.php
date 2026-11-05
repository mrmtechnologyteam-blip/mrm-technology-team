<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

final class ProjectAuthorizationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * بررسی مالکیت پروژه توسط مشتری
     */
    public function customerOwnsProject(
        int $projectId,
        int $userId
    ): bool {
        if ($projectId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM projects p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.id = :project_id
               AND p.user_id = :user_id
               AND u.role = "customer"
               AND u.account_status = "active"
               AND u.email_verified_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی وجود پروژه
     */
    public function projectExists(int $projectId): bool
    {
        if ($projectId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM projects
             WHERE id = :project_id
             LIMIT 1'
        );

        $stmt->execute([
            'project_id' => $projectId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی اجازه مشاهده پروژه
     */
    public function canViewProject(
        int $projectId,
        int $userId,
        string $role
    ): bool {
        if ($projectId <= 0 || $userId <= 0) {
            return false;
        }

        if ($role === 'admin') {
            return $this->isActiveAdmin($userId);
        }

        if ($role === 'customer') {
            return $this->customerOwnsProject(
                $projectId,
                $userId
            );
        }

        return false;
    }

    /**
     * بررسی اجازه ویرایش پاسخ‌های پروژه
     */
    public function canEditProjectAnswers(
        int $projectId,
        int $userId
    ): bool {
        if ($projectId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM projects p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.id = :project_id
               AND p.user_id = :user_id
               AND p.status = "pending"
               AND u.role = "customer"
               AND u.account_status = "active"
               AND u.email_verified_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی فعال بودن مدیر
     */
    public function isActiveAdmin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT 1
             FROM users
             WHERE id = :user_id
               AND role = "admin"
               AND account_status = "active"
               AND email_verified_at IS NOT NULL
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * الزام مالکیت پروژه
     */
    public function requireProjectOwnership(
        int $projectId,
        int $userId
    ): void {
        if (!$this->customerOwnsProject($projectId, $userId)) {
            throw new RuntimeException(
                'You are not authorized to access this project.'
            );
        }
    }

    /**
     * الزام دسترسی مدیر
     */
    public function requireAdmin(int $userId): void
    {
        if (!$this->isActiveAdmin($userId)) {
            throw new RuntimeException(
                'Administrator access required.'
            );
        }
    }
}
