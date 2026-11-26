<div class="panel-card">
    <h2>Payment History</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Payment Ref</th><th>Booking</th><th>Method</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><strong><?= e($p['payment_reference']) ?></strong></td>
                        <td><?= e($p['booking_reference']) ?></td>
                        <td><?= e(ucwords(str_replace('_', ' ', $p['payment_method']))) ?></td>
                        <td><strong><?= e(money($p['amount'], $p['currency'])) ?></strong></td>
                        <td><?= e($p['paid_at'] ?: $p['created_at']) ?></td>
                        <td><span class="badge-pill <?= status_badge_class($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?><tr><td colspan="6" style="color:var(--dash-muted);">No payment records yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel-card">
    <h2>Invoices</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Invoice #</th><th>Booking</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>PDF</th></tr></thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><strong><?= e($inv['invoice_number']) ?></strong></td>
                        <td><?= e($inv['booking_reference']) ?></td>
                        <td><?= e(money($inv['total_amount'])) ?></td>
                        <td><?= e(money($inv['amount_paid'])) ?></td>
                        <td><?= e(money($inv['balance_due'])) ?></td>
                        <td><span class="badge-pill <?= status_badge_class($inv['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $inv['status']))) ?></span></td>
                        <td><a href="<?= url('/customer/invoices/' . (int) $inv['booking_id'] . '/download') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Download PDF</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
