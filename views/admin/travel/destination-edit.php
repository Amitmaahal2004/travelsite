<div style="margin-bottom:14px;"><a href="<?= url('/admin/destinations') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; Back to Destinations</a></div>
<div class="panel-card" style="max-width:860px;">
    <h2>Edit Destination: <?= e($dest['name']) ?></h2>
    <form method="POST" action="<?= url('/admin/destinations/' . (int) $dest['id'] . '/update') ?>" enctype="multipart/form-data">
        <?= \Core\Csrf::field() ?>
        <div class="form-grid-2" style="margin-bottom:12px;">
            <div><label class="form-label-editorial">Name *</label><input type="text" name="name" required class="dash-input" value="<?= e($dest['name']) ?>"></div>
            <div>
                <label class="form-label-editorial">Country *</label>
                <select name="country_id" class="dash-select">
                    <?php foreach ($countries as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $dest['country_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label-editorial">Location Label *</label><input type="text" name="location_label" required class="dash-input" value="<?= e($dest['location_label']) ?>"></div>
            <div><label class="form-label-editorial">Starting Price</label><input type="number" step="0.01" name="starting_price" class="dash-input" value="<?= e($dest['starting_price']) ?>"></div>
            <div><label class="form-label-editorial">Best Season</label><input type="text" name="best_time_to_visit" class="dash-input" value="<?= e($dest['best_time_to_visit']) ?>"></div>
            <div><label class="form-label-editorial">Climate</label><input type="text" name="climate" class="dash-input" value="<?= e($dest['climate']) ?>"></div>
        </div>
        <div style="margin-bottom:12px;"><label class="form-label-editorial">Tagline</label><input type="text" name="tagline" class="dash-input" value="<?= e($dest['tagline']) ?>"></div>
        <div style="margin-bottom:12px;"><label class="form-label-editorial">Summary</label><textarea name="summary" rows="2" class="dash-textarea"><?= e($dest['summary']) ?></textarea></div>
        <div style="margin-bottom:12px;"><label class="form-label-editorial">Description</label><textarea name="description" rows="4" class="dash-textarea"><?= e($dest['description']) ?></textarea></div>
        <div style="margin-bottom:12px;"><label class="form-label-editorial">Highlights (One per line)</label><textarea name="highlights" rows="3" class="dash-textarea"><?= e($dest['highlights_text']) ?></textarea></div>
        <div style="margin-bottom:12px;"><label class="form-label-editorial">Replace Hero Image</label><input type="file" name="hero_image" accept="image/*" class="dash-input"></div>
        <div style="display:flex;gap:16px;margin-bottom:18px;">
            <label><input type="checkbox" name="is_featured" value="1" <?= $dest['is_featured'] ? 'checked' : '' ?>> Featured on Homepage</label>
            <label><input type="checkbox" name="is_active" value="1" <?= $dest['is_active'] ? 'checked' : '' ?>> Active</label>
        </div>
        <button type="submit" class="btn-brand btn-brand-primary">Save Destination</button>
    </form>
</div>
