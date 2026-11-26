<div style="margin-bottom:14px;"><a href="<?= url('/admin/customers') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; All Customers</a></div>
<div class="dash-grid-2">
    <div class="panel-card">
        <h3>Edit Customer Account (<?= e($cust['customer_code']) ?>)</h3>
        <form method="POST" action="<?= url('/admin/customers/' . (int) $cust['id'] . '/update') ?>">
            <?= \Core\Csrf::field() ?>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">First Name</label><input type="text" name="first_name" required class="dash-input" value="<?= e($cust['first_name']) ?>"></div>
                <div><label class="form-label-editorial">Last Name</label><input type="text" name="last_name" required class="dash-input" value="<?= e($cust['last_name']) ?>"></div>
                <div><label class="form-label-editorial">Phone</label><input type="text" name="phone" class="dash-input" value="<?= e($cust['phone']) ?>"></div>
                <div>
                    <label class="form-label-editorial">Account Status</label>
                    <select name="account_status" class="dash-select">
                        <option value="active" <?= $cust['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $cust['account_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="blocked" <?= $cust['account_status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Loyalty Tier</label>
                    <select name="loyalty_tier" class="dash-select">
                        <?php foreach (['explorer','voyager','connoisseur'] as $tier): ?>
                            <option value="<?= $tier ?>" <?= $cust['loyalty_tier'] === $tier ? 'selected' : '' ?>><?= ucfirst($tier) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label-editorial">Nationality</label><input type="text" name="nationality" class="dash-input" value="<?= e($cust['nationality']) ?>"></div>
                <div><label class="form-label-editorial">City</label><input type="text" name="city" class="dash-input" value="<?= e($cust['city']) ?>"></div>
                <div><label class="form-label-editorial">Country</label><input type="text" name="country" class="dash-input" value="<?= e($cust['country']) ?>"></div>
            </div>
            <div style="margin-bottom:14px;"><label class="form-label-editorial">Admin Notes</label><textarea name="notes" rows="3" class="dash-textarea"><?= e($cust['notes']) ?></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary">Save Customer Account</button>
        </form>
    </div>

    <div class="panel-card">
        <h3>Booking History</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Reference</th><th>Package</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><a href="<?= url('/admin/bookings/' . (int) $b['id']) ?>"><strong><?= e($b['booking_reference']) ?></strong></a></td>
                            <td><?= e($b['package_title'] ?: 'Custom') ?></td>
                            <td><?= e(money($b['total_amount'])) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
