<section class="page-hero">
    <img src="<?= e(upload_url($category['image'])) ?>" alt="<?= e($category['name']) ?>" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Travel Category</span>
        <h1><?= e($category['name']) ?></h1>
        <p style="max-width:600px;opacity:0.86;"><?= e($category['tagline']) ?></p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <?php if (empty($packages)): ?>
            <div class="surface-card" style="text-align:center;padding:48px;">
                <h3>New departures arriving soon</h3>
                <p style="color:var(--ink-muted);">Ask our Journey Architects to craft a private <?= e($category['name']) ?> itinerary.</p>
                <a href="<?= url('/custom-enquiry?style=' . urlencode($category['name'])) ?>" class="btn-brand btn-brand-primary">Request Custom Journey</a>
            </div>
        <?php else: ?>
            <div class="packages-grid">
                <?php foreach ($packages as $pkg):
                    $effectivePrice = $pkg['discount_price'] ?: $pkg['base_price'];
                ?>
                    <article class="pkg-card">
                        <div class="pkg-media">
                            <a href="<?= url('/package/' . $pkg['slug']) ?>">
                                <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>">
                            </a>
                            <span class="pkg-badge-top"><?= (int) $pkg['duration_days'] ?>D / <?= (int) $pkg['duration_nights'] ?>N</span>
                        </div>
                        <div class="pkg-body">
                            <div class="pkg-meta-row"><span><?= e($pkg['destination_name']) ?></span><span>&#9733; <?= e($pkg['rating']) ?></span></div>
                            <h3><a href="<?= url('/package/' . $pkg['slug']) ?>"><?= e($pkg['title']) ?></a></h3>
                            <p class="pkg-excerpt"><?= e($pkg['subtitle']) ?></p>
                            <div class="pkg-footer">
                                <div class="pkg-price"><strong><?= e(money($effectivePrice)) ?></strong></div>
                                <a href="<?= url('/package/' . $pkg['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">View Package</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php $baseUrl = '/category/' . $category['slug']; require BASE_PATH . '/views/partials/pagination.php'; ?>
        <?php endif; ?>
    </div>
</section>
