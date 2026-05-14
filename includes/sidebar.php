<?php
$currentPage = $currentPage ?? basename($_SERVER['REQUEST_URI']);
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div>
            <span class="sidebar-title">Expense</span>
            <span class="sidebar-subtitle">Tracker</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="/expense-tracker/dashboard/dashboard.php" class="sidebar-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="sidebar-link-icon"></span>
            <span>Dashboard</span>
        </a>
        <a href="/expense-tracker/transactions/transaction.php" class="sidebar-link <?php echo $currentPage === 'transaction.php' ? 'active' : ''; ?>">
            <span class="sidebar-link-icon"></span>
            <span>Transactions</span>
        </a>
        <a href="/expense-tracker/reports/report.php" class="sidebar-link <?php echo $currentPage === 'report.php' ? 'active' : ''; ?>">
            <span class="sidebar-link-icon"></span>
            <span>Reports</span>
        </a>
        <a href="/expense-tracker/auth/logout.php" class="sidebar-link sidebar-link-logout">
            <span class="sidebar-link-icon"></span>
            <span>Logout</span>
        </a>
    </nav>
</aside>
