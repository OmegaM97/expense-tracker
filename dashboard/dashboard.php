<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../includes/auth_check.php';

// User is verified at this point
$user_id = $_SESSION['user_id'];
$user_fname = $_SESSION['user_fname'];
$user_lname = $_SESSION['user_lname'];

// Get user data to verify in database
$verify_user = $conn->prepare("SELECT id, fname, lname, email FROM users WHERE id = ?");
$verify_user->bind_param("i", $user_id);
$verify_user->execute();
$result = $verify_user->get_result();

if ($result->num_rows === 0) {
    // User doesn't exist in database, destroy session
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$verify_user->close();

require_once '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="navbar">
        <div class="navbar-brand">Expense Tracker</div>
        <div class="navbar-user">
            <span>Welcome, <?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></span>
            <a href="../auth/logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <h2>Dashboard</h2>
        <p>User ID: <?php echo htmlspecialchars($user_id); ?></p>
        <p>Name: <?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></p>
        <p>Status: Verified ✓</p>

        <div class="dashboard-menu">
            <ul>
                <li><a href="../transactions/transaction.php">Add Transaction</a></li>
                <li><a href="../reports/report.php">View Reports</a></li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
