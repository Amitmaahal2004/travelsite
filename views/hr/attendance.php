<div class="panel-card">
    <div class="panel-head">
        <h2>Attendance Register — <?= e($date) ?></h2>
        <form method="GET" action="<?= url($prefix . '/attendance') ?>" class="filter-bar" style="margin:0;">
            <input type="date" name="date" value="<?= e($date) ?>" class="dash-input">
            <select name="department_id" class="dash-select">
                <option value="">All Departments</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= (int) $deptId === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Load Date</button>
        </form>
    </div>

    <form method="POST" action="<?= url($prefix . '/attendance/save') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="attendance_date" value="<?= e($date) ?>">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Employee</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php foreach ($records as $r):
                        $eid = (int) $r['employee_id'];
                        $st = $r['attendance_status'] ?? 'present';
                    ?>
                        <tr>
                            <td><strong><?= e($r['employee_code']) ?></strong></td>
                            <td><?= e($r['full_name']) ?><br><small style="color:var(--dash-muted);"><?= e($r['department_name']) ?></small></td>
                            <td>
                                <select name="status[<?= $eid ?>]" class="dash-select">
                                    <?php foreach (['present','remote','late','half_day','on_leave','absent'] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= $st === $opt ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $opt)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="time" name="check_in[<?= $eid ?>]" value="<?= e(substr((string) ($r['check_in'] ?? '09:00'), 0, 5)) ?>" class="dash-input"></td>
                            <td><input type="time" name="check_out[<?= $eid ?>]" value="<?= e(substr((string) ($r['check_out'] ?? '17:30'), 0, 5)) ?>" class="dash-input"></td>
                            <td><input type="text" name="notes[<?= $eid ?>]" value="<?= e($r['notes']) ?>" class="dash-input" placeholder="Remarks"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:18px;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-brand btn-brand-primary">Save Attendance Register</button>
        </div>
    </form>
</div>
