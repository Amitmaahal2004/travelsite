<?php

use App\Controllers\Hr\HrController;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\RoleMiddleware;

$hrBase = [AuthMiddleware::class, [RoleMiddleware::class, 'hr']];

$router->get('/hr', [HrController::class, 'dashboard'], [...$hrBase, [PermissionMiddleware::class, 'dashboard.view']]);
$router->get('/hr/employees', [HrController::class, 'employees'], [...$hrBase, [PermissionMiddleware::class, 'employees.view']]);
$router->post('/hr/employees/create', [HrController::class, 'storeEmployee'], [...$hrBase, [PermissionMiddleware::class, 'employees.create']]);
$router->get('/hr/employees/{id}', [HrController::class, 'editEmployee'], [...$hrBase, [PermissionMiddleware::class, 'employees.view']]);
$router->post('/hr/employees/{id}/update', [HrController::class, 'updateEmployee'], [...$hrBase, [PermissionMiddleware::class, 'employees.edit']]);

$router->get('/hr/departments', [HrController::class, 'departments'], [...$hrBase, [PermissionMiddleware::class, 'departments.manage']]);
$router->post('/hr/departments/create', [HrController::class, 'storeDepartment'], [...$hrBase, [PermissionMiddleware::class, 'departments.manage']]);

$router->get('/hr/attendance', [HrController::class, 'attendance'], [...$hrBase, [PermissionMiddleware::class, 'attendance.view']]);
$router->post('/hr/attendance/save', [HrController::class, 'saveAttendance'], [...$hrBase, [PermissionMiddleware::class, 'attendance.manage']]);

$router->get('/hr/leaves', [HrController::class, 'leaves'], [...$hrBase, [PermissionMiddleware::class, 'leaves.view']]);
$router->post('/hr/leaves/{id}/decide', [HrController::class, 'decideLeave'], [...$hrBase, [PermissionMiddleware::class, 'leaves.approve']]);

$router->get('/hr/documents', [HrController::class, 'documents'], [...$hrBase, [PermissionMiddleware::class, 'documents.manage']]);
$router->post('/hr/documents/upload', [HrController::class, 'uploadDocument'], [...$hrBase, [PermissionMiddleware::class, 'documents.manage']]);
$router->get('/hr/documents/{id}/download', [HrController::class, 'downloadDocument'], [...$hrBase, [PermissionMiddleware::class, 'documents.manage']]);

$router->get('/hr/assignments', [HrController::class, 'assignments'], [...$hrBase, [PermissionMiddleware::class, 'assignments.manage']]);
$router->post('/hr/assignments/create', [HrController::class, 'storeAssignment'], [...$hrBase, [PermissionMiddleware::class, 'assignments.manage']]);
$router->get('/hr/reports', [HrController::class, 'reports'], [...$hrBase, [PermissionMiddleware::class, 'assignments.manage']]);

$router->get('/hr/notifications', [HrController::class, 'notifications'], $hrBase);
$router->post('/hr/notifications', [HrController::class, 'notifications'], $hrBase);
