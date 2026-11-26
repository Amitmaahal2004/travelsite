<?php
declare(strict_types=1);

namespace App\Controllers\Hr;

use App\Models\Employee;
use App\Services\AuditService;
use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\Uploader;
use Core\Validator;
use Core\View;

/**
 * Dedicated HR Operations Portal Controller
 * All endpoints strictly protected by HR Role + PermissionMiddleware on backend.
 */
class HrController
{
    private Employee $empModel;
    protected string $portal = 'hr';
    protected string $prefix = '/hr';

    public function __construct()
    {
        $this->empModel = new Employee();
    }

    public function dashboard(): void
    {
        $db = Database::getInstance();
        $stats = [
            'total_employees' => (int) $db->fetchColumn("SELECT COUNT(*) FROM employees WHERE deleted_at IS NULL"),
            'active_employees' => (int) $db->fetchColumn("SELECT COUNT(*) FROM employees WHERE status = 'active' AND deleted_at IS NULL"),
            'present_today' => (int) $db->fetchColumn("SELECT COUNT(*) FROM employee_attendance WHERE attendance_date = CURDATE() AND status IN ('present','remote')"),
            'pending_leaves' => (int) $db->fetchColumn("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'"),
            'departments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM departments WHERE is_active = 1"),
            'open_assignments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM work_assignments WHERE status IN ('pending','in_progress')"),
        ];

        $departments = $this->empModel->departments();
        $pendingLeaves = $this->empModel->leaveRequests('pending');
        $todayAttendance = $this->empModel->attendanceForDate(date('Y-m-d'));

        View::render('hr/dashboard', [
            'pageTitle' => 'HR Operations Dashboard',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'stats' => $stats,
            'departments' => $departments,
            'pendingLeaves' => array_slice($pendingLeaves, 0, 5),
            'todayAttendance' => $todayAttendance,
        ], 'dashboard');
    }

