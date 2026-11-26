<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Employee;
use App\Services\AuditService;
use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\View;

class CustomerController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $where = ['c.deleted_at IS NULL', 'u.deleted_at IS NULL'];
        $params = [];
        if ($q !== '') {
            $where[] = "(u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR c.customer_code LIKE :q)";
            $params['q'] = "%{$q}%";
        }
        if ($status !== '') {
            $where[] = 'u.status = :st';
            $params['st'] = $status;
        }
        $wSql = implode(' AND ', $where);
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM customers c INNER JOIN users u ON u.id = c.user_id WHERE {$wSql}", $params);
        $meta = paginate_meta($total, 15, $page);

        $customers = $db->fetchAll(
            "SELECT c.*, u.first_name, u.last_name, u.email, u.phone, u.status AS account_status,
                    (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = c.id AND b.deleted_at IS NULL) AS booking_count
             FROM customers c INNER JOIN users u ON u.id = c.user_id
             WHERE {$wSql} ORDER BY c.id DESC LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        View::render('admin/customers/index', [
            'pageTitle' => 'Customer Management',
            'portal' => 'admin',
            'customers' => $customers,
            'meta' => $meta,
            'filters' => ['q' => $q, 'status' => $status],
        ], 'dashboard');
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $cust = $db->fetch(
            "SELECT c.*, u.first_name, u.last_name, u.email, u.phone, u.status AS account_status, u.last_login_at
             FROM customers c INNER JOIN users u ON u.id = c.user_id WHERE c.id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$cust) {
            redirect('/admin/customers');
        }

        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title FROM bookings b LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE b.customer_id = :cid AND b.deleted_at IS NULL ORDER BY b.created_at DESC",
            ['cid' => $id]
        );
        $enquiries = $db->fetchAll("SELECT * FROM enquiries WHERE customer_id = :cid OR LOWER(email) = :e ORDER BY created_at DESC", ['cid' => $id, 'e' => strtolower($cust['email'])]);

        View::render('admin/customers/show', [
            'pageTitle' => 'Customer: ' . $cust['first_name'] . ' ' . $cust['last_name'],
            'portal' => 'admin',
            'cust' => $cust,
            'bookings' => $bookings,
            'enquiries' => $enquiries,
        ], 'dashboard');
    }

    public function update(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $cust = $db->fetch("SELECT * FROM customers WHERE id = :id", ['id' => $id]);
        if (!$cust) {
            redirect('/admin/customers');
        }

        $status = $_POST['account_status'] ?? 'active';
        if (!in_array($status, ['active', 'inactive', 'blocked'], true)) {
            $status = 'active';
        }

        $db->update('users', [
            'first_name' => trim((string) $_POST['first_name']),
            'last_name' => trim((string) $_POST['last_name']),
            'phone' => trim((string) $_POST['phone']) ?: null,
            'status' => $status,
        ], 'id = :id', ['id' => (int) $cust['user_id']]);

        $db->update('customers', [
            'nationality' => trim((string) ($_POST['nationality'] ?? '')) ?: null,
            'city' => trim((string) ($_POST['city'] ?? '')) ?: null,
            'country' => trim((string) ($_POST['country'] ?? '')) ?: null,
            'loyalty_tier' => $_POST['loyalty_tier'] ?? 'explorer',
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
        ], 'id = :id', ['id' => $id]);

        AuditService::log('customers', 'update', "Updated customer #{$id} (status: {$status})", 'customer', $id, (int) Auth::id());
        flash('success', 'Customer profile and account status updated.');
        redirect('/admin/customers/' . $id);
    }

    public function enquiries(): void
    {
        $db = Database::getInstance();
        $status = trim((string) ($_GET['status'] ?? ''));
        $where = $status !== '' ? 'WHERE e.status = :st' : '';
        $params = $status !== '' ? ['st' => $status] : [];
        $enquiries = $db->fetchAll(
            "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
             FROM enquiries e
             LEFT JOIN employees emp ON emp.id = e.assigned_employee_id
             LEFT JOIN users u ON u.id = emp.user_id
             {$where} ORDER BY e.created_at DESC",
            $params
        );
        $employees = (new Employee())->allActive();

        View::render('admin/enquiries/index', [
            'pageTitle' => 'Customer & Custom Tour Enquiries',
            'portal' => 'admin',
            'enquiries' => $enquiries,
            'employees' => $employees,
            'filterStatus' => $status,
        ], 'dashboard');
    }

    public function updateEnquiry(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $status = $_POST['status'] ?? 'in_progress';
        $empId = !empty($_POST['assigned_employee_id']) ? (int) $_POST['assigned_employee_id'] : null;

        $db = Database::getInstance();
        $db->update('enquiries', [
            'status' => $status,
            'assigned_employee_id' => $empId,
        ], 'id = :id', ['id' => $id]);

        if ($empId) {
            $empUserId = (int) $db->fetchColumn("SELECT user_id FROM employees WHERE id = :id", ['id' => $empId]);
            if ($empUserId > 0) {
                NotificationService::notifyUser($empUserId, 'enquiry', 'Assigned Enquiry #' . $id, 'An enquiry has been assigned to you.', '/employee/assignments');
            }
        }
        flash('success', 'Enquiry updated.');
        redirect('/admin/enquiries');
    }

    public function support(): void
    {
        $db = Database::getInstance();
        $tickets = $db->fetchAll(
            "SELECT sr.*, b.booking_reference, CONCAT(u.first_name,' ',u.last_name) AS customer_name
             FROM support_requests sr
             INNER JOIN customers c ON c.id = sr.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN bookings b ON b.id = sr.booking_id
             ORDER BY FIELD(sr.status,'open','in_progress','resolved','closed'), sr.created_at DESC"
        );
        View::render('admin/support/index', [
            'pageTitle' => 'Customer Support Requests',
            'portal' => 'admin',
            'tickets' => $tickets,
        ], 'dashboard');
    }

    public function supportShow(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $ticket = $db->fetch(
            "SELECT sr.*, b.booking_reference, c.user_id AS customer_user_id, CONCAT(u.first_name,' ',u.last_name) AS customer_name, u.email AS customer_email
             FROM support_requests sr
             INNER JOIN customers c ON c.id = sr.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN bookings b ON b.id = sr.booking_id
             WHERE sr.id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$ticket) {
            redirect('/admin/support');
        }
        $replies = $db->fetchAll(
            "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS author_name FROM support_replies r INNER JOIN users u ON u.id = r.user_id WHERE r.support_request_id = :tid ORDER BY r.created_at ASC",
            ['tid' => $id]
        );
        View::render('admin/support/show', [
            'pageTitle' => 'Support Ticket ' . $ticket['ticket_number'],
            'portal' => 'admin',
            'ticket' => $ticket,
            'replies' => $replies,
        ], 'dashboard');
    }

    public function replySupport(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $ticket = $db->fetch(
            "SELECT sr.*, c.user_id AS customer_user_id FROM support_requests sr INNER JOIN customers c ON c.id = sr.customer_id WHERE sr.id = :id",
            ['id' => $id]
        );
        if (!$ticket) {
            redirect('/admin/support');
        }
        $msg = trim((string) ($_POST['message'] ?? ''));
        $status = $_POST['status'] ?? 'in_progress';
        if ($msg !== '') {
            $db->insert('support_replies', [
                'support_request_id' => $id,
                'user_id' => (int) Auth::id(),
                'message' => $msg,
                'is_staff' => 1,
            ]);
        }
        $db->update('support_requests', ['status' => $status, 'assigned_to' => (int) Auth::id()], 'id = :id', ['id' => $id]);
        NotificationService::notifyUser((int) $ticket['customer_user_id'], 'support', "Reply on Ticket {$ticket['ticket_number']}", $msg ?: "Status changed to {$status}.", "/customer/support/{$id}");
        flash('success', 'Support response sent.');
        redirect('/admin/support/' . $id);
    }
}
