<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuditService;
use Core\Auth;
use Core\Database;
use Core\Validator;
use Core\View;

class RoleController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY is_system DESC, id ASC");
        $permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module ASC, id ASC");

        $groupedPerms = [];
        foreach ($permissions as $p) {
            $groupedPerms[$p['module']][] = $p;
        }

        $assigned = [];
        $rpRows = $db->fetchAll("SELECT role_id, permission_id FROM role_permissions");
        foreach ($rpRows as $rp) {
            $assigned[(int) $rp['role_id']][] = (int) $rp['permission_id'];
        }

        View::render('admin/roles', [
            'pageTitle' => 'Roles & Permission Control',
            'portal' => 'admin',
            'roles' => $roles,
            'groupedPerms' => $groupedPerms,
            'assigned' => $assigned,
        ], 'dashboard');
    }

    public function create(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'name' => 'required|min:2|max:80',
            'description' => 'max:255',
            'dashboard_prefix' => 'required|in:/admin,/hr,/employee,/customer',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/roles');
        }

        $db = Database::getInstance();
        $slug = slugify($clean['name']);
        if ($db->fetchColumn("SELECT id FROM roles WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }

        $roleId = $db->insert('roles', [
            'name' => $clean['name'],
            'slug' => $slug,
            'description' => $clean['description'] ?: null,
            'dashboard_prefix' => $clean['dashboard_prefix'],
            'is_system' => 0,
            'is_active' => 1,
        ]);

        // Ensure custom role always has basic dashboard.view
        $dashPerm = $db->fetchColumn("SELECT id FROM permissions WHERE slug = 'dashboard.view' LIMIT 1");
        if ($dashPerm) {
            $db->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => (int) $dashPerm]);
        }

        AuditService::log('roles', 'create', "Created custom role {$clean['name']}", 'role', $roleId, (int) Auth::id());
        flash('success', "Custom role '{$clean['name']}' created. Configure its permissions below.");
        redirect('/admin/roles');
    }

    public function updatePermissions(array $params): void
    {
        $roleId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $role = $db->fetch("SELECT * FROM roles WHERE id = :id LIMIT 1", ['id' => $roleId]);
        if (!$role) {
            flash('error', 'Role not found.');
            redirect('/admin/roles');
        }

        if ($role['slug'] === 'super_admin') {
            flash('warning', 'Super Admin always retains complete control over all modules.');
            redirect('/admin/roles');
        }

        $permIds = array_map('intval', $_POST['permissions'] ?? []);
        $db->beginTransaction();
        try {
            $db->delete('role_permissions', 'role_id = :rid', ['rid' => $roleId]);
            foreach (array_unique($permIds) as $pid) {
                if ($pid > 0) {
                    $db->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => $pid]);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        AuditService::log('roles', 'update', "Updated permissions for role {$role['name']}", 'role', $roleId, (int) Auth::id());
        flash('success', "Permissions updated for {$role['name']}.");
        redirect('/admin/roles');
    }

    public function delete(array $params): void
    {
        $roleId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $role = $db->fetch("SELECT * FROM roles WHERE id = :id LIMIT 1", ['id' => $roleId]);
        if (!$role || (int) $role['is_system'] === 1) {
            flash('error', 'System roles cannot be deleted.');
            redirect('/admin/roles');
        }
        $inUse = (int) $db->fetchColumn("SELECT COUNT(*) FROM user_roles WHERE role_id = :rid", ['rid' => $roleId]);
        if ($inUse > 0) {
            flash('error', 'Reassign users from this role before deleting it.');
            redirect('/admin/roles');
        }
        $db->delete('roles', 'id = :id', ['id' => $roleId]);
        AuditService::log('roles', 'delete', "Deleted custom role {$role['name']}", 'role', $roleId, (int) Auth::id());
        flash('success', 'Custom role deleted.');
        redirect('/admin/roles');
    }
}
