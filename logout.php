<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy all session data
session_unset();
session_destroy();

// Clear any other cookies that might be set
if (isset($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        setcookie($name, '', time() - 3600, '/');
    }
}

// Redirect to login page
header('Location: http://localhost/PhoneBook/AdminLogin/login.php');
exit();
