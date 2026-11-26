<section class="page-hero">
    <div class="container-editorial">
        <span class="section-eyebrow">Traveller Guidance</span>
        <h1>Frequently Asked Questions</h1>
        <p style="max-width:600px;opacity:0.86;">Clear answers on curated itineraries, booking milestones, payments, and dedicated support.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial" style="max-width:860px;">
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($faqs as $idx => $faq): ?>
                <div class="surface-card" style="padding:20px 24px;">
                    <button type="button" data-faq-trigger aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>"
                            style="width:100%;background:none;border:none;text-align:left;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:0;">
                        <div>
                            <small style="display:block;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--brand-accent);font-weight:700;margin-bottom:4px;"><?= e($faq['category']) ?></small>
                            <strong style="font-family:var(--font-display);font-size:1.4rem;color:var(--brand-primary);"><?= e($faq['question']) ?></strong>
                        </div>
                        <span style="color:var(--brand-primary);"><?= svg_icon('chevron-down', 18) ?></span>
                    </button>
                    <div style="<?= $idx === 0 ? 'display:block;' : 'display:none;' ?>margin-top:14px;padding-top:14px;border-top:1px solid var(--border-hairline);color:var(--ink-muted);">
                        <?= nl2br(e($faq['answer'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
