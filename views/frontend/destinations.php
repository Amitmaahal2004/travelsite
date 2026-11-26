<section class="page-hero">
    <img src="<?= e(upload_url('destinations/swiss-alps.jpg')) ?>" alt="Destinations" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Global Portfolio</span>
        <h1>Extraordinary Destinations</h1>
        <p style="max-width:600px;opacity:0.84;">Curated sanctuaries spanning coastal archipelagos, ancient cultural capitals, alpine ridges, and private savannah concessions.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <form method="GET" action="<?= url('/destinations') ?>" class="surface-card" style="margin-bottom:32px;display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
            <div style="flex:1;min-width:220px;">
                <label class="form-label-editorial">Search Destinations</label>
                <input type="text" name="q" value="<?= e($filters['search'] ?? '') ?>" class="form-control-editorial" placeholder="Country, region, or destination name">
            </div>
            <div style="min-width:200px;">
                <label class="form-label-editorial">Region</label>
                <select name="region" class="form-control-editorial">
                    <option value="">All Regions</option>
                    <?php foreach ($regions as $reg): ?>
                        <option value="<?= e($reg['region']) ?>" <?= ($filters['region'] ?? '') === $reg['region'] ? 'selected' : '' ?>>
                            <?= e($reg['region']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary">Filter Destinations</button>
            <?php if (!empty($filters['search']) || !empty($filters['region'])): ?>
                <a href="<?= url('/destinations') ?>" class="btn-brand btn-brand-outline">Reset</a>
            <?php endif; ?>
        </form>

        <?php if (empty($destinations)): ?>
            <div class="surface-card" style="text-align:center;padding:48px;">
                <h3>No matching destinations found</h3>
                <p style="color:var(--ink-muted);">Try broadening your filter criteria or explore our custom itinerary service.</p>
                <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-primary">Request Custom Journey</a>
            </div>
        <?php else: ?>
            <div class="packages-grid">
                <?php foreach ($destinations as $d): ?>
                    <article class="pkg-card">
                        <div class="pkg-media">
                            <a href="<?= url('/destination/' . $d['slug']) ?>">
                                <img src="<?= e(upload_url($d['hero_image'])) ?>" alt="<?= e($d['name']) ?>">
                            </a>
                            <span class="pkg-badge-top"><?= e($d['region']) ?></span>
                        </div>
                        <div class="pkg-body">
                            <div class="pkg-meta-row">
                                <span><?= e($d['location_label']) ?></span>
                                <span><?= (int) $d['package_count'] ?> Packages</span>
                            </div>
                            <h3><a href="<?= url('/destination/' . $d['slug']) ?>"><?= e($d['name']) ?></a></h3>
                            <p class="pkg-excerpt"><?= e($d['summary']) ?></p>
                            <div class="pkg-footer">
                                <div class="pkg-price">
                                    <small>Starting from</small>
                                    <strong><?= e(money($d['starting_price'])) ?></strong>
                                </div>
                                <a href="<?= url('/destination/' . $d['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Explore</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php $baseUrl = '/destinations'; require BASE_PATH . '/views/partials/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>
