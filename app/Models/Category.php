<?php
declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel
{
    protected string $table = 'travel_categories';

    public function allPublic(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM travel_packages p WHERE p.category_id = c.id AND p.is_active = 1 AND p.deleted_at IS NULL) AS package_count
             FROM travel_categories c
             WHERE c.is_active = 1 AND c.deleted_at IS NULL
             ORDER BY c.sort_order ASC, c.id ASC"
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM travel_categories WHERE slug = :slug AND is_active = 1 AND deleted_at IS NULL LIMIT 1",
            ['slug' => $slug]
        );
    }
}
