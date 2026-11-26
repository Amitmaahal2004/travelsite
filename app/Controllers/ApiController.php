<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\Validator;

/**
 * AJAX & JSON API Controller
 */
class ApiController
{
    public function subscribeNewsletter(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['email' => 'required|email|max:160']);
        if ($validator->fails()) {
            json_response(['success' => false, 'message' => $validator->firstError()], 422);
        }

        $db = Database::getInstance();
        $exists = $db->fetchColumn("SELECT id FROM newsletter_subscribers WHERE LOWER(email) = :e", ['e' => strtolower($clean['email'])]);
        if ($exists) {
            json_response(['success' => true, 'message' => 'You are already subscribed to our dispatches.']);
        }

        $db->insert('newsletter_subscribers', [
            'email' => strtolower($clean['email']),
            'name' => trim((string) ($_POST['name'] ?? '')) ?: null,
            'status' => 'subscribed',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        json_response(['success' => true, 'message' => 'Welcome aboard! You are now subscribed to private dispatches.']);
    }

    public function toggleWishlist(): void
    {
        $user = Auth::user();
        if (!$user || empty($user['customer_id'])) {
            json_response([
                'success' => false,
                'message' => 'Sign in with a customer account to save journeys.',
                'redirect' => url('/login'),
            ], 401);
        }

        $type = ($_POST['item_type'] ?? 'package') === 'destination' ? 'destination' : 'package';
        $itemId = (int) ($_POST['item_id'] ?? 0);
        if ($itemId <= 0) {
            json_response(['success' => false, 'message' => 'Invalid item.'], 422);
        }

        $db = Database::getInstance();
        $col = $type === 'destination' ? 'destination_id' : 'package_id';
        $existing = $db->fetchColumn(
            "SELECT id FROM wishlist WHERE customer_id = :cid AND {$col} = :iid LIMIT 1",
            ['cid' => (int) $user['customer_id'], 'iid' => $itemId]
        );

        if ($existing) {
            $db->delete('wishlist', 'id = :id', ['id' => (int) $existing]);
            json_response(['success' => true, 'saved' => false, 'message' => 'Removed from your wishlist.']);
        }

        $db->insert('wishlist', [
            'customer_id' => (int) $user['customer_id'],
            'item_type' => $type,
            'package_id' => $type === 'package' ? $itemId : null,
            'destination_id' => $type === 'destination' ? $itemId : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        json_response(['success' => true, 'saved' => true, 'message' => 'Saved to your wishlist.']);
    }

    public function verifyPromo(): void
    {
        $code = strtoupper(trim((string) ($_GET['code'] ?? '')));
        $amount = (float) ($_GET['amount'] ?? 0);
        if ($code === '') {
            json_response(['valid' => false, 'message' => 'Please enter a promo code.']);
        }

        $db = Database::getInstance();
        $offer = $db->fetch(
            "SELECT * FROM seasonal_offers
             WHERE code = :code AND is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()
             LIMIT 1",
            ['code' => $code]
        );

        if (!$offer) {
            json_response(['valid' => false, 'message' => 'Offer code is invalid or expired.']);
        }
        if ($amount < (float) $offer['min_booking_amount']) {
            json_response(['valid' => false, 'message' => 'Minimum booking value for this code is ' . money($offer['min_booking_amount']) . '.']);
        }

        $discount = $offer['discount_type'] === 'percentage'
            ? round($amount * ((float) $offer['discount_value'] / 100), 2)
            : min($amount, (float) $offer['discount_value']);

        json_response([
            'valid' => true,
            'discount' => $discount,
            'discount_fmt' => money($discount),
            'message' => "Promo {$code} applied (-" . money($discount) . ").",
        ]);
    }

    public function markNotificationRead(array $params): void
    {
        $user = Auth::user();
        if (!$user) {
            json_response(['success' => false], 401);
        }
        NotificationService::markAsRead((int) ($params['id'] ?? 0), (int) $user['id']);
        json_response(['success' => true]);
    }
}
