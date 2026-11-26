<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Navigation &amp; Footer Menus</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Location</th><th>Label</th><th>URL</th><th>Order</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($menus as $m): ?>
                        <tr>
                            <td><span class="badge-pill badge-neutral"><?= e($m['location']) ?></span></td>
                            <td><strong><?= e($m['label']) ?></strong></td>
                            <td><code><?= e($m['url']) ?></code></td>
                            <td><?= (int) $m['sort_order'] ?></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/menus/' . (int) $m['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Add Menu Link</h3>
        <form method="POST" action="<?= url('/admin/menus/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;">
                <label class="form-label-editorial">Location</label>
                <select name="location" class="dash-select">
                    <option value="header">Header Navigation</option>
                    <option value="footer_explore">Footer: Explore</option>
                    <option value="footer_company">Footer: Company</option>
                    <option value="footer_legal">Footer: Legal</option>
                </select>
            </div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Label *</label><input type="text" name="label" required class="dash-input"></div>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">URL *</label><input type="text" name="url" required class="dash-input" placeholder="/destinations"></div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Sort Order</label><input type="number" name="sort_order" class="dash-input" value="10"></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Add Menu Link</button>
        </form>
    </div>
</div>
