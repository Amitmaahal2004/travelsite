<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\View;

/**
 * Server-Side RBAC Permission Enforcement
 */
class PermissionMiddleware
{
    public function handle(array $params = [], string $permissionSlug = ''): void
    {
        if (!Auth::check()) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            redirect('/login');
        }

        if ($permissionSlug !== '' && !Auth::can($permissionSlug)) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => "Permission denied ({$permissionSlug})."], 403);
            }
            http_response_code(403);
            View::render('frontend/403', [
                'pageTitle' => 'Permission Denied',
                'requiredPermission' => $permissionSlug,
            ], 'frontend');
            exit;
        }
    }
}
