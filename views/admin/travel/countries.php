<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <div class="panel-head">
            <h2>Countries &amp; Regions</h2>
            <form method="GET" action="<?= url('/admin/countries') ?>" class="filter-bar" style="margin:0;">
                <input type="text" name="q" value="<?= e($q) ?>" class="dash-input" placeholder="Search country...">
                <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Search</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Country</th><th>Region</th><th>Currency</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($countries as $c): ?>
                        <tr>
                            <td><strong><?= e($c['code']) ?></strong></td>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['region']) ?></td>
                            <td><?= e($c['currency']) ?></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/countries/' . (int) $c['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" data-confirm="Delete country <?= e($c['name']) ?>?" class="btn-brand btn-brand-outline btn-sm-pill">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Add Country</h3>
        <form method="POST" action="<?= url('/admin/countries/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Country Name *</label><input type="text" name="name" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">ISO Code (3 chars) *</label><input type="text" name="code" maxlength="3" required class="dash-input" placeholder="FRA"></div>
                <div><label class="form-label-editorial">Currency *</label><input type="text" name="currency" maxlength="10" required class="dash-input" value="USD"></div>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Region *</label><input type="text" name="region" required class="dash-input" placeholder="Western Europe"></div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Overview</label><textarea name="description" rows="2" class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Add Country</button>
        </form>
    </div>
</div>
