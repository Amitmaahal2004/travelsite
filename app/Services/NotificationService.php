<?php
declare(strict_types=1);

namespace App\Services;

use Core\Database;

/**
 * In-App Notification & Branded Email Template Service
 */
class NotificationService
{
    public static function notifyUser(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $db = Database::getInstance();
        $db->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function notifyRole(string $roleSlug, string $type, string $title, string $message, ?string $link = null): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.id
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE r.slug = :slug AND u.status = 'active' AND u.deleted_at IS NULL",
            ['slug' => $roleSlug]
        );
        foreach ($users as $u) {
            self::notifyUser((int) $u['id'], $type, $title, $message, $link);
        }
    }

    public static function unreadForUser(int $userId, int $limit = 8): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY is_read ASC, created_at DESC LIMIT :lim",
            ['uid' => $userId, 'lim' => $limit]
        );
    }

    public static function unreadCount(int $userId): int
    {
        $db = Database::getInstance();
        return (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
    }

    public static function markAsRead(int $notificationId, int $userId): void
    {
        $db = Database::getInstance();
        $db->update(
            'notifications',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'id = :id AND user_id = :uid',
            ['id' => $notificationId, 'uid' => $userId]
        );
    }

    public static function markAllRead(int $userId): void
    {
        $db = Database::getInstance();
        $db->update(
            'notifications',
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            'user_id = :uid AND is_read = 0',
            ['uid' => $userId]
        );
    }

    /**
     * Renders a branded email template from MySQL email_templates, wraps it in the dynamic branding layout,
     * logs it to email_logs & storage/logs/mail.log, and optionally calls PHP mail().
     */
    public static function sendBrandedEmail(string $recipientEmail, string $templateKey, array $variables = []): bool
    {
        $db = Database::getInstance();
        $branding = BrandingService::getAll();

        $template = $db->fetch("SELECT * FROM email_templates WHERE template_key = :key AND is_active = 1 LIMIT 1", ['key' => $templateKey]);
        if (!$template) {
            return false;
        }

        $logoUrl = BrandingService::logoUrl('light');
        if (str_starts_with($logoUrl, '/')) {
            $logoUrl = full_url($logoUrl);
        }

        $mergedVars = array_merge([
            'website_name' => $branding['website_name'],
            'company_name' => $branding['company_name'],
            'website_tagline' => $branding['website_tagline'],
            'contact_email' => $branding['contact_email'],
            'contact_number' => $branding['contact_number'],
            'company_address' => $branding['company_address'],
            'primary_color' => $branding['primary_color'],
            'accent_color' => $branding['accent_color'],
            'logo_url' => $logoUrl,
            'year' => date('Y'),
            'site_url' => full_url('/'),
        ], $variables);

        $subject = (string) $template['subject'];
        $body = (string) $template['body_html'];

        foreach ($mergedVars as $k => $v) {
            $safeVal = e((string) $v);
            $subject = str_replace('{{' . $k . '}}', (string) $v, $subject);
            $body = str_replace('{{' . $k . '}}', $safeVal, $body);
        }

        ob_start();
        $emailSubject = $subject;
        $emailBody = $body;
        $brandData = $mergedVars;
        require BASE_PATH . '/views/emails/layout.php';
        $fullHtml = (string) ob_get_clean();

        $status = 'logged';
        $error = null;
        if (app_config('mail.enable_php_mail', false)) {
            $headers = "MIME-Version: 1.0\r\n"
                     . "Content-type:text/html;charset=UTF-8\r\n"
                     . "From: " . $branding['company_name'] . " <" . $branding['contact_email'] . ">\r\n";
            if (@mail($recipientEmail, $subject, $fullHtml, $headers)) {
                $status = 'sent';
            } else {
                $status = 'failed';
                $error = 'PHP mail() transport unavailable on localhost.';
            }
        }

        $db->insert('email_logs', [
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'body_html' => $fullHtml,
            'template_key' => $templateKey,
            'status' => $status,
            'error_message' => $error,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $logLine = sprintf("[%s] TO: %s | KEY: %s | SUBJECT: %s | STATUS: %s\n", date('Y-m-d H:i:s'), $recipientEmail, $templateKey, $subject, $status);
        @file_put_contents(BASE_PATH . '/storage/logs/mail.log', $logLine, FILE_APPEND | LOCK_EX);

        return true;
    }
}
