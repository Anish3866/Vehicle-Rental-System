<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vehicle_rental');

define('SITE_NAME', 'RentRide');

$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_script = $_SERVER['SCRIPT_NAME'] ?? '';
$_base   = dirname($_script);
if (basename($_base) === 'admin') {
    $_base = dirname($_base);
}
$_base = rtrim(str_replace('\\', '/', $_base), '/');
define('SITE_URL', $_scheme . '://' . $_host . $_base);
define('UPLOAD_PATH', __DIR__ . '/../uploads/vehicles/');
define('UPLOAD_URL', SITE_URL . '/uploads/vehicles/');

define('ESEWA_SANDBOX', true);
define('ESEWA_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q');
define('ESEWA_PAYMENT_URL', ESEWA_SANDBOX
    ? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
    : 'https://epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_VERIFY_URL', ESEWA_SANDBOX
    ? 'https://rc.esewa.com.np/api/epay/transaction/status/'
    : 'https://epay.esewa.com.np/api/epay/transaction/status/');

define('KHALTI_SANDBOX', true);
define('KHALTI_SECRET_KEY', 'YOUR_KHALTI_TEST_SECRET_KEY');
define('KHALTI_INITIATE_URL', KHALTI_SANDBOX
    ? 'https://dev.khalti.com/api/v2/epayment/initiate/'
    : 'https://khalti.com/api/v2/epayment/initiate/');
define('KHALTI_LOOKUP_URL', KHALTI_SANDBOX
    ? 'https://dev.khalti.com/api/v2/epayment/lookup/'
    : 'https://khalti.com/api/v2/epayment/lookup/');

define('APP_DEBUG', true);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
