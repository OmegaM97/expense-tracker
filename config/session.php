<?php
// Session Configuration

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = '/expense-tracker';

// Session timeout (30 minutes)
$timeout = 1800;

// Check if user session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header('Location: ' . $baseUrl . '/auth/login.php');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
