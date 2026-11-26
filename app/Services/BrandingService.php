<?php
declare(strict_types=1);

namespace App\Services;

use Core\Auth;
use Core\Database;

/**
 * Centralized White-Label Global Branding Service
 * Loads from MySQL with JSON cache invalidation, default fallback, and Super Admin draft preview mode.
 */
class BrandingService
{
    private const CACHE_FILE = BASE_PATH . '/storage/cache/branding.json';

    private const FALLBACK_DEFAULTS = [
        'website_name' => 'Travel Platform',
        'company_name' => 'Travel Agency Group',
        'website_tagline' => 'Curated Journeys Across Extraordinary Horizons',
        'company_description' => 'Bespoke international travel experiences, private expeditions, and seamless concierge booking management.',
        'primary_logo' => '',
        'secondary_logo' => '',
        'light_logo' => '',
        'dark_logo' => '',
        'favicon' => '',
        'primary_color' => '#0B1D2E',
        'secondary_color' => '#1A4332',
        'accent_color' => '#C59B5F',
        'contact_email' => 'concierge@travelplatform.local',
        'contact_number' => '+1 (555) 234-8910',
        'company_address' => 'Suite 400, Maritime Promenade',
        'social_links' => [
            'instagram' => '#',
            'facebook' => '#',
            'twitter' => '#',
            'linkedin' => '#',
            'youtube' => '#',
        ],
        'has_draft' => false,
        'is_previewing' => false,
    ];

    private static ?array $resolved = null;

    public static function getAll(bool $forceFresh = false): array
    {
        if (self::$resolved !== null && !$forceFresh) {
            return self::$resolved;
        }

        $live = self::loadLiveFromCacheOrDb($forceFresh);
        $merged = array_merge(self::FALLBACK_DEFAULTS, $live);

        // Normalize social_links array
        if (is_string($merged['social_links'])) {
            $decoded = json_decode($merged['social_links'], true);
            $merged['social_links'] = is_array($decoded) ? $decoded : self::FALLBACK_DEFAULTS['social_links'];
        } elseif (!is_array($merged['social_links'])) {
            $merged['social_links'] = self::FALLBACK_DEFAULTS['social_links'];
        }

        $draftData = null;
        if (!empty($live['draft_data'])) {
            $draftData = is_string($live['draft_data']) ? json_decode($live['draft_data'], true) : $live['draft_data'];
        }
        $merged['has_draft'] = is_array($draftData) && !empty($draftData);
        $merged['is_previewing'] = false;

        // Only authenticated Super Admin in preview mode sees draft branding
        if ($merged['has_draft'] && !empty($_SESSION['branding_preview']) && Auth::isSuperAdmin()) {
            foreach ($draftData as $k => $v) {
                if ($v !== null && $v !== '') {
                    $merged[$k] = $v;
                } elseif (in_array($k, ['website_tagline', 'company_description', 'contact_email', 'contact_number', 'company_address'], true)) {
                    $merged[$k] = $v;
                }
            }
            $merged['is_previewing'] = true;
        }

        self::$resolved = $merged;
        return $merged;
    }

