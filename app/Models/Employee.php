<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\AuditService;
use App\Services\NotificationService;

class Employee extends BaseModel
{
    protected string $table = 'employees';

    public function paginateDirectory(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = ['e.deleted_at IS NULL', 'u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR e.employee_code LIKE :q OR e.designation LIKE :q)";
            $params['q'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['department_id'])) {
            $where[] = "e.department_id = :did";
            $params['did'] = (int) $filters['department_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "e.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM employees e INNER JOIN users u ON u.id = e.user_id WHERE {$whereSql}",
            $params
        );
        $meta = paginate_meta($total, $perPage, $page);

        $items = $this->db->fetchAll(
            "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.status AS account_status,
                    d.name AS department_name, r.name AS role_name, r.slug AS role_slug
             FROM employees e
             INNER JOIN users u ON u.id = e.user_id
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE {$whereSql}
             ORDER BY e.id DESC
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        return ['items' => $items, 'meta' => $meta];
    }

    public function findFull(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.status AS account_status,
                    d.name AS department_name, r.id AS role_id, r.name AS role_name, r.slug AS role_slug
             FROM employees e
             INNER JOIN users u ON u.id = e.user_id
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.id = :id AND e.deleted_at IS NULL LIMIT 1",
            ['id' => $id]
        );
    }

    public function allActive(): array
    {
        return $this->db->fetchAll(
            "SELECT e.id, e.employee_code, e.designation, u.id AS user_id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, d.name AS department_name
             FROM employees e
             INNER JOIN users u ON u.id = e.user_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.status = 'active' AND e.deleted_at IS NULL AND u.deleted_at IS NULL
             ORDER BY u.first_name ASC"
        );
    }

    public function createEmployee(array $data, int $actorUserId): int
    {
        $this->db->beginTransaction();
        try {
            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0, 0xffff), random_int(0, 0xffff),
                random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
            );

            $userId = $this->db->insert('users', [
                'uuid' => $uuid,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?: null,
                'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $roleId = (int) ($data['role_id'] ?? $this->db->fetchColumn("SELECT id FROM roles WHERE slug = 'employee' LIMIT 1"));
            $this->db->insert('user_roles', ['user_id' => $userId, 'role_id' => $roleId]);

            $empCode = 'EMP-' . str_pad((string) ($userId + 10), 4, '0', STR_PAD_LEFT);
            $empId = $this->db->insert('employees', [
                'user_id' => $userId,
                'department_id' => !empty($data['department_id']) ? (int) $data['department_id'] : null,
                'employee_code' => $empCode,
                'designation' => $data['designation'],
                'employment_type' => $data['employment_type'] ?? 'full_time',
                'join_date' => $data['join_date'] ?: date('Y-m-d'),
                'base_salary' => (float) ($data['base_salary'] ?? 0),
                'commission_rate' => (float) ($data['commission_rate'] ?? 5.0),
                'emergency_contact' => $data['emergency_contact'] ?: null,
                'address' => $data['address'] ?: null,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();
            AuditService::log('employees', 'create', "Created employee {$empCode} ({$data['first_name']} {$data['last_name']})", 'employee', $empId, $actorUserId);
            return $empId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateEmployee(int $empId, array $data, int $actorUserId, bool $allowRoleChange = false): void
    {
        $emp = $this->findFull($empId);
        if (!$emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $this->db->beginTransaction();
        try {
            $userUpdate = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?: null,
                'status' => $data['status'] === 'terminated' ? 'inactive' : 'active',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->update('users', $userUpdate, 'id = :id', ['id' => (int) $emp['user_id']]);

            if ($allowRoleChange && !empty($data['role_id'])) {
                $this->db->delete('user_roles', 'user_id = :uid', ['uid' => (int) $emp['user_id']]);
                $this->db->insert('user_roles', ['user_id' => (int) $emp['user_id'], 'role_id' => (int) $data['role_id']]);
            }

            $this->db->update('employees', [
                'department_id' => !empty($data['department_id']) ? (int) $data['department_id'] : null,
                'designation' => $data['designation'],
                'employment_type' => $data['employment_type'] ?? 'full_time',
                'base_salary' => (float) ($data['base_salary'] ?? $emp['base_salary']),
                'commission_rate' => (float) ($data['commission_rate'] ?? $emp['commission_rate']),
                'performance_score' => (float) ($data['performance_score'] ?? $emp['performance_score']),
                'emergency_contact' => $data['emergency_contact'] ?: null,
                'address' => $data['address'] ?: null,
                'notes' => $data['notes'] ?: null,
                'status' => $data['status'] ?? 'active',
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $empId]);

            $this->db->commit();
            AuditService::log('employees', 'update', "Updated employee {$emp['employee_code']}", 'employee', $empId, $actorUserId);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function departments(): array
    {
        return $this->db->fetchAll(
            "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS manager_name,
                    (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id AND e.deleted_at IS NULL) AS headcount
             FROM departments d
             LEFT JOIN users u ON u.id = d.manager_user_id
             ORDER BY d.name ASC"
        );
    }

    public function attendanceForDate(string $date, ?int $departmentId = null): array
    {
        $where = "e.deleted_at IS NULL";
        $params = ['dt' => $date];
        if ($departmentId) {
            $where .= " AND e.department_id = :did";
            $params['did'] = $departmentId;
        }
        return $this->db->fetchAll(
            "SELECT e.id AS employee_id, e.employee_code, e.designation,
                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                    d.name AS department_name,
                    ea.id AS attendance_id, ea.check_in, ea.check_out, ea.status AS attendance_status, ea.notes
             FROM employees e
             INNER JOIN users u ON u.id = e.user_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN employee_attendance ea ON ea.employee_id = e.id AND ea.attendance_date = :dt
             WHERE {$where}
             ORDER BY u.first_name ASC",
            $params
        );
    }

    public function markAttendance(int $empId, string $date, string $status, ?string $checkIn, ?string $checkOut, ?string $notes, int $markerId): void
    {
        $existing = $this->db->fetchColumn(
            "SELECT id FROM employee_attendance WHERE employee_id = :eid AND attendance_date = :dt LIMIT 1",
            ['eid' => $empId, 'dt' => $date]
        );
        if ($existing) {
            $this->db->update('employee_attendance', [
                'status' => $status,
                'check_in' => $checkIn ?: null,
                'check_out' => $checkOut ?: null,
                'notes' => $notes ?: null,
                'marked_by' => $markerId,
            ], 'id = :id', ['id' => (int) $existing]);
        } else {
            $this->db->insert('employee_attendance', [
                'employee_id' => $empId,
                'attendance_date' => $date,
                'status' => $status,
                'check_in' => $checkIn ?: null,
                'check_out' => $checkOut ?: null,
                'notes' => $notes ?: null,
                'marked_by' => $markerId,
            ]);
        }
    }

    public function leaveRequests(?string $status = null): array
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where = 'lr.status = :st';
            $params['st'] = $status;
        }
        return $this->db->fetchAll(
            "SELECT lr.*, e.employee_code, e.user_id AS employee_user_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS employee_name, u.email AS employee_email,
                    d.name AS department_name,
                    CONCAT(ru.first_name, ' ', ru.last_name) AS reviewer_name
             FROM leave_requests lr
             INNER JOIN employees e ON e.id = lr.employee_id
             INNER JOIN users u ON u.id = e.user_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN users ru ON ru.id = lr.reviewed_by
             WHERE {$where}
             ORDER BY FIELD(lr.status, 'pending', 'approved', 'rejected', 'cancelled'), lr.created_at DESC",
            $params
        );
    }

    public function decideLeave(int $leaveId, string $decision, string $notes, int $reviewerId): void
    {
        $leave = $this->db->fetch(
            "SELECT lr.*, e.user_id AS emp_user_id, u.email, CONCAT(u.first_name, ' ', u.last_name) AS emp_name
             FROM leave_requests lr
             INNER JOIN employees e ON e.id = lr.employee_id
             INNER JOIN users u ON u.id = e.user_id
             WHERE lr.id = :id LIMIT 1",
            ['id' => $leaveId]
        );
        if (!$leave) {
            throw new \RuntimeException('Leave request not found.');
        }

        $status = $decision === 'approve' ? 'approved' : 'rejected';
        $this->db->update('leave_requests', [
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes ?: null,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $leaveId]);

        NotificationService::notifyUser(
            (int) $leave['emp_user_id'],
            'leave',
            'Leave Request ' . ucfirst($status),
            "Your {$leave['leave_type']} leave ({$leave['start_date']} to {$leave['end_date']}) was {$status}.",
            '/employee/leaves'
        );

        NotificationService::sendBrandedEmail($leave['email'], 'leave_decision', [
            'employee_name' => $leave['emp_name'],
            'leave_type' => ucfirst($leave['leave_type']),
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'status' => ucfirst($status),
            'review_notes' => $notes ?: 'No remarks',
        ]);

        AuditService::log('leaves', $status, "Leave request #{$leaveId} {$status} for {$leave['emp_name']}", 'leave_request', $leaveId, $reviewerId);
    }
}
