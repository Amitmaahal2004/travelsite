-- ============================================================================
-- Travel Website & Travel Agency Management System - MySQL Schema & Seed Data
-- Compatible with MySQL 8.0 / MariaDB 10.4+ (XAMPP)
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `travelsite_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `travelsite_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. RBAC: ROLES, PERMISSIONS, USERS
-- ----------------------------------------------------------------------------

DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `email_templates`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `support_replies`;
DROP TABLE IF EXISTS `support_requests`;
DROP TABLE IF EXISTS `newsletter_subscribers`;
DROP TABLE IF EXISTS `enquiries`;
DROP TABLE IF EXISTS `seo_settings`;
DROP TABLE IF EXISTS `branding_settings`;
DROP TABLE IF EXISTS `media_library`;
DROP TABLE IF EXISTS `website_menus`;
DROP TABLE IF EXISTS `homepage_sections`;
DROP TABLE IF EXISTS `website_pages`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `commissions`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `refunds`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `booking_notes`;
DROP TABLE IF EXISTS `booking_travellers`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `seasonal_offers`;
DROP TABLE IF EXISTS `package_exclusions`;
DROP TABLE IF EXISTS `package_inclusions`;
DROP TABLE IF EXISTS `package_images`;
DROP TABLE IF EXISTS `package_itineraries`;
DROP TABLE IF EXISTS `travel_packages`;
DROP TABLE IF EXISTS `travel_categories`;
DROP TABLE IF EXISTS `destinations`;
DROP TABLE IF EXISTS `countries`;
DROP TABLE IF EXISTS `work_assignments`;
DROP TABLE IF EXISTS `employee_documents`;
DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `employee_attendance`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NULL,
  `dashboard_prefix` VARCHAR(40) NOT NULL DEFAULT '/customer',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(60) NOT NULL,
  `action` VARCHAR(40) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_slug` (`slug`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL,
  `first_name` VARCHAR(80) NOT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) NULL,
  `status` ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `email_verified_at` DATETIME NULL,
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_uuid` (`uuid`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
  `user_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(160) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(160) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_la_identifier` (`identifier`, `attempted_at`),
  KEY `idx_la_ip` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. HR & EMPLOYEES
-- ----------------------------------------------------------------------------

CREATE TABLE `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `description` TEXT NULL,
  `manager_user_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_departments_name` (`name`),
  UNIQUE KEY `uq_departments_code` (`code`),
  CONSTRAINT `fk_dept_manager` FOREIGN KEY (`manager_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED NULL,
  `employee_code` VARCHAR(30) NOT NULL,
  `designation` VARCHAR(120) NOT NULL,
  `employment_type` ENUM('full_time','part_time','contract') NOT NULL DEFAULT 'full_time',
  `join_date` DATE NOT NULL,
  `base_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `emergency_contact` VARCHAR(160) NULL,
  `address` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `performance_score` DECIMAL(3,1) NOT NULL DEFAULT 4.5,
  `status` ENUM('active','on_leave','terminated') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employees_user` (`user_id`),
  UNIQUE KEY `uq_employees_code` (`employee_code`),
  KEY `idx_employees_dept` (`department_id`),
  CONSTRAINT `fk_emp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `check_in` TIME NULL,
  `check_out` TIME NULL,
  `status` ENUM('present','absent','late','half_day','remote','on_leave') NOT NULL DEFAULT 'present',
  `notes` VARCHAR(255) NULL,
  `marked_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_att_date` (`employee_id`, `attendance_date`),
  CONSTRAINT `fk_att_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_marker` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `leave_type` ENUM('annual','sick','casual','unpaid','emergency') NOT NULL DEFAULT 'annual',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` INT UNSIGNED NOT NULL DEFAULT 1,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED NULL,
  `review_notes` VARCHAR(255) NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_emp` (`employee_id`, `status`),
  CONSTRAINT `fk_leave_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leave_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(160) NOT NULL,
  `document_type` VARCHAR(80) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ed_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ed_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. CUSTOMERS & TRAVEL CATALOG
-- ----------------------------------------------------------------------------

CREATE TABLE `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `customer_code` VARCHAR(30) NOT NULL,
  `date_of_birth` DATE NULL,
  `nationality` VARCHAR(80) NULL,
  `passport_number` VARCHAR(60) NULL,
  `passport_expiry` DATE NULL,
  `address` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `country` VARCHAR(100) NULL,
  `travel_preferences` TEXT NULL,
  `loyalty_tier` ENUM('explorer','voyager','connoisseur') NOT NULL DEFAULT 'explorer',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_user` (`user_id`),
  UNIQUE KEY `uq_customers_code` (`customer_code`),
  CONSTRAINT `fk_cust_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `countries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` CHAR(3) NOT NULL,
  `region` VARCHAR(80) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_countries_name` (`name`),
  UNIQUE KEY `uq_countries_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `destinations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `location_label` VARCHAR(120) NOT NULL,
  `tagline` VARCHAR(200) NULL,
  `summary` TEXT NULL,
  `description` LONGTEXT NULL,
  `highlights` JSON NULL,
  `best_time_to_visit` VARCHAR(120) NULL,
  `climate` VARCHAR(120) NULL,
  `starting_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `hero_image` VARCHAR(255) NULL,
  `gallery` JSON NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_destinations_slug` (`slug`),
  KEY `idx_dest_featured` (`is_featured`, `is_active`),
  CONSTRAINT `fk_dest_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `travel_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `tagline` VARCHAR(200) NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(60) NOT NULL DEFAULT 'compass',
  `image` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `travel_packages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `destination_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `package_code` VARCHAR(30) NOT NULL,
  `subtitle` VARCHAR(240) NULL,
  `overview` LONGTEXT NULL,
  `duration_days` INT UNSIGNED NOT NULL DEFAULT 5,
  `duration_nights` INT UNSIGNED NOT NULL DEFAULT 4,
  `max_group_size` INT UNSIGNED NOT NULL DEFAULT 12,
  `min_age` INT UNSIGNED NOT NULL DEFAULT 6,
  `difficulty_level` VARCHAR(50) NOT NULL DEFAULT 'Moderate',
  `base_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_price` DECIMAL(12,2) NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `featured_image` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_seasonal_offer` TINYINT(1) NOT NULL DEFAULT 0,
  `offer_badge` VARCHAR(80) NULL,
  `offer_valid_until` DATE NULL,
  `availability_status` ENUM('available','limited','sold_out','upcoming') NOT NULL DEFAULT 'available',
  `available_slots` INT UNSIGNED NOT NULL DEFAULT 20,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 4.9,
  `review_count` INT UNSIGNED NOT NULL DEFAULT 18,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pkg_slug` (`slug`),
  UNIQUE KEY `uq_pkg_code` (`package_code`),
  KEY `idx_pkg_dest` (`destination_id`),
  KEY `idx_pkg_cat` (`category_id`),
  KEY `idx_pkg_featured` (`is_featured`, `is_seasonal_offer`, `is_active`),
  CONSTRAINT `fk_pkg_dest` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pkg_cat` FOREIGN KEY (`category_id`) REFERENCES `travel_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_itineraries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` INT UNSIGNED NOT NULL,
  `day_number` INT UNSIGNED NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `location` VARCHAR(120) NULL,
  `description` TEXT NOT NULL,
  `meals` VARCHAR(120) NULL,
  `accommodation` VARCHAR(160) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itin_pkg` (`package_id`, `day_number`),
  CONSTRAINT `fk_itin_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(180) NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pimg_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_inclusions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` INT UNSIGNED NOT NULL,
  `item_text` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pinc_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_exclusions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` INT UNSIGNED NOT NULL,
  `item_text` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pexc_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seasonal_offers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `code` VARCHAR(40) NOT NULL,
  `description` TEXT NULL,
  `discount_type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  `min_booking_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `package_id` INT UNSIGNED NULL,
  `banner_image` VARCHAR(255) NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_offer_code` (`code`),
  CONSTRAINT `fk_offer_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. BOOKINGS, TRAVELLERS, PAYMENTS, REFUNDS, INVOICES, FINANCE
-- ----------------------------------------------------------------------------

CREATE TABLE `bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_reference` VARCHAR(40) NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED NULL,
  `assigned_employee_id` INT UNSIGNED NULL,
  `custom_title` VARCHAR(180) NULL,
  `travel_date` DATE NOT NULL,
  `return_date` DATE NULL,
  `adults` INT UNSIGNED NOT NULL DEFAULT 1,
  `children` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_travellers` INT UNSIGNED NOT NULL DEFAULT 1,
  `base_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `promo_code` VARCHAR(40) NULL,
  `booking_status` ENUM(
    'Pending',
    'Enquiry Received',
    'Under Review',
    'Confirmed',
    'Payment Pending',
    'Partially Paid',
    'Paid',
    'Completed',
    'Cancelled',
    'Refunded'
  ) NOT NULL DEFAULT 'Pending',
  `payment_status` ENUM('unpaid','partial','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` VARCHAR(60) NOT NULL DEFAULT 'bank_transfer',
  `special_requests` TEXT NULL,
  `internal_notes` TEXT NULL,
  `cancellation_reason` TEXT NULL,
  `cancelled_at` DATETIME NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookings_ref` (`booking_reference`),
  KEY `idx_bookings_customer` (`customer_id`),
  KEY `idx_bookings_emp` (`assigned_employee_id`),
  KEY `idx_bookings_status` (`booking_status`, `payment_status`),
  KEY `idx_bookings_date` (`travel_date`),
  CONSTRAINT `fk_book_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_book_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_emp` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booking_travellers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(140) NOT NULL,
  `email` VARCHAR(160) NULL,
  `phone` VARCHAR(40) NULL,
  `age` INT UNSIGNED NULL,
  `gender` VARCHAR(20) NULL,
  `nationality` VARCHAR(80) NULL,
  `passport_number` VARCHAR(60) NULL,
  `dietary_requirements` VARCHAR(255) NULL,
  `is_lead` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bt_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booking_notes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `note` TEXT NOT NULL,
  `is_customer_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bn_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `work_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NULL,
  `title` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `due_date` DATE NULL,
  `status` ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `assigned_by` INT UNSIGNED NULL,
  `completed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_wa_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wa_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wa_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_reference` VARCHAR(50) NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `payment_method` ENUM('bank_transfer','card_gateway','upi','cash','cheque','online_portal') NOT NULL DEFAULT 'online_portal',
  `gateway_name` VARCHAR(80) NULL,
  `gateway_transaction_id` VARCHAR(120) NULL,
  `status` ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `paid_at` DATETIME NULL,
  `notes` VARCHAR(255) NULL,
  `recorded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_ref` (`payment_reference`),
  KEY `idx_payments_booking` (`booking_id`),
  CONSTRAINT `fk_pay_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_rec` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `refunds` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `refund_reference` VARCHAR(50) NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `payment_id` INT UNSIGNED NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending','approved','processed','rejected') NOT NULL DEFAULT 'processed',
  `processed_by` INT UNSIGNED NULL,
  `processed_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_refund_ref` (`refund_reference`),
  CONSTRAINT `fk_ref_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_pay` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ref_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_proc` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `balance_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','issued','partially_paid','paid','cancelled','refunded') NOT NULL DEFAULT 'issued',
  `issued_at` DATETIME NOT NULL,
  `due_date` DATE NULL,
  `pdf_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoices_number` (`invoice_number`),
  UNIQUE KEY `uq_invoices_booking` (`booking_id`),
  CONSTRAINT `fk_inv_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expenses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_reference` VARCHAR(50) NOT NULL,
  `category` ENUM('vendor_payout','accommodation','transport','marketing','payroll','operations','software','other') NOT NULL DEFAULT 'operations',
  `title` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `vendor_name` VARCHAR(140) NULL,
  `booking_id` INT UNSIGNED NULL,
  `recorded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_expenses_ref` (`expense_reference`),
  KEY `idx_expenses_date` (`expense_date`),
  CONSTRAINT `fk_exp_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exp_rec` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `commission_rate` DECIMAL(5,2) NOT NULL,
  `commission_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','approved','paid') NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_comm_emp_book` (`employee_id`, `booking_id`),
  CONSTRAINT `fk_comm_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comm_book` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. WEBSITE CMS, GLOBAL BRANDING, SEO, BLOG, MEDIA
-- ----------------------------------------------------------------------------

CREATE TABLE `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(120) NOT NULL,
  `customer_location` VARCHAR(120) NULL,
  `package_id` INT UNSIGNED NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `quote` TEXT NOT NULL,
  `avatar` VARCHAR(255) NULL,
  `trip_taken` VARCHAR(160) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_test_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `faqs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(80) NOT NULL DEFAULT 'General',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blog_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `category` VARCHAR(80) NOT NULL DEFAULT 'Travel Journal',
  `excerpt` TEXT NULL,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) NULL,
  `author_id` INT UNSIGNED NULL,
  `read_time` VARCHAR(30) NOT NULL DEFAULT '5 min read',
  `status` ENUM('draft','published') NOT NULL DEFAULT 'published',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME NULL,
  `seo_title` VARCHAR(200) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_slug` (`slug`),
  CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `website_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `hero_image` VARCHAR(255) NULL,
  `content` LONGTEXT NOT NULL,
  `template` ENUM('default','about','legal','contact') NOT NULL DEFAULT 'default',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `seo_title` VARCHAR(200) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wp_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `homepage_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` VARCHAR(60) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` TEXT NULL,
  `badge_text` VARCHAR(100) NULL,
  `cta_text` VARCHAR(80) NULL,
  `cta_link` VARCHAR(200) NULL,
  `background_image` VARCHAR(255) NULL,
  `config_json` JSON NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hs_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `website_menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `location` ENUM('header','footer_explore','footer_company','footer_legal') NOT NULL DEFAULT 'header',
  `parent_id` INT UNSIGNED NULL,
  `label` VARCHAR(80) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `open_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_menu_parent` FOREIGN KEY (`parent_id`) REFERENCES `website_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media_library` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(80) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `alt_text` VARCHAR(200) NULL,
  `folder` VARCHAR(60) NOT NULL DEFAULT 'media',
  `uploaded_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_media_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `branding_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `website_name` VARCHAR(120) NOT NULL,
  `company_name` VARCHAR(160) NOT NULL,
  `website_tagline` VARCHAR(255) NULL,
  `company_description` TEXT NULL,
  `primary_logo` VARCHAR(255) NULL,
  `secondary_logo` VARCHAR(255) NULL,
  `light_logo` VARCHAR(255) NULL,
  `dark_logo` VARCHAR(255) NULL,
  `favicon` VARCHAR(255) NULL,
  `primary_color` CHAR(7) NOT NULL DEFAULT '#0B1D2E',
  `secondary_color` CHAR(7) NOT NULL DEFAULT '#1A4332',
  `accent_color` CHAR(7) NOT NULL DEFAULT '#C59B5F',
  `contact_email` VARCHAR(160) NULL,
  `contact_number` VARCHAR(60) NULL,
  `company_address` VARCHAR(255) NULL,
  `social_links` JSON NULL,
  `draft_data` JSON NULL,
  `updated_by` INT UNSIGNED NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_brand_updater` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `seo_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(60) NOT NULL,
  `meta_title` VARCHAR(200) NOT NULL,
  `meta_description` VARCHAR(320) NULL,
  `meta_keywords` VARCHAR(255) NULL,
  `og_image` VARCHAR(255) NULL,
  `robots` VARCHAR(60) NOT NULL DEFAULT 'index, follow',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. ENQUIRIES, NEWSLETTER, NOTIFICATIONS, EMAILS, LOGS, SUPPORT, WISHLIST
