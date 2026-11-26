<?php
declare(strict_types=1);

namespace App\Controllers\Employee;

use App\Models\Booking;
use App\Services\AuditService;
use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\Validator;
use Core\View;

/**
 * Employee / Travel Specialist Workspace Controller
 */
class EmployeeController
{
    private function currentEmployee(): array
    {
        $user = Auth::user();
        $db = Database::getInstance();
        $emp = $db->fetch(
            "SELECT e.*, d.name AS department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id WHERE e.user_id = :uid LIMIT 1",
            ['uid' => (int) $user['id']]
        );
        if (!$emp) {
            http_response_code(403);
            exit('Employee profile not linked.');
        }
        return $emp;
    }

    public function dashboard(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $eid = (int) $emp['id'];

        $stats = [
            'assigned_bookings' => (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE assigned_employee_id = :eid AND deleted_at IS NULL", ['eid' => $eid]),
            'open_tasks' => (int) $db->fetchColumn("SELECT COUNT(*) FROM work_assignments WHERE employee_id = :eid AND status IN ('pending','in_progress')", ['eid' => $eid]),
            'pending_leaves' => (int) $db->fetchColumn("SELECT COUNT(*) FROM leave_requests WHERE employee_id = :eid AND status = 'pending'", ['eid' => $eid]),
            'commissions_total' => (float) $db->fetchColumn("SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE employee_id = :eid", ['eid' => $eid]),
        ];

        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title, CONCAT(u.first_name, ' ', u.last_name) AS customer_name
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE b.assigned_employee_id = :eid AND b.deleted_at IS NULL
             ORDER BY b.travel_date ASC LIMIT 6",
            ['eid' => $eid]
        );

        $tasks = $db->fetchAll("SELECT * FROM work_assignments WHERE employee_id = :eid ORDER BY FIELD(status,'pending','in_progress','completed'), due_date ASC LIMIT 6", ['eid' => $eid]);

        View::render('employee/dashboard', [
            'pageTitle' => 'Specialist Workspace',
            'portal' => 'employee',
            'emp' => $emp,
            'stats' => $stats,
            'bookings' => $bookings,
            'tasks' => $tasks,
        ], 'dashboard');
    }

