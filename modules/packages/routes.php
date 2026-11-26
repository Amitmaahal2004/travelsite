<?php

use App\Controllers\FrontendController;

$router->get('/destinations', [FrontendController::class, 'destinations']);
$router->get('/destination/{slug}', [FrontendController::class, 'destinationDetail']);
$router->get('/packages', [FrontendController::class, 'packages']);
$router->get('/package/{slug}', [FrontendController::class, 'packageDetail']);
$router->get('/categories', [FrontendController::class, 'categories']);
$router->get('/category/{slug}', [FrontendController::class, 'categoryDetail']);
