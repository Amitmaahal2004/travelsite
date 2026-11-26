<?php
$effectivePrice = $pkg['discount_price'] ?: $pkg['base_price'];
$inWish = in_array((int) $pkg['id'], $wishlistIds ?? [], true);
?>
<section class="page-hero">
    <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow"><?= e($pkg['destination_name']) ?> &bull; <?= e($pkg['category_name']) ?> &bull; <?= e($pkg['package_code']) ?></span>
        <h1><?= e($pkg['title']) ?></h1>
        <p style="max-width:650px;opacity:0.88;"><?= e($pkg['subtitle']) ?></p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="split-editorial">
            <div>
                <div class="surface-card" style="margin-bottom:26px;">
                    <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>" style="width:100%;height:400px;object-fit:cover;border-radius:12px;margin-bottom:20px;">
                    <?php if (count($pkg['images']) > 1): ?>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:20px;">
                            <?php foreach ($pkg['images'] as $img): ?>
                                <img src="<?= e(upload_url($img['image_path'])) ?>" alt="<?= e($img['caption'] ?: $pkg['title']) ?>" style="height:86px;width:100%;object-fit:cover;border-radius:8px;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <span class="section-eyebrow">Journey Overview</span>
                    <h2>Curated Experience</h2>
                    <div style="color:var(--ink-muted);line-height:1.78;">
                        <?= $pkg['overview'] ?>
                    </div>
                </div>

                <?php if (!empty($pkg['itineraries'])): ?>
                    <div class="surface-card" style="margin-bottom:26px;">
                        <span class="section-eyebrow">Day-by-Day Plan</span>
                        <h2 style="margin-bottom:20px;">Itinerary Architecture</h2>
                        <div style="display:flex;flex-direction:column;gap:16px;">
                            <?php foreach ($pkg['itineraries'] as $day): ?>
                                <div style="padding:18px;border-radius:12px;background:var(--ivory);border:1px solid var(--border-hairline);">
                                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;margin-bottom:6px;">
                                        <span class="badge-pill badge-info">Day <?= (int) $day['day_number'] ?></span>
                                        <?php if (!empty($day['location'])): ?>
                                            <span style="font-size:0.82rem;color:var(--ink-soft);"><?= e($day['location']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 style="font-size:1.3rem;color:var(--brand-primary);"><?= e($day['title']) ?></h4>
                                    <p style="margin:0 0 8px;color:var(--ink-muted);font-size:0.93rem;"><?= e($day['description']) ?></p>
                                    <div style="font-size:0.8rem;color:var(--ink-soft);display:flex;gap:18px;flex-wrap:wrap;">
                                        <?php if (!empty($day['meals'])): ?><span><strong>Meals:</strong> <?= e($day['meals']) ?></span><?php endif; ?>
                                        <?php if (!empty($day['accommodation'])): ?><span><strong>Stay:</strong> <?= e($day['accommodation']) ?></span><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-row-2">
                    <div class="surface-card">
                        <span class="section-eyebrow">Included</span>
                        <h3>Package Inclusions</h3>
                        <ul style="padding-left:18px;margin:12px 0 0;color:var(--ink-muted);">
                            <?php foreach ($pkg['inclusions'] as $inc): ?>
                                <li style="margin-bottom:8px;"><?= e($inc['item_text']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="surface-card">
                        <span class="section-eyebrow">Not Included</span>
                        <h3>Exclusions</h3>
                        <ul style="padding-left:18px;margin:12px 0 0;color:var(--ink-muted);">
                            <?php foreach ($pkg['exclusions'] as $exc): ?>
                                <li style="margin-bottom:8px;"><?= e($exc['item_text']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <aside>
                <div class="surface-card" style="position:sticky;top:100px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span class="badge-pill <?= status_badge_class($pkg['availability_status']) ?>"><?= e(ucfirst($pkg['availability_status'])) ?></span>
                        <button type="button" class="pkg-wish-btn <?= $inWish ? 'active' : '' ?>" style="position:static;border:1px solid var(--border-hairline);"
                                data-wishlist-btn data-item-type="package" data-item-id="<?= (int) $pkg['id'] ?>" data-endpoint="<?= url('/api/wishlist/toggle') ?>">
                            <?= svg_icon('heart', 17) ?>
                        </button>
                    </div>

                    <?php if (!empty($pkg['discount_price']) && (float) $pkg['discount_price'] < (float) $pkg['base_price']): ?>
                        <del style="color:var(--ink-soft);font-size:1rem;"><?= e(money($pkg['base_price'])) ?></del>
                    <?php endif; ?>
                    <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--brand-primary);line-height:1.1;">
                        <?= e(money($effectivePrice)) ?>
                    </div>
                    <div style="font-size:0.84rem;color:var(--ink-soft);margin-bottom:18px;">per adult traveller (taxes calculated at booking)</div>

                    <div style="display:flex;flex-direction:column;gap:10px;font-size:0.9rem;border-top:1px solid var(--border-hairline);padding-top:14px;margin-bottom:20px;">
                        <div style="display:flex;justify-content:space-between;"><span>Duration</span><strong><?= (int) $pkg['duration_days'] ?> Days / <?= (int) $pkg['duration_nights'] ?> Nights</strong></div>
                        <div style="display:flex;justify-content:space-between;"><span>Max Group Size</span><strong><?= (int) $pkg['max_group_size'] ?> Guests</strong></div>
                        <div style="display:flex;justify-content:space-between;"><span>Pace</span><strong><?= e($pkg['difficulty_level']) ?></strong></div>
                        <div style="display:flex;justify-content:space-between;"><span>Remaining Slots</span><strong><?= (int) $pkg['available_slots'] ?></strong></div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <a href="<?= url('/book/' . $pkg['slug']) ?>" class="btn-brand btn-brand-primary">Reserve This Journey</a>
                        <a href="<?= url('/custom-enquiry?destination=' . urlencode($pkg['destination_name'])) ?>" class="btn-brand btn-brand-outline">Customize This Itinerary</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
