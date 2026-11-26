<?php

use App\Controllers\Admin\BookingController;
use App\Controllers\Admin\BrandingController;
use App\Controllers\Admin\CustomerController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EmployeeController;
use App\Controllers\Admin\FinanceController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\TravelController;
use App\Controllers\Admin\WebsiteController;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\RoleMiddleware;

$adminBase = [AuthMiddleware::class, [RoleMiddleware::class, 'super_admin']];

// Super Admin Dashboard & Activity Logs
$router->get('/admin', [DashboardController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'dashboard.view']]);
$router->get('/admin/activity-logs', [DashboardController::class, 'activityLogs'], [...$adminBase, [PermissionMiddleware::class, 'activity_logs.view']]);

// Workforce & HR Control (Admin)
$router->get('/admin/employees', [EmployeeController::class, 'employees'], [...$adminBase, [PermissionMiddleware::class, 'employees.view']]);
$router->post('/admin/employees/create', [EmployeeController::class, 'storeEmployee'], [...$adminBase, [PermissionMiddleware::class, 'employees.create']]);
$router->get('/admin/employees/{id}', [EmployeeController::class, 'editEmployee'], [...$adminBase, [PermissionMiddleware::class, 'employees.view']]);
$router->post('/admin/employees/{id}/update', [EmployeeController::class, 'updateEmployee'], [...$adminBase, [PermissionMiddleware::class, 'employees.edit']]);
$router->get('/admin/departments', [EmployeeController::class, 'departments'], [...$adminBase, [PermissionMiddleware::class, 'departments.manage']]);
$router->post('/admin/departments/create', [EmployeeController::class, 'storeDepartment'], [...$adminBase, [PermissionMiddleware::class, 'departments.manage']]);
$router->get('/admin/attendance', [EmployeeController::class, 'attendance'], [...$adminBase, [PermissionMiddleware::class, 'attendance.view']]);
$router->post('/admin/attendance/save', [EmployeeController::class, 'saveAttendance'], [...$adminBase, [PermissionMiddleware::class, 'attendance.manage']]);
$router->get('/admin/leaves', [EmployeeController::class, 'leaves'], [...$adminBase, [PermissionMiddleware::class, 'leaves.view']]);
$router->post('/admin/leaves/{id}/decide', [EmployeeController::class, 'decideLeave'], [...$adminBase, [PermissionMiddleware::class, 'leaves.approve']]);
$router->get('/admin/documents/{id}/download', [EmployeeController::class, 'downloadDocument'], [...$adminBase, [PermissionMiddleware::class, 'documents.manage']]);

// Centralized Global Website Branding
$router->get('/admin/branding', [BrandingController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'branding.view']]);
$router->post('/admin/branding/save', [BrandingController::class, 'save'], [...$adminBase, [PermissionMiddleware::class, 'branding.manage']]);
$router->post('/admin/branding/discard-preview', [BrandingController::class, 'discardPreview'], [...$adminBase, [PermissionMiddleware::class, 'branding.manage']]);
$router->post('/admin/branding/reset', [BrandingController::class, 'resetDefaults'], [...$adminBase, [PermissionMiddleware::class, 'branding.manage']]);

// RBAC Role & Permission Management
$router->get('/admin/roles', [RoleController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'roles.manage']]);
$router->post('/admin/roles/create', [RoleController::class, 'create'], [...$adminBase, [PermissionMiddleware::class, 'roles.manage']]);
$router->post('/admin/roles/{id}/permissions', [RoleController::class, 'updatePermissions'], [...$adminBase, [PermissionMiddleware::class, 'roles.manage']]);
$router->post('/admin/roles/{id}/delete', [RoleController::class, 'delete'], [...$adminBase, [PermissionMiddleware::class, 'roles.manage']]);

// Travel Catalog Management
$router->get('/admin/countries', [TravelController::class, 'countries'], [...$adminBase, [PermissionMiddleware::class, 'destinations.view']]);
$router->post('/admin/countries/create', [TravelController::class, 'storeCountry'], [...$adminBase, [PermissionMiddleware::class, 'destinations.create']]);
$router->post('/admin/countries/{id}/delete', [TravelController::class, 'deleteCountry'], [...$adminBase, [PermissionMiddleware::class, 'destinations.delete']]);

