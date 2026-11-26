<section class="page-hero">
    <img src="<?= e(upload_url('media/custom-atelier.jpg')) ?>" alt="Custom Tour Enquiry" class="page-hero-bg">
    <div class="container-editorial">
        <span class="section-eyebrow">Private Travel Atelier</span>
        <h1>Custom Tour Enquiry</h1>
        <p style="max-width:620px;opacity:0.86;">Share your ideal horizon, pace, and travel companions. A dedicated Journey Architect will craft a tailored itinerary proposal.</p>
    </div>
</section>

<section class="section-pad-sm">
    <div class="container-editorial" style="max-width:920px;">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
        <div class="surface-card">
            <form method="POST" action="<?= url('/custom-enquiry') ?>">
                <?= \Core\Csrf::field() ?>
                <div class="form-row-2">
                    <div>
                        <label class="form-label-editorial">Full Name *</label>
                        <input type="text" name="full_name" required class="form-control-editorial"
                               value="<?= e(old('full_name', $user ? ($user['first_name'] . ' ' . $user['last_name']) : '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Email Address *</label>
                        <input type="email" name="email" required class="form-control-editorial"
                               value="<?= e(old('email', $user['email'] ?? '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Phone / WhatsApp</label>
                        <input type="text" name="phone" class="form-control-editorial"
                               value="<?= e(old('phone', $user['phone'] ?? '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Preferred Destination / Region *</label>
                        <input type="text" name="destination_interest" required class="form-control-editorial"
                               placeholder="e.g., Kyoto, Serengeti, Norwegian Fjords"
                               value="<?= e(old('destination_interest', $_GET['destination'] ?? '')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Preferred Departure Date</label>
                        <input type="date" name="travel_date" class="form-control-editorial" value="<?= e(old('travel_date')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Number of Travellers</label>
                        <input type="number" min="1" max="40" name="travellers_count" class="form-control-editorial" value="<?= e(old('travellers_count', '2')) ?>">
                    </div>
                    <div>
                        <label class="form-label-editorial">Estimated Budget Range</label>
                        <select name="budget_range" class="form-control-editorial">
                            <option value="">Flexible / To Be Discussed</option>
                            <option value="$3,000 - $6,000">$3,000 - $6,000 per guest</option>
                            <option value="$6,000 - $12,000">$6,000 - $12,000 per guest</option>
                            <option value="$12,000 - $25,000">$12,000 - $25,000 per guest</option>
                            <option value="$25,000+">$25,000+ ultra-bespoke</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-editorial">Preferred Style</label>
                        <select class="form-control-editorial" onchange="document.getElementById('enquiry-msg').value = 'Travel style: ' + this.value + '\n' + document.getElementById('enquiry-msg').value;">
                            <option value="">Select Travel Style</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['name']) ?>" <?= $preselectedStyle === $cat['name'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <label class="form-label-editorial">Vision, Pace &amp; Special Notes *</label>
                    <textarea id="enquiry-msg" name="message" rows="5" required class="form-control-editorial"
                              placeholder="Tell us about accommodation preferences, celebrations, dietary requests, or experiences you dream of..."><?= e(old('message', $preselectedStyle ? "Preferred travel style: {$preselectedStyle}\n" : '')) ?></textarea>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn-brand btn-brand-primary">Submit Custom Enquiry</button>
                </div>
            </form>
        </div>
    </div>
</section>
