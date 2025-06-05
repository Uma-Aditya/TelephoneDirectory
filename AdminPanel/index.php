<?php
session_start();
require_once 'config/config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_name']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}

// Get the requested page
$page = $_GET['page'] ?? 'dashboard';

// Define allowed pages and their corresponding files
$allowed_pages = [
    'dashboard' => 'views/dashboard.php',
    'manage_entries' => 'views/manage_entries.php',
    'requests' => 'views/requests.php',
    'add' => 'views/add.php',
    'bulk' => 'views/bulk.php'
];

// Include the header
include 'views/includes/header.php';

// Include the sidebar
include 'views/includes/sidebar.php';

// Load the requested page if it exists and is allowed
if (isset($allowed_pages[$page])) {
    include $allowed_pages[$page];
} else {
    include 'views/404.php';
}

// Include the footer
include 'views/includes/footer.php'; 