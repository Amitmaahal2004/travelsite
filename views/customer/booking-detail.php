<?php
$balance = max(0, (float) $booking['total_amount'] - (float) $booking['paid_amount']);
$steps = ['Pending', 'Under Review', 'Confirmed', 'Partially Paid', 'Paid', 'Completed'];
$currentIdx = array_search($booking['booking_status'], $steps, true);
?>
<div style="margin-bottom:14px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <a href="<?= url('/customer/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; Back to Bookings</a>
    <a href="<?= url('/customer/invoices/' . (int) $booking['id'] . '/download') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Download PDF Invoice</a>
</div>

<div class="panel-card">
    <div class="panel-head">
        <div>
            <span class="section-eyebrow" style="margin-bottom:2px;">Booking Reference <?= e($booking['booking_reference']) ?></span>
            <h2 style="margin:0;"><?= e($booking['package_title'] ?: 'Custom Travel Experience') ?></h2>
        </div>
        <span class="badge-pill <?= status_badge_class($booking['booking_status']) ?>"><?= e($booking['booking_status']) ?></span>
    </div>

    <!-- Visual Status Timeline -->
    <?php if (!in_array($booking['booking_status'], ['Cancelled', 'Refunded'], true)): ?>
    <div class="timeline-steps">
        <?php foreach ($steps as $idx => $label):
            $done = ($currentIdx !== false && $idx <= $currentIdx);
        ?>
            <div style="text-align:center;">
                <div style="width:26px;height:26px;border-radius:50%;margin:0 auto 6px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;background:<?= $done ? 'var(--brand-accent)' : 'var(--dash-border)' ?>;color:<?= $done ? '#111' : 'var(--dash-muted)' ?>;">
                    <?= $done ? '&#10003;' : ($idx + 1) ?>
                </div>
                <div style="font-size:0.75rem;font-weight:<?= $done ? '700' : '500' ?>;"><?= e($label) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="form-grid-3" style="font-size:0.92rem;">
        <div><strong>Travel Dates:</strong> <?= e($booking['travel_date']) ?> &rarr; <?= e($booking['return_date'] ?: 'TBD') ?></div>
        <div><strong>Travellers:</strong> <?= (int) $booking['adults'] ?> Adult(s), <?= (int) $booking['children'] ?> Child(ren)</div>
        <div><strong>Concierge Assigned:</strong> <?= e($booking['assigned_employee_name'] ?: 'Awaiting Specialist Assignment') ?></div>
    </div>
</div>

<div class="dash-grid-2">
    <div>
        <div class="panel-card">
            <h3>Traveller Manifest</h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Nationality</th><th>Dietary</th></tr></thead>
                    <tbody>
                        <?php foreach ($booking['travellers'] as $t): ?>
                            <tr>
                                <td><strong><?= e($t['full_name']) ?></strong> <?= $t['is_lead'] ? '<span class="badge-pill badge-info">Lead</span>' : '' ?></td>
                                <td><?= e($t['nationality'] ?: '—') ?></td>
                                <td><?= e($t['dietary_requirements'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <h3>Concierge Updates &amp; Notes</h3>
            <?php foreach ($booking['notes'] as $n): ?>
                <div style="padding:10px;border-radius:8px;background:var(--dash-surface-alt);margin-bottom:8px;">
                    <small style="color:var(--dash-muted);"><?= e($n['created_at']) ?> &bull; <?= e($n['author_name'] ?: 'Concierge') ?></small>
                    <div><?= e($n['note']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <div class="panel-card">
            <h3>Financial &amp; Payment Summary</h3>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:0.93rem;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;"><span>Package Subtotal</span><strong><?= e(money($booking['base_amount'], $booking['currency'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Discount</span><strong>-<?= e(money($booking['discount_amount'], $booking['currency'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Taxes</span><strong><?= e(money($booking['tax_amount'], $booking['currency'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid var(--dash-border);padding-top:8px;"><strong>Total</strong><strong><?= e(money($booking['total_amount'], $booking['currency'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;color:var(--status-success);"><span>Paid</span><strong><?= e(money($booking['paid_amount'], $booking['currency'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;font-size:1.1rem;"><strong>Balance Due</strong><strong><?= e(money($balance, $booking['currency'])) ?></strong></div>
            </div>

            <?php if ($balance > 0 && !in_array($booking['booking_status'], ['Cancelled', 'Refunded'], true)): ?>
                <form method="POST" action="<?= url('/customer/bookings/' . (int) $booking['id'] . '/pay') ?>" style="padding-top:14px;border-top:1px solid var(--dash-border);">
                    <?= \Core\Csrf::field() ?>
                    <h4 style="margin-bottom:10px;">Make a Payment</h4>
                    <div class="form-grid-2" style="margin-bottom:10px;">
                        <div>
                            <label class="form-label-editorial">Amount (<?= e($booking['currency']) ?>)</label>
                            <input type="number" step="0.01" min="1" max="<?= e($balance) ?>" name="amount" required class="dash-input" value="<?= e($balance) ?>">
                        </div>
                        <div>
                            <label class="form-label-editorial">Method</label>
                            <select name="payment_method" class="dash-select">
                                <option value="online_portal">Online Portal</option>
                                <option value="card_gateway">Hosted Payment Gateway</option>
                                <option value="bank_transfer">Bank Wire</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Complete Settlement</button>
                </form>
            <?php endif; ?>

            <?php if (!in_array($booking['booking_status'], ['Cancelled', 'Refunded', 'Completed'], true)): ?>
                <form method="POST" action="<?= url('/customer/bookings/' . (int) $booking['id'] . '/cancel') ?>" style="margin-top:18px;padding-top:14px;border-top:1px solid var(--dash-border);">
                    <?= \Core\Csrf::field() ?>
                    <input type="text" name="reason" class="dash-input" style="margin-bottom:8px;" placeholder="Cancellation reason (optional)">
                    <button type="submit" data-confirm="Are you sure you want to cancel this booking?" class="btn-brand btn-brand-outline btn-sm-pill" style="width:100%;color:var(--status-danger);border-color:var(--status-danger);">Request Cancellation</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
