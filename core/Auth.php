<?php
declare(strict_types=1);

namespace Core;

use App\Services\AuditService;

/**
 * Authentication, Session Security, Brute-Force Protection & RBAC Engine
 */
class Auth
{
    private static ?array $cachedUser = null;
    private static ?array $cachedPermissions = null;

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = app_config();
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_name($config['session_name'] ?? 'TRAVEL_PLATFORM_SESSID');
        session_set_cookie_params([
            'lifetime' => (int) ($config['session_lifetime'] ?? 7200),
            'path' => base_path() ?: '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Enforce idle timeout
        $maxLifetime = (int) ($config['session_lifetime'] ?? 7200);
        if (isset($_SESSION['_last_activity']) && (time() - (int) $_SESSION['_last_activity']) > $maxLifetime) {
            self::logout();
            session_start();
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function user(): ?array
    {
        self::startSession();
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = $_SESSION['auth_user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.id AS role_id, r.name AS role_name, r.slug AS role_slug, r.dashboard_prefix
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.id = :id AND u.deleted_at IS NULL
             LIMIT 1",
            ['id' => (int) $userId]
        );

        if (!$user || $user['status'] !== 'active') {
            self::logout();
            return null;
        }

        // Attach role-specific profile ids (employee_id / customer_id)
        $user['employee_id'] = $db->fetchColumn(
            "SELECT id FROM employees WHERE user_id = :uid AND deleted_at IS NULL LIMIT 1",
            ['uid' => $user['id']]
        ) ?: null;

        $user['customer_id'] = $db->fetchColumn(
            "SELECT id FROM customers WHERE user_id = :uid AND deleted_at IS NULL LIMIT 1",
            ['uid' => $user['id']]
        ) ?: null;

        self::$cachedUser = $user;
        return $user;
    }

    public static function isLockedOut(string $email): ?int
    {
        $config = app_config();
        $maxAttempts = (int) ($config['max_login_attempts'] ?? 5);
        $lockoutMins = (int) ($config['lockout_minutes'] ?? 15);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $db = Database::getInstance();
        $since = date('Y-m-d H:i:s', time() - ($lockoutMins * 60));

        $row = $db->fetch(
            "SELECT COUNT(*) AS fail_count, MAX(attempted_at) AS last_attempt
             FROM login_attempts
             WHERE (identifier = :email OR ip_address = :ip)
               AND success = 0
               AND attempted_at >= :since",
            ['email' => strtolower($email), 'ip' => $ip, 'since' => $since]
        );

        if ($row && (int) $row['fail_count'] >= $maxAttempts) {
            $unlockAt = strtotime((string) $row['last_attempt']) + ($lockoutMins * 60);
            $remaining = $unlockAt - time();
            return $remaining > 0 ? (int) ceil($remaining / 60) : null;
        }

        return null;
    }

    public static function recordLoginAttempt(string $email, bool $success): void
    {
        $db = Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $db->insert('login_attempts', [
            'identifier' => strtolower($email),
            'ip_address' => $ip,
            'attempted_at' => date('Y-m-d H:i:s'),
            'success' => $success ? 1 : 0,
        ]);

        if ($success) {
            // Clear prior failed attempts for this identifier
            $db->delete(
                'login_attempts',
                'identifier = :email AND success = 0',
                ['email' => strtolower($email)]
            );
        }
    }

    public static function attempt(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        if ($waitMins = self::isLockedOut($email)) {
            return [
                'ok' => false,
                'message' => "Too many failed login attempts. Please wait {$waitMins} minute(s) before trying again.",
            ];
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.slug AS role_slug, r.dashboard_prefix
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE LOWER(u.email) = :email AND u.deleted_at IS NULL
             LIMIT 1",
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::recordLoginAttempt($email, false);
            return ['ok' => false, 'message' => 'Invalid email address or password.'];
        }

        if ($user['status'] === 'blocked') {
            return ['ok' => false, 'message' => 'Your account has been suspended. Please contact support.'];
        }

        if ($user['status'] !== 'active') {
            return ['ok' => false, 'message' => 'Your account is currently inactive.'];
        }

        self::recordLoginAttempt($email, true);
        self::loginUser((int) $user['id']);

        $db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
        AuditService::log('auth', 'login', 'User signed in successfully', 'user', (int) $user['id']);

        return ['ok' => true, 'user' => self::user()];
    }

    public static function loginUser(int $userId): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = $userId;
        $_SESSION['_last_activity'] = time();
        self::$cachedUser = null;
        self::$cachedPermissions = null;
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!empty($_SESSION['auth_user_id'])) {
                try {
                    AuditService::log('auth', 'logout', 'User signed out', 'user', (int) $_SESSION['auth_user_id']);
                } catch (\Throwable $e) {
                    // Ignore logging failures during logout
                }
            }
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
        }
        self::$cachedUser = null;
        self::$cachedPermissions = null;
    }

    public static function roleSlug(): ?string
    {
        $user = self::user();
        return $user['role_slug'] ?? null;
    }

    public static function isSuperAdmin(): bool
    {
        return self::roleSlug() === 'super_admin';
    }

    public static function permissions(): array
    {
        $user = self::user();
        if (!$user) {
            return [];
        }

        if (self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT p.slug
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :role_id",
            ['role_id' => (int) $user['role_id']]
        );

        self::$cachedPermissions = array_column($rows, 'slug');
        return self::$cachedPermissions;
    }

    public static function can(string $permissionSlug): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        // Super Admin retains complete control over all modules at all times
        if ($user['role_slug'] === 'super_admin') {
            return true;
        }
        return in_array($permissionSlug, self::permissions(), true);
    }

    public static function canAny(array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $slug) {
            if (self::can($slug)) {
                return true;
            }
        }
        return false;
    }

    public static function dashboardUrl(?array $user = null): string
    {
        $user = $user ?? self::user();
        if (!$user) {
            return url('/login');
        }
        $prefix = $user['dashboard_prefix'] ?? '/customer';
        return url($prefix);
    }
}
