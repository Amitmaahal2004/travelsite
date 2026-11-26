<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PdfInvoiceService;

class Booking extends BaseModel
{
    protected string $table = 'bookings';

    public const STATUSES = [
        'Pending',
        'Enquiry Received',
        'Under Review',
        'Confirmed',
        'Payment Pending',
        'Partially Paid',
        'Paid',
        'Completed',
        'Cancelled',
        'Refunded',
    ];

    public function generateReference(): string
    {
        $year = date('Y');
        $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $count = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM bookings") + 101;
        return sprintf('BK-%s-%04d%s', $year, $count, substr($rand, 0, 2));
    }

    public function createBookingWithTravellers(array $payload, array $travellers, ?int $actorUserId = null): int
    {
        $this->db->beginTransaction();
        try {
            $ref = $this->generateReference();
            $pkg = null;
            if (!empty($payload['package_id'])) {
                $pkg = $this->db->fetch("SELECT * FROM travel_packages WHERE id = :id LIMIT 1", ['id' => (int) $payload['package_id']]);
            }

            $adults = max(1, (int) ($payload['adults'] ?? 1));
            $children = max(0, (int) ($payload['children'] ?? 0));
            $totalTravellers = $adults + $children;

            $unitPrice = $pkg ? (float) ($pkg['discount_price'] ?: $pkg['base_price']) : (float) ($payload['custom_unit_price'] ?? 0);
            // Children priced at 75% of adult rate unless custom total provided
            $baseAmount = isset($payload['base_amount'])
                ? (float) $payload['base_amount']
                : (($adults * $unitPrice) + ($children * $unitPrice * 0.75));

            $discountAmount = (float) ($payload['discount_amount'] ?? 0);
            $promoCode = trim((string) ($payload['promo_code'] ?? ''));

            if ($promoCode !== '' && $discountAmount <= 0) {
                $offer = $this->db->fetch(
                    "SELECT * FROM seasonal_offers
                     WHERE code = :code AND is_active = 1
                       AND start_date <= CURDATE() AND end_date >= CURDATE()
                     LIMIT 1",
                    ['code' => strtoupper($promoCode)]
                );
                if ($offer && $baseAmount >= (float) $offer['min_booking_amount']) {
                    if ($offer['discount_type'] === 'percentage') {
                        $discountAmount = round($baseAmount * ((float) $offer['discount_value'] / 100), 2);
                    } else {
                        $discountAmount = min($baseAmount, (float) $offer['discount_value']);
                    }
                } else {
                    $promoCode = '';
                }
            }

            $taxable = max(0, $baseAmount - $discountAmount);
            $taxRate = (float) app_config('tax_rate', 0.08);
            $taxAmount = round($taxable * $taxRate, 2);
            $totalAmount = round($taxable + $taxAmount, 2);

            $travelDate = $payload['travel_date'];
            $returnDate = $payload['return_date'] ?? null;
            if (!$returnDate && $pkg) {
                $days = max(1, (int) $pkg['duration_days']);
                $returnDate = date('Y-m-d', strtotime($travelDate . " +{$days} days"));
            }

            $bookingId = $this->db->insert('bookings', [
                'booking_reference' => $ref,
                'customer_id' => (int) $payload['customer_id'],
                'package_id' => !empty($payload['package_id']) ? (int) $payload['package_id'] : null,
                'assigned_employee_id' => !empty($payload['assigned_employee_id']) ? (int) $payload['assigned_employee_id'] : null,
                'custom_title' => $payload['custom_title'] ?? null,
                'travel_date' => $travelDate,
                'return_date' => $returnDate,
                'adults' => $adults,
                'children' => $children,
                'total_travellers' => $totalTravellers,
                'base_amount' => $baseAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'currency' => $pkg['currency'] ?? app_config('default_currency', 'USD'),
                'promo_code' => $promoCode ?: null,
                'booking_status' => $payload['booking_status'] ?? 'Pending',
                'payment_status' => 'unpaid',
                'payment_method' => $payload['payment_method'] ?? 'bank_transfer',
                'special_requests' => $payload['special_requests'] ?? null,
                'internal_notes' => $payload['internal_notes'] ?? null,
                'created_by' => $actorUserId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($travellers as $index => $t) {
                if (empty(trim((string) ($t['full_name'] ?? '')))) {
                    continue;
                }
                $this->db->insert('booking_travellers', [
                    'booking_id' => $bookingId,
                    'full_name' => trim((string) $t['full_name']),
                    'email' => trim((string) ($t['email'] ?? '')) ?: null,
                    'phone' => trim((string) ($t['phone'] ?? '')) ?: null,
                    'age' => !empty($t['age']) ? (int) $t['age'] : null,
                    'gender' => $t['gender'] ?? null,
                    'nationality' => $t['nationality'] ?? null,
                    'passport_number' => $t['passport_number'] ?? null,
                    'dietary_requirements' => $t['dietary_requirements'] ?? null,
                    'is_lead' => $index === 0 ? 1 : 0,
                ]);
            }

            $this->db->insert('booking_notes', [
                'booking_id' => $bookingId,
                'user_id' => $actorUserId,
                'note' => 'Booking request created with reference ' . $ref . '.',
                'is_customer_visible' => 1,
            ]);

            $this->db->commit();

            // Generate initial invoice + send notifications & branded confirmation email
            PdfInvoiceService::generateForBooking($bookingId);

            $cust = $this->db->fetch(
                "SELECT c.id, u.id AS user_id, u.email, CONCAT(u.first_name, ' ', u.last_name) AS full_name
                 FROM customers c INNER JOIN users u ON u.id = c.user_id WHERE c.id = :cid",
                ['cid' => (int) $payload['customer_id']]
            );

            $pkgTitle = $pkg['title'] ?? ($payload['custom_title'] ?? 'Curated Travel Package');

            NotificationService::notifyRole(
                'super_admin',
                'booking',
                "New Booking {$ref}",
                "{$cust['full_name']} submitted booking {$ref} for {$pkgTitle}.",
                "/admin/bookings/{$bookingId}"
            );

            if ($cust) {
                NotificationService::notifyUser(
                    (int) $cust['user_id'],
                    'booking',
                    "Booking Received: {$ref}",
                    "Your booking request for {$pkgTitle} has been received and is awaiting review.",
                    "/customer/bookings/{$bookingId}"
                );
                NotificationService::sendBrandedEmail($cust['email'], 'booking_confirmation', [
                    'customer_name' => $cust['full_name'],
                    'booking_reference' => $ref,
                    'package_title' => $pkgTitle,
                    'travel_date' => $travelDate,
                    'travellers' => (string) $totalTravellers,
                    'total_amount' => money($totalAmount),
                ]);
            }

            AuditService::log('bookings', 'create', "Created booking {$ref}", 'booking', $bookingId, $actorUserId);

            return $bookingId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findDetailed(int $id): ?array
    {
        $booking = $this->db->fetch(
            "SELECT b.*,
                    p.title AS package_title, p.slug AS package_slug, p.duration_days, p.duration_nights, p.featured_image,
                    d.name AS destination_name, d.location_label,
                    c.customer_code, c.user_id AS customer_user_id, c.address AS customer_address, c.city AS customer_city, c.country AS customer_country,
                    CONCAT(cu.first_name, ' ', cu.last_name) AS customer_name, cu.email AS customer_email, cu.phone AS customer_phone,
                    e.employee_code, e.user_id AS employee_user_id,
                    CONCAT(eu.first_name, ' ', eu.last_name) AS assigned_employee_name, eu.email AS assigned_employee_email
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users cu ON cu.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             LEFT JOIN destinations d ON d.id = p.destination_id
             LEFT JOIN employees e ON e.id = b.assigned_employee_id
             LEFT JOIN users eu ON eu.id = e.user_id
             WHERE b.id = :id AND b.deleted_at IS NULL
             LIMIT 1",
            ['id' => $id]
        );
        if (!$booking) {
            return null;
        }

        $booking['travellers'] = $this->db->fetchAll("SELECT * FROM booking_travellers WHERE booking_id = :bid ORDER BY is_lead DESC, id ASC", ['bid' => $id]);
        $booking['payments'] = $this->db->fetchAll("SELECT * FROM payments WHERE booking_id = :bid ORDER BY created_at DESC", ['bid' => $id]);
        $booking['refunds'] = $this->db->fetchAll("SELECT * FROM refunds WHERE booking_id = :bid ORDER BY created_at DESC", ['bid' => $id]);
        $booking['invoice'] = $this->db->fetch("SELECT * FROM invoices WHERE booking_id = :bid LIMIT 1", ['bid' => $id]);
        $booking['notes'] = $this->db->fetchAll(
            "SELECT bn.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM booking_notes bn LEFT JOIN users u ON u.id = bn.user_id
             WHERE bn.booking_id = :bid ORDER BY bn.created_at DESC",
            ['bid' => $id]
        );

        return $booking;
    }

    public function findByReference(string $ref): ?array
    {
        $id = $this->db->fetchColumn("SELECT id FROM bookings WHERE booking_reference = :ref AND deleted_at IS NULL LIMIT 1", ['ref' => $ref]);
        return $id ? $this->findDetailed((int) $id) : null;
    }

    public function recalculatePayments(int $bookingId): void
    {
        $paid = (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = :bid AND status = 'completed'",
            ['bid' => $bookingId]
        );
        $refunded = (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE booking_id = :bid AND status = 'processed'",
            ['bid' => $bookingId]
        );
        $booking = $this->db->fetch("SELECT total_amount, booking_status FROM bookings WHERE id = :id", ['id' => $bookingId]);
        if (!$booking) {
            return;
        }
        $total = (float) $booking['total_amount'];
        $netPaid = max(0, $paid - $refunded);

        $payStatus = 'unpaid';
        $bStatus = $booking['booking_status'];
        if ($refunded > 0 && $refunded >= $paid) {
            $payStatus = 'refunded';
            $bStatus = 'Refunded';
        } elseif ($netPaid >= $total && $total > 0) {
            $payStatus = 'paid';
            if (!in_array($bStatus, ['Completed', 'Cancelled', 'Refunded'], true)) {
                $bStatus = 'Paid';
            }
        } elseif ($netPaid > 0) {
            $payStatus = 'partial';
            if (!in_array($bStatus, ['Completed', 'Cancelled', 'Refunded'], true)) {
                $bStatus = 'Partially Paid';
            }
        }

        $this->db->update('bookings', [
            'paid_amount' => $netPaid,
            'payment_status' => $payStatus,
            'booking_status' => $bStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $bookingId]);

        PdfInvoiceService::generateForBooking($bookingId);
    }
}
