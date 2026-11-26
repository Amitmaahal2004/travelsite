<div class="dash-grid-2">
    <div class="panel-card">
        <h3>My Profile (<?= e($emp['employee_code']) ?>)</h3>
        <form method="POST" action="<?= url('/employee/profile') ?>">
            <?= \Core\Csrf::field() ?>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div><label class="form-label-editorial">Full Name</label><input type="text" class="dash-input" value="<?= e($user['first_name'] . ' ' . $user['last_name']) ?>" disabled></div>
                <div><label class="form-label-editorial">Email</label><input type="text" class="dash-input" value="<?= e($user['email']) ?>" disabled></div>
                <div><label class="form-label-editorial">Designation</label><input type="text" class="dash-input" value="<?= e($emp['designation']) ?>" disabled></div>
                <div><label class="form-label-editorial">Department</label><input type="text" class="dash-input" value="<?= e($emp['department_name']) ?>" disabled></div>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Phone</label><input type="text" name="phone" class="dash-input" value="<?= e($user['phone']) ?>"></div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Address</label><input type="text" name="address" class="dash-input" value="<?= e($emp['address']) ?>"></div>
            <button type="submit" class="btn-brand btn-brand-primary">Save Contact Details</button>
        </form>
    </div>

    <div class="panel-card">
        <h3>Commission Ledger</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Booking</th><th>Rate</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($commissions as $c): ?>
                        <tr>
                            <td><strong><?= e($c['booking_reference']) ?></strong></td>
                            <td><?= e($c['commission_rate']) ?>%</td>
                            <td><strong><?= e(money($c['commission_amount'])) ?></strong></td>
                            <td><span class="badge-pill <?= status_badge_class($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($commissions)): ?><tr><td colspan="4" style="color:var(--dash-muted);">No commissions yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
