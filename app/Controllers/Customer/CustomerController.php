<?php
declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Models\Booking;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PdfInvoiceService;
use Core\Auth;
use Core\Database;
use Core\Uploader;
use Core\Validator;
use Core\View;

/**
 * Customer / Traveller Dashboard Controller
 * Strictly scopes all queries to the authenticated customer's own records.
 */
class CustomerController
{
    private function currentCustomer(): array
    {
        $user = Auth::user();
        $db = Database::getInstance();
        $cust = $db->fetch(
            "SELECT c.*, u.first_name, u.last_name, u.email, u.phone, u.avatar
             FROM customers c INNER JOIN users u ON u.id = c.user_id
             WHERE c.user_id = :uid AND c.deleted_at IS NULL LIMIT 1",
            ['uid' => (int) $user['id']]
        );
        if (!$cust) {
            http_response_code(403);
            exit('Customer profile not found.');
        }
        return $cust;
    }

    public function dashboard(): void
    {
        $cust = $this->currentCustomer();
        $cid = (int) $cust['id'];
        $db = Database::getInstance();

        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title, p.featured_image, p.duration_days, d.name AS destination_name
             FROM bookings b
             LEFT JOIN travel_packages p ON p.id = b.package_id
             LEFT JOIN destinations d ON d.id = p.destination_id
             WHERE b.customer_id = :cid AND b.deleted_at IS NULL
             ORDER BY b.travel_date ASC",
            ['cid' => $cid]
        );

        $upcoming = null;
        foreach ($bookings as $b) {
            if (!in_array($b['booking_status'], ['Cancelled', 'Refunded', 'Completed'], true) && strtotime($b['travel_date']) >= strtotime('today')) {
                $upcoming = $b;
                break;
            }
        }

        $stats = [
            'total_bookings' => count($bookings),
            'upcoming_count' => count(array_filter($bookings, static fn($x) => !in_array($x['booking_status'], ['Cancelled', 'Refunded', 'Completed'], true))),
            'total_spent' => (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE customer_id = :cid AND status = 'completed'", ['cid' => $cid]),
            'wishlist_count' => (int) $db->fetchColumn("SELECT COUNT(*) FROM wishlist WHERE customer_id = :cid", ['cid' => $cid]),
            'open_tickets' => (int) $db->fetchColumn("SELECT COUNT(*) FROM support_requests WHERE customer_id = :cid AND status IN ('open','in_progress')", ['cid' => $cid]),
        ];

        $recentNotifications = NotificationService::unreadForUser((int) Auth::id(), 5);

        View::render('customer/dashboard', [
            'pageTitle' => 'Welcome, ' . $cust['first_name'],
            'portal' => 'customer',
            'cust' => $cust,
            'stats' => $stats,
            'upcoming' => $upcoming,
            'bookings' => array_slice($bookings, 0, 5),
            'notifications' => $recentNotifications,
        ], 'dashboard');
    }

    public function bookings(): void
    {
        $cust = $this->currentCustomer();
        $db = Database::getInstance();
        $bookings = $db->fetchAll(
            "SELECT b.*, p.title AS package_title, p.featured_image, d.name AS destination_name
             FROM bookings b
             LEFT JOIN travel_packages p ON p.id = b.package_id
             LEFT JOIN destinations d ON d.id = p.destination_id
             WHERE b.customer_id = :cid AND b.deleted_at IS NULL
             ORDER BY b.created_at DESC",
            ['cid' => (int) $cust['id']]
        );
        View::render('customer/bookings', [
            'pageTitle' => 'My Bookings',
            'portal' => 'customer',
            'bookings' => $bookings,
        ], 'dashboard');
    }

