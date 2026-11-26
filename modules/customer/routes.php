<?php

use App\Controllers\Customer\CustomerController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

$custBase = [AuthMiddleware::class, [RoleMiddleware::class, 'customer']];

$router->get('/customer', [CustomerController::class, 'dashboard'], $custBase);
$router->get('/customer/bookings', [CustomerController::class, 'bookings'], $custBase);
$router->get('/customer/bookings/{id}', [CustomerController::class, 'bookingDetail'], $custBase);
$router->post('/customer/bookings/{id}/pay', [CustomerController::class, 'submitPayment'], $custBase);
$router->post('/customer/bookings/{id}/cancel', [CustomerController::class, 'requestCancellation'], $custBase);
$router->get('/customer/invoices/{id}/download', [CustomerController::class, 'downloadInvoice'], $custBase);

$router->get('/customer/payments', [CustomerController::class, 'payments'], $custBase);
$router->get('/customer/wishlist', [CustomerController::class, 'wishlist'], $custBase);
$router->post('/customer/wishlist/{id}/remove', [CustomerController::class, 'removeWishlist'], $custBase);
$router->get('/customer/enquiries', [CustomerController::class, 'enquiries'], $custBase);

$router->get('/customer/support', [CustomerController::class, 'support'], $custBase);
$router->post('/customer/support/create', [CustomerController::class, 'createSupport'], $custBase);
$router->get('/customer/support/{id}', [CustomerController::class, 'supportDetail'], $custBase);
$router->post('/customer/support/{id}/reply', [CustomerController::class, 'replySupport'], $custBase);

$router->get('/customer/notifications', [CustomerController::class, 'notifications'], $custBase);
$router->post('/customer/notifications', [CustomerController::class, 'notifications'], $custBase);

$router->get('/customer/profile', [CustomerController::class, 'profile'], $custBase);
$router->post('/customer/profile', [CustomerController::class, 'updateProfile'], $custBase);
$router->post('/customer/password', [CustomerController::class, 'changePassword'], $custBase);
