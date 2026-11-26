<section class="page-hero">
    <img src="<?= e(upload_url('destinations/maldives-lagoon.jpg')) ?>" alt="Testimonials" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Guest Perspectives</span>
        <h1>Client Testimonials</h1>
        <p style="max-width:600px;opacity:0.86;">Stories and reflections from travellers across the world who journeyed with <?= e(brand('website_name')) ?>.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="packages-grid">
            <?php foreach ($testimonials as $t): ?>
                <div class="surface-card" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="color:var(--brand-accent);font-size:1.1rem;margin-bottom:10px;">
                            <?= str_repeat('&#9733;', max(1, min(5, (int) $t['rating']))) ?>
                        </div>
                        <p style="font-family:var(--font-display);font-size:1.35rem;line-height:1.45;color:var(--brand-primary);margin-bottom:20px;">
                            &ldquo;<?= e($t['quote']) ?>&rdquo;
                        </p>
                    </div>
                    <div style="border-top:1px solid var(--border-hairline);padding-top:14px;">
                        <strong style="display:block;"><?= e($t['customer_name']) ?></strong>
                        <small style="color:var(--ink-soft);"><?= e($t['customer_location']) ?> &bull; <?= e($t['trip_taken'] ?: $t['package_title']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
