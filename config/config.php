<?php
// Application configuration
session_start();

// Base URL configuration
define('BASE_URL', 'http://localhost/restaurant_finder/');
define('ADMIN_URL', BASE_URL . 'admin/');

// File upload paths
define('UPLOAD_PATH', 'uploads/');
define('RESTAURANT_IMAGES_PATH', UPLOAD_PATH . 'restaurants/');
define('MENU_IMAGES_PATH', UPLOAD_PATH . 'menu/');
define('GALLERY_IMAGES_PATH', UPLOAD_PATH . 'gallery/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(RESTAURANT_IMAGES_PATH)) {
    mkdir(RESTAURANT_IMAGES_PATH, 0755, true);
}
if (!file_exists(MENU_IMAGES_PATH)) {
    mkdir(MENU_IMAGES_PATH, 0755, true);
}
if (!file_exists(GALLERY_IMAGES_PATH)) {
    mkdir(GALLERY_IMAGES_PATH, 0755, true);
}

// Include database configuration
require_once 'database.php';

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(ADMIN_URL . 'login.php');
    }
}

function formatPrice($price) {
    return 'R' . number_format($price, 2);
}

function formatRating($rating) {
    return number_format($rating, 1);
}

function getStars($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 0; $i < $fullStars; $i++) {
        $stars .= '<i class="fas fa-star"></i>';
    }
    
    if ($hasHalfStar) {
        $stars .= '<i class="fas fa-star-half-alt"></i>';
    }
    
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    for ($i = 0; $i < $emptyStars; $i++) {
        $stars .= '<i class="far fa-star"></i>';
    }
    
    return $stars;
}
?>
