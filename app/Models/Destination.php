<?php
declare(strict_types=1);

namespace App\Models;

class Destination extends BaseModel
{
    protected string $table = 'destinations';

    public function featured(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT d.*, c.name AS country_name, c.region
             FROM destinations d
             INNER JOIN countries c ON c.id = d.country_id
             WHERE d.is_active = 1 AND d.is_featured = 1 AND d.deleted_at IS NULL
             ORDER BY d.sort_order ASC, d.id ASC
             LIMIT :lim",
            ['lim' => $limit]
        );
    }

    public function paginatePublic(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $where = ['d.is_active = 1', 'd.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(d.name LIKE :q OR d.location_label LIKE :q OR c.name LIKE :q OR d.tagline LIKE :q)';
            $params['q'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['region'])) {
            $where[] = 'c.region = :region';
            $params['region'] = $filters['region'];
        }
        if (!empty($filters['country_id'])) {
            $where[] = 'd.country_id = :cid';
            $params['cid'] = (int) $filters['country_id'];
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM destinations d INNER JOIN countries c ON c.id = d.country_id WHERE {$whereSql}",
            $params
        );
        $meta = paginate_meta($total, $perPage, $page);

        $rows = $this->db->fetchAll(
            "SELECT d.*, c.name AS country_name, c.region,
                    (SELECT COUNT(*) FROM travel_packages p WHERE p.destination_id = d.id AND p.is_active = 1 AND p.deleted_at IS NULL) AS package_count
             FROM destinations d
             INNER JOIN countries c ON c.id = d.country_id
             WHERE {$whereSql}
             ORDER BY d.is_featured DESC, d.sort_order ASC, d.id DESC
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        return ['items' => $rows, 'meta' => $meta];
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->db->fetch(
            "SELECT d.*, c.name AS country_name, c.region, c.currency
             FROM destinations d
             INNER JOIN countries c ON c.id = d.country_id
             WHERE d.slug = :slug AND d.is_active = 1 AND d.deleted_at IS NULL
             LIMIT 1",
            ['slug' => $slug]
        );
        if ($row) {
            $row['highlights'] = is_string($row['highlights']) ? (json_decode($row['highlights'], true) ?: []) : [];
            $row['gallery'] = is_string($row['gallery']) ? (json_decode($row['gallery'], true) ?: []) : [];
        }
        return $row;
    }

    public function allRegions(): array
    {
        return $this->db->fetchAll("SELECT DISTINCT region FROM countries WHERE is_active = 1 ORDER BY region ASC");
    }

    public function allActive(): array
    {
        return $this->db->fetchAll(
            "SELECT d.id, d.name, d.slug, d.location_label, c.name AS country_name
             FROM destinations d
             INNER JOIN countries c ON c.id = d.country_id
             WHERE d.is_active = 1 AND d.deleted_at IS NULL
             ORDER BY d.name ASC"
        );
    }
}