    public function employees(): void
    {
        $db = Database::getInstance();
        $filters = [
            'search' => trim((string) ($_GET['q'] ?? '')),
            'department_id' => trim((string) ($_GET['department_id'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $dir = $this->empModel->paginateDirectory($filters, $page, 12);

        // HR cannot assign Super Admin role; Admin can assign non-customer staff roles
        $allowedRoleSlugs = $this->portal === 'admin' ? ['super_admin', 'hr', 'employee'] : ['employee', 'hr'];
        $roles = $db->fetchAll(
            "SELECT id, name, slug FROM roles WHERE slug IN ('" . implode("','", $allowedRoleSlugs) . "') OR (is_system = 0 AND dashboard_prefix IN ('/hr','/employee'))"
        );

        View::render('hr/employees', [
            'pageTitle' => 'Employee Directory',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'employees' => $dir['items'],
            'meta' => $dir['meta'],
            'departments' => $this->empModel->departments(),
            'roles' => $roles,
            'filters' => $filters,
        ], 'dashboard');
    }

    public function storeEmployee(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'first_name' => 'required|min:2|max:80',
            'last_name' => 'required|min:2|max:80',
            'email' => 'required|email|max:160',
            'phone' => 'max:40',
            'designation' => 'required|min:2|max:120',
            'department_id' => 'integer',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'join_date' => 'required|date',
            'base_salary' => 'numeric|min_val:0',
            'commission_rate' => 'numeric|min_val:0',
            'password' => 'required|min:8|max:100',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect($this->prefix . '/employees');
        }

        $db = Database::getInstance();
        if ($db->fetchColumn("SELECT id FROM users WHERE LOWER(email) = :e", ['e' => strtolower($clean['email'])])) {
            flash('error', 'An account with this email already exists.');
            redirect($this->prefix . '/employees');
        }

        $roleId = (int) ($_POST['role_id'] ?? 0);
        // Prevent HR from ever assigning super_admin role
        if ($this->portal !== 'admin' && $roleId > 0) {
            $slug = $db->fetchColumn("SELECT slug FROM roles WHERE id = :id", ['id' => $roleId]);
            if ($slug === 'super_admin') {
                flash('error', 'Unauthorized role assignment.');
                redirect($this->prefix . '/employees');
            }
        }
        $clean['role_id'] = $roleId ?: null;
        $clean['password'] = (string) $_POST['password'];
        $clean['emergency_contact'] = trim((string) ($_POST['emergency_contact'] ?? ''));
        $clean['address'] = trim((string) ($_POST['address'] ?? ''));

        $this->empModel->createEmployee($clean, (int) Auth::id());
        flash('success', 'Employee account and directory profile created.');
        redirect($this->prefix . '/employees');
    }

    public function editEmployee(array $params): void
    {
        $emp = $this->empModel->findFull((int) ($params['id'] ?? 0));
        if (!$emp) {
            flash('error', 'Employee record not found.');
            redirect($this->prefix . '/employees');
        }
        $db = Database::getInstance();
        $roles = $this->portal === 'admin'
            ? $db->fetchAll("SELECT id, name, slug FROM roles WHERE slug != 'customer'")
            : $db->fetchAll("SELECT id, name, slug FROM roles WHERE slug IN ('employee','hr')");

        $docs = $db->fetchAll("SELECT * FROM employee_documents WHERE employee_id = :eid ORDER BY created_at DESC", ['eid' => $emp['id']]);
        $assignments = $db->fetchAll("SELECT * FROM work_assignments WHERE employee_id = :eid ORDER BY created_at DESC", ['eid' => $emp['id']]);

        View::render('hr/employee-edit', [
            'pageTitle' => 'Employee Profile: ' . $emp['first_name'] . ' ' . $emp['last_name'],
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'emp' => $emp,
            'departments' => $this->empModel->departments(),
            'roles' => $roles,
            'docs' => $docs,
            'assignments' => $assignments,
        ], 'dashboard');
    }

    public function updateEmployee(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'first_name' => 'required|min:2|max:80',
            'last_name' => 'required|min:2|max:80',
            'phone' => 'max:40',
            'designation' => 'required|min:2|max:120',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'status' => 'required|in:active,on_leave,terminated',
            'base_salary' => 'numeric|min_val:0',
            'commission_rate' => 'numeric|min_val:0',
            'performance_score' => 'numeric|min_val:1',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect($this->prefix . '/employees/' . $id);
        }

        $clean['department_id'] = $_POST['department_id'] ?? null;
        $clean['emergency_contact'] = trim((string) ($_POST['emergency_contact'] ?? ''));
        $clean['address'] = trim((string) ($_POST['address'] ?? ''));
        $clean['notes'] = trim((string) ($_POST['notes'] ?? ''));
        $clean['role_id'] = $_POST['role_id'] ?? null;

        $this->empModel->updateEmployee($id, $clean, (int) Auth::id(), $this->portal === 'admin');
        flash('success', 'Employee profile updated.');
        redirect($this->prefix . '/employees/' . $id);
    }

    public function departments(): void
    {
        $db = Database::getInstance();
        $staffUsers = $db->fetchAll(
            "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name
             FROM users u INNER JOIN employees e ON e.user_id = u.id WHERE u.deleted_at IS NULL ORDER BY u.first_name"
        );
        View::render('hr/departments', [
            'pageTitle' => 'Department Management',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'departments' => $this->empModel->departments(),
            'staffUsers' => $staffUsers,
        ], 'dashboard');
    }

    public function storeDepartment(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:20',
            'description' => 'max:1000',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect($this->prefix . '/departments');
        }
        $db = Database::getInstance();
        $db->insert('departments', [
            'name' => $clean['name'],
            'code' => strtoupper($clean['code']),
            'description' => $clean['description'] ?: null,
            'manager_user_id' => !empty($_POST['manager_user_id']) ? (int) $_POST['manager_user_id'] : null,
            'is_active' => 1,
        ]);
        AuditService::log('departments', 'create', "Created department {$clean['name']}", 'department', null, (int) Auth::id());
        flash('success', 'Department added.');
        redirect($this->prefix . '/departments');
    }

    public function attendance(): void
    {
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $deptId = !empty($_GET['department_id']) ? (int) $_GET['department_id'] : null;

        View::render('hr/attendance', [
            'pageTitle' => 'Attendance Management',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'date' => $date,
            'deptId' => $deptId,
            'records' => $this->empModel->attendanceForDate($date, $deptId),
            'departments' => $this->empModel->departments(),
        ], 'dashboard');
    }

    public function saveAttendance(): void
    {
        $date = trim((string) ($_POST['attendance_date'] ?? date('Y-m-d')));
        $statuses = $_POST['status'] ?? [];
        $checkIns = $_POST['check_in'] ?? [];
        $checkOuts = $_POST['check_out'] ?? [];
        $notes = $_POST['notes'] ?? [];
        $markerId = (int) Auth::id();

        foreach ($statuses as $empId => $status) {
            $eid = (int) $empId;
            if ($eid > 0 && in_array($status, ['present', 'absent', 'late', 'half_day', 'remote', 'on_leave'], true)) {
                $this->empModel->markAttendance(
                    $eid,
                    $date,
                    $status,
                    $checkIns[$empId] ?? null,
                    $checkOuts[$empId] ?? null,
                    $notes[$empId] ?? null,
                    $markerId
                );
            }
        }
        AuditService::log('attendance', 'save', "Updated attendance register for {$date}", 'employee_attendance', null, $markerId);
        flash('success', "Attendance register saved for {$date}.");
        redirect($this->prefix . '/attendance?date=' . urlencode($date));
    }

    public function leaves(): void
    {
        $filterStatus = trim((string) ($_GET['status'] ?? '')) ?: null;
        View::render('hr/leaves', [
            'pageTitle' => 'Leave Requests & Approvals',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'leaves' => $this->empModel->leaveRequests($filterStatus),
            'filterStatus' => $filterStatus,
        ], 'dashboard');
    }

    public function decideLeave(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $decision = ($_POST['decision'] ?? '') === 'approve' ? 'approve' : 'reject';
        $notes = trim((string) ($_POST['review_notes'] ?? ''));
        $this->empModel->decideLeave($id, $decision, $notes, (int) Auth::id());
        flash('success', 'Leave request ' . ($decision === 'approve' ? 'approved' : 'rejected') . ' and employee notified.');
        redirect($this->prefix . '/leaves');
    }

    public function documents(): void
    {
        $db = Database::getInstance();
        $docs = $db->fetchAll(
            "SELECT ed.*, e.employee_code, CONCAT(u.first_name, ' ', u.last_name) AS employee_name
             FROM employee_documents ed
             INNER JOIN employees e ON e.id = ed.employee_id
             INNER JOIN users u ON u.id = e.user_id
             ORDER BY ed.created_at DESC"
        );
        View::render('hr/documents', [
            'pageTitle' => 'Employee Documents Vault',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'docs' => $docs,
            'employees' => $this->empModel->allActive(),
        ], 'dashboard');
    }

    public function uploadDocument(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'employee_id' => 'required|integer',
            'title' => 'required|min:2|max:160',
            'document_type' => 'required|min:2|max:80',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect($this->prefix . '/documents');
        }

        $up = Uploader::uploadPrivateDocument($_FILES['document_file'] ?? [], 'documents');
        if (!$up['ok']) {
            flash('error', $up['error']);
            redirect($this->prefix . '/documents');
        }

        $db = Database::getInstance();
        $db->insert('employee_documents', [
            'employee_id' => (int) $clean['employee_id'],
            'title' => $clean['title'],
            'document_type' => $clean['document_type'],
            'file_path' => $up['path'],
            'original_name' => $up['original_name'],
            'file_size' => $up['size'],
            'uploaded_by' => (int) Auth::id(),
        ]);

        AuditService::log('documents', 'upload', "Uploaded document {$clean['title']}", 'employee_document', null, (int) Auth::id());
        flash('success', 'Document securely stored in private vault.');
        redirect($this->prefix . '/documents');
    }

