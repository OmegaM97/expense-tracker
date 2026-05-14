<?php
$user_fname = $_SESSION['user_fname'] ?? ($_SESSION['user']['first_name'] ?? 'Guest');
$user_lname = $_SESSION['user_lname'] ?? ($_SESSION['user']['last_name'] ?? '');
?>
<header class="topbar">
    <div class="topbar-left">
        <div>
            <p class="topbar-greeting">Good day, <?php echo htmlspecialchars($user_fname); ?></p>
            <h1 class="topbar-title">Your financial overview</h1>
        </div>
    </div>

    <div class="topbar-right">

        <div class="profile-chip">
            <div class="profile-avatar"><?php echo strtoupper(substr($user_fname, 0, 1)); ?></div>
            <div>
                <span class="profile-name"><?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></span>
                <span class="profile-role">Member</span>
            </div>
        </div>
    </div>
</header>