-- ----------------------------------------------------------------------------

CREATE TABLE `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_reference` VARCHAR(40) NOT NULL,
  `enquiry_type` ENUM('custom_tour','contact','package_enquiry') NOT NULL DEFAULT 'custom_tour',
  `customer_id` INT UNSIGNED NULL,
  `package_id` INT UNSIGNED NULL,
  `destination_interest` VARCHAR(160) NULL,
  `full_name` VARCHAR(140) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `travel_date` DATE NULL,
  `travellers_count` INT UNSIGNED NULL,
  `budget_range` VARCHAR(80) NULL,
  `subject` VARCHAR(180) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','in_progress','quoted','converted','closed') NOT NULL DEFAULT 'new',
  `assigned_employee_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_enq_ref` (`enquiry_reference`),
  CONSTRAINT `fk_enq_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_enq_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_enq_emp` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter_subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(160) NOT NULL,
  `name` VARCHAR(120) NULL,
  `status` ENUM('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(60) NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`, `is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_key` VARCHAR(60) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `body_html` LONGTEXT NOT NULL,
  `variables_hint` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_et_key` (`template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient_email` VARCHAR(160) NOT NULL,
  `subject` VARCHAR(220) NOT NULL,
  `body_html` LONGTEXT NOT NULL,
  `template_key` VARCHAR(60) NULL,
  `status` ENUM('logged','sent','failed') NOT NULL DEFAULT 'logged',
  `error_message` VARCHAR(255) NULL,
  `sent_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `module` VARCHAR(60) NOT NULL,
  `action` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `entity_type` VARCHAR(60) NULL,
  `entity_id` INT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_user` (`user_id`),
  KEY `idx_al_module` (`module`, `created_at`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(40) NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NULL,
  `subject` VARCHAR(180) NOT NULL,
  `category` VARCHAR(60) NOT NULL DEFAULT 'Booking Support',
  `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_to` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_number` (`ticket_number`),
  CONSTRAINT `fk_sr_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_book` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sr_assign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_replies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `support_request_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `is_staff` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_srep_req` FOREIGN KEY (`support_request_id`) REFERENCES `support_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_srep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wishlist` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `item_type` ENUM('package','destination') NOT NULL DEFAULT 'package',
  `package_id` INT UNSIGNED NULL,
  `destination_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wish_pkg` (`customer_id`, `package_id`),
  UNIQUE KEY `uq_wish_dest` (`customer_id`, `destination_id`),
  CONSTRAINT `fk_wish_cust` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wish_pkg` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wish_dest` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `dashboard_prefix`, `is_system`, `is_active`) VALUES
(1, 'Super Admin', 'super_admin', 'Complete platform control across website, branding, finance, HR, and bookings.', '/admin', 1, 1),
(2, 'HR Manager', 'hr', 'Human resources management: employees, departments, attendance, leaves, and assignments.', '/hr', 1, 1),
(3, 'Travel Specialist', 'employee', 'Manages assigned bookings, itinerary execution, enquiries, and personal attendance.', '/employee', 1, 1),
(4, 'Customer', 'customer', 'Self-service travel portal for bookings, invoices, payments, wishlist, and support.', '/customer', 1, 1);

