<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Database;
use Core\View;

class DashboardController
{
    public function index(): void
    {
        $db = Database::getInstance();

        $kpis = [
            'total_bookings' => (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE deleted_at IS NULL"),
            'confirmed_bookings' => (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('Confirmed','Partially Paid','Paid','Completed') AND deleted_at IS NULL"),
            'cancelled_bookings' => (int) $db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('Cancelled','Refunded') AND deleted_at IS NULL"),
            'total_revenue' => (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")
                - (float) $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE status = 'processed'"),
            'monthly_revenue' => (float) $db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND MONTH(COALESCE(paid_at, created_at)) = MONTH(CURDATE()) AND YEAR(COALESCE(paid_at, created_at)) = YEAR(CURDATE())"
            ),
            'pending_payments' => (float) $db->fetchColumn(
                "SELECT COALESCE(SUM(GREATEST(0, total_amount - paid_amount)), 0) FROM bookings WHERE booking_status NOT IN ('Cancelled','Refunded') AND deleted_at IS NULL"
            ),
            'total_customers' => (int) $db->fetchColumn("SELECT COUNT(*) FROM customers WHERE deleted_at IS NULL"),
            'total_employees' => (int) $db->fetchColumn("SELECT COUNT(*) FROM employees WHERE deleted_at IS NULL"),
            'total_hr_users' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM users u INNER JOIN user_roles ur ON ur.user_id = u.id INNER JOIN roles r ON r.id = ur.role_id WHERE r.slug = 'hr' AND u.deleted_at IS NULL"
            ),
            'open_enquiries' => (int) $db->fetchColumn("SELECT COUNT(*) FROM enquiries WHERE status IN ('new','in_progress')"),
        ];

        // Monthly revenue & booking trends (last 6 months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} months"));
            $label = date('M Y', strtotime("-{$i} months"));
            $rev = (float) $db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') = :mk",
                ['mk' => $monthKey]
            );
            $cnt = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = :mk AND deleted_at IS NULL",
                ['mk' => $monthKey]
            );
            $trends[] = ['label' => $label, 'revenue' => $rev, 'bookings' => $cnt];
        }

        $statusCounts = $db->fetchAll(
            "SELECT booking_status, COUNT(*) AS cnt FROM bookings WHERE deleted_at IS NULL GROUP BY booking_status ORDER BY cnt DESC"
        );

        $recentBookings = $db->fetchAll(
            "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, p.title AS package_title
             FROM bookings b
             INNER JOIN customers c ON c.id = b.customer_id
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN travel_packages p ON p.id = b.package_id
             WHERE b.deleted_at IS NULL
             ORDER BY b.created_at DESC LIMIT 6"
        );

        $recentLogs = $db->fetchAll(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC LIMIT 8"
        );

        View::render('admin/dashboard', [
            'pageTitle' => 'Executive Dashboard',
            'portal' => 'admin',
            'kpis' => $kpis,
            'trends' => $trends,
            'statusCounts' => $statusCounts,
            'recentBookings' => $recentBookings,
            'recentLogs' => $recentLogs,
        ], 'dashboard');
    }

    public function activityLogs(): void
    {
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $module = trim((string) ($_GET['module'] ?? ''));
        $where = '1=1';
        $params = [];
        if ($module !== '') {
            $where = 'a.module = :mod';
            $params['mod'] = $module;
        }
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM activity_logs a WHERE {$where}", $params);
        $meta = paginate_meta($total, 25, $page);

        $logs = $db->fetchAll(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email
             FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id
             WHERE {$where}
             ORDER BY a.created_at DESC
             LIMIT {$meta['per_page']} OFFSET {$meta['offset']}",
            $params
        );
        $modules = $db->fetchAll("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC");

        View::render('admin/activity-logs', [
            'pageTitle' => 'System Activity Logs',
            'portal' => 'admin',
            'logs' => $logs,
            'meta' => $meta,
            'modules' => $modules,
            'activeModule' => $module,
        ], 'dashboard');
    }
}
