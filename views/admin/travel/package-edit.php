<div style="margin-bottom:14px;display:flex;justify-content:space-between;">
    <a href="<?= url('/admin/packages') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; All Packages</a>
    <a href="<?= url('/package/' . $pkg['slug']) ?>" target="_blank" class="btn-brand btn-brand-outline btn-sm-pill">Preview Public Page &rarr;</a>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <h3>Core Details &amp; Pricing</h3>
        <form method="POST" action="<?= url('/admin/packages/' . (int) $pkg['id'] . '/update') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Title *</label><input type="text" name="title" required class="dash-input" value="<?= e($pkg['title']) ?>"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div>
                    <label class="form-label-editorial">Destination</label>
                    <select name="destination_id" class="dash-select">
                        <?php foreach ($destinations as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) $pkg['destination_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Category</label>
                    <select name="category_id" class="dash-select">
                        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $pkg['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label-editorial">Days</label><input type="number" name="duration_days" class="dash-input" value="<?= (int) $pkg['duration_days'] ?>"></div>
                <div><label class="form-label-editorial">Nights</label><input type="number" name="duration_nights" class="dash-input" value="<?= (int) $pkg['duration_nights'] ?>"></div>
                <div><label class="form-label-editorial">Base Price</label><input type="number" step="0.01" name="base_price" class="dash-input" value="<?= e($pkg['base_price']) ?>"></div>
                <div><label class="form-label-editorial">Discount Price</label><input type="number" step="0.01" name="discount_price" class="dash-input" value="<?= e($pkg['discount_price']) ?>"></div>
                <div><label class="form-label-editorial">Max Group</label><input type="number" name="max_group_size" class="dash-input" value="<?= (int) $pkg['max_group_size'] ?>"></div>
                <div>
                    <label class="form-label-editorial">Availability</label>
                    <select name="availability_status" class="dash-select">
                        <?php foreach (['available','limited','sold_out','upcoming'] as $st): ?>
                            <option value="<?= $st ?>" <?= $pkg['availability_status'] === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label-editorial">Slots</label><input type="number" name="available_slots" class="dash-input" value="<?= (int) $pkg['available_slots'] ?>"></div>
                <div><label class="form-label-editorial">Difficulty / Pace</label><input type="text" name="difficulty_level" class="dash-input" value="<?= e($pkg['difficulty_level']) ?>"></div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Subtitle</label><input type="text" name="subtitle" class="dash-input" value="<?= e($pkg['subtitle']) ?>"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Overview</label><textarea name="overview" rows="4" class="dash-textarea"><?= e($pkg['overview']) ?></textarea></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Replace Featured Image</label><input type="file" name="featured_image" accept="image/*" class="dash-input"></div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px;font-size:0.86rem;">
                <label><input type="checkbox" name="is_featured" value="1" <?= $pkg['is_featured'] ? 'checked' : '' ?>> Featured</label>
                <label><input type="checkbox" name="is_seasonal_offer" value="1" <?= $pkg['is_seasonal_offer'] ? 'checked' : '' ?>> Seasonal Offer</label>
                <label><input type="checkbox" name="is_active" value="1" <?= $pkg['is_active'] ? 'checked' : '' ?>> Active</label>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary">Save Package Core</button>
        </form>
    </div>

    <div>
        <div class="panel-card">
            <h3>Day-by-Day Itinerary</h3>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                <?php foreach ($itineraries as $day): ?>
                    <div style="padding:10px;border-radius:8px;background:var(--dash-surface-alt);display:flex;justify-content:space-between;align-items:start;gap:10px;">
                        <div>
                            <strong>Day <?= (int) $day['day_number'] ?>: <?= e($day['title']) ?></strong>
                            <div style="font-size:0.82rem;color:var(--dash-muted);"><?= e($day['description']) ?></div>
                        </div>
                        <form method="POST" action="<?= url('/admin/packages/' . (int) $pkg['id'] . '/itinerary/' . (int) $day['id'] . '/delete') ?>" style="margin:0;">
                            <?= \Core\Csrf::field() ?>
                            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST" action="<?= url('/admin/packages/' . (int) $pkg['id'] . '/itinerary') ?>">
                <?= \Core\Csrf::field() ?>
                <div class="form-grid-2" style="margin-bottom:8px;">
                    <input type="number" name="day_number" min="1" required class="dash-input" value="<?= count($itineraries) + 1 ?>" placeholder="Day #">
                    <input type="text" name="location" class="dash-input" placeholder="Location">
                </div>
                <input type="text" name="title" required class="dash-input" style="margin-bottom:8px;" placeholder="Day title">
                <textarea name="description" rows="2" required class="dash-textarea" style="margin-bottom:8px;" placeholder="Day experiences"></textarea>
                <div class="form-grid-2" style="margin-bottom:10px;">
                    <input type="text" name="meals" class="dash-input" placeholder="Meals (e.g. B, L, D)">
                    <input type="text" name="accommodation" class="dash-input" placeholder="Lodge / Hotel">
                </div>
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">+ Add Day</button>
            </form>
        </div>

        <div class="panel-card">
            <h3>Gallery &amp; Inclusions / Exclusions</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <?php foreach ($images as $im): ?>
                    <img src="<?= e(upload_url($im['image_path'])) ?>" alt="" style="width:70px;height:52px;object-fit:cover;border-radius:6px;">
                <?php endforeach; ?>
            </div>
            <form method="POST" action="<?= url('/admin/packages/' . (int) $pkg['id'] . '/images') ?>" enctype="multipart/form-data" style="display:flex;gap:8px;margin-bottom:18px;">
                <?= \Core\Csrf::field() ?>
                <input type="file" name="gallery_image" required accept="image/*" class="dash-input">
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Upload</button>
            </form>

            <form method="POST" action="<?= url('/admin/packages/' . (int) $pkg['id'] . '/items') ?>" style="display:flex;gap:8px;margin-bottom:12px;">
                <?= \Core\Csrf::field() ?>
                <select name="list_type" class="dash-select" style="width:130px;">
                    <option value="inclusion">Inclusion</option>
                    <option value="exclusion">Exclusion</option>
                </select>
                <input type="text" name="item_text" required class="dash-input" placeholder="Add inclusion or exclusion...">
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Add</button>
            </form>
            <div style="font-size:0.82rem;color:var(--dash-muted);">
                <strong>Inclusions (<?= count($inclusions) ?>):</strong> <?= e(implode(' • ', array_column($inclusions, 'item_text'))) ?><br>
                <strong>Exclusions (<?= count($exclusions) ?>):</strong> <?= e(implode(' • ', array_column($exclusions, 'item_text'))) ?>
            </div>
        </div>
    </div>
</div>
