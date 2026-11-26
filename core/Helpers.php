<?php
declare(strict_types=1);

use App\Services\BrandingService;
use Core\Auth;

/**
 * Global Helper Functions
 */

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/config/app.php';
    }
    if ($key === null) {
        return $config;
    }
    $parts = explode('.', $key);
    $val = $config;
    foreach ($parts as $part) {
        if (!is_array($val) || !array_key_exists($part, $val)) {
            return $default;
        }
        $val = $val[$part];
    }
    return $val;
}

function base_path(): string
{
    static $basePath = null;
    if ($basePath !== null) {
        return $basePath;
    }

    $configured = trim((string) app_config('base_url', ''));
    if ($configured !== '') {
        $parsed = parse_url($configured, PHP_URL_PATH);
        $basePath = $parsed ? rtrim($parsed, '/') : '';
        return $basePath;
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        $basePath = '';
    } else {
        $basePath = rtrim($dir, '/');
    }
    return $basePath;
}

function url(string $path = '/'): string
{
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}

function full_url(string $path = '/'): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . url($path);
}

function asset(string $path): string
{
    return url('public/assets/' . ltrim($path, '/'));
}

function upload_url(?string $path, ?string $fallback = null): string
{
    if ($path !== null && $path !== '') {
        if (preg_match('#^https?://#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($clean, 'public/')) {
            return url($clean);
        }
        return url('public/uploads/' . $clean);
    }
    return $fallback ? asset($fallback) : '';
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function brand(string $key, mixed $default = null): mixed
{
    return BrandingService::get($key, $default);
}

function brand_logo(string $variant = 'primary'): string
{
    return BrandingService::logoUrl($variant);
}

function can(string $permission): bool
{
    return Auth::can($permission);
}

function flash(string $type, ?string $message = null): mixed
{
    Auth::startSession();
    if ($message !== null) {
        $_SESSION['_flash'][$type] = $message;
        return null;
    }
    $val = $_SESSION['_flash'][$type] ?? null;
    unset($_SESSION['_flash'][$type]);
    return $val;
}

function all_flashes(): array
{
    Auth::startSession();
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function old(string $key, mixed $default = ''): string
{
    Auth::startSession();
    $val = $_SESSION['_old'][$key] ?? $default;
    return (string) $val;
}

function set_old(array $data): void
{
    Auth::startSession();
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    Auth::startSession();
    unset($_SESSION['_old']);
}

function redirect(string $to): never
{
    $target = preg_match('#^https?://#i', $to) ? $to : (str_starts_with($to, base_path() . '/') ? $to : url($to));
    header('Location: ' . $target);
    exit;
}

function is_ajax(): bool
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function money(float|int|string|null $amount, ?string $currency = null): string
{
    $val = (float) ($amount ?? 0);
    $symbol = match ($currency ?? app_config('default_currency', 'USD')) {
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        default => app_config('currency_symbol', '$'),
    };
    return $symbol . number_format($val, 2);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item-' . time();
}

function paginate_meta(int $total, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int) ceil($total / max(1, $perPage)));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
    ];
}

function current_route_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim($path, '/');
}

function is_active_nav(string $prefix): string
{
    $current = current_route_path();
    if ($prefix === '/') {
        return $current === '/' ? 'active' : '';
    }
    return str_starts_with($current, $prefix) ? 'active' : '';
}

function status_badge_class(string $status): string
{
    return match (strtolower(trim($status))) {
        'confirmed', 'paid', 'completed', 'approved', 'active', 'published', 'present', 'resolved' => 'badge-success',
        'pending', 'enquiry received', 'under review', 'payment pending', 'partially paid', 'partial', 'in_progress', 'late', 'remote', 'limited' => 'badge-warning',
        'cancelled', 'rejected', 'failed', 'blocked', 'absent', 'sold_out', 'terminated' => 'badge-danger',
        'refunded', 'draft', 'inactive', 'closed', 'on_leave' => 'badge-neutral',
        default => 'badge-info',
    };
}
