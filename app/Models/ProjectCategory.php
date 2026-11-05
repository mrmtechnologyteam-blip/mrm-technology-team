<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ProjectCategory
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all active project categories.
     */
    public function getActiveCategories(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, description
             FROM project_categories
             WHERE is_active = 1
             ORDER BY id ASC"
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find an active category by ID.
     */
    public function findActiveById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, description
             FROM project_categories
             WHERE id = :id
               AND is_active = 1
             LIMIT 1"
        );

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Get category by ID, including inactive categories.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_categories
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new category.
     */
    public function create(
        string $name,
        ?string $description = null
    ): int {
        $name = trim($name);

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Category name is required.'
            );
        }

        $stmt = $this->db->prepare(
            "INSERT INTO project_categories (
                name,
                description,
                is_active
            ) VALUES (
                :name,
                :description,
                1
            )"
        );

        $stmt->execute([
            'name' => $name,
            'description' => $description
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update category information.
     */
    public function update(
        int $id,
        string $name,
        ?string $description
    ): bool {
        $name = trim($name);

        if ($id <= 0 || $name === '') {
            throw new \InvalidArgumentException(
                'Invalid category information.'
            );
        }

        $stmt = $this->db->prepare(
            "UPDATE project_categories
             SET name = :name,
                 description = :description
             WHERE id = :id"
        );

        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Activate or deactivate a category.
     */
    public function setActive(
        int $id,
        bool $active
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE project_categories
             SET is_active = :is_active
             WHERE id = :id"
        );

        $stmt->execute([
            'is_active' => $active ? 1 : 0,
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }
}
