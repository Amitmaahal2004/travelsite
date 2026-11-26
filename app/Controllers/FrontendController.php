<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Cms;
use App\Models\Destination;
use App\Models\Package;
use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\Validator;
use Core\View;

class FrontendController
{
    private Destination $destModel;
    private Category $catModel;
    private Package $pkgModel;
    private Cms $cmsModel;

    public function __construct()
    {
        $this->destModel = new Destination();
        $this->catModel = new Category();
        $this->pkgModel = new Package();
        $this->cmsModel = new Cms();
    }

    private function wishlistIds(): array
    {
        $user = Auth::user();
        if (!$user || empty($user['customer_id'])) {
            return [];
        }
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT package_id FROM wishlist WHERE customer_id = :cid AND package_id IS NOT NULL", ['cid' => (int) $user['customer_id']]);
        return array_map('intval', array_column($rows, 'package_id'));
    }

    public function home(): void
    {
        View::render('frontend/home', [
            'pageKey' => 'home',
            'solidHeader' => false,
            'sections' => $this->cmsModel->homepageSections(),
            'featuredDestinations' => $this->destModel->featured(5),
            'allDestinations' => $this->destModel->allActive(),
            'categories' => $this->catModel->allPublic(),
            'featuredPackages' => $this->pkgModel->featured(6),
            'seasonalOffers' => $this->cmsModel->activeOffers(),
            'offerPackages' => $this->pkgModel->seasonalOffers(3),
            'testimonials' => $this->cmsModel->testimonials(true),
            'blogPosts' => $this->cmsModel->blogPosts(1, 3)['items'],
            'wishlistIds' => $this->wishlistIds(),
        ], 'frontend');
    }

    public function about(): void
    {
        $page = $this->cmsModel->findPageBySlug('about');
        View::render('frontend/about', [
            'pageKey' => 'about',
            'solidHeader' => true,
            'page' => $page,
            'testimonials' => $this->cmsModel->testimonials(true),
        ], 'frontend');
    }

