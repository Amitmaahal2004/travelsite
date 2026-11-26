<?php
$cmsModel = new \App\Models\Cms();
$destModel = new \App\Models\Destination();
$catModel = new \App\Models\Category();
$headerMenus = $cmsModel->menus('header');
$megaDestinations = $destModel->featured(4);
$megaCategories = array_slice($catModel->allPublic(), 0, 7);
$currentUser = \Core\Auth::user();
$isSolid = $isSolidHeader ?? false;
?>
<header class="site-header <?= $isSolid ? 'header-solid' : '' ?>">
    <div class="container-editorial header-inner">
        <a href="<?= url('/') ?>" class="brand-lockup" aria-label="<?= e(brand('website_name')) ?>">
            <img src="<?= e($isSolid ? brand_logo('primary') : brand_logo('light')) ?>" alt="<?= e(brand('website_name')) ?>">
            <div class="brand-wordmark">
                <strong><?= e(brand('website_name')) ?></strong>
                <span><?= e(brand('company_name')) ?></span>
            </div>
        </a>

        <nav class="nav-primary" aria-label="Main Navigation">
            <?php foreach ($headerMenus as $menu):
                $hasMega = in_array($menu['url'], ['/destinations', '/categories'], true);
            ?>
                <div class="nav-item">
                    <a href="<?= url($menu['url']) ?>" class="nav-link <?= is_active_nav($menu['url']) ?>">
                        <?= e($menu['label']) ?>
                        <?php if ($hasMega): ?>
                            <?= svg_icon('chevron-down', 14) ?>
                        <?php endif; ?>
                    </a>

                    <?php if ($hasMega): ?>
                        <div class="mega-menu">
                            <div>
                                <div class="mega-title">Featured Destinations</div>
                                <div class="mega-dest-grid">
                                    <?php foreach ($megaDestinations as $md): ?>
                                        <a href="<?= url('/destination/' . $md['slug']) ?>" class="mega-dest-card">
                                            <img src="<?= e(upload_url($md['hero_image'])) ?>" alt="<?= e($md['name']) ?>">
                                            <div>
                                                <strong><?= e($md['name']) ?></strong>
                                                <small><?= e($md['country_name']) ?> &bull; From <?= e(money($md['starting_price'])) ?></small>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <div class="mega-title">Travel Styles</div>
                                <div class="mega-cat-list">
                                    <?php foreach ($megaCategories as $mc): ?>
                                        <a href="<?= url('/category/' . $mc['slug']) ?>">
                                            <span><?= e($mc['name']) ?></span>
                                            <span>&rarr;</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="header-actions">
            <?php if ($currentUser): ?>
                <a href="<?= e(\Core\Auth::dashboardUrl($currentUser)) ?>" class="btn-brand btn-brand-accent btn-sm-pill">
                    My Portal
                </a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="nav-link" style="font-size:0.86rem;">Sign In</a>
                <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-accent btn-sm-pill">Plan Journey</a>
            <?php endif; ?>
            <button type="button" class="mobile-menu-btn" data-mobile-open aria-label="Open menu">
                <?= svg_icon('menu', 20) ?>
            </button>
        </div>
    </div>
</header>

<div class="mobile-drawer" data-mobile-drawer>
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div class="brand-lockup">
                <img src="<?= e(brand_logo('light')) ?>" alt="<?= e(brand('website_name')) ?>">
                <div class="brand-wordmark"><strong><?= e(brand('website_name')) ?></strong></div>
            </div>
            <button type="button" class="mobile-menu-btn" style="display:inline-flex;" data-mobile-close aria-label="Close menu">
                <?= svg_icon('x', 20) ?>
            </button>
        </div>

        <nav class="mobile-nav-list">
            <?php foreach ($headerMenus as $menu): ?>
                <a href="<?= url($menu['url']) ?>"><?= e($menu['label']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;">
        <?php if ($currentUser): ?>
            <a href="<?= e(\Core\Auth::dashboardUrl($currentUser)) ?>" class="btn-brand btn-brand-accent">Open My Dashboard</a>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="btn-brand btn-brand-outline-light">Client &amp; Staff Login</a>
            <a href="<?= url('/register') ?>" class="btn-brand btn-brand-accent">Create Account</a>
        <?php endif; ?>
    </div>
</div>
