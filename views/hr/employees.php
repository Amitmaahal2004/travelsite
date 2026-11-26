<div class="dash-grid-2" style="grid-template-columns:1.45fr 0.95fr;">
    <div class="panel-card">
        <div class="panel-head">
            <h2>Employee Directory</h2>
        </div>

        <form method="GET" action="<?= url($prefix . '/employees') ?>" class="filter-bar">
            <input type="text" name="q" value="<?= e($filters['search']) ?>" class="dash-input" placeholder="Search name, email, code...">
            <select name="department_id" class="dash-select">
                <option value="">All Departments</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= (string) $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="dash-select">
                <option value="">All Statuses</option>
                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="on_leave" <?= $filters['status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                <option value="terminated" <?= $filters['status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
            </select>
            <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Filter</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Employee</th><th>Department &amp; Role</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($employees as $e): ?>
                        <tr>
                            <td><strong><?= e($e['employee_code']) ?></strong></td>
                            <td>
                                <strong><?= e($e['first_name'] . ' ' . $e['last_name']) ?></strong><br>
                                <small style="color:var(--dash-muted);"><?= e($e['email']) ?></small>
                            </td>
                            <td>
                                <?= e($e['designation']) ?><br>
                                <small style="color:var(--dash-muted);"><?= e($e['department_name'] ?: 'General') ?> &bull; <?= e($e['role_name']) ?></small>
                            </td>
                            <td><span class="badge-pill <?= status_badge_class($e['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $e['status']))) ?></span></td>
                            <td><a href="<?= url($prefix . '/employees/' . (int) $e['id']) ?>" class="btn-brand btn-brand-outline btn-sm-pill">Manage</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $baseUrl = $prefix . '/employees'; require BASE_PATH . '/views/partials/pagination.php'; ?>
    </div>

    <?php if (can('employees.create')): ?>
    <div class="panel-card">
        <h3>Add New Employee</h3>
        <form method="POST" action="<?= url($prefix . '/employees/create') ?>">
            <?= \Core\Csrf::field() ?>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">First Name *</label>
                    <input type="text" name="first_name" required class="dash-input">
                </div>
                <div>
                    <label class="form-label-editorial">Last Name *</label>
                    <input type="text" name="last_name" required class="dash-input">
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Email Address *</label>
                <input type="email" name="email" required class="dash-input">
            </div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Phone</label>
                    <input type="text" name="phone" class="dash-input">
                </div>
                <div>
                    <label class="form-label-editorial">Join Date *</label>
                    <input type="date" name="join_date" required class="dash-input" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Department</label>
                    <select name="department_id" class="dash-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label-editorial">Portal Role</label>
                    <select name="role_id" class="dash-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['id'] ?>" <?= $r['slug'] === 'employee' ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Designation *</label>
                    <input type="text" name="designation" required class="dash-input" placeholder="e.g. Travel Concierge">
                </div>
                <div>
                    <label class="form-label-editorial">Type *</label>
                    <select name="employment_type" class="dash-select">
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                    </select>
                </div>
            </div>
            <div class="form-grid-2" style="margin-bottom:12px;">
                <div>
                    <label class="form-label-editorial">Base Salary</label>
                    <input type="number" step="0.01" name="base_salary" class="dash-input" value="4500">
                </div>
                <div>
                    <label class="form-label-editorial">Commission %</label>
                    <input type="number" step="0.1" name="commission_rate" class="dash-input" value="5.0">
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label class="form-label-editorial">Initial Password *</label>
                <input type="password" name="password" required minlength="8" class="dash-input" placeholder="Min 8 characters">
            </div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Create Employee Account</button>
        </form>
    </div>
    <?php endif; ?>
</div>