    public function bookingDetail(array $params): void
    {
        $cust = $this->currentCustomer();
        $model = new Booking();
        $booking = $model->findDetailed((int) ($params['id'] ?? 0));

        // Strict customer ownership check
        if (!$booking || (int) $booking['customer_id'] !== (int) $cust['id']) {
            http_response_code(403);
            View::render('frontend/403', ['pageTitle' => 'Access Denied', 'solidHeader' => true], 'frontend');
            return;
        }

        $visibleNotes = array_values(array_filter($booking['notes'], static fn($n) => (int) $n['is_customer_visible'] === 1));
        $booking['notes'] = $visibleNotes;

        View::render('customer/booking-detail', [
            'pageTitle' => 'Booking ' . $booking['booking_reference'],
            'portal' => 'customer',
            'booking' => $booking,
        ], 'dashboard');
    }

    public function submitPayment(array $params): void
    {
        $cust = $this->currentCustomer();
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);

        if (!$booking || (int) $booking['customer_id'] !== (int) $cust['id']) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $balance = max(0, (float) $booking['total_amount'] - (float) $booking['paid_amount']);
        $amount = round((float) ($_POST['amount'] ?? 0), 2);
        $method = $_POST['payment_method'] ?? 'online_portal';
        if (!in_array($method, ['bank_transfer', 'card_gateway', 'upi', 'online_portal'], true)) {
            $method = 'online_portal';
        }

        if ($amount <= 0 || $amount > $balance + 0.01) {
            flash('error', 'Please enter a valid payment amount up to ' . money($balance) . '.');
            redirect('/customer/bookings/' . $id);
        }

        $db = Database::getInstance();
        $payRef = 'PAY-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $txnId = 'GW-' . strtoupper(bin2hex(random_bytes(5)));

        $db->insert('payments', [
            'payment_reference' => $payRef,
            'booking_id' => $id,
            'customer_id' => (int) $cust['id'],
            'amount' => $amount,
            'currency' => $booking['currency'] ?? 'USD',
            'payment_method' => $method,
            'gateway_name' => 'Hosted Travel Settlement Gateway',
            'gateway_transaction_id' => $txnId,
            'status' => 'completed',
            'paid_at' => date('Y-m-d H:i:s'),
            'notes' => 'Customer portal payment (no card details stored).',
            'recorded_by' => (int) Auth::id(),
        ]);

        $model->recalculatePayments($id);
        $updated = $model->findDetailed($id);
        $newBalance = max(0, (float) $updated['total_amount'] - (float) $updated['paid_amount']);

        NotificationService::notifyRole(
            'super_admin',
            'payment',
            "Payment Received: {$payRef}",
            "{$cust['first_name']} {$cust['last_name']} paid " . money($amount) . " for {$booking['booking_reference']}.",
            "/admin/bookings/{$id}"
        );
        NotificationService::sendBrandedEmail($cust['email'], 'payment_receipt', [
            'customer_name' => $cust['first_name'] . ' ' . $cust['last_name'],
            'booking_reference' => $booking['booking_reference'],
            'amount_paid' => money($amount),
            'payment_reference' => $payRef,
            'balance_due' => money($newBalance),
        ]);
        AuditService::log('payments', 'create', "Customer submitted payment {$payRef} for {$booking['booking_reference']}", 'booking', $id, (int) Auth::id());

