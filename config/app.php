<?php
/**
 * Application Configuration
 * Update these settings for your local XAMPP or production environment.
 */

return [
    // Leave 'base_url' empty ('') for automatic detection (works for both localhost/folder and root hosts),
    // or specify explicitly e.g. 'http://localhost/travelsite'
    'base_url' => '',

    'timezone' => 'Asia/Kolkata',
    'env' => 'local', // 'local' or 'production'
    'debug' => true,

    // Storage and upload directories (relative to project root)
    'upload_dir' => 'public/uploads',
    'storage_dir' => 'storage',
    'max_upload_bytes' => 5 * 1024 * 1024, // 5 MB

    // Security settings
    'session_name' => 'TRAVEL_PLATFORM_SESSID',
    'session_lifetime' => 7200, // 2 hours
    'max_login_attempts' => 5,
    'lockout_minutes' => 15,

    // Default currency fallback
    'default_currency' => 'USD',
    'currency_symbol' => '$',
    'tax_rate' => 0.08, // 8% service & travel tax

    // Mail settings (uses PHP mail() if enabled, always logs to email_logs & storage/logs/mail.log)
    'mail' => [
        'enable_php_mail' => false,
        'from_name_fallback' => 'Travel Platform',
        'from_email_fallback' => 'noreply@localhost',
    ],
];
