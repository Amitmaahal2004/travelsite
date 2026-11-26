<div class="panel-card">
    <h2>Branded Email Templates</h2>
    <p style="font-size:0.88rem;color:var(--dash-muted);margin-bottom:18px;">
        Every email automatically embeds your dynamic Website Name, Company Name, Logo, Brand Colors, and Contact Details from Website Branding settings.
    </p>
    <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach ($templates as $tpl): ?>
            <form method="POST" action="<?= url('/admin/email-templates/' . (int) $tpl['id'] . '/update') ?>"
                  style="padding:16px;border-radius:12px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                <?= \Core\Csrf::field() ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div><strong><?= e($tpl['name']) ?></strong> <code style="margin-left:8px;"><?= e($tpl['template_key']) ?></code></div>
                    <label style="font-size:0.84rem;"><input type="checkbox" name="is_active" value="1" <?= $tpl['is_active'] ? 'checked' : '' ?>> Active</label>
                </div>
                <div style="font-size:0.78rem;color:var(--dash-muted);margin-bottom:10px;">Available variables: <?= e($tpl['variables_hint']) ?></div>
                <input type="text" name="subject" required class="dash-input" style="margin-bottom:10px;" value="<?= e($tpl['subject']) ?>">
                <textarea name="body_html" rows="4" required class="dash-textarea" style="margin-bottom:10px;"><?= e($tpl['body_html']) ?></textarea>
                <button type="submit" class="btn-brand btn-brand-primary btn-sm-pill">Save Template</button>
            </form>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel-card">
    <h3>Recent Email Dispatch Log</h3>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Sent At</th><th>Recipient</th><th>Template</th><th>Subject</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td><?= e($l['sent_at']) ?></td>
                        <td><?= e($l['recipient_email']) ?></td>
                        <td><code><?= e($l['template_key']) ?></code></td>
                        <td><?= e($l['subject']) ?></td>
                        <td><span class="badge-pill badge-info"><?= e(ucfirst($l['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?><tr><td colspan="5" style="color:var(--dash-muted);">No emails dispatched yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