        flash('success', "Payment {$payRef} recorded! Your updated PDF invoice is ready.");
        redirect('/customer/bookings/' . $id);
    }

    public function requestCancellation(array $params): void
    {
        $cust = $this->currentCustomer();
        $id = (int) ($params['id'] ?? 0);
        $model = new Booking();
        $booking = $model->findDetailed($id);
        if (!$booking || (int) $booking['customer_id'] !== (int) $cust['id']) {
            http_response_code(403);
            exit('Unauthorized');
        }
        if (in_array($booking['booking_status'], ['Cancelled', 'Refunded', 'Completed'], true)) {
            flash('warning', 'This booking can no longer be cancelled.');
            redirect('/customer/bookings/' . $id);
        }

        $reason = trim((string) ($_POST['reason'] ?? 'Customer requested cancellation from portal.'));
        $db = Database::getInstance();
        $db->update('bookings', [
            'booking_status' => 'Cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        $db->insert('booking_notes', [
            'booking_id' => $id,
            'user_id' => (int) Auth::id(),
            'note' => 'Booking cancelled by customer: ' . $reason,
            'is_customer_visible' => 1,
        ]);

        NotificationService::notifyRole('super_admin', 'booking', "Booking Cancelled: {$booking['booking_reference']}", $reason, "/admin/bookings/{$id}");
        AuditService::log('bookings', 'cancel', "Customer cancelled booking {$booking['booking_reference']}", 'booking', $id, (int) Auth::id());
        flash('info', 'Your booking has been marked as Cancelled. Any eligible refund will be processed by our finance team.');
        redirect('/customer/bookings/' . $id);
    }

    public function downloadInvoice(array $params): void
    {
        $cust = $this->currentCustomer();
        $bookingId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $owner = $db->fetchColumn("SELECT customer_id FROM bookings WHERE id = :id AND deleted_at IS NULL LIMIT 1", ['id' => $bookingId]);
        if ((int) $owner !== (int) $cust['id']) {
            http_response_code(403);
            exit('Access denied.');
        }

        $invoice = PdfInvoiceService::generateForBooking($bookingId);
        if (!$invoice || empty($invoice['pdf_binary'])) {
            http_response_code(404);
            exit('Invoice unavailable.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $invoice['invoice_number'] . '.pdf"');
        header('Content-Length: ' . strlen($invoice['pdf_binary']));
        echo $invoice['pdf_binary'];
        exit;
    }

    public function payments(): void
    {
        $cust = $this->currentCustomer();
        $cid = (int) $cust['id'];
        $db = Database::getInstance();

        $payments = $db->fetchAll(
            "SELECT p.*, b.booking_reference
             FROM payments p INNER JOIN bookings b ON b.id = p.booking_id
             WHERE p.customer_id = :cid ORDER BY p.created_at DESC",
            ['cid' => $cid]
        );
        $invoices = $db->fetchAll(
            "SELECT i.*, b.booking_reference
             FROM invoices i INNER JOIN bookings b ON b.id = i.booking_id
             WHERE i.customer_id = :cid ORDER BY i.issued_at DESC",
            ['cid' => $cid]
        );

        View::render('customer/payments', [
            'pageTitle' => 'Payments & Invoices',
            'portal' => 'customer',
            'payments' => $payments,
            'invoices' => $invoices,
        ], 'dashboard');
    }

    public function wishlist(): void
    {
        $cust = $this->currentCustomer();
        $cid = (int) $cust['id'];
        $db = Database::getInstance();

        $packages = $db->fetchAll(
            "SELECT w.id AS wish_id, p.*, d.name AS destination_name
             FROM wishlist w
             INNER JOIN travel_packages p ON p.id = w.package_id
             INNER JOIN destinations d ON d.id = p.destination_id
             WHERE w.customer_id = :cid AND w.item_type = 'package'
             ORDER BY w.created_at DESC",
            ['cid' => $cid]
        );
        $destinations = $db->fetchAll(
            "SELECT w.id AS wish_id, d.*
             FROM wishlist w
             INNER JOIN destinations d ON d.id = w.destination_id
             WHERE w.customer_id = :cid AND w.item_type = 'destination'
             ORDER BY w.created_at DESC",
            ['cid' => $cid]
        );

        View::render('customer/wishlist', [
            'pageTitle' => 'Wishlist & Saved Destinations',
            'portal' => 'customer',
            'packages' => $packages,
            'destinations' => $destinations,
        ], 'dashboard');
    }

    public function removeWishlist(array $params): void
    {
        $cust = $this->currentCustomer();
        $db = Database::getInstance();
        $db->delete('wishlist', 'id = :id AND customer_id = :cid', [
            'id' => (int) ($params['id'] ?? 0),
            'cid' => (int) $cust['id'],
        ]);
        flash('success', 'Removed from your wishlist.');
        redirect('/customer/wishlist');
    }

    public function enquiries(): void
    {
        $cust = $this->currentCustomer();
        $db = Database::getInstance();
        $enquiries = $db->fetchAll(
            "SELECT * FROM enquiries WHERE customer_id = :cid OR LOWER(email) = :email ORDER BY created_at DESC",
            ['cid' => (int) $cust['id'], 'email' => strtolower($cust['email'])]
        );
        View::render('customer/enquiries', [
            'pageTitle' => 'My Travel Enquiries',
            'portal' => 'customer',
            'enquiries' => $enquiries,
        ], 'dashboard');
    }

    public function support(): void
    {
        $cust = $this->currentCustomer();
        $cid = (int) $cust['id'];
        $db = Database::getInstance();

        $tickets = $db->fetchAll(
            "SELECT sr.*, b.booking_reference
             FROM support_requests sr LEFT JOIN bookings b ON b.id = sr.booking_id
             WHERE sr.customer_id = :cid ORDER BY sr.created_at DESC",
            ['cid' => $cid]
        );
        $bookings = $db->fetchAll("SELECT id, booking_reference FROM bookings WHERE customer_id = :cid AND deleted_at IS NULL", ['cid' => $cid]);

        View::render('customer/support', [
            'pageTitle' => 'Concierge Support Requests',
            'portal' => 'customer',
            'tickets' => $tickets,
            'bookings' => $bookings,
        ], 'dashboard');
    }

    public function createSupport(): void
    {
        $cust = $this->currentCustomer();
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'subject' => 'required|min:3|max:180',
            'category' => 'required|max:60',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|min:5|max:3000',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/customer/support');
        }

        $db = Database::getInstance();
        $ticketNum = 'SUP-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $bookingId = !empty($_POST['booking_id']) ? (int) $_POST['booking_id'] : null;
        if ($bookingId) {
            $belongs = $db->fetchColumn("SELECT id FROM bookings WHERE id = :id AND customer_id = :cid", ['id' => $bookingId, 'cid' => (int) $cust['id']]);
            if (!$belongs) {
                $bookingId = null;
            }
        }

        $ticketId = $db->insert('support_requests', [
            'ticket_number' => $ticketNum,
            'customer_id' => (int) $cust['id'],
            'booking_id' => $bookingId,
            'subject' => $clean['subject'],
            'category' => $clean['category'],
            'priority' => $clean['priority'],
            'status' => 'open',
        ]);

        $db->insert('support_replies', [
            'support_request_id' => $ticketId,
            'user_id' => (int) Auth::id(),
            'message' => $clean['message'],
            'is_staff' => 0,
        ]);

        NotificationService::notifyRole('super_admin', 'support', "New Support Ticket {$ticketNum}", $clean['subject'], '/admin/support');
        flash('success', "Support request {$ticketNum} opened.");
        redirect('/customer/support/' . $ticketId);
    }

    public function supportDetail(array $params): void
    {
        $cust = $this->currentCustomer();
        $db = Database::getInstance();
        $ticket = $db->fetch(
            "SELECT sr.*, b.booking_reference FROM support_requests sr LEFT JOIN bookings b ON b.id = sr.booking_id WHERE sr.id = :id AND sr.customer_id = :cid LIMIT 1",
            ['id' => (int) ($params['id'] ?? 0), 'cid' => (int) $cust['id']]
        );
        if (!$ticket) {
            http_response_code(403);
            exit('Access denied.');
        }

        $replies = $db->fetchAll(
            "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM support_replies r INNER JOIN users u ON u.id = r.user_id
             WHERE r.support_request_id = :tid ORDER BY r.created_at ASC",
            ['tid' => (int) $ticket['id']]
        );

        View::render('customer/support-detail', [
            'pageTitle' => 'Ticket ' . $ticket['ticket_number'],
            'portal' => 'customer',
            'ticket' => $ticket,
            'replies' => $replies,
        ], 'dashboard');
    }

    public function replySupport(array $params): void
    {
        $cust = $this->currentCustomer();
        $tid = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $ticket = $db->fetch("SELECT * FROM support_requests WHERE id = :id AND customer_id = :cid LIMIT 1", ['id' => $tid, 'cid' => (int) $cust['id']]);
        if (!$ticket) {
            http_response_code(403);
            exit('Access denied.');
        }

        $msg = trim((string) ($_POST['message'] ?? ''));
        if ($msg === '') {
            flash('error', 'Reply message cannot be empty.');
            redirect('/customer/support/' . $tid);
        }

        $db->insert('support_replies', [
            'support_request_id' => $tid,
            'user_id' => (int) Auth::id(),
            'message' => $msg,
            'is_staff' => 0,
        ]);
        $db->update('support_requests', ['status' => 'open'], 'id = :id', ['id' => $tid]);
        flash('success', 'Reply sent.');
        redirect('/customer/support/' . $tid);
    }

    public function notifications(): void
    {
        $uid = (int) Auth::id();
        $db = Database::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            NotificationService::markAllRead($uid);
            flash('success', 'All notifications marked as read.');
            redirect('/customer/notifications');
        }
        $list = $db->fetchAll("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 40", ['uid' => $uid]);
        View::render('customer/notifications', [
            'pageTitle' => 'My Notifications',
            'portal' => 'customer',
            'notifications' => $list,
        ], 'dashboard');
    }

    public function profile(): void
    {
        $cust = $this->currentCustomer();
        View::render('customer/profile', [
            'pageTitle' => 'Profile & Security',
            'portal' => 'customer',
            'cust' => $cust,
        ], 'dashboard');
    }

    public function updateProfile(): void
    {
        $cust = $this->currentCustomer();
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'first_name' => 'required|min:2|max:80',
            'last_name' => 'required|min:2|max:80',
            'phone' => 'max:40',
            'nationality' => 'max:80',
            'passport_number' => 'max:60',
            'address' => 'max:255',
            'city' => 'max:100',
            'country' => 'max:100',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/customer/profile');
        }

        $avatarPath = $cust['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $up = Uploader::uploadImage($_FILES['avatar'], 'avatars', false);
            if (!$up['ok']) {
                flash('error', $up['error']);
                redirect('/customer/profile');
            }
            $avatarPath = $up['path'];
        }

        $db = Database::getInstance();
        $db->update('users', [
            'first_name' => $clean['first_name'],
            'last_name' => $clean['last_name'],
            'phone' => $clean['phone'] ?: null,
            'avatar' => $avatarPath,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => (int) $cust['user_id']]);

        $db->update('customers', [
            'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            'nationality' => $clean['nationality'] ?: null,
            'passport_number' => $clean['passport_number'] ?: null,
            'passport_expiry' => !empty($_POST['passport_expiry']) ? $_POST['passport_expiry'] : null,
            'address' => $clean['address'] ?: null,
            'city' => $clean['city'] ?: null,
            'country' => $clean['country'] ?: null,
            'travel_preferences' => trim((string) ($_POST['travel_preferences'] ?? '')) ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => (int) $cust['id']]);

        flash('success', 'Your personal travel profile has been saved.');
        redirect('/customer/profile');
    }

    public function changePassword(): void
    {
        $user = Auth::user();
        $validator = new Validator();
        $validator->validate($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|min:8|max:120',
            'confirm_password' => 'required|match:new_password',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/customer/profile');
        }

        if (!password_verify((string) $_POST['current_password'], $user['password_hash'])) {
            flash('error', 'Current password is incorrect.');
            redirect('/customer/profile');
        }

        $db = Database::getInstance();
        $db->update('users', [
            'password_hash' => password_hash((string) $_POST['new_password'], PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => (int) $user['id']]);

        AuditService::log('auth', 'password_change', 'Customer updated account password', 'user', (int) $user['id'], (int) $user['id']);
        flash('success', 'Password changed successfully.');
        redirect('/customer/profile');
    }
}