$router->get('/admin/destinations', [TravelController::class, 'destinations'], [...$adminBase, [PermissionMiddleware::class, 'destinations.view']]);
$router->post('/admin/destinations/create', [TravelController::class, 'storeDestination'], [...$adminBase, [PermissionMiddleware::class, 'destinations.create']]);
$router->get('/admin/destinations/{id}', [TravelController::class, 'editDestination'], [...$adminBase, [PermissionMiddleware::class, 'destinations.edit']]);
$router->post('/admin/destinations/{id}/update', [TravelController::class, 'updateDestination'], [...$adminBase, [PermissionMiddleware::class, 'destinations.edit']]);
$router->post('/admin/destinations/{id}/delete', [TravelController::class, 'deleteDestination'], [...$adminBase, [PermissionMiddleware::class, 'destinations.delete']]);

$router->get('/admin/categories', [TravelController::class, 'categories'], [...$adminBase, [PermissionMiddleware::class, 'categories.view']]);
$router->post('/admin/categories/create', [TravelController::class, 'storeCategory'], [...$adminBase, [PermissionMiddleware::class, 'categories.manage']]);
$router->post('/admin/categories/{id}/delete', [TravelController::class, 'deleteCategory'], [...$adminBase, [PermissionMiddleware::class, 'categories.manage']]);

