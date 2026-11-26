<?php
declare(strict_types=1);

/**
 * Travel Agency Website & Management System
 * Front Controller (Core PHP 8.2+)
 */

define('BASE_PATH', __DIR__);

// PSR-4-inspired lightweight autoloader for Core\ and App\ namespaces
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Core\\' => BASE_PATH . '/core/',
        'App\\'  => BASE_PATH . '/app/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require_once BASE_PATH . '/core/Helpers.php';
require_once BASE_PATH . '/views/partials/icons.php';

$config = app_config();
date_default_timezone_set($config['timezone'] ?? 'UTC');

if (!empty($config['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// Block direct access to internal folders across both Apache and PHP built-in server
$rawUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if (preg_match('#^/(app|config|core|database|modules|storage|views)(/|$)#i', $rawUriPath)) {
    http_response_code(403);
    exit('Forbidden');
}

// Handle PHP built-in server static files cleanly when testing with `php -S`
if (PHP_SAPI === 'cli-server') {
    $uriPath = $rawUriPath;
    $staticFile = BASE_PATH . $uriPath;
    if ($uriPath !== '/' && is_file($staticFile)) {
        if (str_ends_with($uriPath, '.php')) {
            http_response_code(403);
            exit('Forbidden');
        }
        return false;
    }
}

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

Core\Auth::startSession();

// Enforce CSRF verification on every mutating POST request automatically
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    Core\Csrf::verifyOrAbort();
}

$router = new Core\Router();

// Load modular route definitions
foreach ([
    'website',
    'packages',
    'bookings',
    'admin',
    'hr',
    'employee',
    'customer',
] as $module) {
    $routeFile = BASE_PATH . "/modules/{$module}/routes.php";
    if (file_exists($routeFile)) {
        require $routeFile;
    }
}

try {
    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
    clear_old();
} catch (\Throwable $e) {
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    if (!empty($config['debug'])) {
        echo '<div style="font-family:sans-serif;max-width:860px;margin:40px auto;padding:24px;border:1px solid #e5d5c5;background:#faf7f2;color:#0b1d2e;">';
        echo '<h2>Application Error</h2><p><strong>' . e($e->getMessage()) . '</strong></p>';
        echo '<pre style="overflow:auto;background:#fff;padding:12px;border:1px solid #ddd;">' . e($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        Core\View::render('frontend/500', ['pageTitle' => 'Server Error'], 'frontend');
    }
}
