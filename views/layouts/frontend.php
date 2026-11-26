<?php
require_once BASE_PATH . '/views/partials/icons.php';
$brand = \App\Services\BrandingService::getAll();
$seo = $seo ?? \App\Services\SeoService::forPage($pageKey ?? 'global', $seoOverrides ?? []);
$isSolidHeader = $solidHeader ?? false;
$isPreview = !empty($brand['is_previewing']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['description']) ?>">
    <meta name="keywords" content="<?= e($seo['keywords']) ?>">
    <meta name="robots" content="<?= e($seo['robots']) ?>">
    <meta property="og:title" content="<?= e($seo['title']) ?>">
    <meta property="og:description" content="<?= e($seo['description']) ?>">
    <meta property="og:image" content="<?= e($seo['og_image']) ?>">
    <?= \Core\Csrf::meta() ?>
    <link rel="icon" href="<?= e(brand_logo('favicon')) ?>">
    <link rel="stylesheet" href="<?= asset('css/design-system.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/frontend.css') ?>">
    <style><?= \App\Services\BrandingService::cssVariables() ?></style>
</head>
<body class="<?= $isPreview ? 'has-preview-ribbon' : '' ?>">

<?php if ($isPreview): ?>
    <div class="preview-ribbon">
        <div><strong>Branding Preview Mode Active</strong> — Changes below are visible only to you until published.</div>
        <div style="display:flex;gap:10px;">
            <a href="<?= url('/admin/branding') ?>" class="btn-brand btn-brand-accent btn-sm-pill">Return to Branding Studio</a>
            <form method="POST" action="<?= url('/admin/branding/discard-preview') ?>" style="margin:0;">
                <?= \Core\Csrf::field() ?>
                <button type="submit" class="btn-brand btn-brand-outline-light btn-sm-pill">Exit Preview</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require BASE_PATH . '/views/partials/frontend-header.php'; ?>

<main>
    <?= $content ?>
</main>

<?php require BASE_PATH . '/views/partials/frontend-footer.php'; ?>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
