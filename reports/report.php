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
$totalsStmt->bind_result($totalIncome, $totalExpense);
$totalsStmt->fetch();
$totalsStmt->close();

$totalIncome = (float) $totalIncome;
$totalExpense = (float) $totalExpense;
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
$topCategoryStmt->bind_result($topCategoryName, $topCategoryTotal);
$topCategory = [];
if ($topCategoryStmt->fetch()) {
    $topCategory = [
        'name' => $topCategoryName,
        'total_spent' => $topCategoryTotal,
    ];
}
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
$trendStmt->bind_result($trendMonth, $trendIncome, $trendExpense);
$trendData = [];
while ($trendStmt->fetch()) {
    $trendData[] = [
        'month' => $trendMonth,
        'income' => $trendIncome,
        'expense' => $trendExpense,
    ];
}
$trendStmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="app-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-grid">
            <div class="dashboard-intro">
                <div>
                    <p class="eyebrow">Reports</p>
                    <h2>Reports & Analytics</h2>
                    <p>Review your spending performance and savings trends over customized timeframes.</p>
                </div>
                <div class="dashboard-summary-pill">
                    <span><?php echo htmlspecialchars($periodLabel); ?></span>
                    <strong><?php echo date('F j, Y'); ?></strong>
                </div>
            </div>

            <section class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <p class="eyebrow">Filter</p>
                        <h3>Report timeframe</h3>
                    </div>
                </div>
                <form method="GET" action="report.php" class="modal-form">
                    <div class="form-row">
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
                    </div>
                    <div class="form-row">
                        <button type="submit" class="auth-btn auth-btn-primary">Apply</button>
                        <a href="report.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </section>

            <section class="stats-grid">
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
                        <p class="stat-label">Net Savings</p>
                        <h3>$<?php echo number_format($netAmount, 2); ?></h3>
                    </div>
                </article>
                <article class="stat-card">
                    <div class="stat-card-icon"></div>
                    <div>
                        <p class="stat-label">Savings Rate</p>
                        <h3><?php echo number_format($savingsRate, 2); ?>%</h3>
                    </div>
                </article>
            </section>

            <section class="chart-card">
                <div class="chart-card-header">
                    <div>
                        <p class="eyebrow">Highlight</p>
                        <h3>Top Spending Category</h3>
                    </div>
                </div>
                <div>
                    <?php if (!empty($topCategory) && $topCategory['total_spent'] > 0): ?>
                        <p class="stat-label"><?php echo htmlspecialchars($topCategory['name']); ?> — <strong>$<?php echo number_format($topCategory['total_spent'], 2); ?></strong></p>
                    <?php else: ?>
                        <p>No spending found in this period.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="transactions-card">
                <div class="transactions-header">
                    <div>
                        <p class="eyebrow">Trend</p>
                        <h3>Income vs Expense</h3>
                    </div>
                    <a href="../dashboard/dashboard.php" class="link-button">Back to dashboard</a>
                </div>

                <?php if (empty($trendData)): ?>
                    <p>No trend data available.</p>
                <?php else: ?>
                    <div class="table-overflow">
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
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