    public function destinations(): void
    {
        $filters = [
            'search' => trim((string) ($_GET['q'] ?? '')),
            'region' => trim((string) ($_GET['region'] ?? '')),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->destModel->paginatePublic($filters, $page, 9);

        View::render('frontend/destinations', [
            'pageKey' => 'destinations',
            'solidHeader' => true,
            'destinations' => $result['items'],
            'meta' => $result['meta'],
            'regions' => $this->destModel->allRegions(),
            'filters' => $filters,
        ], 'frontend');
    }

    public function destinationDetail(array $params): void
    {
        $dest = $this->destModel->findBySlug($params['slug'] ?? '');
        if (!$dest) {
            http_response_code(404);
            View::render('frontend/404', ['solidHeader' => true], 'frontend');
            return;
        }

        $packages = $this->pkgModel->forDestination((int) $dest['id']);
        View::render('frontend/destination-detail', [
            'pageKey' => 'destinations',
            'solidHeader' => true,
            'seoOverrides' => [
                'title' => $dest['name'] . ' — ' . $dest['location_label'],
                'description' => $dest['summary'] ?? $dest['tagline'],
                'og_image' => $dest['hero_image'],
            ],
            'destination' => $dest,
            'packages' => $packages,
            'wishlistIds' => $this->wishlistIds(),
        ], 'frontend');
    }

    public function packages(): void
    {
        $filters = [
            'search' => trim((string) ($_GET['q'] ?? '')),
            'destination' => trim((string) ($_GET['destination'] ?? '')),
            'category' => trim((string) ($_GET['category'] ?? '')),
            'offer' => !empty($_GET['offer']),
            'max_price' => trim((string) ($_GET['max_price'] ?? '')),
            'sort' => trim((string) ($_GET['sort'] ?? 'featured')),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->pkgModel->paginatePublic($filters, $page, 9);

        View::render('frontend/packages', [
            'pageKey' => 'packages',
            'solidHeader' => true,
            'packages' => $result['items'],
            'meta' => $result['meta'],
            'destinations' => $this->destModel->allActive(),
            'categories' => $this->catModel->allPublic(),
            'filters' => $filters,
            'wishlistIds' => $this->wishlistIds(),
        ], 'frontend');
    }

    public function packageDetail(array $params): void
    {
        $pkg = $this->pkgModel->findFullBySlug($params['slug'] ?? '');
        if (!$pkg) {
            http_response_code(404);
            View::render('frontend/404', ['solidHeader' => true], 'frontend');
            return;
        }

        View::render('frontend/package-detail', [
            'pageKey' => 'packages',
            'solidHeader' => true,
            'seoOverrides' => [
                'title' => $pkg['title'],
                'description' => $pkg['subtitle'],
                'og_image' => $pkg['featured_image'],
            ],
            'pkg' => $pkg,
            'wishlistIds' => $this->wishlistIds(),
        ], 'frontend');
    }

    public function categories(): void
    {
        View::render('frontend/categories', [
            'pageKey' => 'categories',
            'solidHeader' => true,
            'categories' => $this->catModel->allPublic(),
        ], 'frontend');
    }

    public function categoryDetail(array $params): void
    {
        $cat = $this->catModel->findBySlug($params['slug'] ?? '');
        if (!$cat) {
            http_response_code(404);
            View::render('frontend/404', ['solidHeader' => true], 'frontend');
            return;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->pkgModel->paginatePublic(['category' => $cat['slug']], $page, 9);

        View::render('frontend/category-detail', [
            'pageKey' => 'categories',
            'solidHeader' => true,
            'seoOverrides' => ['title' => $cat['name'], 'description' => $cat['description']],
            'category' => $cat,
            'packages' => $result['items'],
            'meta' => $result['meta'],
            'wishlistIds' => $this->wishlistIds(),
        ], 'frontend');
    }

    public function customEnquiryForm(): void
    {
        View::render('frontend/custom-enquiry', [
            'pageKey' => 'custom_enquiry',
            'solidHeader' => true,
            'destinations' => $this->destModel->allActive(),
            'categories' => $this->catModel->allPublic(),
            'preselectedStyle' => trim((string) ($_GET['style'] ?? '')),
            'user' => Auth::user(),
        ], 'frontend');
    }

    public function submitCustomEnquiry(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'full_name' => 'required|min:2|max:140',
            'email' => 'required|email|max:160',
            'phone' => 'max:40',
            'destination_interest' => 'required|max:160',
            'travel_date' => 'date',
            'travellers_count' => 'integer|min_val:1',
            'budget_range' => 'max:80',
            'message' => 'required|min:10|max:3000',
        ]);

        if ($validator->fails()) {
            set_old($_POST);
            flash('error', $validator->firstError());
            redirect('/custom-enquiry');
        }

        $db = Database::getInstance();
        $user = Auth::user();
        $ref = 'ENQ-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        $db->insert('enquiries', [
            'enquiry_reference' => $ref,
            'enquiry_type' => 'custom_tour',
            'customer_id' => $user['customer_id'] ?? null,
            'destination_interest' => $clean['destination_interest'],
            'full_name' => $clean['full_name'],
            'email' => $clean['email'],
            'phone' => $clean['phone'] ?: null,
            'travel_date' => $clean['travel_date'] ?: null,
            'travellers_count' => $clean['travellers_count'] ? (int) $clean['travellers_count'] : 2,
            'budget_range' => $clean['budget_range'] ?: null,
            'subject' => 'Custom Itinerary Request: ' . $clean['destination_interest'],
            'message' => $clean['message'],
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        NotificationService::notifyRole(
            'super_admin',
            'enquiry',
            "Custom Tour Enquiry {$ref}",
            "{$clean['full_name']} requested a bespoke journey to {$clean['destination_interest']}.",
            '/admin/enquiries'
        );

        flash('success', "Thank you! Your bespoke enquiry ({$ref}) has been sent to our Journey Architects.");
        redirect('/custom-enquiry');
    }

    public function bookingForm(array $params = []): void
    {
        $slug = $params['slug'] ?? ($_GET['package'] ?? '');
        $selectedPkg = $slug ? $this->pkgModel->findFullBySlug($slug) : null;
        $allPackages = $this->pkgModel->paginatePublic([], 1, 50)['items'];

        View::render('frontend/booking', [
            'pageKey' => 'packages',
            'solidHeader' => true,
            'selectedPkg' => $selectedPkg,
            'packages' => $allPackages,
            'offers' => $this->cmsModel->activeOffers(),
            'user' => Auth::user(),
        ], 'frontend');
    }

    public function submitBooking(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'package_id' => 'required|integer',
            'travel_date' => 'required|date',
            'adults' => 'required|integer|min_val:1',
            'children' => 'integer|min_val:0',
            'lead_name' => 'required|min:2|max:140',
            'lead_email' => 'required|email|max:160',
            'lead_phone' => 'required|min:6|max:40',
            'payment_method' => 'required|in:bank_transfer,card_gateway,upi,online_portal',
            'promo_code' => 'max:40',
            'special_requests' => 'max:2000',
        ]);

