<?php
$brand = \App\Services\BrandingService::getAll();
$user = \Core\Auth::user();
$portal = $portal ?? 'admin';
$isPreview = !empty($brand['is_previewing']);
$notifications = $user ? \App\Services\NotificationService::unreadForUser((int) $user['id'], 8) : [];
$unreadCount = $user ? \App\Services\NotificationService::unreadCount((int) $user['id']) : 0;

$portalLabel = match ($portal) {
    'hr' => 'HR Operations Portal',
    'employee' => 'Specialist Workspace',
    'customer' => 'Traveller Portal',
    default => 'Executive Control Center',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Dashboard') . ' | ' . $brand['website_name']) ?></title>
    <?= \Core\Csrf::meta() ?>
    <link rel="icon" href="<?= e(brand_logo('favicon')) ?>">
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <style><?= \App\Services\BrandingService::cssVariables() ?></style>
</head>
<body class="portal-<?= e($portal) ?> <?= $isPreview ? 'has-preview-ribbon' : '' ?>">

<?php if ($isPreview): ?>
    <div class="preview-ribbon">
        <div><strong>Branding Preview Active</strong> — Only Super Admin sees these draft styles.</div>
        <div style="display:flex;gap:10px;">
            <form method="POST" action="<?= url('/admin/branding/discard-preview') ?>" style="margin:0;">
                <?= \Core\Csrf::field() ?>
                <button type="submit" class="btn-brand btn-brand-outline-light btn-sm-pill">Exit Preview</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="sidebar-backdrop"></div>

<div class="dash-shell">
    <aside class="dash-sidebar">
        <a href="<?= url('/' . ($portal === 'admin' ? 'admin' : $portal)) ?>" class="sidebar-brand">
            <img src="<?= e(brand_logo('light')) ?>" alt="<?= e($brand['website_name']) ?>">
            <div>
                <div style="font-family:var(--font-display);font-size:1.18rem;font-weight:700;color:#FAF7F2;line-height:1.1;">
                    <?= e($brand['website_name']) ?>
                </div>
                <span class="sidebar-portal-tag"><?= e($portalLabel) ?></span>
            </div>
        </a>

        <div class="sidebar-scroll">
            <?php if ($portal === 'admin'): ?>
                <div class="sidebar-group-label">Overview</div>
                <a href="<?= url('/admin') ?>" class="sidebar-link <?= current_route_path() === '/admin' ? 'active' : '' ?>"><?= svg_icon('layout', 17) ?> Dashboard</a>

                <?php if (can('bookings.view')): ?>
                    <div class="sidebar-group-label">Operations</div>
                    <a href="<?= url('/admin/bookings') ?>" class="sidebar-link <?= is_active_nav('/admin/bookings') ?>"><?= svg_icon('briefcase', 17) ?> Bookings</a>
                <?php endif; ?>
                <?php if (can('customers.view')): ?>
                    <a href="<?= url('/admin/customers') ?>" class="sidebar-link <?= is_active_nav('/admin/customers') ?>"><?= svg_icon('users', 17) ?> Customers</a>
                <?php endif; ?>
                <?php if (can('enquiries.view')): ?>
                    <a href="<?= url('/admin/enquiries') ?>" class="sidebar-link <?= is_active_nav('/admin/enquiries') ?>"><?= svg_icon('compass', 17) ?> Enquiries</a>
                <?php endif; ?>
                <?php if (can('support.manage')): ?>
                    <a href="<?= url('/admin/support') ?>" class="sidebar-link <?= is_active_nav('/admin/support') ?>"><?= svg_icon('bell', 17) ?> Support Tickets</a>
                <?php endif; ?>

                <?php if (can('packages.view') || can('destinations.view') || can('categories.view')): ?>
                    <div class="sidebar-group-label">Travel Catalog</div>
                    <?php if (can('destinations.view')): ?>
                        <a href="<?= url('/admin/countries') ?>" class="sidebar-link <?= is_active_nav('/admin/countries') ?>"><?= svg_icon('globe', 17) ?> Countries</a>
                        <a href="<?= url('/admin/destinations') ?>" class="sidebar-link <?= is_active_nav('/admin/destinations') ?>"><?= svg_icon('map-pin', 17) ?> Destinations</a>
                    <?php endif; ?>
                    <?php if (can('categories.view')): ?>
                        <a href="<?= url('/admin/categories') ?>" class="sidebar-link <?= is_active_nav('/admin/categories') ?>"><?= svg_icon('landmark', 17) ?> Categories</a>
                    <?php endif; ?>
                    <?php if (can('packages.view')): ?>
                        <a href="<?= url('/admin/packages') ?>" class="sidebar-link <?= is_active_nav('/admin/packages') ?>"><?= svg_icon('mountain', 17) ?> Travel Packages</a>
                    <?php endif; ?>
                    <?php if (can('offers.manage')): ?>
                        <a href="<?= url('/admin/offers') ?>" class="sidebar-link <?= is_active_nav('/admin/offers') ?>"><?= svg_icon('gem', 17) ?> Seasonal Offers</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (can('finance.view')): ?>
                    <div class="sidebar-group-label">Finance</div>
                    <a href="<?= url('/admin/finance') ?>" class="sidebar-link <?= is_active_nav('/admin/finance') ?>"><?= svg_icon('credit-card', 17) ?> Finance &amp; P&amp;L</a>
                <?php endif; ?>

                <?php if (can('employees.view')): ?>
                    <div class="sidebar-group-label">Workforce</div>
                    <a href="<?= url('/admin/employees') ?>" class="sidebar-link <?= is_active_nav('/admin/employees') ?>"><?= svg_icon('users', 17) ?> Employees</a>
                    <a href="<?= url('/admin/departments') ?>" class="sidebar-link <?= is_active_nav('/admin/departments') ?>"><?= svg_icon('briefcase', 17) ?> Departments</a>
                    <a href="<?= url('/admin/attendance') ?>" class="sidebar-link <?= is_active_nav('/admin/attendance') ?>"><?= svg_icon('calendar', 17) ?> Attendance</a>
                    <a href="<?= url('/admin/leaves') ?>" class="sidebar-link <?= is_active_nav('/admin/leaves') ?>"><?= svg_icon('clock', 17) ?> Leave Requests</a>
                <?php endif; ?>

                <?php if (can('branding.view') || can('website.view') || can('seo.manage')): ?>
                    <div class="sidebar-group-label">Website &amp; Brand</div>
                    <?php if (can('branding.view')): ?>
                        <a href="<?= url('/admin/branding') ?>" class="sidebar-link <?= is_active_nav('/admin/branding') ?>"><?= svg_icon('sun', 17) ?> Website Branding</a>
                    <?php endif; ?>
                    <?php if (can('website.view')): ?>
                        <a href="<?= url('/admin/homepage-sections') ?>" class="sidebar-link <?= is_active_nav('/admin/homepage-sections') ?>"><?= svg_icon('layout', 17) ?> Homepage Sections</a>
                        <a href="<?= url('/admin/pages') ?>" class="sidebar-link <?= is_active_nav('/admin/pages') ?>"><?= svg_icon('file-text', 17) ?> Pages &amp; Legal</a>
                        <a href="<?= url('/admin/menus') ?>" class="sidebar-link <?= is_active_nav('/admin/menus') ?>"><?= svg_icon('menu', 17) ?> Navigation Menus</a>
                        <a href="<?= url('/admin/blogs') ?>" class="sidebar-link <?= is_active_nav('/admin/blogs') ?>"><?= svg_icon('file-text', 17) ?> Blog Stories</a>
                        <a href="<?= url('/admin/testimonials') ?>" class="sidebar-link <?= is_active_nav('/admin/testimonials') ?>"><?= svg_icon('star', 17) ?> Testimonials</a>
                        <a href="<?= url('/admin/faqs') ?>" class="sidebar-link <?= is_active_nav('/admin/faqs') ?>"><?= svg_icon('compass', 17) ?> FAQs</a>
                        <a href="<?= url('/admin/media') ?>" class="sidebar-link <?= is_active_nav('/admin/media') ?>"><?= svg_icon('gem', 17) ?> Media Library</a>
                        <a href="<?= url('/admin/email-templates') ?>" class="sidebar-link <?= is_active_nav('/admin/email-templates') ?>"><?= svg_icon('file-text', 17) ?> Email Templates</a>
                    <?php endif; ?>
                    <?php if (can('seo.manage')): ?>
                        <a href="<?= url('/admin/seo') ?>" class="sidebar-link <?= is_active_nav('/admin/seo') ?>"><?= svg_icon('search', 17) ?> SEO Settings</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (can('roles.manage') || can('activity_logs.view')): ?>
                    <div class="sidebar-group-label">Governance</div>
                    <?php if (can('roles.manage')): ?>
                        <a href="<?= url('/admin/roles') ?>" class="sidebar-link <?= is_active_nav('/admin/roles') ?>"><?= svg_icon('shield', 17) ?> Roles &amp; Permissions</a>
                    <?php endif; ?>
                    <?php if (can('activity_logs.view')): ?>
                        <a href="<?= url('/admin/activity-logs') ?>" class="sidebar-link <?= is_active_nav('/admin/activity-logs') ?>"><?= svg_icon('clock', 17) ?> Activity Logs</a>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($portal === 'hr'): ?>
                <div class="sidebar-group-label">HR Workspace</div>
                <a href="<?= url('/hr') ?>" class="sidebar-link <?= current_route_path() === '/hr' ? 'active' : '' ?>"><?= svg_icon('layout', 17) ?> HR Overview</a>
                <?php if (can('employees.view')): ?>
                    <a href="<?= url('/hr/employees') ?>" class="sidebar-link <?= is_active_nav('/hr/employees') ?>"><?= svg_icon('users', 17) ?> Employee Directory</a>
                <?php endif; ?>
                <?php if (can('departments.manage')): ?>
                    <a href="<?= url('/hr/departments') ?>" class="sidebar-link <?= is_active_nav('/hr/departments') ?>"><?= svg_icon('briefcase', 17) ?> Departments</a>
                <?php endif; ?>
                <?php if (can('attendance.view')): ?>
                    <a href="<?= url('/hr/attendance') ?>" class="sidebar-link <?= is_active_nav('/hr/attendance') ?>"><?= svg_icon('calendar', 17) ?> Attendance</a>
                <?php endif; ?>
                <?php if (can('leaves.view')): ?>
                    <a href="<?= url('/hr/leaves') ?>" class="sidebar-link <?= is_active_nav('/hr/leaves') ?>"><?= svg_icon('clock', 17) ?> Leave Requests</a>
                <?php endif; ?>
                <?php if (can('documents.manage')): ?>
                    <a href="<?= url('/hr/documents') ?>" class="sidebar-link <?= is_active_nav('/hr/documents') ?>"><?= svg_icon('file-text', 17) ?> Employee Documents</a>
                <?php endif; ?>
                <?php if (can('assignments.manage')): ?>
                    <a href="<?= url('/hr/assignments') ?>" class="sidebar-link <?= is_active_nav('/hr/assignments') ?>"><?= svg_icon('compass', 17) ?> Work Assignments</a>
                    <a href="<?= url('/hr/reports') ?>" class="sidebar-link <?= is_active_nav('/hr/reports') ?>"><?= svg_icon('star', 17) ?> Activity &amp; Performance</a>
                <?php endif; ?>
                <div class="sidebar-group-label">Personal</div>
                <a href="<?= url('/hr/notifications') ?>" class="sidebar-link <?= is_active_nav('/hr/notifications') ?>"><?= svg_icon('bell', 17) ?> Notifications</a>

            <?php elseif ($portal === 'employee'): ?>
                <div class="sidebar-group-label">Specialist Desk</div>
                <a href="<?= url('/employee') ?>" class="sidebar-link <?= current_route_path() === '/employee' ? 'active' : '' ?>"><?= svg_icon('layout', 17) ?> Workspace</a>
                <a href="<?= url('/employee/bookings') ?>" class="sidebar-link <?= is_active_nav('/employee/bookings') ?>"><?= svg_icon('briefcase', 17) ?> Assigned Bookings</a>
                <a href="<?= url('/employee/assignments') ?>" class="sidebar-link <?= is_active_nav('/employee/assignments') ?>"><?= svg_icon('compass', 17) ?> Tasks &amp; Enquiries</a>
                <div class="sidebar-group-label">Self Service</div>
                <a href="<?= url('/employee/attendance') ?>" class="sidebar-link <?= is_active_nav('/employee/attendance') ?>"><?= svg_icon('calendar', 17) ?> My Attendance</a>
                <a href="<?= url('/employee/leaves') ?>" class="sidebar-link <?= is_active_nav('/employee/leaves') ?>"><?= svg_icon('clock', 17) ?> My Leaves</a>
                <a href="<?= url('/employee/notifications') ?>" class="sidebar-link <?= is_active_nav('/employee/notifications') ?>"><?= svg_icon('bell', 17) ?> Notifications</a>
                <a href="<?= url('/employee/profile') ?>" class="sidebar-link <?= is_active_nav('/employee/profile') ?>"><?= svg_icon('settings', 17) ?> Profile</a>

            <?php else: ?>
                <div class="sidebar-group-label">My Travels</div>
                <a href="<?= url('/customer') ?>" class="sidebar-link <?= current_route_path() === '/customer' ? 'active' : '' ?>"><?= svg_icon('layout', 17) ?> Overview</a>
                <a href="<?= url('/customer/bookings') ?>" class="sidebar-link <?= is_active_nav('/customer/bookings') ?>"><?= svg_icon('briefcase', 17) ?> My Bookings</a>
                <a href="<?= url('/customer/payments') ?>" class="sidebar-link <?= is_active_nav('/customer/payments') ?>"><?= svg_icon('credit-card', 17) ?> Payments &amp; Invoices</a>
                <a href="<?= url('/customer/wishlist') ?>" class="sidebar-link <?= is_active_nav('/customer/wishlist') ?>"><?= svg_icon('heart', 17) ?> Wishlist &amp; Saved</a>
                <div class="sidebar-group-label">Concierge</div>
                <a href="<?= url('/customer/enquiries') ?>" class="sidebar-link <?= is_active_nav('/customer/enquiries') ?>"><?= svg_icon('compass', 17) ?> Travel Enquiries</a>
                <a href="<?= url('/customer/support') ?>" class="sidebar-link <?= is_active_nav('/customer/support') ?>"><?= svg_icon('file-text', 17) ?> Support Requests</a>
                <a href="<?= url('/customer/notifications') ?>" class="sidebar-link <?= is_active_nav('/customer/notifications') ?>"><?= svg_icon('bell', 17) ?> Notifications</a>
                <div class="sidebar-group-label">Account</div>
                <a href="<?= url('/customer/profile') ?>" class="sidebar-link <?= is_active_nav('/customer/profile') ?>"><?= svg_icon('settings', 17) ?> Profile &amp; Security</a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <a href="<?= url('/') ?>" style="display:flex;align-items:center;gap:8px;opacity:0.85;margin-bottom:8px;">
                <?= svg_icon('globe', 15) ?> View Public Website
            </a>
            <form method="POST" action="<?= url('/logout') ?>" style="margin:0;">
                <?= \Core\Csrf::field() ?>
                <button type="submit" style="background:none;border:none;color:inherit;padding:0;cursor:pointer;display:flex;align-items:center;gap:8px;font-family:inherit;font-size:inherit;opacity:0.85;">
                    <?= svg_icon('log-out', 15) ?> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <div class="dash-main">
        <header class="dash-topbar">
            <div class="topbar-left">
                <button type="button" class="icon-btn sidebar-toggle-btn" data-sidebar-toggle aria-label="Menu">
                    <?= svg_icon('menu', 18) ?>
                </button>
                <h1 class="topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></h1>
            </div>

            <div class="topbar-actions">
                <button type="button" class="icon-btn" data-theme-toggle title="Toggle light/dark mode" aria-label="Toggle theme">
                    <?= svg_icon('moon', 17) ?>
                </button>

                <div class="dropdown-wrap">
                    <button type="button" class="icon-btn" data-dropdown-btn="notif-popover" aria-label="Notifications">
                        <?= svg_icon('bell', 17) ?>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-dot"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notif-popover" class="dropdown-menu-panel">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <strong style="font-size:0.9rem;">Notifications</strong>
                            <span style="font-size:0.75rem;color:var(--dash-muted);"><?= $unreadCount ?> unread</span>
                        </div>
                        <?php if (empty($notifications)): ?>
                            <p style="font-size:0.84rem;color:var(--dash-muted);margin:8px 0;">You are all caught up.</p>
                        <?php else: ?>
                            <div style="display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;">
                                <?php foreach ($notifications as $n): ?>
                                    <a href="<?= e($n['link'] ? url($n['link']) : '#') ?>"
                                       data-notif-read="<?= url('/api/notifications/' . (int) $n['id'] . '/read') ?>"
                                       style="display:block;padding:9px;border-radius:8px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);opacity:<?= $n['is_read'] ? '0.65' : '1' ?>;">
                                        <strong style="display:block;font-size:0.82rem;"><?= e($n['title']) ?></strong>
                                        <span style="font-size:0.76rem;color:var(--dash-muted);"><?= e($n['message']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dropdown-wrap">
                    <button type="button" class="icon-btn" style="width:auto;padding:0 12px;gap:8px;" data-dropdown-btn="profile-popover">
                        <span style="width:26px;height:26px;border-radius:50%;background:var(--brand-accent);color:#111;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:0.76rem;">
                            <?= e(strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1))) ?>
                        </span>
                        <span style="font-size:0.84rem;font-weight:600;"><?= e($user['first_name'] ?? 'Account') ?></span>
                        <?= svg_icon('chevron-down', 14) ?>
                    </button>
                    <div id="profile-popover" class="dropdown-menu-panel" style="width:230px;">
                        <div style="padding-bottom:10px;border-bottom:1px solid var(--dash-border);margin-bottom:8px;">
                            <strong style="display:block;font-size:0.9rem;"><?= e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></strong>
                            <small style="color:var(--dash-muted);"><?= e($user['role_name'] ?? '') ?></small>
                        </div>
                        <a href="<?= url('/') ?>" style="display:block;padding:7px 8px;border-radius:6px;font-size:0.85rem;">Public Website</a>
                        <?php if ($portal === 'customer'): ?>
                            <a href="<?= url('/customer/profile') ?>" style="display:block;padding:7px 8px;border-radius:6px;font-size:0.85rem;">Profile &amp; Password</a>
                        <?php elseif ($portal === 'employee'): ?>
                            <a href="<?= url('/employee/profile') ?>" style="display:block;padding:7px 8px;border-radius:6px;font-size:0.85rem;">My Profile</a>
                        <?php endif; ?>
                        <form method="POST" action="<?= url('/logout') ?>" style="margin-top:6px;">
                            <?= \Core\Csrf::field() ?>
                            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill" style="width:100%;">Sign Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="dash-content">
            <?php require BASE_PATH . '/views/partials/flash.php'; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<script src="<?= asset('js/dashboard.js') ?>" defer></script>
</body>
</html>
