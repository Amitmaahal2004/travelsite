<section class="page-hero">
    <img src="<?= e(upload_url('destinations/amalfi-coast.jpg')) ?>" alt="Travel Packages" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Curated Voyages</span>
        <h1>Travel Packages</h1>
        <p style="max-width:600px;opacity:0.84;">Handcrafted multi-day blueprints complete with sanctuary accommodations, private transfers, and regional experts.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <form method="GET" action="<?= url('/packages') ?>" class="surface-card" style="margin-bottom:32px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;align-items:end;">
            <div>
                <label class="form-label-editorial">Keyword</label>
                <input type="text" name="q" value="<?= e($filters['search'] ?? '') ?>" class="form-control-editorial" placeholder="Search title or place...">
            </div>
            <div>
                <label class="form-label-editorial">Destination</label>
                <select name="destination" class="form-control-editorial">
                    <option value="">All Destinations</option>
                    <?php foreach ($destinations as $d): ?>
                        <option value="<?= e($d['slug']) ?>" <?= ($filters['destination'] ?? '') === $d['slug'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-editorial">Category</label>
                <select name="category" class="form-control-editorial">
                    <option value="">All Styles</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c['slug']) ?>" <?= ($filters['category'] ?? '') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-editorial">Sort By</label>
                <select name="sort" class="form-control-editorial">
                    <option value="featured" <?= ($filters['sort'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured</option>
                    <option value="price_asc" <?= ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="duration" <?= ($filters['sort'] ?? '') === 'duration' ? 'selected' : '' ?>>Shortest Duration</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-brand btn-brand-primary" style="flex:1;">Filter</button>
                <a href="<?= url('/packages') ?>" class="btn-brand btn-brand-outline">Clear</a>
            </div>
        </form>

        <?php if (empty($packages)): ?>
            <div class="surface-card" style="text-align:center;padding:48px;">
                <h3>No packages match your current filters</h3>
                <p style="color:var(--ink-muted);">Reset filters or let our specialists design a bespoke itinerary around your dates.</p>
                <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-primary">Request Custom Tour</a>
            </div>
        <?php else: ?>
            <div class="packages-grid">
                <?php foreach ($packages as $pkg):
                    $inWish = in_array((int) $pkg['id'], $wishlistIds ?? [], true);
                    $effectivePrice = $pkg['discount_price'] ?: $pkg['base_price'];
                ?>
                    <article class="pkg-card">
                        <div class="pkg-media">
                            <a href="<?= url('/package/' . $pkg['slug']) ?>">
                                <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>">
                            </a>
                            <span class="pkg-badge-top"><?= (int) $pkg['duration_days'] ?>D / <?= (int) $pkg['duration_nights'] ?>N</span>
                            <button type="button"
                                    class="pkg-wish-btn <?= $inWish ? 'active' : '' ?>"
                                    data-wishlist-btn data-item-type="package" data-item-id="<?= (int) $pkg['id'] ?>"
                                    data-endpoint="<?= url('/api/wishlist/toggle') ?>" aria-label="Save to wishlist">
                                <?= svg_icon('heart', 17) ?>
                            </button>
                        </div>
                        <div class="pkg-body">
                            <div class="pkg-meta-row">
                                <span><?= e($pkg['destination_name']) ?> &bull; <?= e($pkg['category_name']) ?></span>
                                <span>&#9733; <?= e(number_format((float) $pkg['rating'], 1)) ?></span>
                            </div>
                            <h3><a href="<?= url('/package/' . $pkg['slug']) ?>"><?= e($pkg['title']) ?></a></h3>
                            <p class="pkg-excerpt"><?= e($pkg['subtitle']) ?></p>
                            <div class="pkg-footer">
                                <div class="pkg-price">
                                    <small>Per guest from</small>
                                    <?php if (!empty($pkg['discount_price']) && (float) $pkg['discount_price'] < (float) $pkg['base_price']): ?>
                                        <del><?= e(money($pkg['base_price'])) ?></del>
                                    <?php endif; ?>
                                    <strong><?= e(money($effectivePrice)) ?></strong>
                                </div>
                                <div style="display:flex;gap:8px;">
                                    <a href="<?= url('/package/' . $pkg['slug']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Details</a>
                                    <a href="<?= url('/book/' . $pkg['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Book</a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php $baseUrl = '/packages'; require BASE_PATH . '/views/partials/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>
