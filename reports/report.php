<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$range = $_GET['range'] ?? 'month';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$today = date('Y-m-d');
$startDate = $today;
$endDate = $today;
$periodLabel = 'Today';

switch ($range) {
    case 'week':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        $periodLabel = 'This Week';
        break;
    case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $periodLabel = 'This Month';
        break;
    case 'year':
        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');
        $periodLabel = 'This Year';
        break;
    case 'custom':
        if ($fromDate !== '') {
            $startDate = $fromDate;
        }
        if ($toDate !== '') {
            $endDate = $toDate;
        }
        $periodLabel = 'Custom Range';
        break;
}

$totalsStmt = $conn->prepare(
    "SELECT
        IFNULL(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS total_income,
        IFNULL(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense
     FROM transactions
     WHERE user_id = ?
       AND transaction_date BETWEEN ? AND ?"
);
$totalsStmt->bind_param('iss', $user_id, $startDate, $endDate);
$totalsStmt->execute();
$totals = $totalsStmt->get_result()->fetch_assoc();
$totalsStmt->close();

$totalIncome = (float) $totals['total_income'];
$totalExpense = (float) $totals['total_expense'];
$netAmount = $totalIncome - $totalExpense;
$savingsRate = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 2) : 0;

$topCategoryStmt = $conn->prepare(
    "SELECT c.name, IFNULL(SUM(t.amount), 0) AS total_spent
     FROM transactions t
     JOIN categories c ON c.id = t.category_id
     WHERE t.user_id = ?
       AND t.type = 'expense'
       AND t.transaction_date BETWEEN ? AND ?
     GROUP BY c.id
     ORDER BY total_spent DESC
     LIMIT 1"
);
$topCategoryStmt->bind_param('iss', $user_id, $startDate, $endDate);
$topCategoryStmt->execute();
$topCategory = $topCategoryStmt->get_result()->fetch_assoc();
$topCategoryStmt->close();

$trendStmt = $conn->prepare(
    "SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month,
        IFNULL(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS income,
        IFNULL(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS expense
     FROM transactions
     WHERE user_id = ?
       AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
     GROUP BY month
     ORDER BY month ASC"
);
$trendStmt->bind_param('i', $user_id);
$trendStmt->execute();
$trendData = [];
while ($row = $trendStmt->get_result()->fetch_assoc()) {
    $trendData[] = $row;
}
$trendStmt->close();

require_once '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="navbar">
        <div class="navbar-brand">Expense Tracker</div>
        <div class="navbar-user">
            <span><?php echo htmlspecialchars($_SESSION['user_fname'] . ' ' . $_SESSION['user_lname']); ?></span>
            <a href="../auth/logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <h2>Reports & Analytics</h2>

        <section class="dashboard-section">
            <h3>Filter</h3>
            <form method="GET" action="report.php" class="filter-form">
                <div class="form-group">
                    <label for="range">Range</label>
                    <select id="range" name="range">
                        <option value="day" <?php echo $range === 'day' ? 'selected' : ''; ?>>Day</option>
                        <option value="week" <?php echo $range === 'week' ? 'selected' : ''; ?>>Week</option>
                        <option value="month" <?php echo $range === 'month' ? 'selected' : ''; ?>>Month</option>
                        <option value="year" <?php echo $range === 'year' ? 'selected' : ''; ?>>Year</option>
                        <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>">
                </div>
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>
        </section>

        <section class="dashboard-section">
            <h3><?php echo htmlspecialchars($periodLabel); ?> Summary</h3>
            <div class="card-grid">
                <div class="summary-card">
                    <h4>Total Income</h4>
                    <p>$<?php echo number_format($totalIncome, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h4>Total Expense</h4>
                    <p>$<?php echo number_format($totalExpense, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h4>Net Savings</h4>
                    <p>$<?php echo number_format($netAmount, 2); ?></p>
                </div>
                <div class="summary-card">
                    <h4>Savings Rate</h4>
                    <p><?php echo number_format($savingsRate, 2); ?>%</p>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <h3>Top Spending Category</h3>
            <?php if (!empty($topCategory) && $topCategory['total_spent'] > 0): ?>
                <p><?php echo htmlspecialchars($topCategory['name']); ?> — $<?php echo number_format($topCategory['total_spent'], 2); ?></p>
            <?php else: ?>
                <p>No spending found in this period.</p>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <h3>Income vs Expense Trend</h3>
            <?php if (empty($trendData)): ?>
                <p>No trend data available.</p>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Income</th>
                            <th>Expense</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trendData as $point): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($point['month']); ?></td>
                                <td>$<?php echo number_format($point['income'], 2); ?></td>
                                <td>$<?php echo number_format($point['expense'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <div class="dashboard-actions">
            <a href="../transactions/transaction.php" class="btn btn-primary">Add Transaction</a>
            <a href="../dashboard/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
