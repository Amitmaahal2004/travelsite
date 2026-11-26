<?php
declare(strict_types=1);

namespace Core;

/**
 * CSRF Protection Service
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(self::token()) . '">';
    }

    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . e(self::token()) . '">';
    }

    public static function verify(?string $token = null): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $provided = $token ?? ($_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        $stored = $_SESSION[self::SESSION_KEY] ?? '';

        if ($provided === '' || $stored === '') {
            return false;
        }

        return hash_equals($stored, (string) $provided);
    }

    public static function verifyOrAbort(): void
    {
        if (!self::verify()) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.'], 419);
            }
            http_response_code(419);
            flash('error', 'Your security session expired. Please submit the form again.');
            redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
        }
    }
}
