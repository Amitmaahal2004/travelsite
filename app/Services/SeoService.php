<?php
declare(strict_types=1);

namespace App\Services;

use Core\Database;

/**
 * Dynamic SEO Metadata Service
 */
class SeoService
{
    public static function forPage(string $pageKey, array $overrides = []): array
    {
        $brand = BrandingService::getAll();
        $siteName = $brand['website_name'] ?? 'Travel Platform';
        $tagline = $brand['website_tagline'] ?? '';

        $dbRow = null;
        $globalRow = null;
        try {
            $db = Database::getInstance();
            $dbRow = $db->fetch("SELECT * FROM seo_settings WHERE page_key = :k LIMIT 1", ['k' => $pageKey]);
            if ($pageKey !== 'global') {
                $globalRow = $db->fetch("SELECT * FROM seo_settings WHERE page_key = 'global' LIMIT 1");
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }

        $title = $overrides['title'] ?? ($dbRow['meta_title'] ?? ($globalRow['meta_title'] ?? $tagline));
        if (!str_contains((string) $title, (string) $siteName)) {
            $title = trim((string) $title) . ' | ' . $siteName;
        }

        $description = $overrides['description'] ?? ($dbRow['meta_description'] ?? ($globalRow['meta_description'] ?? ($brand['company_description'] ?? '')));
        $keywords = $overrides['keywords'] ?? ($dbRow['meta_keywords'] ?? ($globalRow['meta_keywords'] ?? 'travel, luxury tours, holiday packages'));
        $ogImage = $overrides['og_image'] ?? ($dbRow['og_image'] ?? ($globalRow['og_image'] ?? ''));
        $robots = $overrides['robots'] ?? ($dbRow['robots'] ?? ($globalRow['robots'] ?? 'index, follow'));

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'og_image' => $ogImage ? upload_url($ogImage) : BrandingService::logoUrl('primary'),
            'robots' => $robots,
        ];
    }
}
