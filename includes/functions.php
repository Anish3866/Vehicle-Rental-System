<?php
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function formatCurrency($amount) {
    return 'Rs. ' . number_format((float)$amount, 2);
}

function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('d M Y, h:i A', strtotime($datetime));
}

function generateTransactionCode() {
    return 'TXN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function calculateRentalDays($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $diff = $start->diff($end);
    return max(1, $diff->days);
}

function uploadImage($file, $targetDir) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'vehicle_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $targetPath = rtrim($targetDir, '/\\') . '/' . $filename;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }

    return false;
}

function getStatusBadgeClass($status) {
    $map = [
        'available'   => 'badge-success',
        'rented'      => 'badge-warning',
        'maintenance' => 'badge-info',
        'unavailable' => 'badge-danger',
        'pending'     => 'badge-warning',
        'confirmed'   => 'badge-info',
        'completed'   => 'badge-success',
        'cancelled'   => 'badge-danger',
        'unpaid'      => 'badge-warning',
        'paid'        => 'badge-success',
        'failed'      => 'badge-danger',
        'refunded'    => 'badge-info',
        'success'     => 'badge-success',
        'active'      => 'badge-success',
        'disabled'    => 'badge-danger',
    ];
    return $map[strtolower($status)] ?? 'badge-secondary';
}

function getVehicleImage($image) {
    if ($image && file_exists(UPLOAD_PATH . $image)) {
        return UPLOAD_URL . $image;
    }
    return SITE_URL . '/images/no-vehicle.png';
}

function truncateText($str, $length = 100) {
    if (strlen($str) <= $length) return $str;
    return substr($str, 0, $length) . '...';
}
