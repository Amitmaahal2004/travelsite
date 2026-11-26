<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\View;

/**
 * Enforces Role Portal Boundaries while allowing custom admin-scoped roles
 */
class RoleMiddleware
{
    public function handle(array $params = [], string ...$allowedRoles): void
    {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

        if ($user['role_slug'] === 'super_admin') {
            return;
        }

        if (in_array($user['role_slug'], $allowedRoles, true)) {
            return;
        }

        // Allow custom roles whose dashboard_prefix targets '/admin' when accessing '/admin' portal
        // (Granular PermissionMiddleware still enforces every specific module/action!)
        if (in_array('super_admin', $allowedRoles, true) && ($user['dashboard_prefix'] ?? '') === '/admin') {
            return;
        }

        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Access denied for your role.'], 403);
        }
        http_response_code(403);
        View::render('frontend/403', ['pageTitle' => 'Access Restricted', 'solidHeader' => true], 'frontend');
        exit;
    }
}
