<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AuditService;
use Core\Auth;
use Core\Database;
use Core\Uploader;
use Core\Validator;
use Core\View;

class WebsiteController
{
    public function homepageSections(): void
    {
        $sections = Database::getInstance()->fetchAll("SELECT * FROM homepage_sections ORDER BY sort_order ASC, id ASC");
        View::render('admin/website/homepage-sections', [
            'pageTitle' => 'Homepage Sections & Hero Content',
            'portal' => 'admin',
            'sections' => $sections,
        ], 'dashboard');
    }

    public function updateHomepageSection(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();
        $sec = $db->fetch("SELECT * FROM homepage_sections WHERE id = :id", ['id' => $id]);
        if (!$sec) {
            redirect('/admin/homepage-sections');
        }

        $bgPath = $sec['background_image'];
        if (!empty($_FILES['background_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['background_image'], 'media', false);
            if ($up['ok']) {
                $bgPath = $up['path'];
            }
        }

        $db->update('homepage_sections', [
            'title' => trim((string) $_POST['title']),
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null,
            'badge_text' => trim((string) ($_POST['badge_text'] ?? '')) ?: null,
            'cta_text' => trim((string) ($_POST['cta_text'] ?? '')) ?: null,
            'cta_link' => trim((string) ($_POST['cta_link'] ?? '')) ?: null,
            'background_image' => $bgPath,
            'sort_order' => (int) ($_POST['sort_order'] ?? $sec['sort_order']),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ], 'id = :id', ['id' => $id]);

        AuditService::log('website', 'update_section', "Updated homepage section {$sec['section_key']}", 'homepage_section', $id, (int) Auth::id());
        flash('success', "Homepage section '{$sec['section_key']}' updated.");
        redirect('/admin/homepage-sections');
    }

    public function pages(): void
    {
        $pages = Database::getInstance()->fetchAll("SELECT * FROM website_pages ORDER BY is_system DESC, id ASC");
        View::render('admin/website/pages', [
            'pageTitle' => 'Website Pages & Legal Copy',
            'portal' => 'admin',
            'pages' => $pages,
        ], 'dashboard');
    }

    public function storePage(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['title' => 'required|min:2|max:160', 'content' => 'required|min:10']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/pages');
        }
        $db = Database::getInstance();
        $slug = slugify($_POST['slug'] ?: $clean['title']);
        if ($db->fetchColumn("SELECT id FROM website_pages WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }
        $db->insert('website_pages', [
            'title' => $clean['title'],
            'slug' => $slug,
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null,
            'content' => $clean['content'],
            'template' => $_POST['template'] ?? 'default',
            'is_system' => 0,
            'is_active' => 1,
        ]);
        flash('success', 'CMS page published at /page/' . $slug);
        redirect('/admin/pages');
    }

    public function updatePage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        Database::getInstance()->update('website_pages', [
            'title' => trim((string) $_POST['title']),
            'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null,
            'content' => (string) $_POST['content'],
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ], 'id = :id', ['id' => $id]);
        flash('success', 'Page updated.');
        redirect('/admin/pages');
    }

    public function menus(): void
    {
        $menus = Database::getInstance()->fetchAll("SELECT * FROM website_menus ORDER BY location ASC, sort_order ASC, id ASC");
        View::render('admin/website/menus', [
            'pageTitle' => 'Navigation & Footer Menus',
            'portal' => 'admin',
            'menus' => $menus,
        ], 'dashboard');
    }

    public function storeMenu(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'location' => 'required|in:header,footer_explore,footer_company,footer_legal',
            'label' => 'required|min:1|max:80',
            'url' => 'required|min:1|max:255',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/menus');
        }
        Database::getInstance()->insert('website_menus', [
            'location' => $clean['location'],
            'label' => $clean['label'],
            'url' => $clean['url'],
            'sort_order' => (int) ($_POST['sort_order'] ?? 10),
            'is_active' => 1,
        ]);
        flash('success', 'Menu item added.');
        redirect('/admin/menus');
    }

    public function deleteMenu(array $params): void
    {
        Database::getInstance()->delete('website_menus', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Menu item deleted.');
        redirect('/admin/menus');
    }

    public function blogs(): void
    {
        $posts = Database::getInstance()->fetchAll("SELECT * FROM blog_posts WHERE deleted_at IS NULL ORDER BY id DESC");
        View::render('admin/website/blogs', [
            'pageTitle' => 'Blog & Travel Stories',
            'portal' => 'admin',
            'posts' => $posts,
        ], 'dashboard');
    }

    public function storeBlog(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'title' => 'required|min:3|max:200',
            'category' => 'required|max:80',
            'content' => 'required|min:20',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/blogs');
        }
        $imgPath = 'destinations/hero-fjord.jpg';
        if (!empty($_FILES['featured_image']['name'])) {
            $up = Uploader::uploadImage($_FILES['featured_image'], 'blog', false);
            if ($up['ok']) {
                $imgPath = $up['path'];
            }
        }
        $db = Database::getInstance();
        $slug = slugify($clean['title']);
        if ($db->fetchColumn("SELECT id FROM blog_posts WHERE slug = :s", ['s' => $slug])) {
            $slug .= '-' . random_int(10, 99);
        }
        $db->insert('blog_posts', [
            'title' => $clean['title'],
            'slug' => $slug,
            'category' => $clean['category'],
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')) ?: null,
            'content' => $clean['content'],
            'featured_image' => $imgPath,
            'author_id' => (int) Auth::id(),
            'read_time' => trim((string) ($_POST['read_time'] ?? '5 min read')),
            'status' => $_POST['status'] === 'draft' ? 'draft' : 'published',
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
        flash('success', 'Travel story saved.');
        redirect('/admin/blogs');
    }

    public function deleteBlog(array $params): void
    {
        Database::getInstance()->update('blog_posts', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Article archived.');
        redirect('/admin/blogs');
    }

    public function testimonials(): void
    {
        $db = Database::getInstance();
        $testimonials = $db->fetchAll("SELECT t.*, p.title AS package_title FROM testimonials t LEFT JOIN travel_packages p ON p.id = t.package_id ORDER BY t.sort_order ASC, t.id DESC");
        $packages = $db->fetchAll("SELECT id, title FROM travel_packages WHERE deleted_at IS NULL ORDER BY title");
        View::render('admin/website/testimonials', [
            'pageTitle' => 'Testimonials',
            'portal' => 'admin',
            'testimonials' => $testimonials,
            'packages' => $packages,
        ], 'dashboard');
    }

    public function storeTestimonial(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['customer_name' => 'required|min:2|max:120', 'quote' => 'required|min:10', 'rating' => 'required|integer']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/testimonials');
        }
        Database::getInstance()->insert('testimonials', [
            'customer_name' => $clean['customer_name'],
            'customer_location' => trim((string) ($_POST['customer_location'] ?? '')) ?: null,
            'package_id' => !empty($_POST['package_id']) ? (int) $_POST['package_id'] : null,
            'rating' => max(1, min(5, (int) $clean['rating'])),
            'quote' => $clean['quote'],
            'trip_taken' => trim((string) ($_POST['trip_taken'] ?? '')) ?: null,
            'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_approved' => 1,
        ]);
        flash('success', 'Testimonial added.');
        redirect('/admin/testimonials');
    }

    public function deleteTestimonial(array $params): void
    {
        Database::getInstance()->delete('testimonials', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Testimonial deleted.');
        redirect('/admin/testimonials');
    }

    public function faqs(): void
    {
        $faqs = Database::getInstance()->fetchAll("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC");
        View::render('admin/website/faqs', ['pageTitle' => 'FAQ Management', 'portal' => 'admin', 'faqs' => $faqs], 'dashboard');
    }

    public function storeFaq(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['category' => 'required|max:80', 'question' => 'required|max:255', 'answer' => 'required']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/faqs');
        }
        Database::getInstance()->insert('faqs', [
            'category' => $clean['category'],
            'question' => $clean['question'],
            'answer' => $clean['answer'],
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => 1,
        ]);
        flash('success', 'FAQ added.');
        redirect('/admin/faqs');
    }

    public function deleteFaq(array $params): void
    {
        Database::getInstance()->delete('faqs', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'FAQ deleted.');
        redirect('/admin/faqs');
    }

    public function media(): void
    {
        $media = Database::getInstance()->fetchAll("SELECT * FROM media_library ORDER BY created_at DESC");
        View::render('admin/website/media', ['pageTitle' => 'Media Library', 'portal' => 'admin', 'media' => $media], 'dashboard');
    }

    public function uploadMedia(): void
    {
        $up = Uploader::uploadImage($_FILES['media_file'] ?? [], 'media', true);
        if (!$up['ok']) {
            flash('error', $up['error']);
            redirect('/admin/media');
        }
        Database::getInstance()->insert('media_library', [
            'title' => trim((string) ($_POST['title'] ?? '')) ?: $up['original_name'],
            'file_path' => $up['path'],
            'file_type' => $up['mime'],
            'file_size' => $up['size'],
            'alt_text' => trim((string) ($_POST['alt_text'] ?? '')) ?: null,
            'folder' => 'media',
            'uploaded_by' => (int) Auth::id(),
        ]);
        flash('success', 'Media asset uploaded.');
        redirect('/admin/media');
    }

    public function deleteMedia(array $params): void
    {
        Database::getInstance()->delete('media_library', 'id = :id', ['id' => (int) ($params['id'] ?? 0)]);
        flash('success', 'Media entry removed.');
        redirect('/admin/media');
    }

    public function seo(): void
    {
        $seo = Database::getInstance()->fetchAll("SELECT * FROM seo_settings ORDER BY id ASC");
        View::render('admin/website/seo', ['pageTitle' => 'SEO Settings', 'portal' => 'admin', 'seoList' => $seo], 'dashboard');
    }

    public function saveSeo(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['page_key' => 'required|max:60', 'meta_title' => 'required|max:200']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/admin/seo');
        }
        $db = Database::getInstance();
        $exists = $db->fetchColumn("SELECT id FROM seo_settings WHERE page_key = :k", ['k' => $clean['page_key']]);
        $payload = [
            'meta_title' => $clean['meta_title'],
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')) ?: null,
            'meta_keywords' => trim((string) ($_POST['meta_keywords'] ?? '')) ?: null,
            'robots' => trim((string) ($_POST['robots'] ?? 'index, follow')),
        ];
        if ($exists) {
            $db->update('seo_settings', $payload, 'id = :id', ['id' => (int) $exists]);
        } else {
            $payload['page_key'] = $clean['page_key'];
            $db->insert('seo_settings', $payload);
        }
        flash('success', "SEO settings updated for '{$clean['page_key']}'.");
        redirect('/admin/seo');
    }

    public function emailTemplates(): void
    {
        $db = Database::getInstance();
        $templates = $db->fetchAll("SELECT * FROM email_templates ORDER BY id ASC");
        $logs = $db->fetchAll("SELECT * FROM email_logs ORDER BY sent_at DESC LIMIT 20");
        View::render('admin/website/email-templates', [
            'pageTitle' => 'Branded Email Templates & Dispatch Log',
            'portal' => 'admin',
            'templates' => $templates,
            'logs' => $logs,
        ], 'dashboard');
    }

    public function updateEmailTemplate(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        Database::getInstance()->update('email_templates', [
            'subject' => trim((string) $_POST['subject']),
            'body_html' => (string) $_POST['body_html'],
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ], 'id = :id', ['id' => $id]);
        flash('success', 'Email template updated.');
        redirect('/admin/email-templates');
    }
}
