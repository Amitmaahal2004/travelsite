<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Seasonal Offers &amp; Promo Codes</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Offer Title</th><th>Discount</th><th>Valid Until</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($offers as $o): ?>
                        <tr>
                            <td><span class="badge-pill badge-info"><?= e($o['code']) ?></span></td>
                            <td><strong><?= e($o['title']) ?></strong><br><small style="color:var(--dash-muted);"><?= e($o['package_title'] ?: 'All Packages') ?></small></td>
                            <td><?= $o['discount_type'] === 'percentage' ? ($o['discount_value'] . '%') : money($o['discount_value']) ?></td>
                            <td><?= e($o['end_date']) ?></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/offers/' . (int) $o['id'] . '/delete') ?>" style="margin:0;">
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
        <h3>Create Seasonal Offer</h3>
        <form method="POST" action="<?= url('/admin/offers/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:10px;"><label class="form-label-editorial">Offer Title *</label><input type="text" name="title" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <div><label class="form-label-editorial">Promo Code *</label><input type="text" name="code" required class="dash-input" placeholder="WINTER15"></div>
                <div>
                    <label class="form-label-editorial">Type</label>
                    <select name="discount_type" class="dash-select"><option value="percentage">Percentage (%)</option><option value="fixed">Fixed ($)</option></select>
                </div>
                <div><label class="form-label-editorial">Value *</label><input type="number" step="0.01" name="discount_value" required class="dash-input"></div>
                <div><label class="form-label-editorial">Min Booking ($)</label><input type="number" step="0.01" name="min_booking_amount" class="dash-input" value="1000"></div>
                <div><label class="form-label-editorial">Start Date *</label><input type="date" name="start_date" required class="dash-input" value="<?= date('Y-m-d') ?>"></div>
                <div><label class="form-label-editorial">End Date *</label><input type="date" name="end_date" required class="dash-input" value="<?= date('Y-m-d', strtotime('+60 days')) ?>"></div>
            </div>
            <div style="margin-bottom:10px;">
                <label class="form-label-editorial">Package (Optional)</label>
                <select name="package_id" class="dash-select"><option value="">All Packages</option><?php foreach ($packages as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['title']) ?></option><?php endforeach; ?></select>
            </div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Description</label><textarea name="description" rows="2" class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Offer</button>
        </form>
    </div>
</div>
