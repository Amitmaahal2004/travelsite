<?php
declare(strict_types=1);

namespace App\Services;

use Core\Database;
use Core\PdfGenerator;

/**
 * Branded Booking Invoice Generator & Storage Service
 */
class PdfInvoiceService
{
    public static function generateForBooking(int $bookingId): ?array
    {
        $db = Database::getInstance();
        $booking = $db->fetch(
            "SELECT b.*, c.customer_code, c.address AS customer_address, c.city, c.country,
                    u.first_name, u.last_name, u.email AS customer_email, u.phone AS customer_phone,
                    p.title AS package_title, d.name AS destination_name
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             LEFT JOIN destinations d ON d.id = p.destination_id
             WHERE b.id = :id AND b.deleted_at IS NULL LIMIT 1",
            ['id' => $bookingId]
        );

        if (!$booking) {
            return null;
        }

        $brand = BrandingService::getAll();
        $invoice = $db->fetch("SELECT * FROM invoices WHERE booking_id = :bid LIMIT 1", ['bid' => $bookingId]);

        $subtotal = (float) $booking['base_amount'];
        $discount = (float) $booking['discount_amount'];
        $tax = (float) $booking['tax_amount'];
        $total = (float) $booking['total_amount'];
        $paid = (float) $booking['paid_amount'];
        $balance = max(0, $total - $paid);

        $invStatus = 'issued';
        if ($booking['booking_status'] === 'Refunded') {
            $invStatus = 'refunded';
        } elseif ($booking['booking_status'] === 'Cancelled') {
            $invStatus = 'cancelled';
        } elseif ($paid >= $total && $total > 0) {
            $invStatus = 'paid';
        } elseif ($paid > 0) {
            $invStatus = 'partially_paid';
        }

        if (!$invoice) {
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string) $bookingId, 5, '0', STR_PAD_LEFT);
            $invId = $db->insert('invoices', [
                'invoice_number' => $invoiceNumber,
                'booking_id' => $bookingId,
                'customer_id' => (int) $booking['customer_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'amount_paid' => $paid,
                'balance_due' => $balance,
                'status' => $invStatus,
                'issued_at' => date('Y-m-d H:i:s'),
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $invoice = $db->fetch("SELECT * FROM invoices WHERE id = :id", ['id' => $invId]);
        } else {
            $db->update('invoices', [
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'amount_paid' => $paid,
                'balance_due' => $balance,
                'status' => $invStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $invoice['id']]);
            $invoice = $db->fetch("SELECT * FROM invoices WHERE id = :id", ['id' => $invoice['id']]);
        }

        $payments = $db->fetchAll(
            "SELECT payment_reference, payment_method, amount, paid_at, created_at
             FROM payments WHERE booking_id = :bid AND status = 'completed' ORDER BY created_at DESC",
            ['bid' => $bookingId]
        );
        foreach ($payments as &$pay) {
            $pay['amount_fmt'] = money($pay['amount'], $booking['currency'] ?? 'USD');
        }

        $travellers = max(1, (int) $booking['total_travellers']);
        $unitPrice = $travellers > 0 ? ($subtotal / $travellers) : $subtotal;
        $addressLine = trim(($booking['customer_address'] ?? '') . ', ' . ($booking['city'] ?? '') . ' ' . ($booking['country'] ?? ''), ', ');

        $pdf = new PdfGenerator();
        $binary = $pdf->buildInvoicePdf([
            'company_name' => $brand['company_name'],
            'tagline' => $brand['website_tagline'],
            'company_address' => $brand['company_address'],
            'contact_email' => $brand['contact_email'],
            'contact_number' => $brand['contact_number'],
            'primary_color' => $brand['primary_color'],
            'accent_color' => $brand['accent_color'],
            'invoice_number' => $invoice['invoice_number'],
            'status' => $invoice['status'],
            'customer_name' => trim($booking['first_name'] . ' ' . $booking['last_name']),
            'customer_email' => $booking['customer_email'],
            'customer_phone' => $booking['customer_phone'] ?? 'N/A',
            'customer_address' => $addressLine,
            'booking_reference' => $booking['booking_reference'],
            'issued_at' => substr((string) $invoice['issued_at'], 0, 10),
            'travel_date' => $booking['travel_date'],
            'travellers' => $travellers,
            'package_title' => $booking['package_title'] ?: ($booking['custom_title'] ?: 'Custom Travel Package'),
            'destination' => $booking['destination_name'] ?: 'Bespoke Itinerary',
            'unit_price_fmt' => money($unitPrice, $booking['currency']),
            'subtotal_fmt' => money($subtotal, $booking['currency']),
            'discount_fmt' => money($discount, $booking['currency']),
            'tax_fmt' => money($tax, $booking['currency']),
            'total_fmt' => money($total, $booking['currency']),
            'paid_fmt' => money($paid, $booking['currency']),
            'balance_fmt' => money($balance, $booking['currency']),
            'payments' => $payments,
        ]);

        $dir = BASE_PATH . '/storage/invoices';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $relativePath = 'invoices/' . $invoice['invoice_number'] . '.pdf';
        file_put_contents(BASE_PATH . '/storage/' . $relativePath, $binary);

        $db->update('invoices', ['pdf_path' => $relativePath], 'id = :id', ['id' => $invoice['id']]);
        $invoice['pdf_path'] = $relativePath;
        $invoice['pdf_binary'] = $binary;

        return $invoice;
    }
}
