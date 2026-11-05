<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class Notification
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $userId,
        string $title,
        string $message
    ): int {
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid user ID.');
        }

        $title = trim($title);
        $message = trim($message);

        if ($title === '' || mb_strlen($title) > 200) {
            throw new InvalidArgumentException('Invalid notification title.');
        }

        if ($message === '') {
            throw new InvalidArgumentException('Notification message is required.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, title, message)
             VALUES (:user_id, :title, :message)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getByUser(
        int $userId,
        int $limit = 20,
        int $offset = 0
    ): array {
        if ($userId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $stmt = $this->db->prepare(
            'SELECT id, user_id, title, message, is_read, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM notifications
             WHERE user_id = :user_id
             AND is_read = 0'
        );

        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        if ($notificationId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id
             AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id
             AND is_read = 0'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount();
    }

    public function delete(int $notificationId, int $userId): bool
    {
        if ($notificationId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE id = :id
             AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteAllByUser(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount();
    }

    public function countByUser(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM notifications
             WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
