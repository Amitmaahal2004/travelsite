<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuditService;
use App\Services\NotificationService;
use Core\Auth;
use Core\Database;
use Core\Validator;
use Core\View;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(Auth::dashboardUrl());
        }
        View::render('auth/login', ['pageTitle' => 'Sign In'], 'auth');
    }

    public function login(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            set_old(['email' => $_POST['email'] ?? '']);
            flash('error', $validator->firstError());
            redirect('/login');
        }

        $res = Auth::attempt($clean['email'], (string) ($_POST['password'] ?? ''));
        if (!$res['ok']) {
            set_old(['email' => $clean['email']]);
            flash('error', $res['message']);
            redirect('/login');
        }

        flash('success', 'Welcome back, ' . $res['user']['first_name'] . '!');
        redirect(Auth::dashboardUrl($res['user']));
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect(Auth::dashboardUrl());
        }
        View::render('auth/register', ['pageTitle' => 'Create Account'], 'auth');
    }

    public function register(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'first_name' => 'required|min:2|max:80',
            'last_name' => 'required|min:2|max:80',
            'email' => 'required|email|max:160',
            'phone' => 'required|min:6|max:40',
            'password' => 'required|min:8|max:120',
            'password_confirmation' => 'required|match:password',
        ]);

        if ($validator->fails()) {
            set_old($_POST);
            flash('error', $validator->firstError());
            redirect('/register');
        }

        $db = Database::getInstance();
        $email = strtolower($clean['email']);
        if ($db->fetchColumn("SELECT id FROM users WHERE LOWER(email) = :e LIMIT 1", ['e' => $email])) {
            set_old($_POST);
            flash('error', 'An account with this email address already exists.');
            redirect('/register');
        }

        $db->beginTransaction();
        try {
            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0, 0xffff), random_int(0, 0xffff),
                random_int(0, 0xffff), random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
            );
            $userId = $db->insert('users', [
                'uuid' => $uuid,
                'first_name' => $clean['first_name'],
                'last_name' => $clean['last_name'],
                'email' => $email,
                'phone' => $clean['phone'],
                'password_hash' => password_hash((string) $_POST['password'], PASSWORD_BCRYPT),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $custRoleId = (int) $db->fetchColumn("SELECT id FROM roles WHERE slug = 'customer' LIMIT 1");
            $db->insert('user_roles', ['user_id' => $userId, 'role_id' => $custRoleId]);

            $db->insert('customers', [
                'user_id' => $userId,
                'customer_code' => 'CUST-' . (1000 + $userId),
                'loyalty_tier' => 'explorer',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        NotificationService::notifyRole(
            'super_admin',
            'customer',
            'New Customer Registration',
            "{$clean['first_name']} {$clean['last_name']} ({$email}) joined.",
            '/admin/customers'
        );
        NotificationService::sendBrandedEmail($email, 'welcome_customer', [
            'customer_name' => $clean['first_name'] . ' ' . $clean['last_name'],
        ]);
        AuditService::log('auth', 'register', 'New customer registered', 'user', $userId, $userId);

        Auth::loginUser($userId);
        flash('success', 'Your account is active. Welcome to ' . brand('website_name') . '!');
        redirect('/customer');
    }

    public function showForgot(): void
    {
        View::render('auth/forgot-password', ['pageTitle' => 'Forgot Password'], 'auth');
    }

    public function sendReset(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, ['email' => 'required|email']);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/forgot-password');
        }

        $db = Database::getInstance();
        $email = strtolower($clean['email']);
        $user = $db->fetch("SELECT id, first_name, last_name, email FROM users WHERE LOWER(email) = :e AND deleted_at IS NULL LIMIT 1", ['e' => $email]);

        if ($user) {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $db->insert('password_resets', [
                'email' => $email,
                'token_hash' => $tokenHash,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $resetUrl = full_url('/reset-password/' . $rawToken);
            NotificationService::sendBrandedEmail($email, 'password_reset', [
                'customer_name' => $user['first_name'] . ' ' . $user['last_name'],
                'reset_link' => $resetUrl,
            ]);
            AuditService::log('auth', 'password_reset_request', 'Password reset requested', 'user', (int) $user['id'], (int) $user['id']);
        }

        flash('success', 'If an account matches that email, password reset instructions have been dispatched.');
        redirect('/forgot-password');
    }

    public function showReset(array $params): void
    {
        $token = trim((string) ($params['token'] ?? ''));
        $db = Database::getInstance();
        $record = $db->fetch(
            "SELECT * FROM password_resets WHERE token_hash = :th AND used_at IS NULL AND expires_at >= NOW() LIMIT 1",
            ['th' => hash('sha256', $token)]
        );
        if (!$record) {
            flash('error', 'This password reset link is invalid or has expired.');
            redirect('/forgot-password');
        }
        View::render('auth/reset-password', ['pageTitle' => 'Reset Password', 'token' => $token], 'auth');
    }

    public function resetPassword(): void
    {
        $validator = new Validator();
        $clean = $validator->validate($_POST, [
            'token' => 'required',
            'password' => 'required|min:8|max:120',
            'password_confirmation' => 'required|match:password',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/reset-password/' . urlencode((string) ($_POST['token'] ?? '')));
        }

        $db = Database::getInstance();
        $tokenHash = hash('sha256', (string) $clean['token']);
        $record = $db->fetch(
            "SELECT * FROM password_resets WHERE token_hash = :th AND used_at IS NULL AND expires_at >= NOW() LIMIT 1",
            ['th' => $tokenHash]
        );
        if (!$record) {
            flash('error', 'Reset token is invalid or expired.');
            redirect('/forgot-password');
        }

        $db->update('users', [
            'password_hash' => password_hash((string) $_POST['password'], PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'LOWER(email) = :e', ['e' => strtolower($record['email'])]);

        $db->update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $record['id']]);
        flash('success', 'Your password has been updated. Please sign in.');
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been securely signed out.');
        redirect('/login');
    }
}
