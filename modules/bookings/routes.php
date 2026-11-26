<?php

use App\Controllers\FrontendController;

$router->get('/book', [FrontendController::class, 'bookingForm']);
$router->get('/book/{slug}', [FrontendController::class, 'bookingForm']);
$router->post('/book', [FrontendController::class, 'submitBooking']);
$router->get('/booking/success/{ref}', [FrontendController::class, 'bookingSuccess']);
