<div style="margin-bottom:14px;"><a href="<?= url('/employee/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; Back to Bookings</a></div>
<div class="dash-grid-2">
    <div class="panel-card">
        <div class="panel-head">
            <h2><?= e($booking['booking_reference']) ?> — <?= e($booking['package_title'] ?: 'Custom Journey') ?></h2>
            <span class="badge-pill <?= status_badge_class($booking['booking_status']) ?>"><?= e($booking['booking_status']) ?></span>
        </div>
        <div class="form-grid-2" style="margin-bottom:18px;font-size:0.9rem;">
            <div><strong>Customer:</strong> <?= e($booking['customer_name']) ?> (<?= e($booking['customer_email']) ?>)</div>
            <div><strong>Departure:</strong> <?= e($booking['travel_date']) ?> &rarr; <?= e($booking['return_date'] ?: 'TBD') ?></div>
            <div><strong>Travellers:</strong> <?= (int) $booking['adults'] ?> Adults, <?= (int) $booking['children'] ?> Children</div>
            <div><strong>Payment:</strong> <?= e(money($booking['paid_amount'])) ?> / <?= e(money($booking['total_amount'])) ?></div>
        </div>
        <?php if (!empty($booking['special_requests'])): ?>
            <div style="padding:12px;border-radius:10px;background:var(--dash-surface-alt);margin-bottom:18px;">
                <strong>Special Requests:</strong> <?= e($booking['special_requests']) ?>
            </div>
        <?php endif; ?>

        <h3>Traveller Manifest</h3>
        <div class="table-wrap" style="margin-bottom:18px;">
            <table class="data-table">
                <thead><tr><th>Name</th><th>Nationality</th><th>Passport</th><th>Dietary</th></tr></thead>
                <tbody>
                    <?php foreach ($booking['travellers'] as $t): ?>
                        <tr>
                            <td><strong><?= e($t['full_name']) ?></strong> <?= $t['is_lead'] ? '<span class="badge-pill badge-info">Lead</span>' : '' ?></td>
                            <td><?= e($t['nationality'] ?: '—') ?></td>
                            <td><?= e($t['passport_number'] ?: '—') ?></td>
                            <td><?= e($t['dietary_requirements'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Activity &amp; Notes</h3>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($booking['notes'] as $n): ?>
                <div style="padding:10px;border-radius:8px;background:var(--dash-surface-alt);font-size:0.86rem;">
                    <strong><?= e($n['author_name'] ?: 'System') ?></strong>
                    <small style="color:var(--dash-muted);margin-left:8px;"><?= e($n['created_at']) ?></small>
                    <div style="margin-top:3px;"><?= e($n['note']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-card">
        <h3>Update Booking Progress</h3>
        <form method="POST" action="<?= url('/employee/bookings/' . (int) $booking['id'] . '/update') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Booking Status</label>
                <select name="booking_status" class="dash-select">
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= e($st) ?>" <?= $booking['booking_status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Progress Note</label>
                <textarea name="note" rows="4" class="dash-textarea" placeholder="Add lodge confirmation, transfer update, or internal note..."></textarea>
            </div>
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:0.86rem;">
                <input type="checkbox" name="visible_to_customer" value="1" checked> Show note to customer in portal
            </label>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Update &amp; Notify Customer</button>
        </form>
    </div>
</div>
