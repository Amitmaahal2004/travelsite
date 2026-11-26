<?php
$pkgRate = $selectedPkg ? (float) ($selectedPkg['discount_price'] ?: $selectedPkg['base_price']) : 0;
?>
<section class="page-hero">
    <img src="<?= e(upload_url($selectedPkg['featured_image'] ?? 'destinations/hero-fjord.jpg')) ?>" alt="Reserve Journey" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Reservation Desk</span>
        <h1>Complete Your Booking Request</h1>
        <p style="max-width:620px;opacity:0.86;">Receive an immediate booking reference, invoice schedule, and dedicated specialist coordination.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
        <form method="POST" action="<?= url('/book') ?>" class="split-editorial">
            <?= \Core\Csrf::field() ?>

            <div class="surface-card">
                <h2 style="margin-bottom:18px;">1. Journey &amp; Travel Dates</h2>
                <div class="form-row-2">
                    <div class="span-2-desktop">
                        <label class="form-label-editorial">Select Travel Package *</label>
                        <select id="book-package" name="package_id" required class="form-control-editorial">
                            <option value="">Choose a curated package...</option>
                            <?php foreach ($packages as $p):
                                $eff = (float) ($p['discount_price'] ?: $p['base_price']);
                                $isSel = $selectedPkg && (int) $selectedPkg['id'] === (int) $p['id'];
                            ?>
                                <option value="<?= (int) $p['id'] ?>"
                                        data-price="<?= e($eff) ?>"
                                        data-days="<?= (int) $p['duration_days'] ?>"
                                        <?= $isSel ? 'selected' : '' ?>>
                                    <?= e($p['title']) ?> — <?= e(money($eff)) ?> / guest (<?= (int) $p['duration_days'] ?> Days)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-editorial">Departure Date *</label>
                        <input type="date" name="travel_date" min="<?= date('Y-m-d') ?>" required class="form-control-editorial"
                               value="<?= e(old('travel_date', date('Y-m-d', strtotime('+30 days')))) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Preferred Payment Method *</label>
                        <select name="payment_method" required class="form-control-editorial">
                            <option value="online_portal">Online Portal Settlement</option>
                            <option value="bank_transfer">International Bank Wire</option>
                            <option value="card_gateway">Hosted Payment Gateway (External)</option>
                            <option value="upi">UPI / Instant Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-editorial">Adults *</label>
                        <input id="book-adults" type="number" name="adults" min="1" max="20" required class="form-control-editorial" value="<?= e(old('adults', '2')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Children (75% rate)</label>
                        <input id="book-children" type="number" name="children" min="0" max="15" class="form-control-editorial" value="<?= e(old('children', '0')) ?>">
                    </div>
                </div>

                <h2 style="margin:28px 0 14px;">2. Lead Traveller Information</h2>
                <div class="form-row-2">
                    <div>
                        <label class="form-label-editorial">Full Name *</label>
                        <input type="text" name="lead_name" required class="form-control-editorial"
                               value="<?= e(old('lead_name', $user ? ($user['first_name'] . ' ' . $user['last_name']) : '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Email Address *</label>
                        <input type="email" name="lead_email" required class="form-control-editorial"
                               value="<?= e(old('lead_email', $user['email'] ?? '')) ?>" <?= $user ? 'readonly' : '' ?>>
                    </div>
                    <div>
                        <label class="form-label-editorial">Phone Number *</label>
                        <input type="text" name="lead_phone" required class="form-control-editorial"
                               value="<?= e(old('lead_phone', $user['phone'] ?? '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Nationality</label>
                        <input type="text" name="lead_nationality" class="form-control-editorial" value="<?= e(old('lead_nationality')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Passport Number (Optional)</label>
                        <input type="text" name="lead_passport" class="form-control-editorial" value="<?= e(old('lead_passport')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Dietary / Medical Notes</label>
                        <input type="text" name="lead_dietary" class="form-control-editorial" value="<?= e(old('lead_dietary')) ?>">
                    </div>
                    <?php if (!$user): ?>
                        <div class="span-2-desktop" style="background:var(--ivory);padding:14px;border-radius:10px;border:1px solid var(--border-hairline);">
                            <label class="form-label-editorial">Create Customer Portal Password (Optional, min 8 chars)</label>
                            <input type="password" name="account_password" minlength="8" class="form-control-editorial"
                                   placeholder="Leave blank to auto-generate or set password to sign in later">
                            <small style="color:var(--ink-soft);">Already have an account? <a href="<?= url('/login') ?>" style="color:var(--brand-primary);font-weight:600;text-decoration:underline;">Sign in first</a>.</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;">Additional Travellers</h3>
                    <button type="button" id="add-traveller-btn" class="btn-brand btn-brand-outline btn-sm-pill">+ Add Companion</button>
                </div>
                <div id="additional-travellers" style="display:flex;flex-direction:column;gap:12px;margin-top:12px;"></div>

                <div style="margin-top:22px;">
                    <label class="form-label-editorial">Special Requests or Room Preferences</label>
                    <textarea name="special_requests" rows="3" class="form-control-editorial" placeholder="Suite preferences, celebrations, transfers..."><?= e(old('special_requests')) ?></textarea>
                </div>
            </div>

            <aside>
                <div class="surface-card" style="position:sticky;top:100px;">
                    <span class="section-eyebrow">Booking Summary</span>
                    <h3 style="margin-bottom:16px;">Investment Overview</h3>

                    <div style="margin-bottom:16px;">
                        <label class="form-label-editorial">Seasonal Offer Code</label>
                        <div style="display:flex;gap:8px;">
                            <input id="promo-code-input" type="text" name="promo_code" class="form-control-editorial" placeholder="e.g. SOLSTICE10" value="<?= e(old('promo_code')) ?>">
                            <button type="button" id="apply-promo-btn" class="btn-brand btn-brand-outline btn-sm-pill">Apply</button>
                        </div>
                        <small id="promo-feedback" style="display:block;margin-top:6px;color:var(--ink-soft);"></small>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:10px;border-top:1px solid var(--border-hairline);padding-top:14px;font-size:0.93rem;">
                        <div style="display:flex;justify-content:space-between;"><span>Package Subtotal</span><strong id="sum-base">$0.00</strong></div>
                        <div style="display:flex;justify-content:space-between;"><span>Promo Discount</span><strong id="sum-discount" style="color:var(--status-success);">-$0.00</strong></div>
                        <div style="display:flex;justify-content:space-between;"><span>Taxes &amp; Service Fees (8%)</span><strong id="sum-tax">$0.00</strong></div>
                        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border-strong);padding-top:12px;font-size:1.15rem;">
                            <strong>Estimated Total</strong>
                            <strong id="sum-total" style="color:var(--brand-primary);">$0.00</strong>
                        </div>
                    </div>

                    <p style="font-size:0.78rem;color:var(--ink-soft);margin:16px 0;">
                        No payment card details are stored on our servers. You will receive an official booking reference and PDF invoice upon submission.
                    </p>

                    <button type="submit" class="btn-brand btn-brand-primary" style="width:100%;">Submit Booking Request</button>
                </div>
            </aside>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pkgSel = document.getElementById('book-package');
    const adultsEl = document.getElementById('book-adults');
    const childrenEl = document.getElementById('book-children');
    const promoInput = document.getElementById('promo-code-input');
    const promoBtn = document.getElementById('apply-promo-btn');
    const promoMsg = document.getElementById('promo-feedback');
    let activeDiscount = 0;

    function fmt(n) { return '$' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function getBase() {
        const opt = pkgSel.options[pkgSel.selectedIndex];
        const rate = parseFloat(opt?.getAttribute('data-price') || '0');
        const adults = Math.max(1, parseInt(adultsEl.value || '1', 10));
        const children = Math.max(0, parseInt(childrenEl.value || '0', 10));
        return (adults * rate) + (children * rate * 0.75);
    }

    function recalc() {
        const base = getBase();
        const disc = Math.min(base, activeDiscount);
        const taxable = Math.max(0, base - disc);
        const tax = taxable * 0.08;
        const total = taxable + tax;
        document.getElementById('sum-base').textContent = fmt(base);
        document.getElementById('sum-discount').textContent = '-' + fmt(disc);
        document.getElementById('sum-tax').textContent = fmt(tax);
        document.getElementById('sum-total').textContent = fmt(total);
    }

    [pkgSel, adultsEl, childrenEl].forEach((el) => el?.addEventListener('change', recalc));
    [adultsEl, childrenEl].forEach((el) => el?.addEventListener('input', recalc));

    promoBtn?.addEventListener('click', async () => {
        const code = promoInput.value.trim();
        const base = getBase();
        if (!code) return;
        const res = await fetch(`<?= url('/api/promo/verify') ?>?code=${encodeURIComponent(code)}&amount=${base}`);
        const data = await res.json();
        if (data.valid) {
            activeDiscount = parseFloat(data.discount || 0);
            promoMsg.style.color = 'var(--status-success)';
        } else {
            activeDiscount = 0;
            promoMsg.style.color = 'var(--status-danger)';
        }
        promoMsg.textContent = data.message || '';
        recalc();
    });

    document.getElementById('add-traveller-btn')?.addEventListener('click', () => {
        const wrap = document.getElementById('additional-travellers');
        const row = document.createElement('div');
        row.className = 'traveller-row';
        row.innerHTML = `
            <input type="text" name="guest_name[]" class="form-control-editorial" placeholder="Companion full name">
            <input type="number" name="guest_age[]" class="form-control-editorial" placeholder="Age" min="1" max="110">
            <input type="text" name="guest_nationality[]" class="form-control-editorial" placeholder="Nationality">
            <button type="button" onclick="this.parentElement.remove()" class="btn-brand btn-brand-outline btn-sm-pill">&times;</button>
        `;
        wrap.appendChild(row);
    });

    recalc();
});
</script>