        if ($validator->fails()) {
            set_old($_POST);
            flash('error', $validator->firstError());
            redirect('/book');
        }

        if (strtotime($clean['travel_date']) < strtotime('today')) {
            set_old($_POST);
            flash('error', 'Travel departure date must be today or in the future.');
            redirect('/book');
        }

        $db = Database::getInstance();
        $user = Auth::user();
        $customerId = $user['customer_id'] ?? null;
        $actorUserId = $user['id'] ?? null;

        if (!$customerId) {
            // Check if lead email already belongs to an account
            $existingUser = $db->fetch("SELECT id FROM users WHERE LOWER(email) = :email AND deleted_at IS NULL LIMIT 1", [
                'email' => strtolower($clean['lead_email']),
            ]);

            if ($existingUser) {
                set_old($_POST);
                flash('warning', 'An account already exists with this email address. Please sign in first so your booking appears directly in your Customer Portal.');
                redirect('/login');
            }

            // Create a new customer account cleanly for first-time guest booker
            $nameParts = explode(' ', $clean['lead_name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Traveler';
            $rawPass = trim((string) ($_POST['account_password'] ?? ''));
            $passwordToUse = strlen($rawPass) >= 8 ? $rawPass : bin2hex(random_bytes(8));

            $db->beginTransaction();
            try {
                $uuid = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    random_int(0, 0xffff), random_int(0, 0xffff),
                    random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000,
                    random_int(0, 0x3fff) | 0x8000,
                    random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
                );
                $newUserId = $db->insert('users', [
                    'uuid' => $uuid,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => strtolower($clean['lead_email']),
                    'phone' => $clean['lead_phone'],
                    'password_hash' => password_hash($passwordToUse, PASSWORD_BCRYPT),
                    'status' => 'active',
                ]);
                $custRoleId = (int) $db->fetchColumn("SELECT id FROM roles WHERE slug = 'customer' LIMIT 1");
                $db->insert('user_roles', ['user_id' => $newUserId, 'role_id' => $custRoleId]);
                $customerId = $db->insert('customers', [
                    'user_id' => $newUserId,
                    'customer_code' => 'CUST-' . (1000 + $newUserId),
                    'loyalty_tier' => 'explorer',
                ]);
                $db->commit();
                $actorUserId = $newUserId;
                Auth::loginUser($newUserId);
            } catch (\Throwable $e) {
                $db->rollBack();
                throw $e;
            }
        }

        // Build traveller list
        $travellers = [[
            'full_name' => $clean['lead_name'],
            'email' => $clean['lead_email'],
            'phone' => $clean['lead_phone'],
            'nationality' => trim((string) ($_POST['lead_nationality'] ?? '')),
            'passport_number' => trim((string) ($_POST['lead_passport'] ?? '')),
            'dietary_requirements' => trim((string) ($_POST['lead_dietary'] ?? '')),
        ]];

        if (!empty($_POST['guest_name']) && is_array($_POST['guest_name'])) {
            foreach ($_POST['guest_name'] as $idx => $gName) {
                $gName = trim((string) $gName);
                if ($gName !== '') {
                    $travellers[] = [
                        'full_name' => $gName,
                        'age' => $_POST['guest_age'][$idx] ?? null,
                        'nationality' => trim((string) ($_POST['guest_nationality'][$idx] ?? '')),
                    ];
                }
            }
        }

        $bookingModel = new Booking();
        $bookingId = $bookingModel->createBookingWithTravellers([
            'customer_id' => $customerId,
            'package_id' => (int) $clean['package_id'],
            'travel_date' => $clean['travel_date'],
            'adults' => (int) $clean['adults'],
            'children' => (int) ($clean['children'] ?? 0),
            'promo_code' => $clean['promo_code'] ?? '',
            'payment_method' => $clean['payment_method'],
            'special_requests' => $clean['special_requests'] ?? null,
        ], $travellers, $actorUserId ? (int) $actorUserId : null);

        $created = $bookingModel->findDetailed($bookingId);
        redirect('/booking/success/' . $created['booking_reference']);
    }

