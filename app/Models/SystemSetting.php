<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class SystemSetting
{
    private PDO $db;

    private const ALLOWED_TYPES = [
        'text',
        'textarea',
        'boolean',
        'number',
        'email',
        'url',
        'json',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Validate setting key.
     */
    private function validateKey(string $key): string
    {
        $key = trim($key);

        if (
            $key === '' ||
            !preg_match('/^[a-zA-Z][a-zA-Z0-9_.-]{1,149}$/', $key)
        ) {
            throw new InvalidArgumentException(
                'Invalid setting key.'
            );
        }

        return $key;
    }

    /**
     * Validate setting type.
     */
    private function validateType(string $type): string
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(
                'Invalid setting type.'
            );
        }

        return $type;
    }

    /**
     * Get one setting by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->validateKey($key);

        $stmt = $this->db->prepare(
            'SELECT setting_value, setting_type
             FROM system_settings
             WHERE setting_key = :setting_key
             LIMIT 1'
        );

        $stmt->execute([
            'setting_key' => $key,
        ]);

        $setting = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$setting) {
            return $default;
        }

        return $this->castValue(
            $setting['setting_value'],
            $setting['setting_type']
        );
    }

    /**
     * Get setting with all metadata.
     */
    public function findByKey(string $key): ?array
    {
        $key = $this->validateKey($key);

        $stmt = $this->db->prepare(
            'SELECT
                id,
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                setting_label,
                description,
                is_public,
                updated_by,
                created_at,
                updated_at
             FROM system_settings
             WHERE setting_key = :setting_key
             LIMIT 1'
        );

        $stmt->execute([
            'setting_key' => $key,
        ]);

        $setting = $stmt->fetch(PDO::FETCH_ASSOC);

        return $setting ?: null;
    }

    /**
     * Get all settings.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT
                id,
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                setting_label,
                description,
                is_public,
                updated_by,
                created_at,
                updated_at
             FROM system_settings
             ORDER BY setting_group ASC, setting_label ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get settings by group.
     */
    public function getByGroup(string $group): array
    {
        $group = trim($group);

        if ($group === '') {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                setting_label,
                description,
                is_public,
                updated_by,
                created_at,
                updated_at
             FROM system_settings
             WHERE setting_group = :setting_group
             ORDER BY setting_label ASC'
        );

        $stmt->execute([
            'setting_group' => $group,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all public settings as key/value pairs.
     */
    public function getPublicSettings(): array
    {
        $stmt = $this->db->query(
            'SELECT setting_key, setting_value, setting_type
             FROM system_settings
             WHERE is_public = 1
             ORDER BY setting_key ASC'
        );

        $settings = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = $this->castValue(
                $row['setting_value'],
                $row['setting_type']
            );
        }

        return $settings;
    }

    /**
     * Create or update a setting.
     */
    public function set(
        string $key,
        mixed $value,
        string $type = 'text',
        string $group = 'general',
        string $label = '',
        ?string $description = null,
        bool $isPublic = false,
        ?int $updatedBy = null
    ): bool {
        $key = $this->validateKey($key);
        $type = $this->validateType($type);

        $group = trim($group);
        $label = trim($label);

        if (
            $group === '' ||
            mb_strlen($group) > 100
        ) {
            throw new InvalidArgumentException(
                'Invalid setting group.'
            );
        }

        if (
            $label === '' ||
            mb_strlen($label) > 190
        ) {
            throw new InvalidArgumentException(
                'Invalid setting label.'
            );
        }

        if (
            $updatedBy !== null &&
            $updatedBy <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid administrator ID.'
            );
        }

        $normalizedValue = $this->normalizeValue(
            $value,
            $type
        );

        $stmt = $this->db->prepare(
            'INSERT INTO system_settings (
                setting_key,
                setting_value,
                setting_type,
                setting_group,
                setting_label,
                description,
                is_public,
                updated_by
            ) VALUES (
                :setting_key,
                :setting_value,
                :setting_type,
                :setting_group,
                :setting_label,
                :description,
                :is_public,
                :updated_by
            )
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_type = VALUES(setting_type),
                setting_group = VALUES(setting_group),
                setting_label = VALUES(setting_label),
                description = VALUES(description),
                is_public = VALUES(is_public),
                updated_by = VALUES(updated_by)'
        );

        $stmt->execute([
            'setting_key' => $key,
            'setting_value' => $normalizedValue,
            'setting_type' => $type,
            'setting_group' => $group,
            'setting_label' => $label,
            'description' => $description,
            'is_public' => $isPublic ? 1 : 0,
            'updated_by' => $updatedBy,
        ]);

        return true;
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        $key = $this->validateKey($key);

        $stmt = $this->db->prepare(
            'DELETE FROM system_settings
             WHERE setting_key = :setting_key'
        );

        $stmt->execute([
            'setting_key' => $key,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Count all settings.
     */
    public function count(): int
    {
        return (int) $this->db
            ->query('SELECT COUNT(*) FROM system_settings')
            ->fetchColumn();
    }

    /**
     * Count settings in a group.
     */
    public function countByGroup(string $group): int
    {
        $group = trim($group);

        if ($group === '') {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM system_settings
             WHERE setting_group = :setting_group'
        );

        $stmt->execute([
            'setting_group' => $group,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Convert stored value to its appropriate PHP type.
     */
    private function castValue(
        mixed $value,
        string $type
    ): mixed {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => in_array(
                strtolower((string) $value),
                ['1', 'true', 'yes', 'on'],
                true
            ),

            'number' => is_numeric($value)
                ? (str_contains((string) $value, '.')
                    ? (float) $value
                    : (int) $value)
                : 0,

            'json' => json_decode(
                (string) $value,
                true
            ),

            default => (string) $value,
        };
    }

    /**
     * Normalize value before database storage.
     */
    private function normalizeValue(
        mixed $value,
        string $type
    ): ?string {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',

            'number' => $this->normalizeNumber($value),

            'email' => $this->normalizeEmail($value),

            'url' => $this->normalizeUrl($value),

            'json' => $this->normalizeJson($value),

            default => (string) $value,
        };
    }

    /**
     * Validate and normalize number.
     */
    private function normalizeNumber(mixed $value): string
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Setting value must be numeric.'
            );
        }

        return (string) $value;
    }

    /**
     * Validate email setting.
     */
    private function normalizeEmail(mixed $value): string
    {
        $email = trim((string) $value);

        if (
            $email !== '' &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Invalid email setting.'
            );
        }

        return $email;
    }

    /**
     * Validate URL setting.
     */
    private function normalizeUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if (
            $url !== '' &&
            !filter_var($url, FILTER_VALIDATE_URL)
        ) {
            throw new InvalidArgumentException(
                'Invalid URL setting.'
            );
        }

        return $url;
    }

    /**
     * Validate and encode JSON setting.
     */
    private function normalizeJson(mixed $value): string
    {
        if (is_string($value)) {
            json_decode($value);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }

            throw new InvalidArgumentException(
                'Invalid JSON setting.'
            );
        }

        $json = json_encode(
            $value,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new InvalidArgumentException(
                'Could not encode JSON setting.'
            );
        }

        return $json;
    }
}
