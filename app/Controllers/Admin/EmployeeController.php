<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Hr\HrController;

/**
 * Super Admin Workforce Controller
 * Reuses HrController logic cleanly with admin portal shell and Super Admin privileges.
 */
class EmployeeController extends HrController
{
    public function __construct()
    {
        parent::__construct();
        $this->portal = 'admin';
        $this->prefix = '/admin';
    }
}
