<?php

use App\Controllers\Employee\EmployeeController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

$empBase = [AuthMiddleware::class, [RoleMiddleware::class, 'employee']];

$router->get('/employee', [EmployeeController::class, 'dashboard'], $empBase);
$router->get('/employee/bookings', [EmployeeController::class, 'bookings'], $empBase);
$router->get('/employee/bookings/{id}', [EmployeeController::class, 'bookingDetail'], $empBase);
$router->post('/employee/bookings/{id}/update', [EmployeeController::class, 'updateBooking'], $empBase);
$router->get('/employee/assignments', [EmployeeController::class, 'assignments'], $empBase);
$router->post('/employee/assignments/{id}/status', [EmployeeController::class, 'completeTask'], $empBase);
$router->get('/employee/attendance', [EmployeeController::class, 'attendance'], $empBase);
$router->post('/employee/attendance/toggle', [EmployeeController::class, 'checkInOut'], $empBase);
$router->get('/employee/leaves', [EmployeeController::class, 'leaves'], $empBase);
$router->post('/employee/leaves/apply', [EmployeeController::class, 'applyLeave'], $empBase);
$router->get('/employee/notifications', [EmployeeController::class, 'notifications'], $empBase);
$router->post('/employee/notifications', [EmployeeController::class, 'notifications'], $empBase);
$router->get('/employee/profile', [EmployeeController::class, 'profile'], $empBase);
$router->post('/employee/profile', [EmployeeController::class, 'updateProfile'], $empBase);
