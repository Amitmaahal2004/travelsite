<div class="dash-grid-2" style="grid-template-columns:1.35fr 0.9fr;">
    <div class="panel-card">
        <h2>Employee Document Vault</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Employee</th><th>Title</th><th>Category</th><th>Size</th><th>Download</th></tr></thead>
                <tbody>
                    <?php foreach ($docs as $d): ?>
                        <tr>
                            <td><strong><?= e($d['employee_name']) ?></strong><br><small><?= e($d['employee_code']) ?></small></td>
                            <td><?= e($d['title']) ?></td>
                            <td><span class="badge-pill badge-neutral"><?= e($d['document_type']) ?></span></td>
                            <td><?= round((int) $d['file_size'] / 1024, 1) ?> KB</td>
                            <td><a href="<?= url($prefix . '/documents/' . (int) $d['id'] . '/download') ?>" class="btn-brand btn-brand-outline btn-sm-pill">Download</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($docs)): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--dash-muted);">No documents uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h3>Upload Protected Document</h3>
        <form method="POST" action="<?= url($prefix . '/documents/upload') ?>" enctype="multipart/form-data">
            <?= \Core\Csrf::field() ?>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Employee *</label>
                <select name="employee_id" required class="dash-select">
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int) $e['id'] ?>"><?= e($e['full_name']) ?> (<?= e($e['employee_code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:12px;"><label class="form-label-editorial">Document Title *</label><input type="text" name="title" required class="dash-input" placeholder="e.g., Employment Agreement 2026"></div>
            <div style="margin-bottom:12px;">
                <label class="form-label-editorial">Document Type *</label>
                <select name="document_type" class="dash-select">
                    <option value="Contract">Employment Contract</option>
                    <option value="ID Proof">National ID / Passport</option>
                    <option value="Certification">Travel Certification</option>
                    <option value="Review">Performance Review</option>
                </select>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label-editorial">File (PDF, JPG, PNG, DOC) *</label><input type="file" name="document_file" required class="dash-input"></div>
            <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Upload Securely</button>
        </form>
    </div>
</div>
