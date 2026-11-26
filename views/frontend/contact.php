<section class="page-hero">
    <img src="<?= e(upload_url('destinations/kyoto-sanctuary.jpg')) ?>" alt="Contact Concierge" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Concierge Desk</span>
        <h1>Contact Us</h1>
        <p style="max-width:600px;opacity:0.86;">Speak directly with our regional specialists regarding reservations, press enquiries, or private travel coordination.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
        <div class="split-contact">
            <div class="surface-card band-navy" style="color:#FAF7F2;">
                <span class="section-eyebrow">Direct Channels</span>
                <h2 style="color:#959595;margin-bottom:20px;"><?= e(brand('company_name')) ?></h2>
                <div style="display:flex;flex-direction:column;gap:18px;font-size:0.95rem;">
                    <div>
                        <small style="color:var(--brand-accent);text-transform:uppercase;letter-spacing:0.12em;">Email Concierge</small>
                        <div><?= e(brand('contact_email')) ?></div>
                    </div>
                    <div>
                        <small style="color:var(--brand-accent);text-transform:uppercase;letter-spacing:0.12em;">Telephone</small>
                        <div><?= e(brand('contact_number')) ?></div>
                    </div>
                    <div>
                        <small style="color:var(--brand-accent);text-transform:uppercase;letter-spacing:0.12em;">Headquarters</small>
                        <div><?= e(brand('company_address')) ?></div>
                    </div>
                </div>
            </div>

            <div class="surface-card">
                <h2 style="margin-bottom:18px;">Send a Message</h2>
                <form method="POST" action="<?= url('/contact') ?>">
                    <?= \Core\Csrf::field() ?>
                    <div class="form-row-2">
                        <div>
                            <label class="form-label-editorial">Full Name *</label>
                            <input type="text" name="full_name" required class="form-control-editorial"
                                   value="<?= e(old('full_name', $user ? ($user['first_name'] . ' ' . $user['last_name']) : '')) ?>">
                        </div>
                        <div>
                            <label class="form-label-editorial">Email *</label>
                            <input type="email" name="email" required class="form-control-editorial" value="<?= e(old('email', $user['email'] ?? '')) ?>">
                        </div>
                        <div>
                            <label class="form-label-editorial">Phone</label>
                            <input type="text" name="phone" class="form-control-editorial" value="<?= e(old('phone', $user['phone'] ?? '')) ?>">
                        </div>
                        <div>
                            <label class="form-label-editorial">Subject *</label>
                            <input type="text" name="subject" required class="form-control-editorial" value="<?= e(old('subject')) ?>">
                        </div>
                    </div>
                    <div style="margin-top:16px;">
                        <label class="form-label-editorial">Message *</label>
                        <textarea name="message" rows="5" required class="form-control-editorial"><?= e(old('message')) ?></textarea>
                    </div>
                    <div style="margin-top:20px;">
                        <button type="submit" class="btn-brand btn-brand-primary">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
