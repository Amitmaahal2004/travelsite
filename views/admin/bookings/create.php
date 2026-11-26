<div style="margin-bottom:14px;"><a href="<?= url('/admin/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; All Bookings</a></div>
<div class="panel-card" style="max-width:860px;">
    <h2>Create Manual Booking</h2>
    <form method="POST" action="<?= url('/admin/bookings/create') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="form-grid-2" style="margin-bottom:12px;">
            <div>
                <label class="form-label-editorial">Customer *</label>
                <select name="customer_id" required class="dash-select">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['full_name']) ?> (<?= e($c['customer_code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label-editorial">Package (or leave blank for custom)</label>
                <select name="package_id" class="dash-select">
                    <option value="">Custom Package / Bespoke Quote</option>
                    <?php foreach ($packages as $p): ?>
                        <option value="<?= (int) $p['id'] ?>"><?= e($p['title']) ?> (<?= e(money($p['discount_price'] ?: $p['base_price'])) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label-editorial">Custom Title (If no package)</label><input type="text" name="custom_title" class="dash-input" placeholder="Private Japan Art Circuit"></div>
            <div><label class="form-label-editorial">Custom Price Per Adult ($)</label><input type="number" step="0.01" name="custom_unit_price" class="dash-input" value="4500"></div>
            <div><label class="form-label-editorial">Departure Date *</label><input type="date" name="travel_date" required class="dash-input" value="<?= date('Y-m-d', strtotime('+30 days')) ?>"></div>
            <div>
                <label class="form-label-editorial">Assign Specialist</label>
                <select name="assigned_employee_id" class="dash-select">
                    <option value="">Unassigned</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e($e['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label-editorial">Adults *</label><input type="number" min="1" name="adults" required class="dash-input" value="2"></div>
            <div><label class="form-label-editorial">Children</label><input type="number" min="0" name="children" class="dash-input" value="0"></div>
            <div>
                <label class="form-label-editorial">Initial Status</label>
                <select name="booking_status" class="dash-select">
                    <?php foreach ($statuses as $st): ?><option value="<?= e($st) ?>" <?= $st === 'Confirmed' ? 'selected' : '' ?>><?= e($st) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label-editorial">Promo Code</label><input type="text" name="promo_code" class="dash-input"></div>
        </div>
        <div style="margin-bottom:16px;"><label class="form-label-editorial">Special Requests</label><textarea name="special_requests" rows="2" class="dash-textarea"></textarea></div>
        <button type="submit" class="btn-brand btn-brand-primary">Create Booking &amp; Issue Invoice</button>
    </form>
</div>
