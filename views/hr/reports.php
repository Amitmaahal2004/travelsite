<div class="panel-card">
    <h2>Performance &amp; Attendance Summary</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Employee</th><th>Department</th><th>Rating</th><th>Completed Tasks</th><th>Open Tasks</th><th>Present (Month)</th></tr></thead>
            <tbody>
                <?php foreach ($performance as $p): ?>
                    <tr>
                        <td><strong><?= e($p['employee_code']) ?></strong></td>
                        <td><?= e($p['full_name']) ?><br><small style="color:var(--dash-muted);"><?= e($p['designation']) ?></small></td>
                        <td><?= e($p['department_name'] ?: '—') ?></td>
                        <td><span class="badge-pill badge-success"><?= e($p['performance_score']) ?> / 5.0</span></td>
                        <td><?= (int) $p['completed_tasks'] ?></td>
                        <td><?= (int) $p['open_tasks'] ?></td>
                        <td><?= (int) $p['present_days_month'] ?> days</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel-card">
    <h3>Recent Workforce Activity Logs</h3>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>When</th><th>Actor</th><th>Module</th><th>Description</th></tr></thead>
            <tbody>
                <?php foreach ($hrLogs as $log): ?>
                    <tr>
                        <td><?= e($log['created_at']) ?></td>
                        <td><?= e($log['user_name'] ?: 'System') ?></td>
                        <td><span class="badge-pill badge-neutral"><?= e($log['module']) ?></span></td>
                        <td><?= e($log['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
