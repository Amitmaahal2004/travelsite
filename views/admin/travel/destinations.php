<div class="dash-grid-2" style="grid-template-columns:1.4fr 0.9fr;">
    <div class="panel-card">
        <div class="panel-head">
            <h2>Destinations</h2>
            <form method="GET" action="<?= url('/admin/destinations') ?>" class="filter-bar" style="margin:0;">
                <input type="text" name="q" value="<?= e($q) ?>" class="dash-input" placeholder="Search destinations...">
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Search</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Destination</th><th>Country</th><th>Starting Price</th><th>Featured</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($destinations as $d): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="<?= e(upload_url($d['hero_image'])) ?>" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover;">
                                    <div><strong><?= e($d['name']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($d['location_label']) ?></small></div>
                                </div>
                            </td>
                            <td><?= e($d['country_name']) ?></td>
                            <td><?= e(money($d['starting_price'])) ?></td>
                            <td><span class="badge-pill <?= $d['is_featured'] ? 'badge-success' : 'badge-neutral' ?>"><?= $d['is_featured'] ? 'Featured' : 'Standard' ?></span></td>
                            <td style="display:flex;gap:6px;">
                                <a href="<?= url('/admin/destinations/' . (int) $d['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Edit</a>
                                <form method="POST" action="<?= url('/admin/destinations/' . (int) $d['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" data-confirm="Archive destination?" class="btn-brand btn-brand-outline btn-sm-pill">Archive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $baseUrl = '/admin/destinations'; require BASE_PATH . '/views/partials/pagination.php'; ?>
    </div>

    <div class="panel-card">
        <h3>Add Destination</h3>
        <form method="POST" action="<?= url('/admin/destinations/create') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Destination Name *</label><input type="text" name="name" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div>
                    <label class="form-label-editorial">Country *</label>
                    <select name="country_id" required class="dash-select">
                        <?php foreach ($countries as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label-editorial">Starting Price</label><input type="number" step="0.01" name="starting_price" class="dash-input" value="3500"></div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Location Label *</label><input type="text" name="location_label" required class="dash-input" placeholder="e.g. Ravello, Italy"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Tagline</label><input type="text" name="tagline" class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Summary</label><textarea name="summary" rows="2" class="dash-textarea"></textarea></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Full Description (HTML allowed)</label><textarea name="description" rows="3" class="dash-textarea"></textarea></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Highlights (One per line)</label><textarea name="highlights" rows="3" class="dash-textarea"></textarea></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Best Season</label><input type="text" name="best_time_to_visit" class="dash-input"></div>
                <div><label class="form-label-editorial">Climate</label><input type="text" name="climate" class="dash-input"></div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Hero Image</label><input type="file" name="hero_image" accept="image/*" class="dash-input"></div>
            <div style="display:flex;gap:16px;margin-bottom:14px;font-size:0.86rem;">
                <label><input type="checkbox" name="is_featured" value="1" checked> Featured</label>
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Publish Destination</button>
        </form>
    </div>
</div>
