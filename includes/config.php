<?php
/**
 * Application Configuration
 * Contains database credentials, site constants, and payment gateway settings.
 * IMPORTANT: Never expose this file in frontend or version control with real credentials.
 */

// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Default XAMPP password is empty
define('DB_NAME', 'vehicle_rental');

// ============================================================
// Application Settings
// ============================================================
define('SITE_NAME', 'Vehicle Rental System');

// Dynamically determine base URL from the server environment
$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_script = $_SERVER['SCRIPT_NAME'] ?? '';
// Walk up from the current script to the project root (one level above 'includes/', 'admin/', etc.)
$_base   = dirname($_script);
// If we are inside a subdirectory (e.g. admin/), go up one more level
if (basename($_base) === 'admin') {
    $_base = dirname($_base);
}
$_base = rtrim(str_replace('\\', '/', $_base), '/');
define('SITE_URL', $_scheme . '://' . $_host . $_base);
define('UPLOAD_PATH', __DIR__ . '/../uploads/vehicles/');
define('UPLOAD_URL', SITE_URL . '/uploads/vehicles/');

// ============================================================
// eSewa Payment Gateway Configuration (Sandbox / Test Mode)
// Documentation: https://developer.esewa.com.np
// ============================================================
define('ESEWA_SANDBOX', true);  // Set to false for production
define('ESEWA_MERCHANT_CODE', 'EPAYTEST');  // Sandbox merchant code
define('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q');  // Sandbox secret key
define('ESEWA_PAYMENT_URL', ESEWA_SANDBOX
    ? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
    : 'https://epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_VERIFY_URL', ESEWA_SANDBOX
    ? 'https://rc.esewa.com.np/api/epay/transaction/status/'
    : 'https://epay.esewa.com.np/api/epay/transaction/status/');

// eSewa Sandbox Test Credentials (for testing payments):
// Mobile: 9806800001 to 9806800005
// Password: Nepal@123
// MPIN: 1122
// OTP: 123456

// ============================================================
// Khalti Payment Gateway Configuration (Sandbox / Test Mode)
// Documentation: https://docs.khalti.com
// ============================================================
define('KHALTI_SANDBOX', true);  // Set to false for production
define('KHALTI_SECRET_KEY', 'YOUR_KHALTI_TEST_SECRET_KEY');  // Get from https://test-admin.khalti.com
define('KHALTI_INITIATE_URL', KHALTI_SANDBOX
    ? 'https://dev.khalti.com/api/v2/epayment/initiate/'
    : 'https://khalti.com/api/v2/epayment/initiate/');
define('KHALTI_LOOKUP_URL', KHALTI_SANDBOX
    ? 'https://dev.khalti.com/api/v2/epayment/lookup/'
    : 'https://khalti.com/api/v2/epayment/lookup/');

// Khalti Sandbox Test Credentials (for testing payments):
// Mobile: 9800000000 to 9800000005
// MPIN: 1111
// OTP: 987654

// ============================================================
// Error Reporting (Disable in production by setting to false)
// ============================================================
define('APP_DEBUG', true);  // Set to false in production
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
