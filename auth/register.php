<?php
require_once '../config/session.php';
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fname)) {
        $error = 'First name is required';
    } elseif (empty($lname)) {
        $error = 'Last name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insert_user = $conn->prepare("INSERT INTO users (fname, lname, email, password) VALUES (?, ?, ?, ?)");
            $insert_user->bind_param("ssss", $fname, $lname, $email, $hashed_password);

            if ($insert_user->execute()) {
                $success = 'Registration successful! Redirecting to login...';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $check_email->close();
    }
}

require_once '../includes/header.php';
?>

<div class="auth-container">
    <h2>Register</h2>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="fname">First Name</label>
            <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($_POST['fname'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($_POST['lname'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