    public function bookings(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email AS customer_email
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE b.assigned_employee_id = :eid AND b.deleted_at IS NULL
             ORDER BY b.created_at DESC",
            ['eid' => (int) $emp['id']]
        );
        View::render('employee/bookings', [
            'pageTitle' => 'My Assigned Bookings',
            'portal' => 'employee',
            'bookings' => $bookings,
        ], 'dashboard');
    }

    public function bookingDetail(array $params): void
    {
        $emp = $this->currentEmployee();
        $model = new Booking();
        $booking = $model->findDetailed((int) ($params['id'] ?? 0));
        if (!$booking || (int) $booking['assigned_employee_id'] !== (int) $emp['id']) {
            flash('error', 'Booking not assigned to you.');
            redirect('/employee/bookings');
        }
        View::render('employee/booking-detail', [
            'pageTitle' => 'Manage Booking ' . $booking['booking_reference'],
            'portal' => 'employee',
            'booking' => $booking,
            'statuses' => Booking::STATUSES,
        ], 'dashboard');
    }

    public function updateBooking(array $params): void
    {
        $emp = $this->currentEmployee();
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking || (int) $booking['assigned_employee_id'] !== (int) $emp['id']) {
            flash('error', 'Unauthorized booking access.');
            redirect('/employee/bookings');
        }

        $newStatus = trim((string) ($_POST['booking_status'] ?? $booking['booking_status']));
        $note = trim((string) ($_POST['note'] ?? ''));
        if (!in_array($newStatus, Booking::STATUSES, true)) {
            flash('error', 'Invalid status.');
            redirect('/employee/bookings/' . $id);
        }

        $db = Database::getInstance();
        $db->update('bookings', ['booking_status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        if ($note !== '') {
            $db->insert('booking_notes', [
                'booking_id' => $id,
                'user_id' => (int) Auth::id(),
                'note' => $note,
                'is_customer_visible' => !empty($_POST['visible_to_customer']) ? 1 : 0,
            ]);
        }

        if ($newStatus !== $booking['booking_status']) {
            NotificationService::notifyUser(
                (int) $booking['customer_user_id'],
                'booking',
                "Booking {$booking['booking_reference']} Updated",
                "Your booking status is now {$newStatus}.",
                "/customer/bookings/{$id}"
            );
            NotificationService::sendBrandedEmail($booking['customer_email'], 'booking_status_update', [
                'customer_name' => $booking['customer_name'],
                'booking_reference' => $booking['booking_reference'],
                'package_title' => $booking['package_title'] ?: 'Custom Journey',
                'status' => $newStatus,
                'note' => $note ?: 'Updated by your assigned travel specialist.',
            ]);
        }

        AuditService::log('bookings', 'employee_update', "Specialist updated {$booking['booking_reference']} to {$newStatus}", 'booking', $id, (int) Auth::id());
        flash('success', 'Booking updated.');
        redirect('/employee/bookings/' . $id);
    }

    public function assignments(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $tasks = $db->fetchAll(
            "SELECT wa.*, b.booking_reference FROM work_assignments wa LEFT JOIN bookings b ON b.id = wa.booking_id WHERE wa.employee_id = :eid ORDER BY wa.created_at DESC",
            ['eid' => (int) $emp['id']]
        );
        $enquiries = $db->fetchAll("SELECT * FROM enquiries WHERE assigned_employee_id = :eid ORDER BY created_at DESC", ['eid' => (int) $emp['id']]);
        View::render('employee/assignments', [
            'pageTitle' => 'Tasks & Enquiries',
            'portal' => 'employee',
            'tasks' => $tasks,
            'enquiries' => $enquiries,
        ], 'dashboard');
    }

    public function completeTask(array $params): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $status = ($_POST['status'] ?? 'completed') === 'in_progress' ? 'in_progress' : 'completed';
        $db->update('work_assignments', [
            'status' => $status,
            'completed_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
        ], 'id = :id AND employee_id = :eid', ['id' => (int) ($params['id'] ?? 0), 'eid' => (int) $emp['id']]);
        flash('success', 'Task status updated.');
        redirect('/employee/assignments');
    }

    public function attendance(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $today = $db->fetch("SELECT * FROM employee_attendance WHERE employee_id = :eid AND attendance_date = CURDATE()", ['eid' => (int) $emp['id']]);
        $history = $db->fetchAll("SELECT * FROM employee_attendance WHERE employee_id = :eid ORDER BY attendance_date DESC LIMIT 30", ['eid' => (int) $emp['id']]);
        View::render('employee/attendance', [
            'pageTitle' => 'My Attendance',
            'portal' => 'employee',
            'today' => $today,
            'history' => $history,
        ], 'dashboard');
    }

    public function checkInOut(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $eid = (int) $emp['id'];
        $today = $db->fetch("SELECT * FROM employee_attendance WHERE employee_id = :eid AND attendance_date = CURDATE()", ['eid' => $eid]);
        if (!$today) {
            $db->insert('employee_attendance', [
                'employee_id' => $eid,
                'attendance_date' => date('Y-m-d'),
                'check_in' => date('H:i:s'),
                'status' => 'present',
                'marked_by' => (int) Auth::id(),
            ]);
            flash('success', 'Checked in for today.');
        } elseif (empty($today['check_out'])) {
            $db->update('employee_attendance', ['check_out' => date('H:i:s')], 'id = :id', ['id' => $today['id']]);
            flash('success', 'Checked out for today.');
        }
        redirect('/employee/attendance');
    }

    public function leaves(): void
    {
        $emp = $this->currentEmployee();
        $db = Database::getInstance();
        $leaves = $db->fetchAll("SELECT * FROM leave_requests WHERE employee_id = :eid ORDER BY created_at DESC", ['eid' => (int) $emp['id']]);
        View::render('employee/leaves', [
            'pageTitle' => 'My Leave Requests',
            'portal' => 'employee',
            'leaves' => $leaves,
        ], 'dashboard');
    }

    public function applyLeave(): void
    {
        $emp = $this->currentEmployee();
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'leave_type' => 'required|in:annual,sick,casual,unpaid,emergency',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|min:5|max:1500',
        ]);
        if ($validator->fails() || strtotime($clean['end_date']) < strtotime($clean['start_date'])) {
            flash('error', $validator->firstError() ?: 'End date must be on or after start date.');
            redirect('/employee/leaves');
        }

        $days = max(1, (int) ((strtotime($clean['end_date']) - strtotime($clean['start_date'])) / 86400) + 1);
        $db = Database::getInstance();
        $db->insert('leave_requests', [
            'employee_id' => (int) $emp['id'],
            'leave_type' => $clean['leave_type'],
            'start_date' => $clean['start_date'],
            'end_date' => $clean['end_date'],
            'total_days' => $days,
            'reason' => $clean['reason'],
            'status' => 'pending',
        ]);

        $user = Auth::user();
        NotificationService::notifyRole('hr', 'leave', 'New Leave Request', "{$user['first_name']} {$user['last_name']} requested {$days} day(s) of {$clean['leave_type']} leave.", '/hr/leaves');
        flash('success', 'Leave request submitted to HR.');
        redirect('/employee/leaves');
    }

    public function notifications(): void
    {
        $uid = (int) Auth::id();
        $db = Database::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            NotificationService::markAllRead($uid);
            flash('success', 'Notifications cleared.');
            redirect('/employee/notifications');
        }
        $list = $db->fetchAll("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 40", ['uid' => $uid]);
        View::render('employee/notifications', ['pageTitle' => 'Notifications', 'portal' => 'employee', 'notifications' => $list], 'dashboard');
    }

    public function profile(): void
    {
        $emp = $this->currentEmployee();
        $user = Auth::user();
        $db = Database::getInstance();
        $commissions = $db->fetchAll(
            "SELECT c.*, b.booking_reference FROM commissions c INNER JOIN bookings b ON b.id = c.booking_id WHERE c.employee_id = :eid ORDER BY c.created_at DESC",
            ['eid' => (int) $emp['id']]
        );
        View::render('employee/profile', [
            'pageTitle' => 'My Profile & Commissions',
            'portal' => 'employee',
            'emp' => $emp,
            'user' => $user,
            'commissions' => $commissions,
        ], 'dashboard');
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $db->update('users', ['phone' => $phone], 'id = :id', ['id' => (int) $user['id']]);
        $db->update('employees', ['address' => $address], 'user_id = :uid', ['uid' => (int) $user['id']]);
        flash('success', 'Profile updated.');
        redirect('/employee/profile');
    }
}
