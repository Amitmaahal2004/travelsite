<section class="page-hero">
    <img src="<?= e(upload_url($destination['hero_image'])) ?>" alt="<?= e($destination['name']) ?>" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow"><?= e($destination['country_name']) ?> &bull; <?= e($destination['region']) ?></span>
        <h1><?= e($destination['name']) ?></h1>
        <p style="max-width:650px;opacity:0.88;"><?= e($destination['tagline']) ?></p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="split-editorial">
            <div>
                <div class="surface-card" style="margin-bottom:28px;">
                    <img src="<?= e(upload_url($destination['hero_image'])) ?>" alt="<?= e($destination['name']) ?>" style="width:100%;height:390px;object-fit:cover;border-radius:12px;margin-bottom:22px;">
                    <span class="section-eyebrow">Destination Dossier</span>
                    <h2 style="font-size:2.1rem;"><?= e($destination['location_label']) ?></h2>
                    <div style="color:var(--ink-muted);line-height:1.8;">
                        <?= $destination['description'] ?>
                    </div>
                </div>

                <?php if (!empty($destination['highlights'])): ?>
                    <div class="surface-card">
                        <span class="section-eyebrow">Signature Highlights</span>
                        <h3>What Makes This Journey Unforgettable</h3>
                        <ul class="form-row-2" style="list-style:none;padding:0;margin:16px 0 0;">
                            <?php foreach ($destination['highlights'] as $hl): ?>
                                <li style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--ivory);border-radius:10px;">
                                    <span style="color:var(--brand-accent);"><?= svg_icon('check', 18) ?></span>
                                    <span><?= e($hl) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <aside>
                <div class="surface-card" style="position:sticky;top:100px;">
                    <span class="section-eyebrow">At a Glance</span>
                    <h3 style="margin-bottom:16px;">Journey Essentials</h3>
                    <div style="display:flex;flex-direction:column;gap:14px;font-size:0.92rem;">
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-hairline);padding-bottom:10px;">
                            <span style="color:var(--ink-soft);">Country</span>
                            <strong><?= e($destination['country_name']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-hairline);padding-bottom:10px;">
                            <span style="color:var(--ink-soft);">Best Season</span>
                            <strong><?= e($destination['best_time_to_visit'] ?: 'Year-round') ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border-hairline);padding-bottom:10px;">
                            <span style="color:var(--ink-soft);">Climate</span>
                            <strong><?= e($destination['climate'] ?: 'Seasonal') ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--ink-soft);">Starting From</span>
                            <strong style="color:var(--brand-primary);font-size:1.15rem;"><?= e(money($destination['starting_price'])) ?></strong>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:24px;">
                        <a href="<?= url('/packages?destination=' . $destination['slug']) ?>" class="btn-brand btn-brand-primary">View Curated Packages</a>
                        <a href="<?= url('/custom-enquiry?destination=' . urlencode($destination['name'])) ?>" class="btn-brand btn-brand-outline">Tailor Private Itinerary</a>
                    </div>
                </div>
            </aside>
        </div>

        <?php if (!empty($packages)): ?>
            <div style="margin-top:56px;">
                <span class="section-eyebrow">Curated Departures</span>
                <h2 style="font-size:2.2rem;margin-bottom:24px;">Packages in <?= e($destination['name']) ?></h2>
                <div class="packages-grid">
                    <?php foreach ($packages as $pkg):
                        $effectivePrice = $pkg['discount_price'] ?: $pkg['base_price']; ?>
                        <article class="pkg-card">
                            <div class="pkg-media">
                                <a href="<?= url('/package/' . $pkg['slug']) ?>">
                                    <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>">
                                </a>
                                <span class="pkg-badge-top"><?= (int) $pkg['duration_days'] ?> Days / <?= (int) $pkg['duration_nights'] ?> Nights</span>
                            </div>
                            <div class="pkg-body">
                                <div class="pkg-meta-row"><span><?= e($pkg['category_name']) ?></span><span>&#9733; <?= e($pkg['rating']) ?></span></div>
                                <h3><a href="<?= url('/package/' . $pkg['slug']) ?>"><?= e($pkg['title']) ?></a></h3>
                                <p class="pkg-excerpt"><?= e($pkg['subtitle']) ?></p>
                                <div class="pkg-footer">
                                    <div class="pkg-price"><strong><?= e(money($effectivePrice)) ?></strong></div>
                                    <a href="<?= url('/package/' . $pkg['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Explore</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
