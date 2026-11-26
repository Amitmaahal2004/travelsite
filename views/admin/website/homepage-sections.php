<div class="panel-card">
    <div class="panel-head">
        <h2>Homepage Sections &amp; Hero Content</h2>
        <a href="<?= url('/') ?>" target="_blank" class="btn-brand btn-brand-outline btn-sm-pill">View Live Homepage &rarr;</a>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px;">
        <?php foreach ($sections as $sec): ?>
            <form method="POST" action="<?= url('/admin/homepage-sections/' . (int) $sec['id'] . '/update') ?>" enctype="multipart/form-data"
                  style="padding:18px;border-radius:12px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                <?= \Core\Csrf::field() ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <strong style="text-transform:uppercase;letter-spacing:0.1em;font-size:0.85rem;color:var(--brand-accent);">
                        Section: <?= e(str_replace('_', ' ', $sec['section_key'])) ?>
                    </strong>
                    <label style="font-size:0.86rem;"><input type="checkbox" name="is_active" value="1" <?= $sec['is_active'] ? 'checked' : '' ?>> Active on Homepage</label>
                </div>
                <div class="form-grid-3" style="margin-bottom:10px;">
                    <div><label class="form-label-editorial">Headline</label><input type="text" name="title" required class="dash-input" value="<?= e($sec['title']) ?>"></div>
                    <div><label class="form-label-editorial">Badge / Eyebrow</label><input type="text" name="badge_text" class="dash-input" value="<?= e($sec['badge_text']) ?>"></div>
                    <div><label class="form-label-editorial">Sort Order</label><input type="number" name="sort_order" class="dash-input" value="<?= (int) $sec['sort_order'] ?>"></div>
                    <div><label class="form-label-editorial">CTA Text</label><input type="text" name="cta_text" class="dash-input" value="<?= e($sec['cta_text']) ?>"></div>
                    <div><label class="form-label-editorial">CTA Link</label><input type="text" name="cta_link" class="dash-input" value="<?= e($sec['cta_link']) ?>"></div>
                    <div><label class="form-label-editorial">Background / Feature Image</label><input type="file" name="background_image" accept="image/*" class="dash-input" style="font-size:0.8rem;"></div>
                </div>
                <div style="margin-bottom:10px;"><label class="form-label-editorial">Subtitle</label><textarea name="subtitle" rows="2" class="dash-textarea"><?= e($sec['subtitle']) ?></textarea></div>
                <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save Section</button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
