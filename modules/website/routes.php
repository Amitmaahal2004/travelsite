<?php

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\FrontendController;

// Public Website Routes
$router->get('/', [FrontendController::class, 'home']);
$router->get('/about', [FrontendController::class, 'about']);
$router->get('/contact', [FrontendController::class, 'contact']);
$router->post('/contact', [FrontendController::class, 'submitContact']);
$router->get('/custom-enquiry', [FrontendController::class, 'customEnquiryForm']);
$router->post('/custom-enquiry', [FrontendController::class, 'submitCustomEnquiry']);
$router->get('/blog', [FrontendController::class, 'blog']);
$router->get('/blog/{slug}', [FrontendController::class, 'blogDetail']);
$router->get('/testimonials', [FrontendController::class, 'testimonials']);
$router->get('/faq', [FrontendController::class, 'faq']);
$router->get('/privacy-policy', [FrontendController::class, 'cmsPage']);
$router->get('/terms-and-conditions', [FrontendController::class, 'cmsPage']);
$router->get('/page/{slug}', [FrontendController::class, 'cmsPage']);

// Authentication Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/forgot-password', [AuthController::class, 'showForgot']);
$router->post('/forgot-password', [AuthController::class, 'sendReset']);
$router->get('/reset-password/{token}', [AuthController::class, 'showReset']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']);

// AJAX API Endpoints
$router->post('/api/newsletter', [ApiController::class, 'subscribeNewsletter']);
$router->post('/api/wishlist/toggle', [ApiController::class, 'toggleWishlist']);
$router->get('/api/promo/verify', [ApiController::class, 'verifyPromo']);
$router->post('/api/notifications/{id}/read', [ApiController::class, 'markNotificationRead']);
