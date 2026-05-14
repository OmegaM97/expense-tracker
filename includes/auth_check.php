<?php
// Authentication Check

$baseUrl = '/expense-tracker';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ' . $baseUrl . '/index.php');
    exit;
}

// Check if user is verified
if (!isset($_SESSION['user_verified']) || $_SESSION['user_verified'] !== true) {
    session_destroy();
    header('Location: ' . $baseUrl . '/index.php');
    exit;
}
?>