    public static function getRawRecord(): array
    {
        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT * FROM branding_settings WHERE id = 1 LIMIT 1");
            if ($row) {
                $row['social_links'] = is_string($row['social_links']) ? (json_decode($row['social_links'], true) ?: []) : [];
                $row['draft_data'] = is_string($row['draft_data']) ? (json_decode($row['draft_data'], true) ?: []) : [];
                return array_merge(self::FALLBACK_DEFAULTS, $row);
            }
        } catch (\Throwable $e) {
            // Fall through to defaults
        }
        return self::FALLBACK_DEFAULTS;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::getAll();
        return $all[$key] ?? ($default ?? (self::FALLBACK_DEFAULTS[$key] ?? ''));
    }

    public static function logoUrl(string $variant = 'primary'): string
    {
        $all = self::getAll();
        $map = [
            'primary' => $all['primary_logo'] ?? '',
            'secondary' => $all['secondary_logo'] ?: ($all['primary_logo'] ?? ''),
            'light' => $all['light_logo'] ?: ($all['primary_logo'] ?? ''),
            'dark' => $all['dark_logo'] ?: ($all['primary_logo'] ?? ''),
            'favicon' => $all['favicon'] ?? '',
        ];

        $path = $map[$variant] ?? ($all['primary_logo'] ?? '');
        if ($path !== '') {
            return upload_url($path);
        }

        // Dynamic inline SVG monogram fallback derived purely from current DB website_name (zero hardcoding)
        $name = trim((string) ($all['website_name'] ?? 'TP'));
        $words = preg_split('/\s+/', $name) ?: ['T'];
        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : substr($words[0], 1, 1)));
        $bg = ($variant === 'light') ? '#FAF7F2' : ($all['primary_color'] ?? '#0B1D2E');
        $fg = ($variant === 'light') ? ($all['primary_color'] ?? '#0B1D2E') : '#FAF7F2';
        $accent = $all['accent_color'] ?? '#C59B5F';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">'
             . '<rect width="64" height="64" rx="14" fill="' . htmlspecialchars($bg, ENT_QUOTES) . '"/>'
             . '<circle cx="32" cy="32" r="24" fill="none" stroke="' . htmlspecialchars($accent, ENT_QUOTES) . '" stroke-width="2"/>'
             . '<text x="32" y="39" text-anchor="middle" font-family="Georgia, serif" font-weight="bold" font-size="22" fill="' . htmlspecialchars($fg, ENT_QUOTES) . '">'
             . htmlspecialchars($initials, ENT_QUOTES)
             . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function saveDraft(array $data, int $userId): void
    {
        $db = Database::getInstance();
        $existing = self::getRawRecord();
        $mergedDraft = array_merge($existing['draft_data'] ?? [], $data);

        $db->update(
            'branding_settings',
            [
                'draft_data' => json_encode($mergedDraft, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = 1'
        );

        $_SESSION['branding_preview'] = true;
        self::invalidateCache();
    }

    public static function discardDraft(): void
    {
        $db = Database::getInstance();
        $db->update('branding_settings', ['draft_data' => null], 'id = 1');
        unset($_SESSION['branding_preview']);
        self::invalidateCache();
    }

    public static function publish(array $data, int $userId): void
    {
        $db = Database::getInstance();

        // If publishing directly from preview mode, merge existing draft values for untouched upload fields
        $raw = self::getRawRecord();
        $draft = is_array($raw['draft_data'] ?? null) ? $raw['draft_data'] : [];
        foreach (['primary_logo', 'secondary_logo', 'light_logo', 'dark_logo', 'favicon'] as $imgKey) {
            if (empty($data[$imgKey]) && !empty($draft[$imgKey])) {
                $data[$imgKey] = $draft[$imgKey];
            }
        }

        $socialJson = is_array($data['social_links'] ?? null)
            ? json_encode($data['social_links'], JSON_UNESCAPED_SLASHES)
            : ($data['social_links'] ?? '{}');

        $payload = [
            'website_name' => $data['website_name'] ?? $raw['website_name'],
            'company_name' => $data['company_name'] ?? $raw['company_name'],
            'website_tagline' => $data['website_tagline'] ?? $raw['website_tagline'],
            'company_description' => $data['company_description'] ?? $raw['company_description'],
            'primary_logo' => $data['primary_logo'] ?? $raw['primary_logo'],
            'secondary_logo' => $data['secondary_logo'] ?? $raw['secondary_logo'],
            'light_logo' => $data['light_logo'] ?? $raw['light_logo'],
            'dark_logo' => $data['dark_logo'] ?? $raw['dark_logo'],
            'favicon' => $data['favicon'] ?? $raw['favicon'],
            'primary_color' => $data['primary_color'] ?? $raw['primary_color'],
            'secondary_color' => $data['secondary_color'] ?? $raw['secondary_color'],
            'accent_color' => $data['accent_color'] ?? $raw['accent_color'],
            'contact_email' => $data['contact_email'] ?? $raw['contact_email'],
            'contact_number' => $data['contact_number'] ?? $raw['contact_number'],
            'company_address' => $data['company_address'] ?? $raw['company_address'],
            'social_links' => $socialJson,
            'draft_data' => null,
            'updated_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $exists = $db->fetchColumn("SELECT id FROM branding_settings WHERE id = 1");
        if ($exists) {
            $db->update('branding_settings', $payload, 'id = 1');
        } else {
            $payload['id'] = 1;
            $db->insert('branding_settings', $payload);
        }

        unset($_SESSION['branding_preview']);
        self::invalidateCache();
    }

    public static function resetToDefaults(int $userId): void
    {
        self::publish(self::FALLBACK_DEFAULTS, $userId);
    }

    public static function invalidateCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            @unlink(self::CACHE_FILE);
        }
        self::$resolved = null;
    }

    public static function cssVariables(): string
    {
        $all = self::getAll();
        $primary = self::sanitizeHex($all['primary_color'] ?? '#0B1D2E', '#0B1D2E');
        $secondary = self::sanitizeHex($all['secondary_color'] ?? '#1A4332', '#1A4332');
        $accent = self::sanitizeHex($all['accent_color'] ?? '#C59B5F', '#C59B5F');

        return sprintf(
            ":root {\n  --brand-primary: %s;\n  --brand-primary-rgb: %s;\n  --brand-secondary: %s;\n  --brand-secondary-rgb: %s;\n  --brand-accent: %s;\n  --brand-accent-rgb: %s;\n}",
            $primary,
            self::hexToRgbString($primary),
            $secondary,
            self::hexToRgbString($secondary),
            $accent,
            self::hexToRgbString($accent)
        );
    }

    private static function sanitizeHex(string $hex, string $fallback): string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $hex) ? strtoupper($hex) : $fallback;
    }

    private static function hexToRgbString(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return sprintf('%d, %d, %d', hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    private static function loadLiveFromCacheOrDb(bool $forceFresh): array
    {
        if (!$forceFresh && file_exists(self::CACHE_FILE)) {
            $raw = @file_get_contents(self::CACHE_FILE);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT * FROM branding_settings WHERE id = 1 LIMIT 1");
            if ($row) {
                $cacheDir = dirname(self::CACHE_FILE);
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0755, true);
                }
                @file_put_contents(self::CACHE_FILE, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
                return $row;
            }
        } catch (\Throwable $e) {
            // Safe fallback before DB import or during connection errors
        }

        return [];
    }
}
