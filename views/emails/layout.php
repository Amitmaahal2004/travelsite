<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($emailSubject ?? '') ?></title>
</head>
<body style="margin:0;padding:24px;background:#FAF7F2;font-family:Arial,Helvetica,sans-serif;color:#131B24;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;margin:0 auto;background:#FFFFFF;border-radius:14px;overflow:hidden;border:1px solid #E8DFD1;">
    <tr>
        <td style="background:<?= e($brandData['primary_color'] ?? '#0B1D2E') ?>;padding:28px 32px;color:#FAF7F2;">
            <table width="100%">
                <tr>
                    <td>
                        <strong style="font-size:20px;letter-spacing:1px;font-family:Georgia,serif;"><?= e($brandData['website_name'] ?? '') ?></strong><br>
                        <span style="font-size:12px;opacity:0.8;"><?= e($brandData['website_tagline'] ?? '') ?></span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:32px;font-size:15px;line-height:1.65;color:#1F2937;">
            <?= $emailBody ?? '' ?>
        </td>
    </tr>
    <tr>
        <td style="background:#F5F0E7;padding:20px 32px;font-size:12px;color:#66615A;border-top:1px solid #E8DFD1;">
            <strong><?= e($brandData['company_name'] ?? '') ?></strong><br>
            <?= e($brandData['company_address'] ?? '') ?><br>
            <?= e($brandData['contact_email'] ?? '') ?> &bull; <?= e($brandData['contact_number'] ?? '') ?><br>
            &copy; <?= e($brandData['year'] ?? date('Y')) ?> <?= e($brandData['company_name'] ?? '') ?>
        </td>
    </tr>
</table>
</body>
</html>