-- Granular permissions across modules and actions (view, create, edit, delete, export, approve, manage)
INSERT INTO `permissions` (`module`, `action`, `slug`, `description`) VALUES
('dashboard', 'view', 'dashboard.view', 'View dashboard analytics'),
('branding', 'view', 'branding.view', 'View website branding settings'),
('branding', 'manage', 'branding.manage', 'Preview, update, and publish global branding'),
('website', 'view', 'website.view', 'View CMS pages, menus, banners, testimonials, blogs, FAQs'),
('website', 'create', 'website.create', 'Create CMS content'),
('website', 'edit', 'website.edit', 'Edit CMS content'),
('website', 'delete', 'website.delete', 'Delete CMS content'),
('seo', 'manage', 'seo.manage', 'Manage SEO metadata and robots rules'),
('destinations', 'view', 'destinations.view', 'View countries and destinations'),
('destinations', 'create', 'destinations.create', 'Create destinations'),
('destinations', 'edit', 'destinations.edit', 'Edit destinations'),
('destinations', 'delete', 'destinations.delete', 'Delete destinations'),
('categories', 'view', 'categories.view', 'View tour categories'),
('categories', 'manage', 'categories.manage', 'Create, edit, and delete tour categories'),
('packages', 'view', 'packages.view', 'View travel packages and itineraries'),
('packages', 'create', 'packages.create', 'Create travel packages'),
('packages', 'edit', 'packages.edit', 'Edit travel packages, itineraries, and pricing'),
('packages', 'delete', 'packages.delete', 'Delete travel packages'),
('offers', 'manage', 'offers.manage', 'Manage seasonal offers and promo codes'),
('bookings', 'view', 'bookings.view', 'View bookings'),
('bookings', 'create', 'bookings.create', 'Create manual bookings'),
('bookings', 'edit', 'bookings.edit', 'Update bookings, statuses, and employee assignments'),
('bookings', 'delete', 'bookings.delete', 'Cancel or archive bookings'),
('bookings', 'export', 'bookings.export', 'Export booking reports'),
('customers', 'view', 'customers.view', 'View customers'),
('customers', 'edit', 'customers.edit', 'Edit, activate, deactivate, or block customers'),
('enquiries', 'view', 'enquiries.view', 'View customer and custom tour enquiries'),
('enquiries', 'edit', 'enquiries.edit', 'Update and assign enquiries'),
('support', 'manage', 'support.manage', 'Respond to and resolve customer support tickets'),
('employees', 'view', 'employees.view', 'View employee directory'),
('employees', 'create', 'employees.create', 'Add new employees'),
('employees', 'edit', 'employees.edit', 'Edit employee profiles'),
('employees', 'delete', 'employees.delete', 'Archive or terminate employees'),
('departments', 'manage', 'departments.manage', 'Create and manage departments'),
('attendance', 'view', 'attendance.view', 'View employee attendance records'),
('attendance', 'manage', 'attendance.manage', 'Mark and update employee attendance'),
('leaves', 'view', 'leaves.view', 'View leave requests'),
('leaves', 'approve', 'leaves.approve', 'Approve or reject employee leave requests'),
('documents', 'manage', 'documents.manage', 'Upload, view, and delete employee documents'),
('assignments', 'manage', 'assignments.manage', 'Assign tasks and monitor employee performance'),
('finance', 'view', 'finance.view', 'View revenue, expenses, and financial analytics'),
('finance', 'manage', 'finance.manage', 'Record payments, refunds, expenses, and commissions'),
('finance', 'export', 'finance.export', 'Download financial reports'),
('roles', 'manage', 'roles.manage', 'Create roles and manage RBAC permissions'),
('activity_logs', 'view', 'activity_logs.view', 'View system audit logs');

-- Super Admin gets all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- HR gets scoped HR permissions ONLY (no branding, no finance, no website CMS, no super admin roles)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'employees.view', 'employees.create', 'employees.edit',
  'departments.manage',
  'attendance.view', 'attendance.manage',
  'leaves.view', 'leaves.approve',
  'documents.manage',
  'assignments.manage'
);

-- Employee gets scoped operational permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, `id` FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'bookings.view', 'bookings.edit',
  'enquiries.view', 'enquiries.edit',
  'support.manage'
);

-- Customer role permissions (self-scoped in customer controller)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions`
WHERE `slug` IN ('dashboard.view');

-- Default Users (passwords: Admin@123, Hr@12345, Staff@123, Traveler@123)
INSERT INTO `users` (`id`, `uuid`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `status`, `email_verified_at`) VALUES
(1, '11111111-1111-4111-8111-111111111101', 'Elena', 'Vance', 'admin@travelplatform.local', '+1 (555) 201-4400', '$2y$10$bTXV0xApgdQ0TvnzkhxFZ.mu5Qq3VYRfr8uXhIGy3nTWsP3t44SD6', 'active', NOW()),
(2, '11111111-1111-4111-8111-111111111102', 'Marcus', 'Sterling', 'hr@travelplatform.local', '+1 (555) 201-4402', '$2y$10$GNczq8uvvsHQ9jgY.AG//eN4F1HT5D1XBxrz5Ct4WDWX3uRpRDjMW', 'active', NOW()),
(3, '11111111-1111-4111-8111-111111111103', 'Clara', 'Beaumont', 'staff@travelplatform.local', '+1 (555) 201-4405', '$2y$10$6Gkgy1gtZzW9nKFFlcqZSO8RvMaq5T7nc57Nr4R0MgVnFMfYU4DTq', 'active', NOW()),
(4, '11111111-1111-4111-8111-111111111104', 'Julian', 'Mercer', 'customer@travelplatform.local', '+1 (555) 390-1820', '$2y$10$.X9Djz8p5JzAx7SmH2w5l.FwHzPQpuT/X/EZEhobKrzmnOXUvDnTa', 'active', NOW()),
(5, '11111111-1111-4111-8111-111111111105', 'Sophia', 'Lindqvist', 'sophia.client@travelplatform.local', '+46 70 123 4567', '$2y$10$.X9Djz8p5JzAx7SmH2w5l.FwHzPQpuT/X/EZEhobKrzmnOXUvDnTa', 'active', NOW()),
(6, '11111111-1111-4111-8111-111111111106', 'Rafael', 'Moreau', 'rafael.staff@travelplatform.local', '+33 6 42 18 90 12', '$2y$10$6Gkgy1gtZzW9nKFFlcqZSO8RvMaq5T7nc57Nr4R0MgVnFMfYU4DTq', 'active', NOW());

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 4), (6, 3);

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `manager_user_id`, `is_active`) VALUES
(1, 'Luxury Travel Concierge', 'LTC', 'Designs and oversees bespoke multi-country itineraries and private journeys.', 3, 1),
(2, 'People & Culture (HR)', 'HR', 'Talent operations, attendance, leave governance, and team wellbeing.', 2, 1),
(3, 'Expedition Operations', 'OPS', 'On-ground logistics, private aviation, lodges, and safety coordination.', 6, 1);

INSERT INTO `employees` (`id`, `user_id`, `department_id`, `employee_code`, `designation`, `employment_type`, `join_date`, `base_salary`, `commission_rate`, `emergency_contact`, `address`, `performance_score`, `status`) VALUES
(1, 2, 2, 'EMP-0001', 'Head of People & Operations', 'full_time', '2024-02-15', 7800.00, 0.00, 'David Sterling (+1 555-801-2211)', '42 Beacon Hill, Suite 3', 4.9, 'active'),
(2, 3, 1, 'EMP-0002', 'Senior Journey Architect', 'full_time', '2024-04-01', 6400.00, 6.50, 'Claire Beaumont (+1 555-809-1190)', '18 Harbor Crest Ave', 4.8, 'active'),
(3, 6, 3, 'EMP-0003', 'Expedition Operations Lead', 'full_time', '2024-07-10', 6100.00, 5.50, 'Luc Moreau (+33 6 11 22 33 44)', '95 Promenade Lane', 4.7, 'active');

INSERT INTO `employee_attendance` (`employee_id`, `attendance_date`, `check_in`, `check_out`, `status`, `notes`, `marked_by`) VALUES
(1, CURDATE(), '09:00:00', '17:45:00', 'present', 'On site', 2),
(2, CURDATE(), '09:10:00', '18:00:00', 'present', 'Client itinerary consultations', 2),
(3, CURDATE(), '09:25:00', '17:50:00', 'remote', 'Coordinating Alpine transfers', 2),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:55:00', '17:30:00', 'present', NULL, 2),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:05:00', '17:40:00', 'present', NULL, 2);

INSERT INTO `leave_requests` (`employee_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `reviewed_by`, `review_notes`, `reviewed_at`) VALUES
(2, 'annual', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY), 4, 'Family coastal retreat planned in advance.', 'pending', NULL, NULL, NULL),
(3, 'casual', DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 11 DAY), 2, 'Personal relocation errands.', 'approved', 2, 'Approved with handover coverage.', NOW());

INSERT INTO `customers` (`id`, `user_id`, `customer_code`, `date_of_birth`, `nationality`, `passport_number`, `address`, `city`, `country`, `travel_preferences`, `loyalty_tier`) VALUES
(1, 4, 'CUST-1001', '1988-06-19', 'United States', 'US9084122', '740 Mercer Street, Apt 8B', 'New York', 'United States', 'Boutique cliffside stays, private culinary tours, scenic rail', 'connoisseur'),
(2, 5, 'CUST-1002', '1991-11-03', 'Sweden', 'SE7741908', 'Strandvagen 22', 'Stockholm', 'Sweden', 'Eco-luxury lodges, wildlife photography, wellness retreats', 'voyager');

-- Global Branding Settings (Changeable anytime from Super Admin -> Website Branding)
INSERT INTO `branding_settings` (`id`, `website_name`, `company_name`, `website_tagline`, `company_description`, `primary_logo`, `secondary_logo`, `light_logo`, `dark_logo`, `favicon`, `primary_color`, `secondary_color`, `accent_color`, `contact_email`, `contact_number`, `company_address`, `social_links`) VALUES
(1, 'Aurelia Expeditions', 'Aurelia Luxury Travel & Concierge Ltd.', 'Curated Journeys Across Extraordinary Horizons', 'An editorial travel atelier designing bespoke international expeditions, private cultural immersions, and effortless sanctuary stays worldwide.', 'branding/logo-primary.svg', 'branding/logo-secondary.svg', 'branding/logo-light.svg', 'branding/logo-dark.svg', 'branding/favicon.svg', '#0B1D2E', '#1A4332', '#C59B5F', 'concierge@aurelia-expeditions.local', '+1 (800) 555-0194', '480 Horizon Promenade, Suite 900, New York, NY 10013', '{"instagram":"https://instagram.com","facebook":"https://facebook.com","twitter":"https://x.com","linkedin":"https://linkedin.com","youtube":"https://youtube.com"}');

