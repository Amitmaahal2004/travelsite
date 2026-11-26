<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuditService;
use App\Services\BrandingService;
use Core\Auth;
use Core\Uploader;
use Core\Validator;
use Core\View;

class BrandingController
{
    public function index(): void
    {
        $raw = BrandingService::getRawRecord();
        $active = BrandingService::getAll(true);

        View::render('admin/branding', [
            'pageTitle' => 'Website Branding & White-Label Settings',
            'portal' => 'admin',
            'raw' => $raw,
            'branding' => $active,
        ], 'dashboard');
    }

    public function save(): void
    {
        $action = ($_POST['submit_mode'] ?? 'publish') === 'preview' ? 'preview' : 'publish';

        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'website_name' => 'required|min:2|max:120',
            'company_name' => 'required|min:2|max:160',
            'website_tagline' => 'max:255',
            'company_description' => 'max:1500',
            'primary_color' => 'required|hex_color',
            'secondary_color' => 'required|hex_color',
            'accent_color' => 'required|hex_color',
            'contact_email' => 'required|email|max:160',
            'contact_number' => 'max:60',
            'company_address' => 'max:255',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/branding');
        }

        $raw = BrandingService::getRawRecord();
        $draft = is_array($raw['draft_data'] ?? null) ? $raw['draft_data'] : [];
        $base = array_merge($raw, $draft);

        $data = $clean;
        $data['social_links'] = [
            'instagram' => trim((string) ($_POST['social_instagram'] ?? '')),
            'facebook' => trim((string) ($_POST['social_facebook'] ?? '')),
            'twitter' => trim((string) ($_POST['social_twitter'] ?? '')),
            'linkedin' => trim((string) ($_POST['social_linkedin'] ?? '')),
            'youtube' => trim((string) ($_POST['social_youtube'] ?? '')),
        ];

        $logoFields = ['primary_logo', 'secondary_logo', 'light_logo', 'dark_logo', 'favicon'];
        foreach ($logoFields as $field) {
            if (!empty($_FILES[$field]['name'])) {
                $up = Uploader::uploadImage($_FILES[$field], 'branding', true);
                if (!$up['ok']) {
                    flash('error', ucwords(str_replace('_', ' ', $field)) . ': ' . $up['error']);
                    redirect('/admin/branding');
                }
                $data[$field] = $up['path'];
            } else {
                $data[$field] = $base[$field] ?? '';
            }
        }

        $userId = (int) Auth::id();
        if ($action === 'preview') {
            BrandingService::saveDraft($data, $userId);
            AuditService::log('branding', 'preview', 'Saved global branding preview draft', 'branding_settings', 1, $userId);
            flash('info', 'Preview mode activated! Navigate across the website or dashboards to verify changes, then click Publish Changes.');
        } else {
            BrandingService::publish($data, $userId);
            AuditService::log('branding', 'publish', 'Published global branding settings across entire platform', 'branding_settings', 1, $userId);
            flash('success', 'Global branding published! Website, dashboards, emails, and PDF invoices updated immediately.');
        }

        redirect('/admin/branding');
    }

    public function discardPreview(): void
    {
        BrandingService::discardDraft();
        flash('info', 'Branding preview exited. Live published branding restored.');
        redirect('/admin/branding');
    }

    public function resetDefaults(): void
    {
        $userId = (int) Auth::id();
        BrandingService::resetToDefaults($userId);
        AuditService::log('branding', 'reset', 'Reset branding to default white-label fallback', 'branding_settings', 1, $userId);
        flash('success', 'Branding restored to neutral white-label default fallback.');
        redirect('/admin/branding');
    }
}
