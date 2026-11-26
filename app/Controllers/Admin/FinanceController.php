<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuditService;
use App\Services\BrandingService;
use Core\Auth;
use Core\Database;
use Core\PdfGenerator;
use Core\Validator;
use Core\View;

class FinanceController
{
    private function gatherSummary(Database $db): array
    {
        $grossRevenue = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'");
        $refundsTotal = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE status = 'processed'");
        $expensesTotal = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM expenses");
        $commissionsTotal = (float) $db->fetchColumn("SELECT COALESCE(SUM(commission_amount), 0) FROM commissions WHERE status IN ('approved','paid')");
        $netRevenue = $grossRevenue - $refundsTotal;
        $netProfit = $netRevenue - $expensesTotal - $commissionsTotal;

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $mk = date('Y-m', strtotime("-{$i} months"));
            $rev = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') = :mk", ['mk' => $mk]);
            $ref = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE status = 'processed' AND DATE_FORMAT(COALESCE(processed_at, created_at), '%Y-%m') = :mk", ['mk' => $mk]);
            $exp = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = :mk", ['mk' => $mk]);
            $monthly[] = [
                'month' => date('M Y', strtotime("-{$i} months")),
                'revenue' => $rev,
                'refunds' => $ref,
                'expenses' => $exp,
                'net' => ($rev - $ref - $exp),
            ];
        }

        return [
            'gross_revenue' => $grossRevenue,
            'refunds_total' => $refundsTotal,
            'net_revenue' => $netRevenue,
            'expenses_total' => $expensesTotal,
            'commissions_total' => $commissionsTotal,
            'net_profit' => $netProfit,
            'monthly' => $monthly,
        ];
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $summary = $this->gatherSummary($db);

        $expenses = $db->fetchAll("SELECT e.*, b.booking_reference FROM expenses e LEFT JOIN bookings b ON b.id = e.booking_id ORDER BY e.expense_date DESC LIMIT 25");
        $payments = $db->fetchAll(
            "SELECT p.*, b.booking_reference, CONCAT(u.first_name,' ',u.last_name) AS customer_name
             FROM payments p INNER JOIN bookings b ON b.id = p.booking_id
             INNER JOIN customers c ON c.id = p.customer_id INNER JOIN users u ON u.id = c.user_id
             ORDER BY p.created_at DESC LIMIT 20"
        );
        $refunds = $db->fetchAll(
            "SELECT r.*, b.booking_reference, CONCAT(u.first_name,' ',u.last_name) AS customer_name
             FROM refunds r INNER JOIN bookings b ON b.id = r.booking_id
             INNER JOIN customers c ON c.id = r.customer_id INNER JOIN users u ON u.id = c.user_id
             ORDER BY r.created_at DESC LIMIT 20"
        );
        $commissions = $db->fetchAll(
            "SELECT cm.*, b.booking_reference, CONCAT(u.first_name,' ',u.last_name) AS employee_name
             FROM commissions cm INNER JOIN employees emp ON emp.id = cm.employee_id INNER JOIN users u ON u.id = emp.user_id
             INNER JOIN bookings b ON b.id = cm.booking_id ORDER BY cm.created_at DESC LIMIT 20"
        );
        $bookings = $db->fetchAll("SELECT id, booking_reference FROM bookings WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 30");

        View::render('admin/finance/index', [
            'pageTitle' => 'Finance, Profit & Loss & Reports',
            'portal' => 'admin',
            'summary' => $summary,
            'expenses' => $expenses,
            'payments' => $payments,
            'refunds' => $refunds,
            'commissions' => $commissions,
            'bookings' => $bookings,
        ], 'dashboard');
    }

    public function storeExpense(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'title' => 'required|min:2|max:180',
            'category' => 'required|in:vendor_payout,accommodation,transport,marketing,payroll,operations,software,other',
            'amount' => 'required|numeric|min_val:0.01',
            'expense_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/finance');
        }

        $db = Database::getInstance();
        $ref = 'EXP-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $db->insert('expenses', [
            'expense_reference' => $ref,
            'category' => $clean['category'],
            'title' => $clean['title'],
            'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
            'amount' => (float) $clean['amount'],
            'expense_date' => $clean['expense_date'],
            'vendor_name' => trim((string) ($_POST['vendor_name'] ?? '')) ?: null,
            'booking_id' => !empty($_POST['booking_id']) ? (int) $_POST['booking_id'] : null,
            'recorded_by' => (int) Auth::id(),
        ]);

        AuditService::log('finance', 'create_expense', "Recorded expense {$ref}: {$clean['title']}", 'expense', null, (int) Auth::id());
        flash('success', "Expense {$ref} recorded.");
        redirect('/admin/finance');
    }

    public function deleteExpense(array $params): void
    {
        Database::getInstance()->delete('expenses', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Expense deleted.');
        redirect('/admin/finance');
    }

    public function updateCommission(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $status = $_POST['status'] ?? 'approved';
        if (!in_array($status, ['pending', 'approved', 'paid'], true)) {
            $status = 'approved';
        }
        Database::getInstance()->update('commissions', [
            'status' => $status,
            'paid_at' => $status === 'paid' ? date('Y-m-d H:i:s') : null,
        ], 'id = :id', ['id' => $id]);

        AuditService::log('finance', 'update_commission', "Commission #{$id} marked {$status}", 'commission', $id, (int) Auth::id());
        flash('success', 'Commission updated.');
        redirect('/admin/finance');
    }

    public function exportCsv(): void
    {
        $db = Database::getInstance();
        $summary = $this->gatherSummary($db);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="financial-pnl-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Metric', 'Amount']);
        fputcsv($out, ['Gross Revenue', $summary['gross_revenue']]);
        fputcsv($out, ['Processed Refunds', $summary['refunds_total']]);
        fputcsv($out, ['Net Revenue', $summary['net_revenue']]);
        fputcsv($out, ['Operating Expenses', $summary['expenses_total']]);
        fputcsv($out, ['Staff Commissions', $summary['commissions_total']]);
        fputcsv($out, ['Net Profit / Loss', $summary['net_profit']]);
        fputcsv($out, []);
        fputcsv($out, ['Month', 'Gross Revenue', 'Refunds', 'Expenses', 'Net Contribution']);
        foreach ($summary['monthly'] as $m) {
            fputcsv($out, [$m['month'], $m['revenue'], $m['refunds'], $m['expenses'], $m['net']]);
        }
        fclose($out);
        exit;
    }

    public function exportPdf(): void
    {
        $db = Database::getInstance();
        $summary = $this->gatherSummary($db);
        $brand = BrandingService::getAll();

        $rows = [];
        foreach ($summary['monthly'] as $m) {
            $rows[] = [
                $m['month'],
                money($m['revenue']),
                money($m['refunds']),
                money($m['expenses']),
                money($m['net']),
            ];
        }

        $pdf = (new PdfGenerator())->buildTableReportPdf(
            'Financial Profit & Loss Report',
            $brand['company_name'],
            $brand['primary_color'],
            ['Month', 'Gross Revenue', 'Refunds', 'Expenses', 'Net P&L'],
            $rows,
            [
                'Gross Revenue: ' . money($summary['gross_revenue']) . '  |  Refunds: ' . money($summary['refunds_total']) . '  |  Net Revenue: ' . money($summary['net_revenue']),
                'Expenses: ' . money($summary['expenses_total']) . '  |  Commissions: ' . money($summary['commissions_total']) . '  |  Net Profit: ' . money($summary['net_profit']),
            ]
        );

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="financial-report-' . date('Ymd') . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
