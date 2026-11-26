<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Booking;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PdfInvoiceService;
use Core\Auth;
use Core\Database;
use Core\Validator;
use Core\View;

class BookingController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $payStatus = trim((string) ($_GET['payment_status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $where = ['b.deleted_at IS NULL'];
        $params = [];
        if ($q !== '') {
            $where[] = "(b.booking_reference LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR p.title LIKE :q)";
            $params['q'] = "%{$q}%";
        }
        if ($status !== '') {
            $where[] = 'b.booking_status = :st';
            $params['st'] = $status;
        }
        if ($payStatus !== '') {
            $where[] = 'b.payment_status = :pst';
            $params['pst'] = $payStatus;
        }
        $wSql = implode(' AND ', $where);

        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE {$wSql}",
            $params
        );
        $meta = paginate_meta($total, 15, $page);

        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title,
                    CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email AS customer_email,
                    CONCAT(eu.first_name, ' ', eu.last_name) AS employee_name
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             LEFT JOIN employees e ON e.id = b.assigned_employee_id
             LEFT JOIN users eu ON eu.id = e.user_id
             WHERE {$wSql}
             ORDER BY b.created_at DESC
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        View::render('admin/bookings/index', [
            'pageTitle' => 'Booking Management',
            'portal' => 'admin',
            'bookings' => $bookings,
            'meta' => $meta,
            'statuses' => Booking::STATUSES,
            'filters' => ['q' => $q, 'status' => $status, 'payment_status' => $payStatus],
        ], 'dashboard');
    }

    public function create(): void
    {
        $db = Database::getInstance();
        $customers = $db->fetchAll(
            "SELECT c.id, c.customer_code, CONCAT(u.first_name, ' ', u.last_name) AS full_name, u.email
             FROM customers c INNER JOIN users u ON u.id = c.user_id WHERE c.deleted_at IS NULL ORDER BY u.first_name"
        );
        $packages = $db->fetchAll("SELECT id, title, base_price, discount_price FROM travel_packages WHERE deleted_at IS NULL ORDER BY title");
        $employees = (new Employee())->allActive();

        View::render('admin/bookings/create', [
            'pageTitle' => 'Create Manual Booking',
            'portal' => 'admin',
            'customers' => $customers,
            'packages' => $packages,
            'employees' => $employees,
            'statuses' => Booking::STATUSES,
        ], 'dashboard');
    }

    public function store(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'customer_id' => 'required|integer',
            'travel_date' => 'required|date',
            'adults' => 'required|integer|min_val:1',
            'children' => 'integer|min_val:0',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/bookings/create');
        }

        $db = Database::getInstance();
        $cust = $db->fetch(
            "SELECT c.id, CONCAT(u.first_name, ' ', u.last_name) AS full_name, u.email, u.phone
             FROM customers c INNER JOIN users u ON u.id = c.user_id WHERE c.id = :cid",
            ['cid' => (int) $clean['customer_id']]
        );
        if (!$cust) {
            flash('error', 'Selected customer does not exist.');
            redirect('/admin/bookings/create');
        }

        $model = new Booking();
        $payload = [
            'customer_id' => (int) $clean['customer_id'],
            'package_id' => !empty($_POST['package_id']) ? (int) $_POST['package_id'] : null,
            'assigned_employee_id' => !empty($_POST['assigned_employee_id']) ? (int) $_POST['assigned_employee_id'] : null,
            'custom_title' => trim((string) ($_POST['custom_title'] ?? '')) ?: null,
            'travel_date' => $clean['travel_date'],
            'adults' => (int) $clean['adults'],
            'children' => (int) ($clean['children'] ?? 0),
            'custom_unit_price' => (float) ($_POST['custom_unit_price'] ?? 0),
            'promo_code' => trim((string) ($_POST['promo_code'] ?? '')),
            'booking_status' => $_POST['booking_status'] ?? 'Confirmed',
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'special_requests' => trim((string) ($_POST['special_requests'] ?? '')) ?: null,
            'internal_notes' => trim((string) ($_POST['internal_notes'] ?? '')) ?: null,
        ];

        $bookingId = $model->createBookingWithTravellers($payload, [[
            'full_name' => $cust['full_name'],
            'email' => $cust['email'],
            'phone' => $cust['phone'],
        ]], (int) Auth::id());

        flash('success', 'Manual booking created and invoice generated.');
        redirect('/admin/bookings/' . $bookingId);
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking) {
            flash('error', 'Booking not found.');
            redirect('/admin/bookings');
        }

        $employees = (new Employee())->allActive();
        View::render('admin/bookings/show', [
            'pageTitle' => 'Booking ' . $booking['booking_reference'],
            'portal' => 'admin',
            'booking' => $booking,
            'employees' => $employees,
            'statuses' => Booking::STATUSES,
        ], 'dashboard');
    }

    public function update(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking) {
            redirect('/admin/bookings');
        }

        $newStatus = trim((string) ($_POST['booking_status'] ?? $booking['booking_status']));
        if (!in_array($newStatus, Booking::STATUSES, true)) {
            $newStatus = $booking['booking_status'];
        }
        $empId = !empty($_POST['assigned_employee_id']) ? (int) $_POST['assigned_employee_id'] : null;

        $db = Database::getInstance();
        $db->update('bookings', [
            'booking_status' => $newStatus,
            'assigned_employee_id' => $empId,
            'travel_date' => $_POST['travel_date'] ?: $booking['travel_date'],
            'return_date' => $_POST['return_date'] ?: $booking['return_date'],
            'internal_notes' => trim((string) ($_POST['internal_notes'] ?? $booking['internal_notes'])),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        // Notify newly assigned employee & ensure commission record
        if ($empId && (int) $empId !== (int) $booking['assigned_employee_id']) {
            $emp = $db->fetch("SELECT user_id, commission_rate FROM employees WHERE id = :id", ['id' => $empId]);
            if ($emp) {
                NotificationService::notifyUser(
                    (int) $emp['user_id'],
                    'assignment',
                    "Assigned Booking {$booking['booking_reference']}",
                    "You have been assigned to manage {$booking['booking_reference']}.",
                    "/employee/bookings/{$id}"
                );
                $hasComm = $db->fetchColumn("SELECT id FROM commissions WHERE booking_id = :bid AND employee_id = :eid", ['bid' => $id, 'eid' => $empId]);
                if (!$hasComm) {
                    $rate = (float) $emp['commission_rate'];
                    $commAmt = round((float) $booking['total_amount'] * ($rate / 100), 2);
                    $db->insert('commissions', [
                        'employee_id' => $empId,
                        'booking_id' => $id,
                        'commission_rate' => $rate,
                        'commission_amount' => $commAmt,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        $note = trim((string) ($_POST['new_note'] ?? ''));
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
                "Booking {$booking['booking_reference']} Status: {$newStatus}",
                "Your booking status has been updated to {$newStatus}.",
                "/customer/bookings/{$id}"
            );
            NotificationService::sendBrandedEmail($booking['customer_email'], 'booking_status_update', [
                'customer_name' => $booking['customer_name'],
                'booking_reference' => $booking['booking_reference'],
                'package_title' => $booking['package_title'] ?: 'Custom Journey',
                'status' => $newStatus,
                'note' => $note ?: 'Updated by agency administration.',
            ]);
        }

        AuditService::log('bookings', 'update', "Updated booking {$booking['booking_reference']} ({$newStatus})", 'booking', $id, (int) Auth::id());
        flash('success', 'Booking updated.');
        redirect('/admin/bookings/' . $id);
    }

    public function recordPayment(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking) {
            redirect('/admin/bookings');
        }

        $amount = round((float) ($_POST['amount'] ?? 0), 2);
        if ($amount <= 0) {
            flash('error', 'Payment amount must be greater than zero.');
            redirect('/admin/bookings/' . $id);
        }

        $method = $_POST['payment_method'] ?? 'bank_transfer';
        if (!in_array($method, ['bank_transfer', 'card_gateway', 'upi', 'cash', 'cheque', 'online_portal'], true)) {
            $method = 'bank_transfer';
        }

        $db = Database::getInstance();
        $payRef = 'PAY-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $db->insert('payments', [
            'payment_reference' => $payRef,
            'booking_id' => $id,
            'customer_id' => (int) $booking['customer_id'],
            'amount' => $amount,
            'currency' => $booking['currency'] ?? 'USD',
            'payment_method' => $method,
            'gateway_name' => trim((string) ($_POST['gateway_name'] ?? '')) ?: 'Manual / Offline Settlement',
            'gateway_transaction_id' => trim((string) ($_POST['gateway_transaction_id'] ?? '')) ?: null,
            'status' => 'completed',
            'paid_at' => date('Y-m-d H:i:s'),
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            'recorded_by' => (int) Auth::id(),
        ]);

        $model->recalculatePayments($id);
        $updated = $model->findDetailed($id);
        $balance = max(0, (float) $updated['total_amount'] - (float) $updated['paid_amount']);

        NotificationService::notifyUser(
            (int) $booking['customer_user_id'],
            'payment',
            "Payment Recorded: {$payRef}",
            money($amount) . " recorded toward {$booking['booking_reference']}.",
            "/customer/bookings/{$id}"
        );
        NotificationService::sendBrandedEmail($booking['customer_email'], 'payment_receipt', [
            'customer_name' => $booking['customer_name'],
            'booking_reference' => $booking['booking_reference'],
            'amount_paid' => money($amount),
            'payment_reference' => $payRef,
            'balance_due' => money($balance),
        ]);

        AuditService::log('payments', 'record', "Recorded payment {$payRef} (" . money($amount) . ")", 'booking', $id, (int) Auth::id());
        flash('success', "Payment {$payRef} recorded and invoice updated.");
        redirect('/admin/bookings/' . $id);
    }

    public function processRefund(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking) {
            redirect('/admin/bookings');
        }

        $amount = round((float) ($_POST['amount'] ?? 0), 2);
        $reason = trim((string) ($_POST['reason'] ?? 'Booking refund processed.'));
        if ($amount <= 0 || $amount > (float) $booking['paid_amount']) {
            flash('error', 'Refund amount must be between $0.01 and total paid (' . money($booking['paid_amount']) . ').');
            redirect('/admin/bookings/' . $id);
        }

        $db = Database::getInstance();
        $ref = 'REF-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $db->insert('refunds', [
            'refund_reference' => $ref,
            'booking_id' => $id,
            'customer_id' => (int) $booking['customer_id'],
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'processed',
            'processed_by' => (int) Auth::id(),
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        $model->recalculatePayments($id);
        NotificationService::notifyUser(
            (int) $booking['customer_user_id'],
            'payment',
            "Refund Processed: {$ref}",
            "A refund of " . money($amount) . " for {$booking['booking_reference']} has been processed.",
            "/customer/bookings/{$id}"
        );
        AuditService::log('refunds', 'process', "Processed refund {$ref} (" . money($amount) . ")", 'booking', $id, (int) Auth::id());
        flash('success', "Refund {$ref} processed.");
        redirect('/admin/bookings/' . $id);
    }

    public function invoice(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $invoice = PdfInvoiceService::generateForBooking($id);
        if (!$invoice || empty($invoice['pdf_binary'])) {
            flash('error', 'Unable to generate invoice.');
            redirect('/admin/bookings/' . $id);
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $invoice['invoice_number'] . '.pdf"');
        header('Content-Length: ' . strlen($invoice['pdf_binary']));
        echo $invoice['pdf_binary'];
        exit;
    }

    public function exportCsv(): void
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT b.booking_reference, CONCAT(u.first_name,' ',u.last_name) AS customer, u.email,
                    COALESCE(p.title, b.custom_title) AS package, b.travel_date, b.total_travellers,
                    b.total_amount, b.paid_amount, b.booking_status, b.payment_status, b.created_at
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE b.deleted_at IS NULL ORDER BY b.created_at DESC"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bookings-export-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Reference', 'Customer', 'Email', 'Package', 'Travel Date', 'Travellers', 'Total', 'Paid', 'Status', 'Payment', 'Created']);
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        fclose($out);
        exit;
    }
}
