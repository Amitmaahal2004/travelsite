<?php
declare(strict_types=1);

namespace App\Models;

class Package extends BaseModel
{
    protected string $table = 'travel_packages';

    public function featured(int $limit = 6): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, d.name AS destination_name, d.slug AS destination_slug, d.location_label,
                    cat.name AS category_name, cat.slug AS category_slug
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE p.is_active = 1 AND p.is_featured = 1 AND p.deleted_at IS NULL
             ORDER BY p.id ASC
             LIMIT :lim",
            ['lim' => $limit]
        );
    }

    public function seasonalOffers(int $limit = 4): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, d.name AS destination_name, cat.name AS category_name
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE p.is_active = 1 AND p.is_seasonal_offer = 1 AND p.deleted_at IS NULL
             ORDER BY p.id ASC
             LIMIT :lim",
            ['lim' => $limit]
        );
    }

    public function paginatePublic(array $filters = [], int $page = 1, int $perPage = 9): array
    {
        $where = ['p.is_active = 1', 'p.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE :q OR p.subtitle LIKE :q OR d.name LIKE :q OR c.name LIKE :q)';
            $params['q'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['destination'])) {
            $where[] = '(d.slug = :dest OR d.id = :dest_id)';
            $params['dest'] = $filters['destination'];
            $params['dest_id'] = (int) $filters['destination'];
        }
        if (!empty($filters['category'])) {
            $where[] = '(cat.slug = :cat OR cat.id = :cat_id)';
            $params['cat'] = $filters['category'];
            $params['cat_id'] = (int) $filters['category'];
        }
        if (!empty($filters['offer'])) {
            $where[] = 'p.is_seasonal_offer = 1';
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'COALESCE(p.discount_price, p.base_price) <= :max_price';
            $params['max_price'] = (float) $filters['max_price'];
        }

        $orderSql = match ($filters['sort'] ?? 'featured') {
            'price_asc' => 'COALESCE(p.discount_price, p.base_price) ASC',
            'price_desc' => 'COALESCE(p.discount_price, p.base_price) DESC',
            'duration' => 'p.duration_days ASC',
            default => 'p.is_featured DESC, p.id DESC',
        };

        $whereSql = implode(' AND ', $where);
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN countries c ON c.id = d.country_id
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE {$whereSql}",
            $params
        );

        $meta = paginate_meta($total, $perPage, $page);

        $rows = $this->db->fetchAll(
            "SELECT p.*, d.name AS destination_name, d.slug AS destination_slug, d.location_label,
                    c.name AS country_name, cat.name AS category_name, cat.slug AS category_slug
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN countries c ON c.id = d.country_id
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE {$whereSql}
             ORDER BY {$orderSql}
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        return ['items' => $rows, 'meta' => $meta];
    }

    public function findFullBySlug(string $slug): ?array
    {
        $pkg = $this->db->fetch(
            "SELECT p.*, d.name AS destination_name, d.slug AS destination_slug, d.location_label,
                    c.name AS country_name, cat.name AS category_name, cat.slug AS category_slug
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN countries c ON c.id = d.country_id
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE p.slug = :slug AND p.is_active = 1 AND p.deleted_at IS NULL
             LIMIT 1",
            ['slug' => $slug]
        );
        if (!$pkg) {
            return null;
        }

        $id = (int) $pkg['id'];
        $pkg['itineraries'] = $this->db->fetchAll(
            "SELECT * FROM package_itineraries WHERE package_id = :pid ORDER BY day_number ASC",
            ['pid' => $id]
        );
        $pkg['images'] = $this->db->fetchAll(
            "SELECT * FROM package_images WHERE package_id = :pid ORDER BY is_primary DESC, sort_order ASC",
            ['pid' => $id]
        );
        $pkg['inclusions'] = $this->db->fetchAll(
            "SELECT * FROM package_inclusions WHERE package_id = :pid ORDER BY sort_order ASC",
            ['pid' => $id]
        );
        $pkg['exclusions'] = $this->db->fetchAll(
            "SELECT * FROM package_exclusions WHERE package_id = :pid ORDER BY sort_order ASC",
            ['pid' => $id]
        );

        return $pkg;
    }

    public function forDestination(int $destinationId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, cat.name AS category_name
             FROM travel_packages p
             INNER JOIN travel_categories cat ON cat.id = p.category_id
             WHERE p.destination_id = :did AND p.is_active = 1 AND p.deleted_at IS NULL
             ORDER BY p.is_featured DESC, p.id ASC",
            ['did' => $destinationId]
        );
    }
}
