<?php
require_once '../config/session.php';
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        // Get user from database
        $login_query = $conn->prepare("SELECT id, fname, lname, email, password FROM users WHERE email = ?");
        $login_query->bind_param("s", $email);
        $login_query->execute();
        $result = $login_query->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_fname'] = $user['fname'];
                $_SESSION['user_lname'] = $user['lname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_verified'] = true;

                // Redirect to dashboard
                header('Location: ../dashboard/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        $login_query->close();
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