-- Countries & Destinations
INSERT INTO `countries` (`id`, `name`, `code`, `region`, `currency`, `description`) VALUES
(1, 'Norway', 'NOR', 'Scandinavia', 'USD', 'Dramatic fjords, midnight sun, and minimalist Nordic sanctuaries.'),
(2, 'Italy', 'ITA', 'Mediterranean Europe', 'USD', 'Sun-drenched coastlines, Renaissance artistry, and vineyard estates.'),
(3, 'Japan', 'JPN', 'East Asia', 'USD', 'Timeless temples, cedar forests, Michelin ryokans, and alpine peaks.'),
(4, 'Tanzania', 'TZA', 'East Africa', 'USD', 'Infinite golden savannahs, conservation camps, and wildlife migrations.'),
(5, 'Switzerland', 'CHE', 'Central Europe', 'USD', 'Glacial alpine ridges, panoramic rail voyages, and wellness chalets.'),
(6, 'Maldives', 'MDV', 'Indian Ocean', 'USD', 'Private coral atolls, tranquil lagoons, and overwater sanctuaries.'),
(7, 'Chile', 'CHL', 'South America', 'USD', 'Wind-sculpted Patagonia granite towers and untouched southern wilderness.');

INSERT INTO `destinations` (`id`, `country_id`, `name`, `slug`, `location_label`, `tagline`, `summary`, `description`, `highlights`, `best_time_to_visit`, `climate`, `starting_price`, `hero_image`, `gallery`, `is_featured`, `sort_order`) VALUES
(1, 1, 'Norwegian Fjords', 'norwegian-fjords', 'Aurland & Geiranger, Norway', 'Silence Carved in Glacier & Emerald Water', 'Private fjord cruises, architectural timber retreats perched above cascading waterfalls, and Nordic gastronomy.', '<p>Sail along mirror-still glacial inlets framed by sheer granite walls rising a kilometer above sea level. Stay in architect-designed cedar pavilions with panoramic glass facades and wood-fired fjord saunas.</p><p>Each journey combines private electric yacht excursions, guided glacier walks, and intimate farm-to-table tasting menus showcasing wild Arctic seafood.</p>', '["Private Fjord Electric Yacht Charter","Architectural Cliffside Timber Lodges","Guided Jostedalsbreen Glacier Walk","Nordic Chef Tasting Experiences"]', 'May – September', 'Crisp Alpine Maritime (8°C – 19°C)', 3850.00, 'destinations/hero-fjord.jpg', '["destinations/hero-fjord.jpg","destinations/swiss-alps.jpg"]', 1, 1),
(2, 2, 'Amalfi Coast', 'amalfi-coast', 'Ravello & Positano, Italy', 'Cliffside Villas & Azure Mediterranean Horizons', 'Private Riva boat charters along limestone coves, cliff-top lemon groves, and historic palazzi.', '<p>Drift along the Tyrrhenian coastline aboard a classic mahogany vessel before ascending to Ravello''s terraced gardens. Days unfold between secluded cove swims, private vineyard harvests, and sunset aperitifs overlooking Positano.</p>', '["Private Classic Riva Yacht Day","Ravello Villa & Garden Access","Sommelier Coastal Vineyard Pairing","Sunset Cliffside Dining"]', 'April – October', 'Warm Mediterranean (18°C – 29°C)', 3490.00, 'destinations/amalfi-coast.jpg', '["destinations/amalfi-coast.jpg","destinations/hero-fjord.jpg"]', 1, 2),
(3, 3, 'Kyoto & Arashiyama', 'kyoto-arashiyama', 'Kyoto Prefecture, Japan', 'Whispering Bamboo Groves & Living Heritage', 'After-hours temple access, private kaiseki evenings with artisans, and secluded cedar ryokans.', '<p>Step beyond the curated tourist trail into Kyoto''s quiet wooden machiya lanes, moss-draped Zen gardens, and dawn rituals at vermilion mountain shrines before the city awakens.</p>', '["Dawn Private Shrine & Bamboo Walk","Master Tea Ceremony in Uji","Luxury Onsen Ryokan Suite","Multi-Course Seasonal Kaiseki"]', 'March – May & October – November', 'Temperate Four-Season (12°C – 24°C)', 4200.00, 'destinations/kyoto-sanctuary.jpg', '["destinations/kyoto-sanctuary.jpg"]', 1, 3),
(4, 4, 'Serengeti Plains', 'serengeti-plains', 'Northern Circuit, Tanzania', 'Golden Savannahs & Conservation Sanctuaries', 'Low-impact luxury canvas camps positioned along private wildlife corridors with expert naturalists.', '<p>Wake to dawn chorus over the endless plains before setting out in custom open-sided vehicles alongside veteran conservation guides. Return to candlelit bush dinners beneath a canopy of southern stars.</p>', '["Private 4x4 Conservation Game Drives","Dawn Hot-Air Balloon Over Plains","Luxury Tented Savannah Pavilions","Stargazing & Bush Dining"]', 'June – October & January – March', 'Warm Dry Savannah (16°C – 28°C)', 5400.00, 'destinations/serengeti-safari.jpg', '["destinations/serengeti-safari.jpg"]', 1, 4),
(5, 5, 'Swiss Alps & Engadin', 'swiss-alps-engadin', 'Zermatt & St. Moritz, Switzerland', 'Alpenglow Summits & Panoramic Rail Odyssey', 'First-class alpine rail carriages, private mountain guides, and thermal spa chalets.', '<p>Traverse snow-dusted passes aboard panoramic glass trains before retreating to timber-and-stone alpine sanctuaries overlooking turquoise glacial lakes.</p>', '["First-Class Panoramic Alpine Rail","Helicopter Glacier Overflight","Thermal Mineral Spa Access","Private Alpine Ridge Hiking"]', 'June – September & December – March', 'Crisp High Alpine (-2°C – 20°C)', 4650.00, 'destinations/swiss-alps.jpg', '["destinations/swiss-alps.jpg","destinations/hero-fjord.jpg"]', 1, 5),
(6, 6, 'Baa Atoll Sanctuary', 'baa-atoll-maldives', 'Baa Atoll, Maldives', 'Turquoise Coral Lagoons & Overwater Stillness', 'Seaplane transfers to private reef villas, marine biologist dives, and sandbank twilight dining.', '<p>Surrounded by UNESCO Biosphere Reserve waters, unwind on private teak decks suspended above bioluminescent coral gardens with dedicated island hosts.</p>', '["Seaplane Transfer & Overwater Villa","Manta Ray Marine Biologist Dive","Private Castaway Sandbank Dinner","Holistic Overwater Spa Rituals"]', 'November – April', 'Tropical Maritime (26°C – 31°C)', 4950.00, 'destinations/maldives-lagoon.jpg', '["destinations/maldives-lagoon.jpg"]', 1, 6),
(7, 7, 'Torres del Paine', 'torres-del-paine', 'Magallanes, Chilean Patagonia', 'Granite Spires at the Edge of the World', 'Guided wilderness traverses, glacial lake navigation, and eco-architectural estancias.', '<p>Witness first light ignite Patagonia''s jagged granite towers above mirror-still turquoise lakes while exploring remote windswept valleys with seasoned mountaineers.</p>', '["Private Patagonia Mountaineer Guide","Grey Glacier Boat Navigation","Architectural Wilderness Eco-Lodge","Estancia Culinary & Horseback Trail"]', 'October – April', 'Subpolar Alpine (4°C – 16°C)', 5150.00, 'destinations/patagonia-peaks.jpg', '["destinations/patagonia-peaks.jpg","destinations/swiss-alps.jpg"]', 1, 7);

-- 7 Travel Categories (exact match to requirements)
INSERT INTO `travel_categories` (`id`, `name`, `slug`, `tagline`, `description`, `icon`, `image`, `is_featured`, `sort_order`) VALUES
(1, 'Adventure Tours', 'adventure-tours', 'High-altitude trails, glacier crossings & wild frontiers', 'Expeditions crafted for curious explorers seeking rugged beauty paired with thoughtful comfort.', 'mountain', 'destinations/patagonia-peaks.jpg', 1, 1),
(2, 'Family Holidays', 'family-holidays', 'Multi-generational journeys with effortless pacing', 'Thoughtfully balanced itineraries combining interactive discovery, private villas, and dedicated guides.', 'users', 'destinations/swiss-alps.jpg', 1, 2),
(3, 'Honeymoon Packages', 'honeymoon-packages', 'Intimate sanctuaries & candlelit coastal escapes', 'Secluded overwater retreats, cliffside hideaways, and unhurried moments designed for two.', 'heart', 'destinations/maldives-lagoon.jpg', 1, 3),
(4, 'Cultural Tours', 'cultural-tours', 'Living heritage, architecture & artisan encounters', 'Behind-the-scenes access to historic sanctuaries, local masters, and regional gastronomy.', 'landmark', 'destinations/kyoto-sanctuary.jpg', 1, 4),
(5, 'Wildlife Tours', 'wildlife-tours', 'Ethical safaris & private conservation sanctuaries', 'Close encounters in untamed ecosystems guided by passionate naturalists and trackers.', 'binoculars', 'destinations/serengeti-safari.jpg', 1, 5),
(6, 'Luxury Travel', 'luxury-travel', 'Private aviation, iconic suites & bespoke concierges', 'Seamless white-glove curation across the world''s finest architectural lodges and vessels.', 'gem', 'destinations/amalfi-coast.jpg', 1, 6),
(7, 'Weekend Getaways', 'weekend-getaways', 'Restorative 3-to-4 day escapes without friction', 'Short-haul alpine chalets, fjord cabins, and coastal retreats calibrated for rapid renewal.', 'sun', 'destinations/hero-fjord.jpg', 1, 7);

