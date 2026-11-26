<?php
$b = $branding;
$socials = $b['social_links'] ?? [];
?>
<div class="panel-card">
    <div class="panel-head">
        <div>
            <h2>Global White-Label Branding Studio</h2>
            <p style="margin:4px 0 0;color:var(--dash-muted);font-size:0.9rem;">
                Updates here propagate automatically across the public website, all 4 dashboards, auth pages, email templates, SEO titles, favicon, and PDF invoices.
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (!empty($b['has_draft'])): ?>
                <form method="POST" action="<?= url('/admin/branding/discard-preview') ?>" style="margin:0;">
                    <?= \Core\Csrf::field() ?>
                    <button type="submit" class="btn-brand btn-brand-outline btn-sm-pill">Discard Draft Preview</button>
                </form>
            <?php endif; ?>
            <form method="POST" action="<?= url('/admin/branding/reset') ?>" style="margin:0;">
                <?= \Core\Csrf::field() ?>
                <button type="submit" data-confirm="Reset all branding fields to neutral white-label defaults?" class="btn-brand btn-brand-outline btn-sm-pill">Reset Fallback</button>
            </form>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/branding/save') ?>" enctype="multipart/form-data">
        <?= \Core\Csrf::field() ?>

        <h3 style="margin:14px 0 12px;">1. Identity &amp; Copy</h3>
        <div class="form-grid-2" style="margin-bottom:18px;">
            <div>
                <label class="form-label-editorial">Website Name *</label>
                <input type="text" name="website_name" required class="dash-input" value="<?= e($b['website_name']) ?>">
            </div>
            <div>
                <label class="form-label-editorial">Company / Legal Name *</label>
                <input type="text" name="company_name" required class="dash-input" value="<?= e($b['company_name']) ?>">
            </div>
            <div>
                <label class="form-label-editorial">Website Tagline</label>
                <input type="text" name="website_tagline" class="dash-input" value="<?= e($b['website_tagline']) ?>">
            </div>
            <div>
                <label class="form-label-editorial">Primary Contact Email *</label>
                <input type="email" name="contact_email" required class="dash-input" value="<?= e($b['contact_email']) ?>">
            </div>
            <div>
                <label class="form-label-editorial">Contact Phone Number</label>
                <input type="text" name="contact_number" class="dash-input" value="<?= e($b['contact_number']) ?>">
            </div>
            <div>
                <label class="form-label-editorial">Company Address</label>
                <input type="text" name="company_address" class="dash-input" value="<?= e($b['company_address']) ?>">
            </div>
        </div>
        <div style="margin-bottom:22px;">
            <label class="form-label-editorial">Company Description</label>
            <textarea name="company_description" rows="3" class="dash-textarea"><?= e($b['company_description']) ?></textarea>
        </div>

        <h3 style="margin:18px 0 12px;">2. Theme Colors (Dynamic CSS Variables)</h3>
        <div class="form-grid-3" style="margin-bottom:24px;">
            <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                <label class="form-label-editorial">Primary Color</label>
                <div style="display:flex;align-items:center;gap:12px;">
                    <input type="color" name="primary_color" value="<?= e($b['primary_color']) ?>" data-color-sync="--brand-primary" style="width:52px;height:38px;border:none;background:none;cursor:pointer;">
                    <strong data-color-hex="primary_color"><?= e($b['primary_color']) ?></strong>
                </div>
            </div>
            <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                <label class="form-label-editorial">Secondary Color</label>
                <div style="display:flex;align-items:center;gap:12px;">
                    <input type="color" name="secondary_color" value="<?= e($b['secondary_color']) ?>" data-color-sync="--brand-secondary" style="width:52px;height:38px;border:none;background:none;cursor:pointer;">
                    <strong data-color-hex="secondary_color"><?= e($b['secondary_color']) ?></strong>
                </div>
            </div>
            <div style="padding:14px;border-radius:10px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                <label class="form-label-editorial">Accent Color</label>
                <div style="display:flex;align-items:center;gap:12px;">
                    <input type="color" name="accent_color" value="<?= e($b['accent_color']) ?>" data-color-sync="--brand-accent" style="width:52px;height:38px;border:none;background:none;cursor:pointer;">
                    <strong data-color-hex="accent_color"><?= e($b['accent_color']) ?></strong>
                </div>
            </div>
        </div>

        <h3 style="margin:18px 0 12px;">3. Brand Logos &amp; Favicon (PNG, JPG, WEBP, SVG, ICO)</h3>
        <div class="form-grid-3" style="margin-bottom:24px;">
            <?php
            $logoInputs = [
                'primary_logo' => ['Primary Logo', brand_logo('primary'), '#FAF7F2'],
                'secondary_logo' => ['Secondary Logo', brand_logo('secondary'), '#FAF7F2'],
                'light_logo' => ['Light Theme Logo (Dark BG)', brand_logo('light'), '#0B1D2E'],
                'dark_logo' => ['Dark Theme Logo', brand_logo('dark'), '#FAF7F2'],
                'favicon' => ['Browser Favicon', brand_logo('favicon'), '#FAF7F2'],
            ];
            foreach ($logoInputs as $field => [$label, $currentUrl, $bgPreview]):
            ?>
                <div style="padding:16px;border-radius:12px;background:var(--dash-surface-alt);border:1px solid var(--dash-border);">
                    <label class="form-label-editorial"><?= e($label) ?></label>
                    <div style="height:72px;display:flex;align-items:center;justify-content:center;background:<?= e($bgPreview) ?>;border-radius:8px;margin-bottom:10px;padding:10px;border:1px solid var(--dash-border);">
                        <img id="prev-<?= e($field) ?>" src="<?= e($currentUrl) ?>" alt="<?= e($label) ?>" style="max-height:54px;max-width:100%;object-fit:contain;">
                    </div>
                    <input type="file" name="<?= e($field) ?>" accept="image/*" data-image-preview="#prev-<?= e($field) ?>" class="dash-input" style="font-size:0.8rem;">
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="margin:18px 0 12px;">4. Social Media Links</h3>
        <div class="form-grid-3" style="margin-bottom:26px;">
            <div>
                <label class="form-label-editorial">Instagram URL</label>
                <input type="text" name="social_instagram" class="dash-input" value="<?= e($socials['instagram'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label-editorial">Facebook URL</label>
                <input type="text" name="social_facebook" class="dash-input" value="<?= e($socials['facebook'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label-editorial">X (Twitter) URL</label>
                <input type="text" name="social_twitter" class="dash-input" value="<?= e($socials['twitter'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label-editorial">LinkedIn URL</label>
                <input type="text" name="social_linkedin" class="dash-input" value="<?= e($socials['linkedin'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label-editorial">YouTube URL</label>
                <input type="text" name="social_youtube" class="dash-input" value="<?= e($socials['youtube'] ?? '') ?>">
            </div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--dash-border);">
            <button type="submit" name="submit_mode" value="preview" class="btn-brand btn-brand-outline">Preview Changes</button>
            <button type="submit" name="submit_mode" value="publish" class="btn-brand btn-brand-primary">Publish Changes Platform-Wide</button>
        </div>
    </form>
</div>
