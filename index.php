<?php
require_once 'config/session.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_verified']) && $_SESSION['user_verified'] === true) {
    header('Location: dashboard/dashboard.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="landing-page">
    <div class="landing-container">
        <h1>Expense Tracker</h1>
        <p>Manage your expenses efficiently</p>

        <div class="auth-options">
            <a href="auth/login.php" class="btn btn-primary">Login</a>
            <a href="auth/register.php" class="btn btn-secondary">Register</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