-- Featured Travel Packages & Seasonal Offers
INSERT INTO `travel_packages` (`id`, `destination_id`, `category_id`, `title`, `slug`, `package_code`, `subtitle`, `overview`, `duration_days`, `duration_nights`, `max_group_size`, `difficulty_level`, `base_price`, `discount_price`, `featured_image`, `is_featured`, `is_seasonal_offer`, `offer_badge`, `offer_valid_until`, `availability_status`, `available_slots`, `rating`, `review_count`) VALUES
(1, 1, 7, 'Nordic Fjord & Timber Sanctuary', 'nordic-fjord-timber-sanctuary', 'PKG-NOR-01', 'Private electric fjord cruising, cliffside saunas & Nordic tasting tables', '<p>Immerse yourself in the monumental stillness of Western Norway. Over six curated days, glide silently across UNESCO-protected fjords, hike glacier-fed valleys, and rest in architectural cliffside pavilions overlooking emerald waters.</p>', 6, 5, 8, 'Easy', 4150.00, 3850.00, 'destinations/hero-fjord.jpg', 1, 1, 'Summer Solstice Privilege', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'available', 14, 4.9, 34),
(2, 2, 6, 'Amalfi Cliffside & Riva Odyssey', 'amalfi-cliffside-riva-odyssey', 'PKG-ITA-02', 'Seven days between Ravello palazzi, Capri sea caves & private vineyards', '<p>Experience Southern Italy at its most poetic. Arrive by private transfer to a 13th-century cliffside palazzo, cruise past Positano in a classic mahogany Riva, and dine beneath terraced lemon pergolas.</p>', 7, 6, 10, 'Easy', 3890.00, 3490.00, 'destinations/amalfi-coast.jpg', 1, 1, 'Early Autumn Escapes', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'available', 10, 5.0, 42),
(3, 3, 4, 'Kyoto Dawn & Zen Sanctuary Trail', 'kyoto-dawn-zen-sanctuary', 'PKG-JPN-03', 'Private temple openings, Uji tea masters & cedar ryokan retreats', '<p>Unlock Kyoto''s quietest sanctuaries with exclusive dawn access to vermilion torii trails and moss-garden temples, paired with kaiseki gastronomy and mineral onsen soaking.</p>', 8, 7, 8, 'Moderate', 4450.00, NULL, 'destinations/kyoto-sanctuary.jpg', 1, 0, NULL, NULL, 'limited', 6, 4.9, 29),
(4, 4, 5, 'Serengeti Golden Plains Private Safari', 'serengeti-golden-plains-safari', 'PKG-TZA-04', 'Conservation-led game drives, dawn balloon flight & luxury canvas camp', '<p>Follow the rhythms of East Africa''s great wilderness from intimate solar-powered canvas pavilions. Track prides at sunrise with private wildlife guides and toast the horizon over starlit savannah dinners.</p>', 7, 6, 6, 'Moderate', 5800.00, 5400.00, 'destinations/serengeti-safari.jpg', 1, 1, 'Migration Season Offer', DATE_ADD(CURDATE(), INTERVAL 75 DAY), 'available', 8, 5.0, 27),
(5, 5, 2, 'Alpine Peaks & Panoramic Rail Journey', 'alpine-peaks-panoramic-rail', 'PKG-CHE-05', 'Glacier Express first-class cabins, family chalet suites & mountain guides', '<p>Designed for effortless multi-generational discovery across Zermatt and the Engadin valley, combining scenic rail passages, alpine meadow picnics, and restorative spa afternoons.</p>', 6, 5, 12, 'Easy', 4650.00, NULL, 'destinations/swiss-alps.jpg', 1, 0, NULL, NULL, 'available', 16, 4.8, 21),
(6, 6, 3, 'Maldives Lagoon Overwater Honeymoon', 'maldives-lagoon-overwater-honeymoon', 'PKG-MDV-06', 'Seaplane arrival, private reef villa, sandbank dining & couple spa rituals', '<p>Surrender to unhurried ocean rhythm in a private overwater sanctuary suspended above crystalline coral gardens in the Baa Atoll Biosphere Reserve.</p>', 5, 4, 2, 'Relaxed', 5350.00, 4950.00, 'destinations/maldives-lagoon.jpg', 1, 1, 'Private Honeymoon Gift', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'available', 12, 5.0, 38),
(7, 7, 1, 'Patagonia Granite Spires Expedition', 'patagonia-granite-spires-expedition', 'PKG-CHL-07', 'Guided Torres del Paine traverses, glacial navigation & luxury estancia', '<p>Venture to the dramatic edge of South America with private mountaineering guides, staying in architectural eco-lodges framed by towering granite peaks.</p>', 9, 8, 8, 'Challenging', 5150.00, NULL, 'destinations/patagonia-peaks.jpg', 1, 0, NULL, NULL, 'limited', 5, 4.9, 19);

-- Detailed Itineraries for Packages
INSERT INTO `package_itineraries` (`package_id`, `day_number`, `title`, `location`, `description`, `meals`, `accommodation`) VALUES
(1, 1, 'Bergen Arrival & Private Fjord Transfer', 'Bergen to Aurland', 'Private chauffeur reception at Bergen Airport followed by a scenic mountain pass transfer to your timber fjord pavilion. Evening welcome tasting menu.', 'Dinner', 'Aurland Cliffside Sanctuary'),
(1, 2, 'Silent Electric Yacht Through Nærøyfjord', 'Nærøyfjord', 'Board a private zero-emission vessel gliding beneath 1,000-meter waterfalls and remote Viking-era hamlets with a Nordic historian.', 'Breakfast, Lunch', 'Aurland Cliffside Sanctuary'),
(1, 3, 'Glacier Ridge Walk & Wood-Fired Sauna', 'Jostedalsbreen Edge', 'Morning guided trek across ancient blue glacial ice, returning for cold-water fjord plunging and cedar sauna ritual.', 'Breakfast, Dinner', 'Aurland Cliffside Sanctuary'),
(1, 4, 'Mountain Farm Culinary Immersion', 'Flåm Highlands', 'Visit an organic hillside dairy reachable only by mountain trail for artisan cheese pairing and sunset cider tasting.', 'Breakfast, Lunch, Dinner', 'Aurland Cliffside Sanctuary'),
(2, 1, 'Naples Touchdown & Ravello Cliffside Arrival', 'Ravello', 'Chauffeur escort along the coastal corniche to your historic Ravello villa overlooking the Gulf of Salerno.', 'Dinner', 'Palazzo Ravello Residence'),
(2, 2, 'Classic Mahogany Riva Coastline & Capri', 'Amalfi to Capri', 'Full-day private boat charter swimming in hidden limestone grottoes and anchoring off Capri for lunch.', 'Breakfast, Lunch', 'Palazzo Ravello Residence'),
(2, 3, 'Terraced Lemon Groves & Private Cooking Atelier', 'Amalfi Valley', 'Guided walk through centuries-old lemon pergolas followed by a hands-on coastal pasta and pastry workshop.', 'Breakfast, Dinner', 'Palazzo Ravello Residence'),
(3, 1, 'Kyoto Arrival & Machiya Evening Walk', 'Gion District', 'Settle into your restored cedar ryokan before an atmospheric lantern walk through Kyoto''s historic geisha quarters.', 'Dinner', 'Kyoto Cedar Ryokan'),
(3, 2, 'Dawn Bamboo Sanctuary & Private Shrine Access', 'Arashiyama & Fushimi', 'Enter the bamboo groves at sunrise hours ahead of crowds, followed by a curated monk-led temple meditation.', 'Breakfast, Lunch', 'Kyoto Cedar Ryokan'),
(3, 3, 'Uji Tea Masters & Kaiseki Finale', 'Uji & Higashiyama', 'Private heritage tea harvest tasting and an intimate multi-course kaiseki dinner celebrating seasonal Kyoto produce.', 'Breakfast, Dinner', 'Kyoto Cedar Ryokan'),
(4, 1, 'Kilimanjaro Arrival & Bush Plane to Serengeti', 'Central Serengeti', 'Charter flight over the Rift Valley directly to your private canvas camp, followed by an afternoon sunset game drive.', 'Lunch, Dinner', 'Serengeti Canopy Camp'),
(4, 2, 'Dawn Balloon Flight & Great Plains Tracking', 'Serengeti Plains', 'Float silently over migrating herds at daybreak before landing for a champagne bush breakfast.', 'Breakfast, Lunch, Dinner', 'Serengeti Canopy Camp'),
(6, 1, 'Seaplane Touchdown & Lagoon Villa Welcome', 'Baa Atoll', 'Scenic twin-otter seaplane transfer over coral rings to your private overwater deck with sunset infinity pool.', 'Dinner', 'Baa Atoll Overwater Sanctuary'),
(6, 2, 'Marine Biologist Snorkel & Castaway Sandbank', 'Hanifaru Bay', 'Guided coral restoration dive followed by a private lantern-lit dinner sculpted into an uninhabited sandbank.', 'Breakfast, Dinner', 'Baa Atoll Overwater Sanctuary');

INSERT INTO `package_images` (`package_id`, `image_path`, `caption`, `is_primary`, `sort_order`) VALUES
(1, 'destinations/hero-fjord.jpg', 'Timber cliffside pavilion over misty Nordic waters', 1, 1),
(1, 'destinations/swiss-alps.jpg', 'High alpine glacial vistas', 0, 2),
(2, 'destinations/amalfi-coast.jpg', 'Amalfi terraced coastline at twilight', 1, 1),
(3, 'destinations/kyoto-sanctuary.jpg', 'Dawn light across Kyoto sanctuary gate', 1, 1),
(4, 'destinations/serengeti-safari.jpg', 'Savannah pavilion at sunset', 1, 1),
(5, 'destinations/swiss-alps.jpg', 'Alpenglow ridges and timber chalet', 1, 1),
(6, 'destinations/maldives-lagoon.jpg', 'Overwater villas above turquoise lagoon', 1, 1),
(7, 'destinations/patagonia-peaks.jpg', 'Torres del Paine granite towers', 1, 1);

