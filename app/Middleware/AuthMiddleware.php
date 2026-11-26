<?php
declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        if (!Auth::check()) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'Authentication required.'], 401);
            }
            flash('error', 'Please sign in to access this area.');
            redirect('/login');
        }
    }
}