    public function bookingSuccess(array $params): void
    {
        $bookingModel = new Booking();
        $booking = $bookingModel->findByReference($params['ref'] ?? '');
        if (!$booking) {
            redirect('/');
        }
        $user = Auth::user();
        // Only reveal booking details if logged in as the owning customer or staff/admin
        $canViewDetails = $user && (
            $user['role_slug'] === 'super_admin' ||
            (int) ($user['customer_id'] ?? 0) === (int) $booking['customer_id']
        );

        if (!$canViewDetails) {
            redirect('/login');
        }

        View::render('frontend/booking-success', [
            'pageKey' => 'packages',
            'solidHeader' => true,
            'booking' => $booking,
        ], 'frontend');
    }

    public function contact(): void
    {
        View::render('frontend/contact', [
            'pageKey' => 'contact',
            'solidHeader' => true,
            'user' => Auth::user(),
        ], 'frontend');
    }

    public function submitContact(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'full_name' => 'required|min:2|max:140',
            'email' => 'required|email|max:160',
            'phone' => 'max:40',
            'subject' => 'required|min:3|max:180',
            'message' => 'required|min:10|max:3000',
        ]);

        if ($validator->fails()) {
            set_old($_POST);
            flash('error', $validator->firstError());
            redirect('/contact');
        }

        $db = Database::getInstance();
        $user = Auth::user();
        $ref = 'CNT-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        $db->insert('enquiries', [
            'enquiry_reference' => $ref,
            'enquiry_type' => 'contact',
            'customer_id' => $user['customer_id'] ?? null,
            'full_name' => $clean['full_name'],
            'email' => $clean['email'],
            'phone' => $clean['phone'] ?: null,
            'subject' => $clean['subject'],
            'message' => $clean['message'],
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        NotificationService::notifyRole(
            'super_admin',
            'enquiry',
            "New Contact Message {$ref}",
            "{$clean['full_name']}: {$clean['subject']}",
            '/admin/enquiries'
        );

        flash('success', "Message received ({$ref}). Our concierge desk will respond within 24 hours.");
        redirect('/contact');
    }

    public function blog(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $cat = trim((string) ($_GET['category'] ?? '')) ?: null;
        $result = $this->cmsModel->blogPosts($page, 6, $cat);

        View::render('frontend/blog', [
            'pageKey' => 'blog',
            'solidHeader' => true,
            'posts' => $result['items'],
            'meta' => $result['meta'],
            'activeCategory' => $cat,
        ], 'frontend');
    }

    public function blogDetail(array $params): void
    {
        $post = $this->cmsModel->findBlogBySlug($params['slug'] ?? '');
        if (!$post) {
            http_response_code(404);
            View::render('frontend/404', ['solidHeader' => true], 'frontend');
            return;
        }
        View::render('frontend/blog-detail', [
            'pageKey' => 'blog',
            'solidHeader' => true,
            'seoOverrides' => [
                'title' => $post['seo_title'] ?: $post['title'],
                'description' => $post['seo_description'] ?: $post['excerpt'],
                'og_image' => $post['featured_image'],
            ],
            'post' => $post,
        ], 'frontend');
    }

    public function testimonials(): void
    {
        View::render('frontend/testimonials', [
            'pageKey' => 'home',
            'solidHeader' => true,
            'testimonials' => $this->cmsModel->testimonials(false),
        ], 'frontend');
    }

    public function faq(): void
    {
        View::render('frontend/faq', [
            'pageKey' => 'home',
            'solidHeader' => true,
            'faqs' => $this->cmsModel->faqs(),
        ], 'frontend');
    }

    public function cmsPage(array $params): void
    {
        $slug = $params['slug'] ?? current_route_path();
        $slug = ltrim($slug, '/');
        $page = $this->cmsModel->findPageBySlug($slug);
        if (!$page) {
            http_response_code(404);
            View::render('frontend/404', ['solidHeader' => true], 'frontend');
            return;
        }

        View::render('frontend/legal', [
            'pageKey' => $slug,
            'solidHeader' => true,
            'seoOverrides' => [
                'title' => $page['seo_title'] ?: $page['title'],
                'description' => $page['seo_description'] ?: $page['subtitle'],
            ],
            'page' => $page,
        ], 'frontend');
    }
}
