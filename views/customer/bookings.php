<div class="panel-card">
    <div class="panel-head">
        <h2>My Travel Bookings</h2>
        <a href="<?= url('/packages') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Book New Journey</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Package</th><th>Travel Date</th><th>Total</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><strong><?= e($b['booking_reference']) ?></strong></td>
                        <td><?= e($b['package_title'] ?: ($b['custom_title'] ?: 'Custom Journey')) ?><br><small style="color:var(--dash-muted);"><?= e($b['destination_name']) ?></small></td>
                        <td><?= e($b['travel_date']) ?></td>
                        <td><?= e(money($b['total_amount'], $b['currency'])) ?></td>
                        <td><?= e(money($b['paid_amount'], $b['currency'])) ?></td>
                        <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        <td style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="<?= url('/customer/bookings/' . (int) $b['id']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Details</a>
                            <a href="<?= url('/customer/invoices/' . (int) $b['id'] . '/download') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Invoice PDF</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--dash-muted);">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
