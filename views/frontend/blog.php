<section class="page-hero">
    <img src="<?= e(upload_url('destinations/patagonia-peaks.jpg')) ?>" alt="Travel Journal" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Editorial Dispatch</span>
        <h1>Travel Stories &amp; Field Notes</h1>
        <p style="max-width:600px;opacity:0.86;">Slow travel essays, sanctuary reviews, and insider perspectives written from the field.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="packages-grid">
            <?php foreach ($posts as $post): ?>
                <article class="pkg-card">
                    <div class="pkg-media">
                        <a href="<?= url('/blog/' . $post['slug']) ?>">
                            <img src="<?= e(upload_url($post['featured_image'] ?: 'destinations/hero-fjord.jpg')) ?>" alt="<?= e($post['title']) ?>">
                        </a>
                        <span class="pkg-badge-top"><?= e($post['category']) ?></span>
                    </div>
                    <div class="pkg-body">
                        <div class="pkg-meta-row">
                            <span><?= e($post['read_time']) ?></span>
                            <span><?= e(substr((string) ($post['published_at'] ?? $post['created_at']), 0, 10)) ?></span>
                        </div>
                        <h3><a href="<?= url('/blog/' . $post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                        <p class="pkg-excerpt"><?= e($post['excerpt']) ?></p>
                        <a href="<?= url('/blog/' . $post['slug']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Read Article &rarr;</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php $baseUrl = '/blog'; require BASE_PATH . '/views/partials/pagination.php'; ?>
    </div>
</section>
