<section class="page-hero">
    <img src="<?= e(upload_url($page['hero_image'] ?? 'media/custom-atelier.jpg')) ?>" alt="About Us" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow"><?= e(brand('website_name')) ?></span>
        <h1><?= e($page['title'] ?? 'About Us') ?></h1>
        <p style="max-width:620px;opacity:0.86;"><?= e($page['subtitle'] ?? brand('website_tagline')) ?></p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="atelier-grid" style="margin-bottom:54px;">
            <div class="surface-card">
                <span class="section-eyebrow">Who We Are</span>
                <h2><?= e(brand('company_name')) ?></h2>
                <div style="color:var(--ink-muted);line-height:1.85;">
                    <?= $page['content'] ?? '<p>' . e(brand('company_description')) . '</p>' ?>
                </div>
            </div>
            <div class="atelier-image-wrap">
                <img src="<?= e(upload_url('media/custom-atelier.jpg')) ?>" alt="<?= e(brand('company_name')) ?>">
            </div>
        </div>

        <?php if (!empty($testimonials)): ?>
            <div class="surface-card band-navy" style="padding:40px;">
                <span class="section-eyebrow">Guest Trust</span>
                <h2 style="color:#FAF7F2;margin-bottom:24px;">Reflections From Our Travellers</h2>
                <div class="testimonials-grid">
                    <?php foreach (array_slice($testimonials, 0, 3) as $t): ?>
                        <div class="testimonial-card">
                            <p class="testimonial-quote">&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
                            <div class="testimonial-author">
                                <strong><?= e($t['customer_name']) ?></strong>
                                <span><?= e($t['customer_location']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
