<div class="panel-card">
    <div class="panel-head">
        <h2>My Assigned Bookings</h2>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Customer</th><th>Package</th><th>Departure</th><th>Travellers</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><strong><?= e($b['booking_reference']) ?></strong></td>
                        <td><?= e($b['customer_name']) ?><br><small style="color:var(--dash-muted);"><?= e($b['customer_email']) ?></small></td>
                        <td><?= e($b['package_title'] ?: ($b['custom_title'] ?: 'Custom Journey')) ?></td>
                        <td><?= e($b['travel_date']) ?></td>
                        <td><?= (int) $b['total_travellers'] ?></td>
                        <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                        <td><a href="<?= url('/employee/bookings/' . (int) $b['id']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?><tr><td colspan="7" style="text-align:center;color:var(--dash-muted);">No bookings assigned yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
