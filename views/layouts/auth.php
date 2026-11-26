<?php
$brand = \App\Services\BrandingService::getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'Account') . ' | ' . $brand['website_name']) ?></title>
    <?= \Core\Csrf::meta() ?>
    <link rel="icon" href="<?= e(brand_logo('favicon')) ?>">
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/frontend.css') ?>">
    <style>
        <?= \App\Services\BrandingService::cssVariables() ?>
        .auth-split {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
        }
        .auth-visual {
            position: relative;
            background: var(--brand-primary);
            color: #FAF7F2;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .auth-visual img.bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.42;
        }
        .auth-visual > * { position: relative; z-index: 2; }
        .auth-form-pane {
            background: var(--ivory);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: var(--ivory-pure);
            padding: 36px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-hairline);
            box-shadow: var(--shadow-card);
        }
        @media (max-width: 960px) {
            .auth-split { grid-template-columns: 1fr; }
            .auth-visual { min-height: 240px; padding: 28px; }
        }
    </style>
</head>
<body>
<div class="auth-split">
    <div class="auth-visual">
        <img class="bg" src="<?= e(upload_url('destinations/hero-fjord.jpg')) ?>" alt="<?= e($brand['website_name']) ?>">
        <a href="<?= url('/') ?>" class="brand-lockup">
            <img src="<?= e(brand_logo('light')) ?>" alt="<?= e($brand['website_name']) ?>">
            <div class="brand-wordmark">
                <strong style="color:#FAF7F2;"><?= e($brand['website_name']) ?></strong>
                <span style="color:rgba(250,247,242,0.75);"><?= e($brand['company_name']) ?></span>
            </div>
        </a>
        <div>
            <span class="section-eyebrow"><?= e($brand['website_name']) ?> Portal</span>
            <h1 style="font-size:clamp(2rem,3.4vw,3.1rem);color:#FAF7F2;"><?= e($brand['website_tagline']) ?></h1>
            <p style="color:rgba(250,247,242,0.8);max-width:440px;"><?= e($brand['company_description']) ?></p>
        </div>
        <div style="font-size:0.84rem;opacity:0.76;">
            &larr; <a href="<?= url('/') ?>" style="text-decoration:underline;">Return to Public Website</a>
        </div>
    </div>

    <div class="auth-form-pane">
        <div class="auth-card">
            <?php require BASE_PATH . '/views/partials/flash.php'; ?>
            <?= $content ?>
        </div>
    </div>
</div>
</body>
</html>
