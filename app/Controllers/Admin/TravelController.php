<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuditService;
use Core\Auth;
use Core\Database;
use Core\Uploader;
use Core\Validator;
use Core\View;

class TravelController
{
    public function countries(): void
    {
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = $q !== '' ? 'WHERE name LIKE :q OR region LIKE :q OR code LIKE :q' : '';
        $params = $q !== '' ? ['q' => "%{$q}%"] : [];
        $countries = $db->fetchAll("SELECT * FROM countries {$where} ORDER BY name ASC", $params);

        View::render('admin/travel/countries', [
            'pageTitle' => 'Countries & Regions',
            'portal' => 'admin',
            'countries' => $countries,
            'q' => $q,
        ], 'dashboard');
    }

    public function storeCountry(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:3',
            'region' => 'required|min:2|max:80',
            'currency' => 'required|min:3|max:10',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/countries');
        }
        $db = Database::getInstance();
        $db->insert('countries', [
            'name' => $clean['name'],
            'code' => strtoupper($clean['code']),
            'region' => $clean['region'],
            'currency' => strtoupper($clean['currency']),
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'is_active' => 1,
        ]);
        AuditService::log('destinations', 'create_country', "Added country {$clean['name']}", 'country', null, (int) Auth::id());
        flash('success', 'Country added.');
        redirect('/admin/countries');
    }

    public function deleteCountry(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $inUse = (int) $db->fetchColumn("SELECT COUNT(*) FROM destinations WHERE country_id = :id AND deleted_at IS NULL", ['id' => $id]);
        if ($inUse > 0) {
            flash('error', 'Cannot delete a country that still has active destinations.');
            redirect('/admin/countries');
        }
        $db->delete('countries', 'id = :id', ['id' => $id]);
        flash('success', 'Country deleted.');
        redirect('/admin/countries');
    }

    public function destinations(): void
    {
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $where = ['d.deleted_at IS NULL'];
        $params = [];
        if ($q !== '') {
            $where[] = '(d.name LIKE :q OR d.location_label LIKE :q OR c.name LIKE :q)';
            $params['q'] = "%{$q}%";
        }
        $wSql = implode(' AND ', $where);
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM destinations d INNER JOIN countries c ON c.id = d.country_id WHERE {$wSql}", $params);
        $meta = paginate_meta($total, 12, $page);

        $destinations = $db->fetchAll(
            "SELECT d.*, c.name AS country_name FROM destinations d INNER JOIN countries c ON c.id = d.country_id
             WHERE {$wSql} ORDER BY d.sort_order ASC, d.id DESC LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );
        $countries = $db->fetchAll("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name");

        View::render('admin/travel/destinations', [
            'pageTitle' => 'Manage Destinations',
            'portal' => 'admin',
            'destinations' => $destinations,
            'countries' => $countries,
            'meta' => $meta,
            'q' => $q,
        ], 'dashboard');
    }

    public function storeDestination(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'name' => 'required|min:2|max:120',
            'country_id' => 'required|integer',
            'location_label' => 'required|min:2|max:120',
            'starting_price' => 'numeric|min_val:0',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/destinations');
        }

        $heroPath = 'destinations/hero-fjord.jpg';
        if (!empty($_FILES['hero_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['hero_image'], 'destinations', false);
            if (!$up['ok']) {
                flash('error', $up['error']);
                redirect('/admin/destinations');
            }
            $heroPath = $up['path'];
        }

        $db = Database::getInstance();
        $slug = slugify($clean['name']);
        if ($db->fetchColumn("SELECT id FROM destinations WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }

        $highlights = array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['highlights'] ?? '')))));
        $id = $db->insert('destinations', [
            'country_id' => (int) $clean['country_id'],
            'name' => $clean['name'],
            'slug' => $slug,
            'location_label' => $clean['location_label'],
            'tagline' => trim((string) ($_POST['tagline'] ?? '')) ?: null,
            'summary' => trim((string) ($_POST['summary'] ?? '')) ?: null,
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'highlights' => json_encode($highlights),
            'best_time_to_visit' => trim((string) ($_POST['best_time_to_visit'] ?? '')) ?: null,
            'climate' => trim((string) ($_POST['climate'] ?? '')) ?: null,
            'starting_price' => (float) ($clean['starting_price'] ?? 0),
            'hero_image' => $heroPath,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ]);

        AuditService::log('destinations', 'create', "Created destination {$clean['name']}", 'destination', $id, (int) Auth::id());
        flash('success', 'Destination published.');
        redirect('/admin/destinations');
    }

    public function editDestination(array $params): void
    {
        $db = Database::getInstance();
        $dest = $db->fetch("SELECT * FROM destinations WHERE id = :id AND deleted_at IS NULL", ['id' => (int) ($params['id'] ?? 0)]);
        if (!$dest) {
            redirect('/admin/destinations');
        }
        $dest['highlights_text'] = implode("\n", json_decode((string) ($dest['highlights'] ?? '[]'), true) ?: []);
        $countries = $db->fetchAll("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY name");

        View::render('admin/travel/destination-edit', [
            'pageTitle' => 'Edit Destination: ' . $dest['name'],
            'portal' => 'admin',
            'dest' => $dest,
            'countries' => $countries,
        ], 'dashboard');
    }

    public function updateDestination(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $dest = $db->fetch("SELECT * FROM destinations WHERE id = :id", ['id' => $id]);
        if (!$dest) {
            redirect('/admin/destinations');
        }

        $heroPath = $dest['hero_image'];
        if (!empty($_FILES['hero_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['hero_image'], 'destinations', false);
            if (!$up['ok']) {
                flash('error', $up['error']);
                redirect('/admin/destinations/' . $id);
            }
            $heroPath = $up['path'];
        }

        $highlights = array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['highlights'] ?? '')))));
        $db->update('destinations', [
            'country_id' => (int) $_POST['country_id'],
            'name' => trim((string) $_POST['name']),
            'location_label' => trim((string) $_POST['location_label']),
            'tagline' => trim((string) $_POST['tagline']) ?: null,
            'summary' => trim((string) $_POST['summary']) ?: null,
            'description' => trim((string) $_POST['description']) ?: null,
            'highlights' => json_encode($highlights),
            'best_time_to_visit' => trim((string) $_POST['best_time_to_visit']) ?: null,
            'climate' => trim((string) $_POST['climate']) ?: null,
            'starting_price' => (float) ($_POST['starting_price'] ?? 0),
            'hero_image' => $heroPath,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ], 'id = :id', ['id' => $id]);

        AuditService::log('destinations', 'update', "Updated destination #{$id}", 'destination', $id, (int) Auth::id());
        flash('success', 'Destination updated.');
        redirect('/admin/destinations/' . $id);
    }

    public function deleteDestination(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $db->update('destinations', ['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0], 'id = :id', ['id' => $id]);
        AuditService::log('destinations', 'delete', "Archived destination #{$id}", 'destination', $id, (int) Auth::id());
        flash('success', 'Destination archived.');
        redirect('/admin/destinations');
    }

    public function categories(): void
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM travel_packages p WHERE p.category_id = c.id AND p.deleted_at IS NULL) AS package_count
             FROM travel_categories c WHERE c.deleted_at IS NULL ORDER BY c.sort_order ASC, c.id ASC"
        );
        View::render('admin/travel/categories', [
            'pageTitle' => 'Tour Categories',
            'portal' => 'admin',
            'categories' => $categories,
        ], 'dashboard');
    }

    public function storeCategory(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['name' => 'required|min:2|max:100', 'icon' => 'max:60']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/categories');
        }
        $imgPath = 'destinations/hero-fjord.jpg';
        if (!empty($_FILES['image']['name'])) {
            $up = Uploader::uploadImage($_FILES['image'], 'destinations', false);
            if ($up['ok']) {
                $imgPath = $up['path'];
            }
        }
        $db = Database::getInstance();
        $slug = slugify($clean['name']);
        if ($db->fetchColumn("SELECT id FROM travel_categories WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }
        $db->insert('travel_categories', [
            'name' => $clean['name'],
            'slug' => $slug,
            'tagline' => trim((string) ($_POST['tagline'] ?? '')) ?: null,
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'icon' => $clean['icon'] ?: 'compass',
            'image' => $imgPath,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_active' => 1,
        ]);
        AuditService::log('categories', 'create', "Created category {$clean['name']}", 'travel_category', null, (int) Auth::id());
        flash('success', 'Travel category added.');
        redirect('/admin/categories');
    }

    public function deleteCategory(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $inUse = (int) $db->fetchColumn("SELECT COUNT(*) FROM travel_packages WHERE category_id = :id AND deleted_at IS NULL", ['id' => $id]);
        if ($inUse > 0) {
            flash('error', 'Reassign packages in this category before deleting it.');
            redirect('/admin/categories');
        }
        $db->update('travel_categories', ['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0], 'id = :id', ['id' => $id]);
        flash('success', 'Category removed.');
        redirect('/admin/categories');
    }

    public function packages(): void
    {
        $db = Database::getInstance();
        $q = trim((string) ($_GET['q'] ?? ''));
        $catId = trim((string) ($_GET['category_id'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $where = ['p.deleted_at IS NULL'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.title LIKE :q OR p.package_code LIKE :q OR d.name LIKE :q)';
            $params['q'] = "%{$q}%";
        }
        if ($catId !== '') {
            $where[] = 'p.category_id = :cid';
            $params['cid'] = (int) $catId;
        }
        $wSql = implode(' AND ', $where);

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM travel_packages p INNER JOIN destinations d ON d.id = p.destination_id WHERE {$wSql}", $params);
        $meta = paginate_meta($total, 12, $page);

        $packages = $db->fetchAll(
            "SELECT p.*, d.name AS destination_name, c.name AS category_name
             FROM travel_packages p
             INNER JOIN destinations d ON d.id = p.destination_id
             INNER JOIN travel_categories c ON c.id = p.category_id
             WHERE {$wSql} ORDER BY p.id DESC LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );

        $destinations = $db->fetchAll("SELECT id, name FROM destinations WHERE deleted_at IS NULL ORDER BY name");
        $categories = $db->fetchAll("SELECT id, name FROM travel_categories WHERE deleted_at IS NULL ORDER BY name");

        View::render('admin/travel/packages', [
            'pageTitle' => 'Travel Packages & Itineraries',
            'portal' => 'admin',
            'packages' => $packages,
            'destinations' => $destinations,
            'categories' => $categories,
            'meta' => $meta,
            'q' => $q,
            'catId' => $catId,
        ], 'dashboard');
    }

    public function storePackage(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'title' => 'required|min:3|max:180',
            'destination_id' => 'required|integer',
            'category_id' => 'required|integer',
            'duration_days' => 'required|integer|min_val:1',
            'duration_nights' => 'required|integer|min_val:0',
            'base_price' => 'required|numeric|min_val:1',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/packages');
        }

        $featuredImg = 'destinations/hero-fjord.jpg';
        if (!empty($_FILES['featured_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['featured_image'], 'packages', false);
            if ($up['ok']) {
                $featuredImg = $up['path'];
            }
        }

        $db = Database::getInstance();
        $slug = slugify($clean['title']);
        if ($db->fetchColumn("SELECT id FROM travel_packages WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }
        $code = 'PKG-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $pkgId = $db->insert('travel_packages', [
            'destination_id' => (int) $clean['destination_id'],
            'category_id' => (int) $clean['category_id'],
            'title' => $clean['title'],
            'slug' => $slug,
            'package_code' => $code,
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null,
            'overview' => trim((string) ($_POST['overview'] ?? '')) ?: null,
            'duration_days' => (int) $clean['duration_days'],
            'duration_nights' => (int) $clean['duration_nights'],
            'max_group_size' => (int) ($_POST['max_group_size'] ?? 10),
            'difficulty_level' => trim((string) ($_POST['difficulty_level'] ?? 'Moderate')),
            'base_price' => (float) $clean['base_price'],
            'discount_price' => !empty($_POST['discount_price']) ? (float) $_POST['discount_price'] : null,
            'featured_image' => $featuredImg,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_seasonal_offer' => !empty($_POST['is_seasonal_offer']) ? 1 : 0,
            'availability_status' => $_POST['availability_status'] ?? 'available',
            'available_slots' => (int) ($_POST['available_slots'] ?? 15),
            'is_active' => 1,
        ]);

        $db->insert('package_images', ['package_id' => $pkgId, 'image_path' => $featuredImg, 'is_primary' => 1, 'sort_order' => 1]);
        AuditService::log('packages', 'create', "Created package {$code} ({$clean['title']})", 'travel_package', $pkgId, (int) Auth::id());
        flash('success', 'Package created! Add itinerary days, gallery photos, inclusions & exclusions below.');
        redirect('/admin/packages/' . $pkgId);
    }

    public function editPackage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $pkg = $db->fetch("SELECT * FROM travel_packages WHERE id = :id AND deleted_at IS NULL", ['id' => $id]);
        if (!$pkg) {
            redirect('/admin/packages');
        }

        View::render('admin/travel/package-edit', [
            'pageTitle' => 'Manage Package: ' . $pkg['title'],
            'portal' => 'admin',
            'pkg' => $pkg,
            'destinations' => $db->fetchAll("SELECT id, name FROM destinations WHERE deleted_at IS NULL ORDER BY name"),
            'categories' => $db->fetchAll("SELECT id, name FROM travel_categories WHERE deleted_at IS NULL ORDER BY name"),
            'itineraries' => $db->fetchAll("SELECT * FROM package_itineraries WHERE package_id = :id ORDER BY day_number ASC", ['id' => $id]),
            'images' => $db->fetchAll("SELECT * FROM package_images WHERE package_id = :id ORDER BY is_primary DESC, sort_order ASC", ['id' => $id]),
            'inclusions' => $db->fetchAll("SELECT * FROM package_inclusions WHERE package_id = :id ORDER BY sort_order ASC", ['id' => $id]),
            'exclusions' => $db->fetchAll("SELECT * FROM package_exclusions WHERE package_id = :id ORDER BY sort_order ASC", ['id' => $id]),
        ], 'dashboard');
    }

    public function updatePackage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $pkg = $db->fetch("SELECT * FROM travel_packages WHERE id = :id", ['id' => $id]);
        if (!$pkg) {
            redirect('/admin/packages');
        }

        $imgPath = $pkg['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['featured_image'], 'packages', false);
            if ($up['ok']) {
                $imgPath = $up['path'];
            }
        }

        $db->update('travel_packages', [
            'destination_id' => (int) $_POST['destination_id'],
            'category_id' => (int) $_POST['category_id'],
            'title' => trim((string) $_POST['title']),
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null,
            'overview' => trim((string) ($_POST['overview'] ?? '')) ?: null,
            'duration_days' => (int) ($_POST['duration_days'] ?? 5),
            'duration_nights' => (int) ($_POST['duration_nights'] ?? 4),
            'max_group_size' => (int) ($_POST['max_group_size'] ?? 10),
            'difficulty_level' => trim((string) ($_POST['difficulty_level'] ?? 'Moderate')),
            'base_price' => (float) $_POST['base_price'],
            'discount_price' => !empty($_POST['discount_price']) ? (float) $_POST['discount_price'] : null,
            'featured_image' => $imgPath,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_seasonal_offer' => !empty($_POST['is_seasonal_offer']) ? 1 : 0,
            'offer_badge' => trim((string) ($_POST['offer_badge'] ?? '')) ?: null,
            'availability_status' => $_POST['availability_status'] ?? 'available',
            'available_slots' => (int) ($_POST['available_slots'] ?? 10),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ], 'id = :id', ['id' => $id]);

        AuditService::log('packages', 'update', "Updated package #{$id}", 'travel_package', $id, (int) Auth::id());
        flash('success', 'Package details saved.');
        redirect('/admin/packages/' . $id);
    }

    public function addItineraryDay(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $db->insert('package_itineraries', [
            'package_id' => $id,
            'day_number' => max(1, (int) ($_POST['day_number'] ?? 1)),
            'title' => trim((string) $_POST['title']),
            'location' => trim((string) ($_POST['location'] ?? '')) ?: null,
            'description' => trim((string) $_POST['description']),
            'meals' => trim((string) ($_POST['meals'] ?? '')) ?: null,
            'accommodation' => trim((string) ($_POST['accommodation'] ?? '')) ?: null,
        ]);
        flash('success', 'Itinerary day added.');
        redirect('/admin/packages/' . $id);
    }

    public function deleteItineraryDay(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('package_itineraries', 'id = :did AND package_id = :pid', [
            'did' => (int) $params['dayId'],
            'pid' => (int) $params['id'],
        ]);
        flash('success', 'Itinerary day removed.');
        redirect('/admin/packages/' . (int) $params['id']);
    }

    public function addPackageImage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $up = Uploader::uploadImage($_FILES['gallery_image'] ?? [], 'packages', false);
        if (!$up['ok']) {
            flash('error', $up['error']);
            redirect('/admin/packages/' . $id);
        }
        $db = Database::getInstance();
        $db->insert('package_images', [
            'package_id' => $id,
            'image_path' => $up['path'],
            'caption' => trim((string) ($_POST['caption'] ?? '')) ?: null,
            'is_primary' => 0,
        ]);
        flash('success', 'Gallery image uploaded.');
        redirect('/admin/packages/' . $id);
    }

    public function addPackageInclusionExclusion(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $type = ($_POST['list_type'] ?? 'inclusion') === 'exclusion' ? 'package_exclusions' : 'package_inclusions';
        $text = trim((string) ($_POST['item_text'] ?? ''));
        if ($text !== '') {
            Database::getInstance()->insert($type, ['package_id' => $id, 'item_text' => $text]);
            flash('success', 'Item added.');
        }
        redirect('/admin/packages/' . $id);
    }

    public function deletePackage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        Database::getInstance()->update('travel_packages', ['deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0], 'id = :id', ['id' => $id]);
        AuditService::log('packages', 'delete', "Archived package #{$id}", 'travel_package', $id, (int) Auth::id());
        flash('success', 'Package archived.');
        redirect('/admin/packages');
    }

    public function offers(): void
    {
        $db = Database::getInstance();
        $offers = $db->fetchAll(
            "SELECT o.*, p.title AS package_title FROM seasonal_offers o LEFT JOIN travel_packages p ON p.id = o.package_id ORDER BY o.id DESC"
        );
        $packages = $db->fetchAll("SELECT id, title FROM travel_packages WHERE deleted_at IS NULL ORDER BY title");
        View::render('admin/travel/offers', [
            'pageTitle' => 'Seasonal Offers & Promo Codes',
            'portal' => 'admin',
            'offers' => $offers,
            'packages' => $packages,
        ], 'dashboard');
    }

    public function storeOffer(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'title' => 'required|min:3|max:160',
            'code' => 'required|min:3|max:40',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min_val:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/offers');
        }
        $db = Database::getInstance();
        $db->insert('seasonal_offers', [
            'title' => $clean['title'],
            'code' => strtoupper($clean['code']),
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'discount_type' => $clean['discount_type'],
            'discount_value' => (float) $clean['discount_value'],
            'min_booking_amount' => (float) ($_POST['min_booking_amount'] ?? 0),
            'package_id' => !empty($_POST['package_id']) ? (int) $_POST['package_id'] : null,
            'banner_image' => 'destinations/hero-fjord.jpg',
            'start_date' => $clean['start_date'],
            'end_date' => $clean['end_date'],
            'is_active' => 1,
        ]);
        AuditService::log('offers', 'create', "Created offer {$clean['code']}", 'seasonal_offer', null, (int) Auth::id());
        flash('success', 'Seasonal offer created.');
        redirect('/admin/offers');
    }

    public function deleteOffer(array $params): void
    {
        Database::getInstance()->delete('seasonal_offers', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Offer deleted.');
        redirect('/admin/offers');
    }
}
