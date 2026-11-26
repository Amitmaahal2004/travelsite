<div style="margin-bottom:14px;"><a href="<?= url('/customer/support') ?>" class="btn-brand btn-brand-outline btn-sm-pill">&larr; Back to Tickets</a></div>

<div class="panel-card">
    <div class="panel-head">
        <div>
            <span class="section-eyebrow" style="margin-bottom:2px;"><?= e($ticket['ticket_number']) ?> &bull; <?= e($ticket['category']) ?></span>
            <h2 style="margin:0;"><?= e($ticket['subject']) ?></h2>
        </div>
        <span class="badge-pill <?= status_badge_class($ticket['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $ticket['status']))) ?></span>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:22px;">
        <?php foreach ($replies as $rep): ?>
            <div style="padding:14px 16px;border-radius:12px;background:<?= $rep['is_staff'] ? 'rgba(var(--brand-accent-rgb), 0.12)' : 'var(--dash-surface-alt)' ?>;border:1px solid var(--dash-border);">
                <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px;">
                    <strong><?= e($rep['author_name']) ?> <?= $rep['is_staff'] ? '<span class="badge-pill badge-info" style="font-size:0.65rem;padding:2px 7px;">Concierge</span>' : '' ?></strong>
                    <span style="color:var(--dash-muted);"><?= e($rep['created_at']) ?></span>
                </div>
                <div><?= nl2br(e($rep['message'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="<?= url('/customer/support/' . (int) $ticket['id'] . '/reply') ?>">
        <?= \Core\Csrf::field() ?>
        <label class="form-label-editorial">Add Reply</label>
        <textarea name="message" rows="3" required class="dash-textarea" style="margin-bottom:12px;" placeholder="Write your message..."></textarea>
        <button type="submit" class="btn-brand btn-brand-primary">Send Reply</button>
    </form>
</div>