$router->get('/admin/packages', [TravelController::class, 'packages'], [...$adminBase, [PermissionMiddleware::class, 'packages.view']]);
$router->post('/admin/packages/create', [TravelController::class, 'storePackage'], [...$adminBase, [PermissionMiddleware::class, 'packages.create']]);
$router->get('/admin/packages/{id}', [TravelController::class, 'editPackage'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/update', [TravelController::class, 'updatePackage'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/itinerary', [TravelController::class, 'addItineraryDay'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/itinerary/{dayId}/delete', [TravelController::class, 'deleteItineraryDay'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/images', [TravelController::class, 'addPackageImage'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/items', [TravelController::class, 'addPackageInclusionExclusion'], [...$adminBase, [PermissionMiddleware::class, 'packages.edit']]);
$router->post('/admin/packages/{id}/delete', [TravelController::class, 'deletePackage'], [...$adminBase, [PermissionMiddleware::class, 'packages.delete']]);

$router->get('/admin/offers', [TravelController::class, 'offers'], [...$adminBase, [PermissionMiddleware::class, 'offers.manage']]);
$router->post('/admin/offers/create', [TravelController::class, 'storeOffer'], [...$adminBase, [PermissionMiddleware::class, 'offers.manage']]);
$router->post('/admin/offers/{id}/delete', [TravelController::class, 'deleteOffer'], [...$adminBase, [PermissionMiddleware::class, 'offers.manage']]);

// Booking Management
$router->get('/admin/bookings', [BookingController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'bookings.view']]);
$router->get('/admin/bookings/create', [BookingController::class, 'create'], [...$adminBase, [PermissionMiddleware::class, 'bookings.create']]);
$router->post('/admin/bookings/create', [BookingController::class, 'store'], [...$adminBase, [PermissionMiddleware::class, 'bookings.create']]);
$router->get('/admin/bookings/export', [BookingController::class, 'exportCsv'], [...$adminBase, [PermissionMiddleware::class, 'bookings.export']]);
$router->get('/admin/bookings/{id}', [BookingController::class, 'show'], [...$adminBase, [PermissionMiddleware::class, 'bookings.view']]);
$router->post('/admin/bookings/{id}/update', [BookingController::class, 'update'], [...$adminBase, [PermissionMiddleware::class, 'bookings.edit']]);
$router->post('/admin/bookings/{id}/payment', [BookingController::class, 'recordPayment'], [...$adminBase, [PermissionMiddleware::class, 'bookings.edit']]);
$router->post('/admin/bookings/{id}/refund', [BookingController::class, 'processRefund'], [...$adminBase, [PermissionMiddleware::class, 'bookings.edit']]);
$router->get('/admin/bookings/{id}/invoice', [BookingController::class, 'invoice'], [...$adminBase, [PermissionMiddleware::class, 'bookings.view']]);

// Customer, Enquiries & Support Management
$router->get('/admin/customers', [CustomerController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'customers.view']]);
$router->get('/admin/customers/{id}', [CustomerController::class, 'show'], [...$adminBase, [PermissionMiddleware::class, 'customers.view']]);
$router->post('/admin/customers/{id}/update', [CustomerController::class, 'update'], [...$adminBase, [PermissionMiddleware::class, 'customers.edit']]);

$router->get('/admin/enquiries', [CustomerController::class, 'enquiries'], [...$adminBase, [PermissionMiddleware::class, 'enquiries.view']]);
$router->post('/admin/enquiries/{id}/update', [CustomerController::class, 'updateEnquiry'], [...$adminBase, [PermissionMiddleware::class, 'enquiries.edit']]);

$router->get('/admin/support', [CustomerController::class, 'support'], [...$adminBase, [PermissionMiddleware::class, 'support.manage']]);
$router->get('/admin/support/{id}', [CustomerController::class, 'supportShow'], [...$adminBase, [PermissionMiddleware::class, 'support.manage']]);
$router->post('/admin/support/{id}/reply', [CustomerController::class, 'replySupport'], [...$adminBase, [PermissionMiddleware::class, 'support.manage']]);

// Finance & Reporting
$router->get('/admin/finance', [FinanceController::class, 'index'], [...$adminBase, [PermissionMiddleware::class, 'finance.view']]);
$router->post('/admin/finance/expenses/create', [FinanceController::class, 'storeExpense'], [...$adminBase, [PermissionMiddleware::class, 'finance.manage']]);
$router->post('/admin/finance/expenses/{id}/delete', [FinanceController::class, 'deleteExpense'], [...$adminBase, [PermissionMiddleware::class, 'finance.manage']]);
$router->post('/admin/finance/commissions/{id}', [FinanceController::class, 'updateCommission'], [...$adminBase, [PermissionMiddleware::class, 'finance.manage']]);
$router->get('/admin/finance/export-csv', [FinanceController::class, 'exportCsv'], [...$adminBase, [PermissionMiddleware::class, 'finance.export']]);
$router->get('/admin/finance/export-pdf', [FinanceController::class, 'exportPdf'], [...$adminBase, [PermissionMiddleware::class, 'finance.export']]);

// Website CMS & SEO Management
$router->get('/admin/homepage-sections', [WebsiteController::class, 'homepageSections'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/homepage-sections/{id}/update', [WebsiteController::class, 'updateHomepageSection'], [...$adminBase, [PermissionMiddleware::class, 'website.edit']]);
$router->get('/admin/pages', [WebsiteController::class, 'pages'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/pages/create', [WebsiteController::class, 'storePage'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/pages/{id}/update', [WebsiteController::class, 'updatePage'], [...$adminBase, [PermissionMiddleware::class, 'website.edit']]);
$router->get('/admin/menus', [WebsiteController::class, 'menus'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/menus/create', [WebsiteController::class, 'storeMenu'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/menus/{id}/delete', [WebsiteController::class, 'deleteMenu'], [...$adminBase, [PermissionMiddleware::class, 'website.delete']]);
$router->get('/admin/blogs', [WebsiteController::class, 'blogs'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/blogs/create', [WebsiteController::class, 'storeBlog'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/blogs/{id}/delete', [WebsiteController::class, 'deleteBlog'], [...$adminBase, [PermissionMiddleware::class, 'website.delete']]);
$router->get('/admin/testimonials', [WebsiteController::class, 'testimonials'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/testimonials/create', [WebsiteController::class, 'storeTestimonial'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/testimonials/{id}/delete', [WebsiteController::class, 'deleteTestimonial'], [...$adminBase, [PermissionMiddleware::class, 'website.delete']]);
$router->get('/admin/faqs', [WebsiteController::class, 'faqs'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/faqs/create', [WebsiteController::class, 'storeFaq'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/faqs/{id}/delete', [WebsiteController::class, 'deleteFaq'], [...$adminBase, [PermissionMiddleware::class, 'website.delete']]);
$router->get('/admin/media', [WebsiteController::class, 'media'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/media/upload', [WebsiteController::class, 'uploadMedia'], [...$adminBase, [PermissionMiddleware::class, 'website.create']]);
$router->post('/admin/media/{id}/delete', [WebsiteController::class, 'deleteMedia'], [...$adminBase, [PermissionMiddleware::class, 'website.delete']]);
$router->get('/admin/email-templates', [WebsiteController::class, 'emailTemplates'], [...$adminBase, [PermissionMiddleware::class, 'website.view']]);
$router->post('/admin/email-templates/{id}/update', [WebsiteController::class, 'updateEmailTemplate'], [...$adminBase, [PermissionMiddleware::class, 'website.edit']]);
$router->get('/admin/seo', [WebsiteController::class, 'seo'], [...$adminBase, [PermissionMiddleware::class, 'seo.manage']]);
$router->post('/admin/seo/save', [WebsiteController::class, 'saveSeo'], [...$adminBase, [PermissionMiddleware::class, 'seo.manage']]);
