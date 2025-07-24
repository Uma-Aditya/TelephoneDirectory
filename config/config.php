<?php
/**
 * Main Configuration File
 * This file contains all the necessary configuration settings for the Admin Panel application
 */

// ====================================
// Database Configuration
// ====================================
$host = 'localhost';        // Database host (usually localhost for XAMPP)
$dbname = 'admin_panel';    // Database name for the admin panel
$username = 'root';         // Database username (default for XAMPP)
$password = '';            // Database password (default empty for XAMPP)
$charset = 'utf8mb4';      // Character set for proper UTF-8 support

// ====================================
// Application Settings
// ====================================
define('APP_NAME', 'Admin Panel');  // Application name used throughout the system
define('BASE_URL', 'http://localhost/AdminPanel');  // Base URL for the application
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');  // Directory for file uploads

// ====================================
// Error Reporting Configuration
// ====================================
error_reporting(E_ALL);  // Report all PHP errors
ini_set('display_errors', 1);  // Display errors on screen (set to 0 in production)
ini_set('log_errors', 1);  // Enable error logging
ini_set('error_log', __DIR__ . '/../logs/error.log');  // Path to error log file

// ====================================
// Database Connection
// ====================================
try {
    // Create DSN (Data Source Name) for database connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // PDO connection options for better error handling and performance
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Return results as associative array
        PDO::ATTR_EMULATE_PREPARES => false,  // Use real prepared statements
    ];
    
    // Create PDO instance for database connection
    $pdo = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact system administrator.");
}

// ====================================
// Helper Functions
// ====================================

/**
 * Redirects to a specific page within the application
 * @param string $page The page name to redirect to
 */
function redirect($page) {
    header("Location: " . BASE_URL . "/?page=" . $page);
    exit();
}

/**
 * Retrieves and clears any stored session message
 * @return string|null The message if exists, null otherwise
 */
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Stores a message in the session for display on next page load
 * @param string $message The message to store
 */
function set_message($message) {
    $_SESSION['message'] = $message;
} 