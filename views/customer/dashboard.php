<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total Bookings</div>
        <div class="kpi-value"><?= (int) $stats['total_bookings'] ?></div>
        <div class="kpi-sub"><?= (int) $stats['upcoming_count'] ?> active / upcoming</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Total Paid</div>
        <div class="kpi-value"><?= e(money($stats['total_spent'])) ?></div>
        <div class="kpi-sub">Across confirmed journeys</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Saved Wishlist</div>
        <div class="kpi-value"><?= (int) $stats['wishlist_count'] ?></div>
        <div class="kpi-sub">Packages &amp; destinations</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Membership Tier</div>
        <div class="kpi-value" style="font-size:1.6rem;text-transform:capitalize;"><?= e($cust['loyalty_tier']) ?></div>
        <div class="kpi-sub"><?= e($cust['customer_code']) ?></div>
    </div>
</div>

<?php if ($upcoming): ?>
<div class="panel-card" style="background:linear-gradient(120deg, var(--brand-primary), var(--brand-secondary));color:#FAF7F2;border:none;">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:18px;">
        <div>
            <span class="badge-pill" style="background:var(--brand-accent);color:#111;">Next Departure</span>
            <h2 style="color:#FAF7F2;margin:10px 0 4px;"><?= e($upcoming['package_title'] ?: 'Custom Journey') ?></h2>
            <p style="margin:0;opacity:0.85;">
                <?= e($upcoming['destination_name'] ?: '') ?> &bull; Departure: <strong><?= e($upcoming['travel_date']) ?></strong> &bull; Ref: <?= e($upcoming['booking_reference']) ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="<?= url('/customer/bookings/' . (int) $upcoming['id']) ?>" class="btn-brand btn-brand-accent btn-sm-pill">Track Journey</a>
            <a href="<?= url('/customer/invoices/' . (int) $upcoming['id'] . '/download') ?>" class="btn-brand btn-brand-outline-light btn-sm-pill">Download Invoice</a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h3>My Recent Bookings</h3>
            <a href="<?= url('/customer/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">View All</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ref</th><th>Journey</th><th>Travel Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong><?= e($b['booking_reference']) ?></strong></td>
                            <td><?= e($b['package_title'] ?: 'Custom') ?></td>
                            <td><?= e($b['travel_date']) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($b['booking_status']) ?>"><?= e($b['booking_status']) ?></span></td>
                            <td><a href="<?= url('/customer/bookings/' . (int) $b['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="5" style="color:var(--dash-muted);">You haven't booked a journey yet. <a href="<?= url('/packages') ?>" style="color:var(--brand-primary);font-weight:600;">Explore Packages &rarr;</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <h3>Recent Updates</h3>
            <a href="<?= url('/customer/notifications') ?>" class="btn-brand btn-brand-outline btn-sm-pill">All Alerts</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($notifications as $n): ?>
                <div style="padding:12px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <strong style="font-size:0.88rem;"><?= e($n['title']) ?></strong>
                    <p style="margin:4px 0 0;font-size:0.82rem;color:var(--dash-muted);"><?= e($n['message']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($notifications)): ?><p style="color:var(--dash-muted);">No unread alerts.</p><?php endif; ?>
        </div>
    </div>
</div>