INSERT INTO `package_inclusions` (`package_id`, `item_text`, `sort_order`) VALUES
(1, 'Private airport & inter-fjord chauffeured transfers', 1),
(1, '5 nights in architect-designed fjord sanctuary suite', 2),
(1, 'Private electric yacht charter & certified glacier guide', 3),
(1, 'Daily Nordic breakfast & two signature tasting dinners', 4),
(2, 'Private Mercedes V-Class transfers & porterage', 1),
(2, '6 nights cliffside boutique suite with sea terrace', 2),
(2, 'Full-day private Riva yacht charter with skipper', 3),
(3, '7 nights luxury ryokan & private Machiya heritage stay', 1),
(3, 'Dedicated bilingual cultural specialist throughout', 2),
(4, 'Scenic bush charter flights & private 4x4 safari vehicle', 1),
(4, 'All-inclusive fine dining & conservation park fees', 2),
(6, 'Round-trip private seaplane lounge & reef transfers', 1),
(6, 'Private sandbank twilight dinner & couple spa ritual', 2);

INSERT INTO `package_exclusions` (`package_id`, `item_text`, `sort_order`) VALUES
(1, 'International airfare to and from Bergen (available on request)', 1),
(1, 'Comprehensive travel & medical evacuation insurance', 2),
(2, 'International flights & personal gratuities', 1),
(3, 'International flights & personal shopping', 1),
(4, 'International flights & visa fees', 1),
(6, 'International arrival flights to Male (MLE)', 1);

INSERT INTO `seasonal_offers` (`title`, `code`, `description`, `discount_type`, `discount_value`, `min_booking_amount`, `package_id`, `banner_image`, `start_date`, `end_date`, `is_active`) VALUES
('Summer Solstice Fjord Privilege', 'SOLSTICE10', 'Enjoy 10% savings on private Nordic Fjord & Timber Sanctuary departures booked this season.', 'percentage', 10.00, 3000.00, 1, 'destinations/hero-fjord.jpg', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 1),
('Private Atelier Welcome Credit', 'ATELIER250', 'Receive $250 instant voyage credit on any curated package over $2,500.', 'fixed', 250.00, 2500.00, NULL, 'destinations/amalfi-coast.jpg', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 1);

-- Bookings, Travellers, Payments, Invoices, Expenses, Commissions
INSERT INTO `bookings` (`id`, `booking_reference`, `customer_id`, `package_id`, `assigned_employee_id`, `travel_date`, `return_date`, `adults`, `children`, `total_travellers`, `base_amount`, `discount_amount`, `tax_amount`, `total_amount`, `paid_amount`, `currency`, `booking_status`, `payment_status`, `payment_method`, `special_requests`, `created_by`) VALUES
(1, 'BK-2026-00101', 1, 1, 2, DATE_ADD(CURDATE(), INTERVAL 42 DAY), DATE_ADD(CURDATE(), INTERVAL 48 DAY), 2, 0, 2, 7700.00, 250.00, 596.00, 8046.00, 8046.00, 'USD', 'Paid', 'paid', 'online_portal', 'Upper-level fjord suite preferred; vegetarian tasting option for one guest.', 4),
(2, 'BK-2026-00102', 2, 4, 2, DATE_ADD(CURDATE(), INTERVAL 65 DAY), DATE_ADD(CURDATE(), INTERVAL 72 DAY), 2, 0, 2, 10800.00, 0.00, 864.00, 11664.00, 5000.00, 'USD', 'Partially Paid', 'partial', 'bank_transfer', 'Window seats on bush plane for wildlife photography equipment.', 5),
(3, 'BK-2026-00103', 1, 2, 3, DATE_ADD(CURDATE(), INTERVAL 95 DAY), DATE_ADD(CURDATE(), INTERVAL 102 DAY), 2, 1, 3, 10470.00, 0.00, 837.60, 11307.60, 0.00, 'USD', 'Confirmed', 'unpaid', 'bank_transfer', 'Adjoining family suite in Ravello.', 1);

INSERT INTO `booking_travellers` (`booking_id`, `full_name`, `email`, `phone`, `age`, `gender`, `nationality`, `is_lead`) VALUES
(1, 'Julian Mercer', 'customer@travelplatform.local', '+1 (555) 390-1820', 38, 'Male', 'United States', 1),
(1, 'Claire Mercer', 'claire.mercer@example.local', '+1 (555) 390-1821', 36, 'Female', 'United States', 0),
(2, 'Sophia Lindqvist', 'sophia.client@travelplatform.local', '+46 70 123 4567', 35, 'Female', 'Sweden', 1),
(2, 'Henrik Lindqvist', 'henrik@example.local', '+46 70 123 4568', 37, 'Male', 'Sweden', 0),
(3, 'Julian Mercer', 'customer@travelplatform.local', '+1 (555) 390-1820', 38, 'Male', 'United States', 1);

INSERT INTO `payments` (`payment_reference`, `booking_id`, `customer_id`, `amount`, `currency`, `payment_method`, `gateway_name`, `gateway_transaction_id`, `status`, `paid_at`, `notes`, `recorded_by`) VALUES
('PAY-2026-9001', 1, 1, 8046.00, 'USD', 'online_portal', 'Verified Settlement Portal', 'TXN-8891204A', 'completed', DATE_SUB(NOW(), INTERVAL 18 DAY), 'Full settlement received.', 1),
('PAY-2026-9002', 2, 2, 5000.00, 'USD', 'bank_transfer', 'Corporate Wire', 'WIRE-449012', 'completed', DATE_SUB(NOW(), INTERVAL 6 DAY), '50% advance deposit confirmed.', 1);

