<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$user_fname = $_SESSION['user_fname'];
$user_lname = $_SESSION['user_lname'];

// Totals
$totalsStmt = $conn->prepare(
    "SELECT
        IFNULL(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS total_income,
        IFNULL(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense
     FROM transactions
     WHERE user_id = ?"
);
$totalsStmt->bind_param('i', $user_id);
$totalsStmt->execute();
$totals = $totalsStmt->get_result()->fetch_assoc();
$totalsStmt->close();

$totalIncome = (float) $totals['total_income'];
$totalExpense = (float) $totals['total_expense'];
$totalBalance = $totalIncome - $totalExpense;

$currentMonth = date('Y-m');
$monthStmt = $conn->prepare(
    "SELECT
        IFNULL(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS monthly_income,
        IFNULL(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS monthly_expense
     FROM transactions
     WHERE user_id = ?
       AND DATE_FORMAT(transaction_date, '%Y-%m') = ?"
);
$monthStmt->bind_param('is', $user_id, $currentMonth);
$monthStmt->execute();
$monthly = $monthStmt->get_result()->fetch_assoc();
$monthStmt->close();

$monthlyIncome = (float) $monthly['monthly_income'];
$monthlyExpense = (float) $monthly['monthly_expense'];
$monthlySavings = $monthlyIncome - $monthlyExpense;

// Expense by category
$categoryStmt = $conn->prepare(
    "SELECT c.name, IFNULL(SUM(t.amount), 0) AS total
     FROM categories c
     LEFT JOIN transactions t ON t.category_id = c.id
         AND t.user_id = ?
         AND t.type = 'expense'
     GROUP BY c.id
     ORDER BY total DESC"
);
$categoryStmt->bind_param('i', $user_id);
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();
$expensesByCategory = [];
while ($row = $categoryResult->fetch_assoc()) {
    $expensesByCategory[] = $row;
}
$categoryStmt->close();

// Monthly expense trend for last 6 months
$trendStmt = $conn->prepare(
    "SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month,
        IFNULL(SUM(amount), 0) AS expense
     FROM transactions
     WHERE user_id = ?
       AND type = 'expense'
       AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
     GROUP BY month
     ORDER BY month ASC"
);
$trendStmt->bind_param('i', $user_id);
$trendStmt->execute();
$trendResult = $trendStmt->get_result();
$monthlySpending = [];
while ($row = $trendResult->fetch_assoc()) {
    $monthlySpending[] = $row;
}
$trendStmt->close();

// Recent transactions
$recentStmt = $conn->prepare(
    "SELECT t.id, t.title, t.amount, t.type, t.transaction_date, c.name AS category
     FROM transactions t
     JOIN categories c ON c.id = t.category_id
     WHERE t.user_id = ?
     ORDER BY t.transaction_date DESC, t.created_at DESC
     LIMIT 10"
);
$recentStmt->bind_param('i', $user_id);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();
$recentTransactions = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentTransactions[] = $row;
}
$recentStmt->close();

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

        <div class="card-grid">
            <div class="summary-card">
                <h3>Total Balance</h3>
                <p>$<?php echo number_format($totalBalance, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Income</h3>
                <p>$<?php echo number_format($totalIncome, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Expense</h3>
                <p>$<?php echo number_format($totalExpense, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Monthly Savings</h3>
                <p>$<?php echo number_format($monthlySavings, 2); ?></p>
            </div>
        </div>

        <section class="dashboard-section">
            <h3>Expense Breakdown by Category</h3>
            <ul class="analytics-list">
                <?php foreach ($expensesByCategory as $category): ?>
                    <li><?php echo htmlspecialchars($category['name']); ?>: $<?php echo number_format($category['total'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="dashboard-section">
            <h3>Monthly Expense Trend</h3>
            <ul class="analytics-list">
                <?php if (empty($monthlySpending)): ?>
                    <li>No expense data available yet.</li>
                <?php else: ?>
                    <?php foreach ($monthlySpending as $month): ?>
                        <li><?php echo htmlspecialchars($month['month']); ?>: $<?php echo number_format($month['expense'], 2); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <section class="dashboard-section">
            <h3>Recent Transactions</h3>
            <?php if (empty($recentTransactions)): ?>
                <p>No recent transactions.</p>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                                <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <div class="dashboard-actions">
            <a href="../transactions/transaction.php" class="btn btn-primary">Quick Add Transaction</a>
            <a href="../reports/report.php" class="btn btn-secondary">View Reports</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
