<?php
/**
 * Database Connection
 * Establishes a MySQLi connection to the vehicle_rental database.
 * Uses prepared statements throughout the application to prevent SQL injection.
 */

require_once __DIR__ . '/config.php';

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    $error_detail = '';
    if (defined('APP_DEBUG') && APP_DEBUG) {
        $error_detail = '<br><br><small>Error: ' . htmlspecialchars($conn->connect_error) . '</small>';
    }
    die('<div style="padding:20px;background:#fee;color:#c00;border:1px solid #c00;margin:20px;border-radius:5px;">
        <strong>Database Connection Failed:</strong> Unable to connect to the database. 
        Please make sure MySQL is running in XAMPP and the database "vehicle_rental" has been created.'
        . $error_detail .
    '</div>');
}

// Set character set to UTF-8 for proper encoding
$conn->set_charset('utf8mb4');
