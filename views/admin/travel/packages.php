<div class="dash-grid-2" style="grid-template-columns:1.45fr 0.9fr;">
    <div class="panel-card">
        <div class="panel-head">
            <h2>Travel Packages</h2>
            <form method="GET" action="<?= url('/admin/packages') ?>" class="filter-bar" style="margin:0;">
                <input type="text" name="q" value="<?= e($q) ?>" class="dash-input" placeholder="Search package or code...">
                <select name="category_id" class="dash-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (string) $catId === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Filter</button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Package</th><th>Destination</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><strong><?= e($p['package_code']) ?></strong></td>
                            <td>
                                <strong><?= e($p['title']) ?></strong><br>
                                <small style="color:var(--dash-muted);"><?= (int) $p['duration_days'] ?>D/<?= (int) $p['duration_nights'] ?>N &bull; <?= e($p['category_name']) ?></small>
                            </td>
                            <td><?= e($p['destination_name']) ?></td>
                            <td>
                                <?php if (!empty($p['discount_price'])): ?><del style="color:var(--dash-muted);"><?= e(money($p['base_price'])) ?></del><br><?php endif; ?>
                                <strong><?= e(money($p['discount_price'] ?: $p['base_price'])) ?></strong>
                            </td>
                            <td><span class="badge-pill <?= status_badge_class($p['availability_status']) ?>"><?= e($p['availability_status']) ?></span></td>
                            <td style="display:flex;gap:6px;">
                                <a href="<?= url('/admin/packages/' . (int) $p['id']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Studio</a>
                                <form method="POST" action="<?= url('/admin/packages/' . (int) $p['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" data-confirm="Archive package?" class="btn-brand btn-brand-outline btn-sm-pill">Archive</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $baseUrl = '/admin/packages'; require BASE_PATH . '/views/partials/pagination.php'; ?>
    </div>

    <div class="panel-card">
        <h3>Create Travel Package</h3>
        <form method="POST" action="<?= url('/admin/packages/create') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Package Title *</label><input type="text" name="title" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div>
                    <label class="form-label-editorial">Destination *</label>
                    <select name="destination_id" required class="dash-select">
                        <?php foreach ($destinations as $d): ?><option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Category *</label>
                    <select name="category_id" required class="dash-select">
                        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Days *</label><input type="number" min="1" name="duration_days" required class="dash-input" value="6"></div>
                <div><label class="form-label-editorial">Nights *</label><input type="number" min="0" name="duration_nights" required class="dash-input" value="5"></div>
                <div><label class="form-label-editorial">Base Price *</label><input type="number" step="0.01" name="base_price" required class="dash-input" value="4200"></div>
                <div><label class="form-label-editorial">Offer Price (Opt.)</label><input type="number" step="0.01" name="discount_price" class="dash-input"></div>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Subtitle</label><input type="text" name="subtitle" class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Overview</label><textarea name="overview" rows="3" class="dash-textarea"></textarea></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Featured Image</label><input type="file" name="featured_image" accept="image/*" class="dash-input"></div>
            <div style="display:flex;gap:14px;margin-bottom:14px;font-size:0.86rem;">
                <label><input type="checkbox" name="is_featured" value="1" checked> Featured</label>
                <label><input type="checkbox" name="is_seasonal_offer" value="1"> Seasonal Offer</label>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create &amp; Build Itinerary</button>
        </form>
    </div>
</div>
