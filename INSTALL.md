# Travel Platform & Agency Management System — Local XAMPP Installation Guide

## Requirements
- **XAMPP** with **PHP 8.2+** (`pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`)
- **MySQL / MariaDB** (included in XAMPP)
- **Apache** with `mod_rewrite` enabled (enabled by default in XAMPP)

## Installation Steps

1. **Copy Project to XAMPP `htdocs`**
   - Place the entire project folder inside `C:\xampp\htdocs\travelsite` (or your chosen folder name, e.g. `C:\xampp\htdocs\myagency`).

2. **Start Apache & MySQL**
   - Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.

3. **Create & Import the Database**
   - Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
   - Click the **Import** tab.
   - Choose `database/database.sql` from the project directory and click **Go** (or **Import**).
   - This creates the `travelsite_db` database, all 49 relational tables, role permissions, default branding settings, email templates, and initial travel catalog data.

4. **Configure Credentials (if different from XAMPP defaults)**
   - Edit `config/database.php`:
     ```php
     return [
         'host' => '127.0.0.1',
         'port' => '3306',
         'database' => 'travelsite_db',
         'username' => 'root',
         'password' => '',
     ];
     ```
   - Edit `config/app.php` if you want to pin an explicit `base_url`, change timezone, currency symbol, or upload directory. Leave `'base_url' => ''` for automatic subfolder detection.

5. **Open the Platform in Your Browser**
   - Public Website: `http://localhost/travelsite/`
   - Login Portal: `http://localhost/travelsite/login`

## Default Seeded Accounts (Change Passwords After First Login)
| Role | Email | Password | Portal URL |
| --- | --- | --- | --- |
| Super Admin | `admin@travelplatform.local` | `Admin@123` | `/admin` |
| HR Manager | `hr@travelplatform.local` | `Hr@12345` | `/hr` |
| Employee / Specialist | `staff@travelplatform.local` | `Staff@123` | `/employee` |
| Customer | `customer@travelplatform.local` | `Traveler@123` | `/customer` |

## Dynamic White-Label Branding
Sign in as **Super Admin**, navigate to **Website & Brand -> Website Branding** (`/admin/branding`), and customize the Website Name, Company Name, Tagline, Logos, Favicon, Colors, Contact Information, and Social Links. Use **Preview Changes** before clicking **Publish Changes Platform-Wide**.
