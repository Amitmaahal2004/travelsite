<section class="page-hero">
    <div class="container-editorial">
        <span class="section-eyebrow">Booking Registered</span>
        <h1>Thank You — Reference <?= e($booking['booking_reference']) ?></h1>
        <p style="max-width:620px;opacity:0.86;">Your journey request is now active in our concierge workflow and saved to your Customer Portal.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial" style="max-width:840px;">
        <div class="surface-card">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;padding-bottom:18px;border-bottom:1px solid var(--border-hairline);">
                <div>
                    <small style="color:var(--ink-soft);text-transform:uppercase;letter-spacing:0.1em;">Package</small>
                    <h2 style="margin:4px 0 0;"><?= e($booking['package_title'] ?: 'Custom Travel Package') ?></h2>
                </div>
                <span class="badge-pill <?= status_badge_class($booking['booking_status']) ?>"><?= e($booking['booking_status']) ?></span>
            </div>

            <div class="form-row-2" style="margin:22px 0;font-size:0.94rem;">
                <div><span style="color:var(--ink-soft);">Booking Reference:</span> <strong><?= e($booking['booking_reference']) ?></strong></div>
                <div><span style="color:var(--ink-soft);">Departure Date:</span> <strong><?= e($booking['travel_date']) ?></strong></div>
                <div><span style="color:var(--ink-soft);">Travellers:</span> <strong><?= (int) $booking['total_travellers'] ?> Guest(s)</strong></div>
                <div><span style="color:var(--ink-soft);">Total Amount:</span> <strong><?= e(money($booking['total_amount'], $booking['currency'])) ?></strong></div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:24px;">
                <a href="<?= url('/customer/bookings/' . (int) $booking['id']) ?>" class="btn-brand btn-brand-primary">Manage in Customer Portal</a>
                <a href="<?= url('/customer/invoices/' . (int) $booking['id'] . '/download') ?>" class="btn-brand btn-brand-outline">Download PDF Invoice</a>
                <a href="<?= url('/packages') ?>" class="btn-brand btn-brand-outline">Explore More Voyages</a>
            </div>
        </div>
    </div>
</section>
