<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;
use RuntimeException;

final class ContactMessage
{
    private PDO $db;

    private const STATUSES = [
        'new',
        'in_progress',
        'replied',
        'closed',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new contact message.
     */
    public function create(array $data): int
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $whatsapp = trim((string) ($data['whatsapp_number'] ?? ''));
        $subject = trim((string) ($data['subject'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

        $userId = isset($data['user_id']) && (int) $data['user_id'] > 0
            ? (int) $data['user_id']
            : null;

        if ($fullName === '' || mb_strlen($fullName) > 150) {
            throw new InvalidArgumentException('نام کامل معتبر نیست.');
        }

        if (
            $email === '' ||
            mb_strlen($email) > 190 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException('آدرس ایمیل معتبر نیست.');
        }

        if (mb_strlen($whatsapp) > 30) {
            throw new InvalidArgumentException('شماره واتساپ بیش از حد طولانی است.');
        }

        if ($subject === '' || mb_strlen($subject) > 200) {
            throw new InvalidArgumentException('موضوع پیام معتبر نیست.');
        }

        if ($message === '' || mb_strlen($message) > 20000) {
            throw new InvalidArgumentException('متن پیام معتبر نیست.');
        }

        $sql = "
            INSERT INTO contact_messages (
                user_id,
                full_name,
                email,
                whatsapp_number,
                subject,
                message,
                status
            ) VALUES (
                :user_id,
                :full_name,
                :email,
                :whatsapp_number,
                :subject,
                :message,
                'new'
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'full_name' => $fullName,
            'email' => $email,
            'whatsapp_number' => $whatsapp !== '' ? $whatsapp : null,
            'subject' => $subject,
            'message' => $message,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find a contact message by ID.
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                cm.*,
                u.full_name AS customer_account_name,
                u.email AS customer_account_email,
                admin.full_name AS admin_name
            FROM contact_messages cm
            LEFT JOIN users u ON u.id = cm.user_id
            LEFT JOIN users admin ON admin.id = cm.handled_by
            WHERE cm.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        return $message ?: null;
    }

    /**
     * Get all contact messages with filters and pagination.
     */
    public function getAll(
        array $filters = [],
        int $limit = 20,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            if (!in_array($filters['status'], self::STATUSES, true)) {
                throw new InvalidArgumentException('وضعیت پیام معتبر نیست.');
            }

            $where[] = 'cm.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(
                cm.full_name LIKE :search_name
                OR cm.email LIKE :search_email
                OR cm.subject LIKE :search_subject
                OR cm.message LIKE :search_message
            )";

            $search = '%' . trim((string) $filters['search']) . '%';

            $params['search_name'] = $search;
            $params['search_email'] = $search;
            $params['search_subject'] = $search;
            $params['search_message'] = $search;
        }

        if (!empty($filters['email'])) {
            $where[] = 'cm.email LIKE :filter_email';
            $params['filter_email'] = '%' . trim((string) $filters['email']) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'cm.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'cm.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = "
            SELECT
                cm.*,
                admin.full_name AS admin_name
            FROM contact_messages cm
            LEFT JOIN users admin ON admin.id = cm.handled_by
        ";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= "
            ORDER BY cm.created_at DESC, cm.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count contact messages with filters.
     */
    public function countAll(array $filters = []): int
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            if (!in_array($filters['status'], self::STATUSES, true)) {
                throw new InvalidArgumentException('وضعیت پیام معتبر نیست.');
            }

            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(
                full_name LIKE :search_name
                OR email LIKE :search_email
                OR subject LIKE :search_subject
                OR message LIKE :search_message
            )";

            $search = '%' . trim((string) $filters['search']) . '%';

            $params['search_name'] = $search;
            $params['search_email'] = $search;
            $params['search_subject'] = $search;
            $params['search_message'] = $search;
        }

        if (!empty($filters['email'])) {
            $where[] = 'email LIKE :filter_email';
            $params['filter_email'] = '%' . trim((string) $filters['email']) . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql = 'SELECT COUNT(*) FROM contact_messages';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get contact message statistics.
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) AS replied,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed,
                SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) AS unread
            FROM contact_messages
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'new' => 0,
            'in_progress' => 0,
            'replied' => 0,
            'closed' => 0,
            'unread' => 0,
        ];
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            UPDATE contact_messages
            SET read_at = COALESCE(read_at, CURRENT_TIMESTAMP)
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update message status by an active, verified administrator.
     */
    public function updateStatus(
        int $id,
        int $adminId,
        string $status
    ): bool {
        if (
            $id <= 0 ||
            $adminId <= 0 ||
            !in_array($status, self::STATUSES, true)
        ) {
            return false;
        }

        $sql = "
            UPDATE contact_messages
            SET
                status = :status,
                handled_by = :handled_by,
                replied_at = CASE
                    WHEN :replied_status = 'replied'
                    THEN COALESCE(replied_at, CURRENT_TIMESTAMP)
                    ELSE replied_at
                END
            WHERE id = :id
              AND EXISTS (
                  SELECT 1
                  FROM users
                  WHERE users.id = :admin_id
                    AND users.role = 'admin'
                    AND users.account_status = 'active'
                    AND users.email_verified_at IS NOT NULL
              )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'status' => $status,
            'handled_by' => $adminId,
            'replied_status' => $status,
            'id' => $id,
            'admin_id' => $adminId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update internal administrator note.
     */
    public function updateAdminNote(
        int $id,
        int $adminId,
        string $note
    ): bool {
        if ($id <= 0 || $adminId <= 0 || mb_strlen($note) > 10000) {
            return false;
        }

        $sql = "
            UPDATE contact_messages
            SET
                admin_note = :admin_note,
                handled_by = :handled_by
            WHERE id = :id
              AND EXISTS (
                  SELECT 1
                  FROM users
                  WHERE users.id = :admin_id
                    AND users.role = 'admin'
                    AND users.account_status = 'active'
                    AND users.email_verified_at IS NOT NULL
              )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'admin_note' => trim($note) !== '' ? trim($note) : null,
            'handled_by' => $adminId,
            'id' => $id,
            'admin_id' => $adminId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a contact message.
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM contact_messages WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
