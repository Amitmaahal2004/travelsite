<section class="page-hero">
    <img src="<?= e(upload_url('destinations/serengeti-safari.jpg')) ?>" alt="Tour Categories" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Travel Styles</span>
        <h1>Explore Tour Categories</h1>
        <p style="max-width:600px;opacity:0.84;">Every traveller seeks a distinct rhythm—adventure, romance, wildlife, heritage, or effortless family connection.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="packages-grid">
            <?php foreach ($categories as $cat): ?>
                <article class="pkg-card">
                    <div class="pkg-media">
                        <a href="<?= url('/category/' . $cat['slug']) ?>">
                            <img src="<?= e(upload_url($cat['image'])) ?>" alt="<?= e($cat['name']) ?>">
                        </a>
                        <span class="pkg-badge-top"><?= (int) $cat['package_count'] ?> Packages</span>
                    </div>
                    <div class="pkg-body">
                        <div class="pkg-meta-row"><span>Travel Style</span><span><?= svg_icon($cat['icon'] ?: 'compass', 15) ?></span></div>
                        <h3><a href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a></h3>
                        <p class="pkg-excerpt"><?= e($cat['description'] ?: $cat['tagline']) ?></p>
                        <a href="<?= url('/category/' . $cat['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Browse Journeys</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