    public function downloadDocument(array $params): void
    {
        $db = Database::getInstance();
        $doc = $db->fetch("SELECT * FROM employee_documents WHERE id = :id LIMIT 1", ['id' => (int) ($params['id'] ?? 0)]);
        if (!$doc) {
            http_response_code(404);
            exit('Document not found.');
        }

        $fullPath = BASE_PATH . '/storage/' . ltrim($doc['file_path'], '/');
        if (!is_file($fullPath)) {
            http_response_code(404);
            exit('File missing on disk.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($doc['original_name']) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function assignments(): void
    {
        $db = Database::getInstance();
        $tasks = $db->fetchAll(
            "SELECT wa.*, e.employee_code, CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                    b.booking_reference
             FROM work_assignments wa
             INNER JOIN employees e ON e.id = wa.employee_id
             INNER JOIN users u ON u.id = e.user_id
             LEFT JOIN bookings b ON b.id = wa.booking_id
             ORDER BY FIELD(wa.status,'pending','in_progress','completed','cancelled'), wa.due_date ASC"
        );
        $bookings = $db->fetchAll("SELECT id, booking_reference FROM bookings WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 30");

        View::render('hr/assignments', [
            'pageTitle' => 'Work Assignments & Performance',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'tasks' => $tasks,
            'employees' => $this->empModel->allActive(),
            'bookings' => $bookings,
        ], 'dashboard');
    }

    public function storeAssignment(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'employee_id' => 'required|integer',
            'title' => 'required|min:3|max:180',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'date',
            'description' => 'max:2000',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect($this->prefix . '/assignments');
        }

        $db = Database::getInstance();
        $taskId = $db->insert('work_assignments', [
            'employee_id' => (int) $clean['employee_id'],
            'booking_id' => !empty($_POST['booking_id']) ? (int) $_POST['booking_id'] : null,
            'title' => $clean['title'],
            'description' => $clean['description'] ?: null,
            'priority' => $clean['priority'],
            'due_date' => $clean['due_date'] ?: null,
            'status' => 'pending',
            'assigned_by' => (int) Auth::id(),
        ]);

        $empUserId = (int) $db->fetchColumn("SELECT user_id FROM employees WHERE id = :id", ['id' => (int) $clean['employee_id']]);
        if ($empUserId > 0) {
            NotificationService::notifyUser($empUserId, 'assignment', 'New Work Assignment', $clean['title'], '/employee/assignments');
        }

        AuditService::log('assignments', 'create', "Assigned task '{$clean['title']}'", 'work_assignment', $taskId, (int) Auth::id());
        flash('success', 'Work assignment dispatched.');
        redirect($this->prefix . '/assignments');
    }

    public function reports(): void
    {
        $db = Database::getInstance();
        $performance = $db->fetchAll(
            "SELECT e.id, e.employee_code, e.designation, e.performance_score, d.name AS department_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                    (SELECT COUNT(*) FROM work_assignments wa WHERE wa.employee_id = e.id AND wa.status = 'completed') AS completed_tasks,
                    (SELECT COUNT(*) FROM work_assignments wa WHERE wa.employee_id = e.id AND wa.status IN ('pending','in_progress')) AS open_tasks,
                    (SELECT COUNT(*) FROM employee_attendance ea WHERE ea.employee_id = e.id AND ea.status IN ('present','remote') AND MONTH(ea.attendance_date) = MONTH(CURDATE())) AS present_days_month
             FROM employees e
             INNER JOIN users u ON u.id = e.user_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.deleted_at IS NULL
             ORDER BY e.performance_score DESC"
        );

        $hrLogs = $db->fetchAll(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name
             FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id
             WHERE a.module IN ('employees','departments','attendance','leaves','documents','assignments')
             ORDER BY a.created_at DESC LIMIT 20"
        );

        View::render('hr/reports', [
            'pageTitle' => 'Employee Performance & Activity Reports',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'performance' => $performance,
            'hrLogs' => $hrLogs,
        ], 'dashboard');
    }

    public function notifications(): void
    {
        $userId = (int) Auth::id();
        $db = Database::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            NotificationService::markAllRead($userId);
            flash('success', 'All notifications marked read.');
            redirect($this->prefix . '/notifications');
        }
        $list = $db->fetchAll("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 40", ['uid' => $userId]);
        View::render('hr/notifications', [
            'pageTitle' => 'HR Notifications',
            'portal' => $this->portal,
            'prefix' => $this->prefix,
            'notifications' => $list,
        ], 'dashboard');
    }
}
