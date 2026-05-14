<?php
require_once '../config/session.php';

// Destroy session
session_destroy();

// Redirect to landing page
header('Location: ../index.php');
exit;
?>