INSERT INTO `invoices` (`invoice_number`, `booking_id`, `customer_id`, `subtotal`, `discount_amount`, `tax_amount`, `total_amount`, `amount_paid`, `balance_due`, `status`, `issued_at`, `due_date`) VALUES
('INV-2026-00001', 1, 1, 7700.00, 250.00, 596.00, 8046.00, 8046.00, 0.00, 'paid', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('INV-2026-00002', 2, 2, 10800.00, 0.00, 864.00, 11664.00, 5000.00, 6664.00, 'partially_paid', DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
('INV-2026-00003', 3, 1, 10470.00, 0.00, 837.60, 11307.60, 0.00, 11307.60, 'issued', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY));

INSERT INTO `expenses` (`expense_reference`, `category`, `title`, `description`, `amount`, `expense_date`, `vendor_name`, `booking_id`, `recorded_by`) VALUES
('EXP-2026-301', 'accommodation', 'Aurland Sanctuary Suite Deposit', '5-night private pavilion allocation for BK-2026-00101', 3400.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Aurland Fjord Lodges AS', 1, 1),
('EXP-2026-302', 'marketing', 'Editorial Print & Digital Feature', 'Seasonal destination showcase campaign', 1250.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Monocle Atelier Media', NULL, 1),
('EXP-2026-303', 'transport', 'Private Bush Charter Advance', 'Kilimanjaro to Serengeti charter seat deposit', 1800.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Coastal Aviation Ltd', 2, 1);

INSERT INTO `commissions` (`employee_id`, `booking_id`, `commission_rate`, `commission_amount`, `status`, `notes`) VALUES
(2, 1, 6.50, 522.99, 'paid', 'Paid with monthly settlement'),
(2, 2, 6.50, 758.16, 'approved', 'Pending final balance settlement');

INSERT INTO `work_assignments` (`employee_id`, `booking_id`, `title`, `description`, `priority`, `due_date`, `status`, `assigned_by`) VALUES
(2, 1, 'Finalize Fjord Dietary & Yacht Briefing', 'Confirm vegetarian tasting course and private skippered departure pier.', 'high', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'in_progress', 2),
(2, 2, 'Confirm Serengeti Balloon Flight Window', 'Coordinate dawn launch weather slot with Serengeti Canopy Camp.', 'medium', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'pending', 2),
(3, 3, 'Secure Adjoining Ravello Suites', 'Verify ocean-facing terrace room numbers and Riva dock permit.', 'high', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'in_progress', 1);

-- Testimonials, FAQs, Blog Posts
INSERT INTO `testimonials` (`customer_name`, `customer_location`, `package_id`, `rating`, `quote`, `trip_taken`, `is_featured`, `is_approved`, `sort_order`) VALUES
('Julian & Claire Mercer', 'New York, USA', 1, 5, 'Every transition felt invisible. Standing on our cliffside deck over the Aurland fjord at 5 a.m. while mist lifted off the water remains the single most restorative week we have ever spent.', 'Nordic Fjord & Timber Sanctuary', 1, 1, 1),
('Sophia Lindqvist', 'Stockholm, Sweden', 4, 5, 'Our specialist knew the exact camp orientation for golden-hour photography and arranged a conservation briefing we never could have discovered independently.', 'Serengeti Golden Plains Private Safari', 1, 1, 2),
('Dr. Alistair & Maya Chen', 'London, UK', 3, 5, 'Entering Kyoto''s bamboo forest in total silence at daybreak changed how we think about travel. Thoughtful pacing, extraordinary hosts, zero tourist friction.', 'Kyoto Dawn & Zen Sanctuary Trail', 1, 1, 3);

INSERT INTO `faqs` (`category`, `question`, `answer`, `sort_order`, `is_active`) VALUES
('Booking & Planning', 'How far in advance should I reserve a bespoke or signature package?', 'We recommend reserving 3 to 6 months ahead for peak seasonal departures such as the Serengeti migration, Kyoto autumn foliage, or summer Norwegian fjords, though our concierge team regularly crafts short-notice escapes within two weeks.', 1, 1),
('Payments & Invoices', 'How do deposits, balance payments, and invoices work?', 'Once your itinerary is confirmed, an initial reservation deposit secures all lodges and private charters. You can track real-time payment milestones and download official PDF invoices anytime inside your Customer Dashboard.', 2, 1),
('Customization', 'Can every package itinerary be tailored to private dates and preferences?', 'Yes. Every itinerary serves as a curated blueprint. Our Journey Architects adjust pacing, room categories, dietary needs, private aviation, and special celebrations through our Custom Tour Enquiry workflow.', 3, 1),
('Safety & Support', 'Do we receive dedicated concierge assistance while travelling?', 'Every confirmed booking is assigned a dedicated specialist and 24/7 operations support line accessible directly via your booking workspace.', 4, 1);

INSERT INTO `blog_posts` (`title`, `slug`, `category`, `excerpt`, `content`, `featured_image`, `author_id`, `read_time`, `status`, `is_featured`, `published_at`) VALUES
('The Art of Slow Fjord Travel in Western Norway', 'art-of-slow-fjord-travel-norway', 'Editorial Guide', 'Why trading rapid sightseeing checklists for three unhurried nights above Aurland transforms the Nordic experience.', '<p>Most travellers glimpse the Norwegian fjords from a crowded deck for ninety minutes before racing onward. Yet the fjord reveals its true character only when the day ferries depart and silence settles across the valley.</p><p>Spending dawn on a cedar terrace overlooking mirror-still emerald water allows light, mist, and mountain echoes to set your tempo. Pair silent electric cruising with long-table Nordic lunches sourced from alpine pastures miles above the shoreline.</p>', 'destinations/hero-fjord.jpg', 1, '6 min read', 'published', 1, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('Dawn Before Crowds: Inside Kyoto''s Quietest Sanctuaries', 'dawn-before-crowds-kyoto-sanctuaries', 'Cultural Journal', 'Navigating cedar temples, moss gardens, and artisan tea houses through early-morning mindfulness.', '<p>To experience Kyoto intimately is to align your rhythm with temple bells at 6:00 a.m. Stone lanterns flicker along damp moss corridors while morning sunlight filters through towering bamboo.</p><p>Our cultural specialists partner with local stewards and Uji tea families to open doors long before standard visiting hours begin.</p>', 'destinations/kyoto-sanctuary.jpg', 3, '5 min read', 'published', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Conservation-First Luxury Across the Serengeti', 'conservation-first-luxury-serengeti', 'Wilderness Notes', 'How low-footprint architectural camps fund habitat corridors while elevating the safari experience.', '<p>True wilderness luxury is measured in space, silence, and stewardship. Modern solar-powered canvas pavilions leave zero permanent trace when seasons shift, yet deliver serene comfort deep inside protected wildlife corridors.</p>', 'destinations/serengeti-safari.jpg', 3, '7 min read', 'published', 0, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Website Pages
INSERT INTO `website_pages` (`title`, `slug`, `subtitle`, `hero_image`, `content`, `template`, `is_system`, `is_active`) VALUES
('About Us', 'about', 'Designing unhurried, deeply researched journeys shaped by local craft and quiet luxury.', 'media/custom-atelier.jpg', '<h3>Our Philosophy</h3><p>Founded as an independent travel design atelier, we believe extraordinary travel is defined not by mileage covered, but by stillness, cultural authenticity, and seamless care behind the scenes.</p><h3>Bespoke Craft & Dedicated Specialists</h3><p>Every journey is personally architected by regional specialists who inspect each sanctuary lodge, private vessel, and trail firsthand. From initial discovery to return home, a dedicated concierge oversees every transfer, suite preference, and milestone.</p>', 'about', 1, 1),
('Privacy Policy', 'privacy-policy', 'How we protect your personal information, booking records, and travel preferences.', NULL, '<h3>1. Information We Collect</h3><p>We collect only the personal, passport, and itinerary details necessary to arrange your travel reservations, issue invoices, and provide concierge support.</p><h3>2. Payment Security</h3><p>We never store raw payment card numbers or CVV codes on our servers. All transactions are tracked using secure external gateway references and verified settlement receipts.</p><h3>3. Data Access & Rights</h3><p>You may review, export, or request updates to your personal profile anytime through your authenticated Customer Dashboard.</p>', 'legal', 1, 1),
('Terms & Conditions', 'terms-and-conditions', 'Clear terms governing curated bookings, reservations, payments, and cancellations.', NULL, '<h3>1. Reservations & Confirmation</h3><p>All travel bookings remain provisional until confirmed by an assigned Travel Specialist and acknowledged via official booking confirmation and invoice.</p><h3>2. Payments & Balances</h3><p>Deposits secure lodges, charters, and permits. Remaining balances are due prior to departure in accordance with your invoice schedule.</p><h3>3. Cancellations & Refunds</h3><p>Cancellations and refund requests are handled transparently through your dashboard in alignment with supplier and seasonal policy windows.</p>', 'legal', 1, 1);

-- Homepage Sections (12-section architecture manageable from Super Admin)
INSERT INTO `homepage_sections` (`section_key`, `title`, `subtitle`, `badge_text`, `cta_text`, `cta_link`, `background_image`, `sort_order`, `is_active`) VALUES
('hero', 'Journeys Crafted Beyond Ordinary Maps', 'Private sanctuaries, architectural wilderness lodges, and effortless concierge orchestration across six continents.', 'Bespoke Travel Atelier', 'Explore Destinations', '/destinations', 'destinations/hero-fjord.jpg', 1, 1),
('featured_destinations', 'Iconic Horizons, Quietly Accessed', 'Hand-selected regions chosen for dramatic natural architecture, living heritage, and restorative seclusion.', 'Featured Destinations', 'View All Destinations', '/destinations', NULL, 2, 1),
('travel_categories', 'Seven Ways to Experience the World', 'Filter expeditions by atmosphere—from serene coastal honeymoons to alpine traverses and conservation safaris.', 'Explore Travel Styles', 'Browse All Categories', '/categories', NULL, 3, 1),
('featured_packages', 'Signature Curated Voyages', 'Complete day-by-day blueprints engineered with private transfers, acclaimed stays, and local experts.', 'Curated Packages', 'Explore All Packages', '/packages', NULL, 4, 1),
('why_choose_us', 'Precision Concierge, Unmistakable Soul', 'Behind every effortless morning on the road lies meticulous logistics, vetted private hosts, and human care.', 'Why Travel With Us', 'Read Our Story', '/about', 'destinations/kyoto-sanctuary.jpg', 5, 1),
('custom_experience', 'Your Compass. Your Pace. Designed From Blank Paper.', 'Celebrating a milestone, planning a multi-generational retreat, or seeking off-map solitude? Our atelier tailors every detail.', 'Private Atelier', 'Request Custom Itinerary', '/custom-enquiry', 'media/custom-atelier.jpg', 6, 1),
('how_it_works', 'From First Spark to Seamless Return', 'Four calm, transparent stages connecting inspiration to confirmed departure and real-time trip tracking.', 'How Booking Works', 'Start a Booking', '/packages', NULL, 7, 1),
('testimonials', 'Words From Returning Voyagers', 'Reflections shared by travellers who entrusted their milestone journeys to our specialists.', 'Guest Perspectives', 'Read All Stories', '/testimonials', NULL, 8, 1),
('seasonal_offers', 'Limited Seasonal Privileges', 'Private savings and curated voyage credits available on select upcoming seasonal departures.', 'Seasonal Offers', 'Claim Seasonal Offer', '/packages?offer=1', NULL, 9, 1),
('blog', 'The Dispatch: Field Notes & Stories', 'Essays, architectural lodge dispatches, and cultural guides written by our regional scouts.', 'Travel Inspiration', 'Read The Journal', '/blog', NULL, 10, 1),
('newsletter', 'Receive Private Dispatches & Seasonal Openings', 'Occasional notes on newly opened sanctuaries, migration windows, and limited departure invitations.', 'Editorial Dispatch', 'Subscribe', '/api/newsletter', NULL, 11, 1);

-- Navigation Menus
INSERT INTO `website_menus` (`location`, `label`, `url`, `sort_order`, `is_active`) VALUES
('header', 'Destinations', '/destinations', 1, 1),
('header', 'Packages', '/packages', 2, 1),
('header', 'Categories', '/categories', 3, 1),
('header', 'Custom Tour', '/custom-enquiry', 4, 1),
('header', 'Journal', '/blog', 5, 1),
('header', 'About', '/about', 6, 1),
('header', 'Contact', '/contact', 7, 1),
('footer_explore', 'All Destinations', '/destinations', 1, 1),
('footer_explore', 'Signature Packages', '/packages', 2, 1),
('footer_explore', 'Tour Categories', '/categories', 3, 1),
('footer_explore', 'Custom Tour Enquiry', '/custom-enquiry', 4, 1),
('footer_company', 'About Us', '/about', 1, 1),
('footer_company', 'Travel Stories', '/blog', 2, 1),
('footer_company', 'Guest Testimonials', '/testimonials', 3, 1),
('footer_company', 'FAQ', '/faq', 4, 1),
('footer_company', 'Contact Concierge', '/contact', 5, 1),
('footer_legal', 'Privacy Policy', '/privacy-policy', 1, 1),
('footer_legal', 'Terms & Conditions', '/terms-and-conditions', 2, 1);

INSERT INTO `media_library` (`title`, `file_path`, `file_type`, `file_size`, `alt_text`, `folder`, `uploaded_by`) VALUES
('Norwegian Fjords Sunrise', 'destinations/hero-fjord.jpg', 'image/jpeg', 851244, 'Misty Norwegian fjord cliffside pavilion at sunrise', 'destinations', 1),
('Amalfi Coast Dusk', 'destinations/amalfi-coast.jpg', 'image/jpeg', 1064964, 'Amalfi terraced cliffside villas at twilight', 'destinations', 1),
('Kyoto Bamboo Sanctuary', 'destinations/kyoto-sanctuary.jpg', 'image/jpeg', 1082673, 'Vermilion gate and bamboo grove in Kyoto mist', 'destinations', 1),
('Serengeti Safari Pavilion', 'destinations/serengeti-safari.jpg', 'image/jpeg', 868958, 'Luxury safari pavilion overlooking Serengeti plains', 'destinations', 1),
('Swiss Alps Lodge', 'destinations/swiss-alps.jpg', 'image/jpeg', 934504, 'Snow-capped Swiss Alpine peaks above timber lodge', 'destinations', 1),
('Maldives Lagoon Villas', 'destinations/maldives-lagoon.jpg', 'image/jpeg', 708115, 'Overwater luxury villas in Maldives lagoon', 'destinations', 1),
('Patagonia Granite Peaks', 'destinations/patagonia-peaks.jpg', 'image/jpeg', 974354, 'Torres del Paine granite towers at sunrise', 'destinations', 1),
('Travel Design Atelier', 'media/custom-atelier.jpg', 'image/jpeg', 920000, 'Private luxury travel atelier desk with compass and maps', 'media', 1);

-- SEO Settings
INSERT INTO `seo_settings` (`page_key`, `meta_title`, `meta_description`, `meta_keywords`, `robots`) VALUES
('global', 'Curated Luxury Journeys & Bespoke Expeditions', 'Bespoke international travel packages, private sanctuaries, and concierge trip management.', 'luxury travel, bespoke tours, holiday packages, custom travel agency', 'index, follow'),
('home', 'Curated Journeys Beyond Ordinary Maps', 'Explore iconic global destinations, signature travel packages, and bespoke private itineraries.', 'curated travel, luxury holidays, destinations, travel packages', 'index, follow'),
('destinations', 'Destinations | Iconic Global Horizons', 'Browse hand-selected destinations across Europe, Asia, Africa, and the Americas.', 'travel destinations, luxury places, world tours', 'index, follow'),
('packages', 'Travel Packages | Curated Multi-Day Expeditions', 'Explore all-inclusive curated itineraries with transparent pricing and day-by-day plans.', 'travel packages, tour packages, luxury itineraries', 'index, follow'),
('custom_enquiry', 'Custom Tour Enquiry | Private Itinerary Atelier', 'Design your personalized travel experience with our dedicated specialists.', 'custom tour, bespoke itinerary, private travel designer', 'index, follow');

-- Enquiries, Support, Wishlist, Notifications, Email Templates
INSERT INTO `enquiries` (`enquiry_reference`, `enquiry_type`, `customer_id`, `package_id`, `destination_interest`, `full_name`, `email`, `phone`, `travel_date`, `travellers_count`, `budget_range`, `subject`, `message`, `status`, `assigned_employee_id`) VALUES
('ENQ-2026-501', 'custom_tour', 1, NULL, 'Kyoto & Hokkaido', 'Julian Mercer', 'customer@travelplatform.local', '+1 (555) 390-1820', DATE_ADD(CURDATE(), INTERVAL 120 DAY), 2, '$10,000 - $18,000', 'Autumn Foliage Ryokan Circuit', 'Looking for an 11-night private architectural & culinary journey across Kyoto, Naoshima, and Hakone.', 'in_progress', 2),
('ENQ-2026-502', 'contact', NULL, NULL, 'Amalfi Coast', 'Elena Rostova', 'elena.r@example.local', '+44 7700 900123', DATE_ADD(CURDATE(), INTERVAL 75 DAY), 4, '$8,000 - $14,000', 'Private Family Villa Inquiry', 'Could you confirm availability for four adults in Ravello this September?', 'new', NULL);

INSERT INTO `support_requests` (`id`, `ticket_number`, `customer_id`, `booking_id`, `subject`, `category`, `priority`, `status`, `assigned_to`) VALUES
(1, 'SUP-2026-001', 1, 1, 'Airport Meet & Greet Arrival Flight Update', 'Transfer Coordination', 'medium', 'in_progress', 3);

INSERT INTO `support_replies` (`support_request_id`, `user_id`, `message`, `is_staff`) VALUES
(1, 4, 'Hello Clara, our Bergen arrival flight number has shifted to SK-287 landing at 11:20 AM. Could we update our chauffeur pickup?', 0),
(1, 3, 'Thank you Julian! I have updated your transfer manifest with our Bergen lead driver for 11:20 AM arrival.', 1);

INSERT INTO `wishlist` (`customer_id`, `item_type`, `package_id`, `destination_id`) VALUES
(1, 'package', 3, NULL),
(1, 'destination', NULL, 6),
(2, 'package', 7, NULL);

INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `is_read`) VALUES
(1, 'booking', 'Confirmed Booking BK-2026-00103', 'Booking BK-2026-00103 for Amalfi Cliffside & Riva Odyssey is confirmed and awaiting deposit.', '/admin/bookings/3', 0),
(1, 'enquiry', 'New Custom Tour Enquiry ENQ-2026-502', 'Elena Rostova requested availability for Amalfi Coast.', '/admin/enquiries', 0),
(2, 'leave', 'Pending Leave Request', 'Clara Beaumont requested 4 days of annual leave.', '/hr/leaves', 0),
(3, 'assignment', 'Assigned Booking BK-2026-00101', 'You have been assigned Nordic Fjord & Timber Sanctuary for Julian Mercer.', '/employee/bookings/1', 0),
(4, 'booking', 'Booking Paid: BK-2026-00101', 'Your invoice INV-2026-00001 has been settled in full. PDF ready for download.', '/customer/bookings/1', 0);

INSERT INTO `email_templates` (`template_key`, `name`, `subject`, `body_html`, `variables_hint`, `is_active`) VALUES
('booking_confirmation', 'Booking Confirmation & Reference', 'Booking Request Received ({{booking_reference}}) - {{website_name}}', '<h2>Thank You, {{customer_name}}</h2><p>We have received your travel booking request <strong>{{booking_reference}}</strong> for <strong>{{package_title}}</strong>.</p><p><strong>Travel Date:</strong> {{travel_date}}<br><strong>Travellers:</strong> {{travellers}}<br><strong>Total Amount:</strong> {{total_amount}}</p><p>Our travel specialist will review your details and update your status shortly. You can track live progress anytime in your customer dashboard.</p>', '{{customer_name}}, {{booking_reference}}, {{package_title}}, {{travel_date}}, {{travellers}}, {{total_amount}}, {{website_name}}', 1),
('booking_status_update', 'Booking Status Changed', 'Booking {{booking_reference}} Status Updated: {{status}} - {{website_name}}', '<h2>Booking Update</h2><p>Hello {{customer_name}},</p><p>The status of your booking <strong>{{booking_reference}}</strong> ({{package_title}}) is now: <strong>{{status}}</strong>.</p><p>{{note}}</p><p>Visit your dashboard to view full itinerary details and invoices.</p>', '{{customer_name}}, {{booking_reference}}, {{package_title}}, {{status}}, {{note}}', 1),
('payment_receipt', 'Payment Receipt & Invoice', 'Payment Confirmed for {{booking_reference}} - {{website_name}}', '<h2>Payment Receipt</h2><p>Dear {{customer_name}},</p><p>We have recorded your payment of <strong>{{amount_paid}}</strong> (Ref: {{payment_reference}}) toward booking <strong>{{booking_reference}}</strong>.</p><p>Remaining balance: <strong>{{balance_due}}</strong>. Your updated PDF invoice is ready in your dashboard.</p>', '{{customer_name}}, {{booking_reference}}, {{amount_paid}}, {{payment_reference}}, {{balance_due}}', 1),
('password_reset', 'Password Reset Request', 'Reset Your Password - {{website_name}}', '<h2>Password Reset Instructions</h2><p>Hello {{customer_name}},</p><p>We received a request to reset your password for {{website_name}}. Use the secure link below within 60 minutes:</p><p><a href="{{reset_link}}" style="display:inline-block;padding:12px 24px;background:{{primary_color}};color:#ffffff;text-decoration:none;border-radius:6px;">Reset Password</a></p><p>If you did not request this, you can safely ignore this message.</p>', '{{customer_name}}, {{reset_link}}, {{website_name}}, {{primary_color}}', 1),
('welcome_customer', 'Welcome New Customer', 'Welcome to {{website_name}}', '<h2>Welcome, {{customer_name}}</h2><p>Thank you for creating your personal account with <strong>{{company_name}}</strong>. Explore curated global destinations, save favourites to your wishlist, and manage every booking seamlessly.</p>', '{{customer_name}}, {{website_name}}, {{company_name}}', 1),
('leave_decision', 'Leave Request Decision', 'Leave Request {{status}} - {{company_name}}', '<h2>Leave Request Update</h2><p>Hello {{employee_name}},</p><p>Your {{leave_type}} leave request ({{start_date}} to {{end_date}}) has been <strong>{{status}}</strong>.</p><p>Reviewer note: {{review_notes}}</p>', '{{employee_name}}, {{leave_type}}, {{start_date}}, {{end_date}}, {{status}}, {{review_notes}}', 1);

INSERT INTO `activity_logs` (`user_id`, `module`, `action`, `description`, `entity_type`, `entity_id`, `ip_address`) VALUES
(1, 'branding', 'publish', 'Published initial white-label website branding profile', 'branding_settings', 1, '127.0.0.1'),
(1, 'bookings', 'create', 'Created booking BK-2026-00103 for Julian Mercer', 'booking', 3, '127.0.0.1'),
(2, 'leaves', 'approve', 'Approved leave request for Rafael Moreau', 'leave_request', 2, '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;
