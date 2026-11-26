<?php
declare(strict_types=1);

namespace App\Models;

class Cms extends BaseModel
{
    protected string $table = 'website_pages';

    public function homepageSections(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM homepage_sections WHERE is_active = 1 ORDER BY sort_order ASC");
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['section_key']] = $row;
        }
        return $indexed;
    }

    public function menus(string $location): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM website_menus WHERE location = :loc AND is_active = 1 ORDER BY sort_order ASC, id ASC",
            ['loc' => $location]
        );
    }

    public function testimonials(bool $featuredOnly = false): array
    {
        $sql = "SELECT t.*, p.title AS package_title
                FROM testimonials t
                LEFT JOIN travel_packages p ON p.id = t.package_id
                WHERE t.is_approved = 1";
        if ($featuredOnly) {
            $sql .= " AND t.is_featured = 1";
        }
        $sql .= " ORDER BY t.sort_order ASC, t.id DESC";
        return $this->db->fetchAll($sql);
    }

    public function faqs(): array
    {
        return $this->db->fetchAll("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    }

    public function blogPosts(int $page = 1, int $perPage = 6, ?string $category = null): array
    {
        $where = ["b.status = 'published'", "b.deleted_at IS NULL"];
        $params = [];
        if ($category) {
            $where[] = "b.category = :cat";
            $params['cat'] = $category;
        }
        $whereSql = implode(' AND ', $where);
        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM blog_posts b WHERE {$whereSql}", $params);
        $meta = paginate_meta($total, $perPage, $page);

        $items = $this->db->fetchAll(
            "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM blog_posts b
             LEFT JOIN users u ON u.id = b.author_id
             WHERE {$whereSql}
             ORDER BY b.is_featured DESC, b.published_at DESC, b.id DESC
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        return ['items' => $items, 'meta' => $meta];
    }

    public function findBlogBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM blog_posts b
             LEFT JOIN users u ON u.id = b.author_id
             WHERE b.slug = :slug AND b.status = 'published' AND b.deleted_at IS NULL LIMIT 1",
            ['slug' => $slug]
        );
    }

    public function findPageBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM website_pages WHERE slug = :slug AND is_active = 1 LIMIT 1",
            ['slug' => $slug]
        );
    }

    public function activeOffers(): array
    {
        return $this->db->fetchAll(
            "SELECT o.*, p.title AS package_title, p.slug AS package_slug
             FROM seasonal_offers o
             LEFT JOIN travel_packages p ON p.id = o.package_id
             WHERE o.is_active = 1 AND o.end_date >= CURDATE()
             ORDER BY o.end_date ASC"
        );
    }
}
