<div class="dash-grid-2" style="grid-template-columns:1.3fr 0.95fr;">
    <div class="panel-card">
        <h2>My Support Requests</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket #</th><th>Subject</th><th>Booking</th><th>Priority</th><th>Status</th><th>Open</th></tr></thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><strong><?= e($t['ticket_number']) ?></strong></td>
                            <td><?= e($t['subject']) ?><br><small style="color:var(--dash-muted);"><?= e($t['category']) ?></small></td>
                            <td><?= e($t['booking_reference'] ?: 'General') ?></td>
                            <td><?= e(ucfirst($t['priority'])) ?></td>
                            <td><span class="badge-pill <?= status_badge_class($t['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $t['status']))) ?></span></td>
                            <td><a href="<?= url('/customer/support/' . (int) $t['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tickets)): ?><tr><td colspan="6" style="color:var(--dash-muted);">No support requests opened.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Open Support Request</h3>
        <form method="POST" action="<?= url('/customer/support/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Subject *</label><input type="text" name="subject" required class="dash-input"></div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Category</label>
                    <select name="category" class="dash-select">
                        <option value="Booking Support">Booking Support</option>
                        <option value="Transfer Coordination">Transfer Coordination</option>
                        <option value="Payment & Invoice">Payment &amp; Invoice</option>
                        <option value="Passport & Visa">Passport &amp; Visa</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Priority</label>
                    <select name="priority" class="dash-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Related Booking (Optional)</label>
                <select name="booking_id" class="dash-select">
                    <option value="">General Account Question</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= (int) $b['id'] ?>"><?= e($b['booking_reference']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">Message *</label><textarea name="message" rows="4" required class="dash-textarea"></textarea></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Submit Ticket</button>
        </form>
    </div>
</div>
