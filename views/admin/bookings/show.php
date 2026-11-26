<?php
$balance = max(0, (float) $booking['total_amount'] - (float) $booking['paid_amount']);
?>
<div style="margin-bottom:14px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <a href="<?= url('/admin/bookings') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; All Bookings</a>
    <a href="<?= url('/admin/bookings/' . (int) $booking['id'] . '/invoice') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Download Branded PDF Invoice</a>
</div>

<div class="dash-grid-2">
    <div>
        <div class="panel-card">
            <div class="panel-head">
                <div>
                    <h2><?= e($booking['booking_reference']) ?> — <?= e($booking['package_title'] ?: ($booking['custom_title'] ?: 'Custom Journey')) ?></h2>
                    <small style="color:var(--dash-muted);">Customer: <strong><?= e($booking['customer_name']) ?></strong> (<?= e($booking['customer_email']) ?>)</small>
                </div>
                <span class="badge-pill <?= status_badge_class($booking['booking_status']) ?>"><?= e($booking['booking_status']) ?></span>
            </div>

            <form method="POST" action="<?= url('/admin/bookings/' . (int) $booking['id'] . '/update') ?>">
                <?= \Core\Csrf::field() ?>
                <div class="form-grid-2" style="margin-bottom:12px;">
                    <div>
                        <label class="form-label-editorial">Booking Status</label>
                        <select name="booking_status" class="dash-select">
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= e($st) ?>" <?= $booking['booking_status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-editorial">Assign Specialist</label>
                        <select name="assigned_employee_id" class="dash-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>" <?= (int) $booking['assigned_employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= e($e['full_name']) ?> (<?= e($e['designation']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="form-label-editorial">Travel Date</label><input type="date" name="travel_date" class="dash-input" value="<?= e($booking['travel_date']) ?>"></div>
                    <div><label class="form-label-editorial">Return Date</label><input type="date" name="return_date" class="dash-input" value="<?= e($booking['return_date']) ?>"></div>
                </div>
                <div style="margin-bottom:12px;"><label class="form-label-editorial">Internal Concierge Notes</label><textarea name="internal_notes" rows="2" class="dash-textarea"><?= e($booking['internal_notes']) ?></textarea></div>
                <div style="margin-bottom:10px;"><label class="form-label-editorial">Add Timeline Update Note</label><input type="text" name="new_note" class="dash-input" placeholder="Optional note logged to booking timeline"></div>
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;font-size:0.86rem;">
                    <input type="checkbox" name="visible_to_customer" value="1" checked> Make timeline note visible to customer
                </label>
                <button type="submit" class="btn-brand btn-brand-primary">Save Booking &amp; Notify</button>
            </form>
        </div>

        <div class="panel-card">
            <h3>Travellers &amp; Booking Timeline</h3>
            <div class="table-wrap" style="margin-bottom:16px;">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Nationality</th><th>Dietary</th></tr></thead>
                    <tbody>
                        <?php foreach ($booking['travellers'] as $t): ?>
                            <tr>
                                <td><strong><?= e($t['full_name']) ?></strong> <?= $t['is_lead'] ? '<span class="badge-pill badge-info">Lead</span>' : '' ?></td>
                                <td><?= e($t['email'] ?: '—') ?></td>
                                <td><?= e($t['nationality'] ?: '—') ?></td>
                                <td><?= e($t['dietary_requirements'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php foreach ($booking['notes'] as $n): ?>
                <div style="padding:10px;border-radius:8px;background:var(--dash-surface-alt);margin-bottom:8px;font-size:0.85rem;">
                    <small style="color:var(--dash-muted);"><?= e($n['created_at']) ?> &bull; <?= e($n['author_name'] ?: 'System') ?></small>
                    <div><?= e($n['note']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <div class="panel-card">
            <h3>Payments &amp; Settlements</h3>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;font-size:0.92rem;">
                <div style="display:flex;justify-content:space-between;"><span>Total Amount</span><strong><?= e(money($booking['total_amount'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;color:var(--status-success);"><span>Paid Amount</span><strong><?= e(money($booking['paid_amount'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;font-size:1.1rem;"><span>Balance Due</span><strong><?= e(money($balance)) ?></strong></div>
            </div>

            <form method="POST" action="<?= url('/admin/bookings/' . (int) $booking['id'] . '/payment') ?>" style="padding-top:12px;border-top:1px solid var(--dash-border);margin-bottom:18px;">
                <?= \Core\Csrf::field() ?>
                <h4 style="margin-bottom:10px;">Record Payment</h4>
                <div class="form-grid-2" style="margin-bottom:10px;">
                    <div><label class="form-label-editorial">Amount *</label><input type="number" step="0.01" name="amount" required class="dash-input" value="<?= e($balance > 0 ? $balance : '') ?>"></div>
                    <div>
                        <label class="form-label-editorial">Method</label>
                        <select name="payment_method" class="dash-select">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online_portal">Online Portal</option>
                            <option value="card_gateway">External Gateway</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                </div>
                <input type="text" name="gateway_transaction_id" class="dash-input" style="margin-bottom:10px;" placeholder="External Transaction / Wire Ref (No card numbers)">
                <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill" style="width:100%;">Record Payment &amp; Refresh Invoice</button>
            </form>

            <?php if ((float) $booking['paid_amount'] > 0): ?>
            <form method="POST" action="<?= url('/admin/bookings/' . (int) $booking['id'] . '/refund') ?>" style="padding-top:12px;border-top:1px solid var(--dash-border);">
                <?= \Core\Csrf::field() ?>
                <h4 style="margin-bottom:10px;">Process Refund</h4>
                <div class="form-grid-2" style="margin-bottom:10px;">
                    <input type="number" step="0.01" max="<?= e($booking['paid_amount']) ?>" name="amount" required class="dash-input" placeholder="Refund amount">
                    <input type="text" name="reason" required class="dash-input" placeholder="Reason">
                </div>
                <button type="submit" data-confirm="Process refund and update invoice?" class="btn-brand btn-brand-outline btn-sm-pill" style="width:100%;">Process Refund</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="panel-card">
            <h3>Payment Ledger</h3>
            <?php foreach ($booking['payments'] as $p): ?>
                <div style="padding:10px;border-radius:8px;background:var(--dash-surface-alt);margin-bottom:8px;font-size:0.85rem;display:flex;justify-content:space-between;">
                    <div><strong><?= e($p['payment_reference']) ?></strong><br><small><?= e($p['payment_method']) ?> &bull; <?= e($p['gateway_transaction_id'] ?: '') ?></small></div>
                    <strong><?= e(money($p['amount'])) ?></strong>
                </div>
            <?php endforeach; ?>
            <?php if (empty($booking['payments'])): ?><p style="color:var(--dash-muted);">No payments recorded.</p><?php endif; ?>
        </div>
    </div>
</div>
