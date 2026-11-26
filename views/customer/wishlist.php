<div class="panel-card">
    <div class="panel-head">
        <h2>Saved Travel Packages</h2>
        <a href="<?= url('/packages') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Browse Packages</a>
    </div>
    <?php if (empty($packages)): ?>
        <p style="color:var(--dash-muted);">No saved packages yet. Tap the heart icon on any journey to save it here.</p>
    <?php else: ?>
        <div class="form-grid-3">
            <?php foreach ($packages as $p): ?>
                <div style="border:1px solid var(--dash-border);border-radius:12px;overflow:hidden;background:var(--dash-surface-alt);">
                    <img src="<?= e(upload_url($p['featured_image'])) ?>" alt="<?= e($p['title']) ?>" style="height:160px;width:100%;object-fit:cover;">
                    <div style="padding:14px;">
                        <small style="color:var(--dash-muted);"><?= e($p['destination_name']) ?></small>
                        <h4 style="margin:4px 0 8px;"><?= e($p['title']) ?></h4>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <a href="<?= url('/package/' . $p['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">View</a>
                            <form method="POST" action="<?= url('/customer/wishlist/' . (int) $p['wish_id'] . '/remove') ?>" style="margin:0;">
                                <?= \Core\Csrf::field() ?>
                                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="panel-card">
    <div class="panel-head">
        <h2>Saved Destinations</h2>
        <a href="<?= url('/destinations') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Explore Destinations</a>
    </div>
    <?php if (empty($destinations)): ?>
        <p style="color:var(--dash-muted);">No saved destinations yet.</p>
    <?php else: ?>
        <div class="form-grid-3">
            <?php foreach ($destinations as $d): ?>
                <div style="border:1px solid var(--dash-border);border-radius:12px;overflow:hidden;background:var(--dash-surface-alt);">
                    <img src="<?= e(upload_url($d['hero_image'])) ?>" alt="<?= e($d['name']) ?>" style="height:150px;width:100%;object-fit:cover;">
                    <div style="padding:14px;">
                        <small style="color:var(--dash-muted);"><?= e($d['location_label']) ?></small>
                        <h4 style="margin:4px 0 8px;"><?= e($d['name']) ?></h4>
                        <div style="display:flex;justify-content:space-between;">
                            <a href="<?= url('/destination/' . $d['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Explore</a>
                            <form method="POST" action="<?= url('/customer/wishlist/' . (int) $d['wish_id'] . '/remove') ?>" style="margin:0;">
                                <?= \Core\Csrf::field() ?>
                                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
