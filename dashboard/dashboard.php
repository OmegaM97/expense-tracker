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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="app-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-grid">
            <div class="dashboard-intro">
                <div>
                    <p class="eyebrow">Overview</p>
                    <h2>Financial snapshot</h2>
                    <p>Track your balance, spending habits, and savings in one elegant dashboard.</p>
                </div>
                <div class="dashboard-summary-pill">
                    <span>Today</span>
                    <strong><?php echo date('F j, Y'); ?></strong>
                </div>
            </div>

            <div class="stats-grid dashboard-stats">
                <article class="stat-card">
                    <div class="stat-card-icon"></div>
                    <div>
                        <p class="stat-label">Total Balance</p>
                        <h3>$<?php echo number_format($totalBalance, 2); ?></h3>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-card-icon"></div>
                    <div>
                        <p class="stat-label">Total Income</p>
                        <h3>$<?php echo number_format($totalIncome, 2); ?></h3>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-card-icon"></div>
                    <div>
                        <p class="stat-label">Total Expense</p>
                        <h3>$<?php echo number_format($totalExpense, 2); ?></h3>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-card-icon"></div>
                    <div>
                        <p class="stat-label">Monthly Savings</p>
                        <h3>$<?php echo number_format($monthlySavings, 2); ?></h3>
                    </div>
                </article>
            </div>

            <section class="chart-section">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <p class="eyebrow">Insights</p>
                            <h3>Expenses by Category</h3>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <p class="eyebrow">Trend</p>
                            <h3>Monthly Spending Overview</h3>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </section>

            <section class="transactions-card">
                <div class="transactions-header">
                    <div>
                        <p class="eyebrow">Recent activity</p>
                        <h3>Latest Transactions</h3>
                    </div>
                    <a href="../transactions/transaction.php" class="link-button">See all</a>
                </div>

                <div class="table-overflow">
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
                            <?php if (empty($recentTransactions)): ?>
                                <tr>
                                    <td colspan="5">No recent transactions.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('M j, Y', strtotime($transaction['transaction_date']))); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                                        <td class="transaction-type <?php echo $transaction['type'] === 'income' ? 'type-income' : 'type-expense'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($transaction['type'])); ?>
                                        </td>
                                        <td class="amount <?php echo $transaction['type'] === 'income' ? 'amount-positive' : 'amount-negative'; ?>">
                                            <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<button class="fab" id="dashboardFab" aria-label="Add transaction">
    <span>+</span>
</button>

<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal" id="quickAddModal">
    <div class="modal-header">
        <div>
            <p class="eyebrow">Quick add</p>
            <h3>Add transaction</h3>
        </div>
        <button class="modal-close" id="modalCloseButton" aria-label="Close modal">×</button>
    </div>
    <form id="quickAddForm" class="modal-form">
        <div class="form-row">
            <label>Title</label>
            <input type="text" placeholder="Groceries" required>
        </div>
        <div class="form-row">
            <label>Category</label>
            <select required>
                <option value="Food">Food</option>
                <option value="Shopping">Shopping</option>
                <option value="Transport">Transport</option>
                <option value="Bills">Bills</option>
                <option value="Entertainment">Entertainment</option>
            </select>
        </div>
        <div class="form-row split-row">
            <div>
                <label>Amount</label>
                <input type="number" step="0.01" placeholder="0.00" required>
            </div>
            <div>
                <label>Type</label>
                <select required>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <label>Date</label>
            <input type="date" required>
        </div>
        <button type="submit" class="auth-btn auth-btn-primary">Add Transaction</button>
    </form>
</div>

<script>
    window.expenseCategoryData = <?php echo json_encode($expensesByCategory); ?>;
    window.monthlySpendingData = <?php echo json_encode($monthlySpending); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/expense-tracker/assets/js/dashboard.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
