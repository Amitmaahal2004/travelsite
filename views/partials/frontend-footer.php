<?php
$cmsModel = new \App\Models\Cms();
$exploreLinks = $cmsModel->menus('footer_explore');
$companyLinks = $cmsModel->menus('footer_company');
$legalLinks = $cmsModel->menus('footer_legal');
$socials = brand('social_links', []);
?>
<footer class="site-footer">
    <div class="container-editorial">
        <div class="footer-grid">
            <div>
                <a href="<?= url('/') ?>" class="brand-lockup" style="margin-bottom:16px;">
                    <img src="<?= e(brand_logo('light')) ?>" alt="<?= e(brand('website_name')) ?>">
                    <div class="brand-wordmark">
                        <strong style="color:#FAF7F2;"><?= e(brand('website_name')) ?></strong>
                        <span style="color:rgba(250,247,242,0.7);"><?= e(brand('website_tagline')) ?></span>
                    </div>
                </a>
                <p style="font-size:0.9rem;color:rgba(250,247,242,0.74);max-width:340px;">
                    <?= e(brand('company_description')) ?>
                </p>
                <div class="social-row">
                    <?php if (!empty($socials['instagram'])): ?>
                        <a href="<?= e($socials['instagram']) ?>" target="_blank" rel="noopener" class="social-btn" aria-label="Instagram">IG</a>
                    <?php endif; ?>
                    <?php if (!empty($socials['facebook'])): ?>
                        <a href="<?= e($socials['facebook']) ?>" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook">FB</a>
                    <?php endif; ?>
                    <?php if (!empty($socials['twitter'])): ?>
                        <a href="<?= e($socials['twitter']) ?>" target="_blank" rel="noopener" class="social-btn" aria-label="X">X</a>
                    <?php endif; ?>
                    <?php if (!empty($socials['linkedin'])): ?>
                        <a href="<?= e($socials['linkedin']) ?>" target="_blank" rel="noopener" class="social-btn" aria-label="LinkedIn">IN</a>
                    <?php endif; ?>
                    <?php if (!empty($socials['youtube'])): ?>
                        <a href="<?= e($socials['youtube']) ?>" target="_blank" rel="noopener" class="social-btn" aria-label="YouTube">YT</a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="footer-heading">Explore</div>
                <ul class="footer-links">
                    <?php foreach ($exploreLinks as $link): ?>
                        <li><a href="<?= url($link['url']) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <div class="footer-heading">Company</div>
                <ul class="footer-links">
                    <?php foreach ($companyLinks as $link): ?>
                        <li><a href="<?= url($link['url']) ?>"><?= e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <div class="footer-heading">Concierge Desk</div>
                <ul class="footer-links" style="font-size:0.9rem;">
                    <li><strong>Email:</strong> <a href="mailto:<?= e(brand('contact_email')) ?>"><?= e(brand('contact_email')) ?></a></li>
                    <li><strong>Telephone:</strong> <a href="tel:<?= e(brand('contact_number')) ?>"><?= e(brand('contact_number')) ?></a></li>
                    <li><strong>Atelier:</strong> <?= e(brand('company_address')) ?></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div>&copy; <?= date('Y') ?> <?= e(brand('company_name')) ?>. All rights reserved.</div>
            <div style="display:flex;gap:20px;">
                <?php foreach ($legalLinks as $link): ?>
                    <a href="<?= url($link['url']) ?>"><?= e($link['label']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</footer>
