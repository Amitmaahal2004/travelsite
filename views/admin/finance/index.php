<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Gross Revenue</div>
        <div class="kpi-value"><?= e(money($summary['gross_revenue'])) ?></div>
        <div class="kpi-sub">Total completed payments</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Processed Refunds</div>
        <div class="kpi-value"><?= e(money($summary['refunds_total'])) ?></div>
        <div class="kpi-sub">Net Revenue: <?= e(money($summary['net_revenue'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Expenses &amp; Commissions</div>
        <div class="kpi-value"><?= e(money($summary['expenses_total'] + $summary['commissions_total'])) ?></div>
        <div class="kpi-sub">Exp <?= e(money($summary['expenses_total'])) ?> &bull; Comm <?= e(money($summary['commissions_total'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Net Profit / Loss</div>
        <div class="kpi-value" style="color:<?= $summary['net_profit'] >= 0 ? 'var(--status-success)' : 'var(--status-danger)' ?>;">
            <?= e(money($summary['net_profit'])) ?>
        </div>
        <div class="kpi-sub">Real-time margin calculation</div>
    </div>
</div>

<div class="panel-card">
    <div class="panel-head">
        <h2>Monthly Financial Profit &amp; Loss</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (can('finance.export')): ?>
                <a href="<?= url('/admin/finance/export-csv') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Download CSV</a>
                <a href="<?= url('/admin/finance/export-pdf') ?>" class="btn-brand btn-brand-primary btn-sm-pill">Download PDF Report</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Month</th><th>Gross Revenue</th><th>Refunds</th><th>Expenses</th><th>Net P&amp;L</th></tr></thead>
            <tbody>
                <?php foreach ($summary['monthly'] as $m): ?>
                    <tr>
                        <td><strong><?= e($m['month']) ?></strong></td>
                        <td><?= e(money($m['revenue'])) ?></td>
                        <td><?= e(money($m['refunds'])) ?></td>
                        <td><?= e(money($m['expenses'])) ?></td>
                        <td><strong style="color:<?= $m['net'] >= 0 ? 'var(--status-success)' : 'var(--status-danger)' ?>;"><?= e(money($m['net'])) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dash-grid-2">
    <div class="panel-card">
        <h3>Expense Ledger</h3>
        <div class="table-wrap" style="margin-bottom:18px;">
            <table class="data-table">
                <thead><tr><th>Ref</th><th>Title</th><th>Category</th><th>Amount</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($expenses as $ex): ?>
                        <tr>
                            <td><strong><?= e($ex['expense_reference']) ?></strong></td>
                            <td><?= e($ex['title']) ?><br><small style="color:var(--dash-muted);"><?= e($ex['vendor_name'] ?: ($ex['booking_reference'] ?: '')) ?></small></td>
                            <td><span class="badge-pill badge-neutral"><?= e(str_replace('_', ' ', $ex['category'])) ?></span></td>
                            <td><strong><?= e(money($ex['amount'])) ?></strong></td>
                            <td><?= e($ex['expense_date']) ?></td>
                            <td>
                                <form method="POST" action="<?= url('/admin/finance/expenses/' . (int) $ex['id'] . '/delete') ?>" style="margin:0;">
                                    <?= \Core\Csrf::field() ?>
                                    <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">&times;</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h4>Record Expense</h4>
        <form method="POST" action="<?= url('/admin/finance/expenses/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div class="form-grid-2" style="margin-bottom:10px;">
                <input type="text" name="title" required class="dash-input" placeholder="Expense title">
                <select name="category" class="dash-select">
                    <?php foreach (['vendor_payout','accommodation','transport','marketing','payroll','operations','software','other'] as $cat): ?>
                        <option value="<?= $cat ?>"><?= ucwords(str_replace('_', ' ', $cat)) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" name="amount" required class="dash-input" placeholder="Amount ($)">
                <input type="date" name="expense_date" required class="dash-input" value="<?= date('Y-m-d') ?>">
                <input type="text" name="vendor_name" class="dash-input" placeholder="Vendor / Supplier">
                <select name="booking_id" class="dash-select">
                    <option value="">General Overhead</option>
                    <?php foreach ($bookings as $b): ?><option value="<?= (int) $b['id'] ?>"><?= e($b['booking_reference']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Add Expense</button>
        </form>
    </div>

    <div>
        <div class="panel-card">
            <h3>Staff Commissions</h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Staff</th><th>Booking</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($commissions as $cm): ?>
                            <tr>
                                <td><?= e($cm['employee_name']) ?> (<?= e($cm['commission_rate']) ?>%)</td>
                                <td><?= e($cm['booking_reference']) ?></td>
                                <td><strong><?= e(money($cm['commission_amount'])) ?></strong></td>
                                <td><span class="badge-pill <?= status_badge_class($cm['status']) ?>"><?= e(ucfirst($cm['status'])) ?></span></td>
                                <td>
                                    <?php if ($cm['status'] !== 'paid'): ?>
                                        <form method="POST" action="<?= url('/admin/finance/commissions/' . (int) $cm['id']) ?>" style="margin:0;">
                                            <?= \Core\Csrf::field() ?>
                                            <button type="submit" name="status" value="paid" class="btn-brand btn-brand-primary btn-sm-pill">Mark Paid</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <h3>Recent Payments &amp; Refunds</h3>
            <?php foreach (array_slice($payments, 0, 5) as $p): ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--dash-border);font-size:0.86rem;">
                    <span><strong><?= e($p['payment_reference']) ?></strong> &bull; <?= e($p['customer_name']) ?></span>
                    <strong style="color:var(--status-success);">+<?= e(money($p['amount'])) ?></strong>
                </div>
            <?php endforeach; ?>
            <?php foreach (array_slice($refunds, 0, 3) as $rf): ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--dash-border);font-size:0.86rem;">
                    <span><strong><?= e($rf['refund_reference']) ?></strong> &bull; <?= e($rf['customer_name']) ?></span>
                    <strong style="color:var(--status-danger);">-<?= e(money($rf['amount'])) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
