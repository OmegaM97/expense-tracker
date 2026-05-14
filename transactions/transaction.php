<?php
require_once '../config/session.php';
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ensure default categories exist
$categoryQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
if ($categoryQuery->num_rows === 0) {
    $defaultCategories = ['Food','Transport','Shopping','Bills','Entertainment','Health','Salary','Freelance'];
    $insertCategory = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    foreach ($defaultCategories as $categoryName) {
        $insertCategory->bind_param('s', $categoryName);
        $insertCategory->execute();
    }
    $insertCategory->close();
    $categoryQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
}
$categories = [];
while ($row = $categoryQuery->fetch_assoc()) {
    $categories[] = $row;
}

// Transaction form handling
$editMode = false;
$transaction = [
    'id' => null,
    'title' => '',
    'amount' => '',
    'type' => 'expense',
    'category_id' => '',
    'payment_method' => 'cash',
    'transaction_date' => date('Y-m-d'),
    'note' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = isset($_POST['transaction_id']) ? (int) $_POST['transaction_id'] : null;
    $transaction['title'] = trim($_POST['title'] ?? '');
    $transaction['amount'] = trim($_POST['amount'] ?? '');
    $transaction['type'] = $_POST['type'] ?? 'expense';
    $transaction['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $transaction['payment_method'] = $_POST['payment_method'] ?? 'cash';
    $transaction['transaction_date'] = $_POST['transaction_date'] ?? date('Y-m-d');
    $transaction['note'] = trim($_POST['note'] ?? '');

    if ($transaction['title'] === '') {
        $error = 'Title is required.';
    } elseif (!is_numeric($transaction['amount']) || $transaction['amount'] <= 0) {
        $error = 'Amount must be greater than zero.';
    } elseif ($transaction['category_id'] === 0) {
        $error = 'Category is required.';
    } elseif (!in_array($transaction['type'], ['income', 'expense'], true)) {
        $error = 'Invalid transaction type.';
    } elseif (!in_array($transaction['payment_method'], ['cash', 'bank', 'credit_card', 'debit_card', 'mobile_payment'], true)) {
        $error = 'Invalid payment method.';
    } else {
        if ($transactionId) {
            $updateStmt = $conn->prepare(
                "UPDATE transactions
                 SET title = ?, amount = ?, type = ?, category_id = ?, payment_method = ?, note = ?, transaction_date = ?
                 WHERE id = ? AND user_id = ?"
            );
            $updateStmt->bind_param(
                'sdsiissii',
                $transaction['title'],
                $transaction['amount'],
                $transaction['type'],
                $transaction['category_id'],
                $transaction['payment_method'],
                $transaction['note'],
                $transaction['transaction_date'],
                $transactionId,
                $user_id
            );
            if ($updateStmt->execute()) {
                $success = 'Transaction updated successfully.';
            } else {
                $error = 'Unable to update transaction.';
            }
            $updateStmt->close();
        } else {
            $insertStmt = $conn->prepare(
                "INSERT INTO transactions (user_id, category_id, title, amount, type, payment_method, note, transaction_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $insertStmt->bind_param(
                'iissdsss',
                $user_id,
                $transaction['category_id'],
                $transaction['title'],
                $transaction['amount'],
                $transaction['type'],
                $transaction['payment_method'],
                $transaction['note'],
                $transaction['transaction_date']
            );
            if ($insertStmt->execute()) {
                $success = 'Transaction added successfully.';
                $transaction = [
                    'id' => null,
                    'title' => '',
                    'amount' => '',
                    'type' => 'expense',
                    'category_id' => '',
                    'payment_method' => 'cash',
                    'transaction_date' => date('Y-m-d'),
                    'note' => '',
                ];
            } else {
                $error = 'Unable to add transaction.';
            }
            $insertStmt->close();
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int) $_GET['id'];
    $deleteStmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param('ii', $deleteId, $user_id);
    $deleteStmt->execute();
    $deleteStmt->close();
    header('Location: transaction.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    $editStmt = $conn->prepare(
        "SELECT id, title, amount, type, category_id, payment_method, note, transaction_date
         FROM transactions
         WHERE id = ? AND user_id = ?"
    );
    $editStmt->bind_param('ii', $editId, $user_id);
    $editStmt->execute();
    $result = $editStmt->get_result();
    if ($result->num_rows === 1) {
        $transaction = $result->fetch_assoc();
        $editMode = true;
    }
    $editStmt->close();
}

$search = trim($_GET['search'] ?? '');
$filterCategory = (int) ($_GET['category'] ?? 0);
$filterType = $_GET['type'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$where = 'WHERE t.user_id = ?';
$types = 'i';
$params = [$user_id];

if ($search !== '') {
    $where .= ' AND (t.title LIKE ? OR c.name LIKE ? OR t.payment_method LIKE ? OR CAST(t.amount AS CHAR) LIKE ?)';
    $like = "%{$search}%";
    $types .= 'ssss';
    $params[] = &$like;
    $params[] = &$like;
    $params[] = &$like;
    $params[] = &$like;
}

if ($filterCategory > 0) {
    $where .= ' AND t.category_id = ?';
    $types .= 'i';
    $params[] = &$filterCategory;
}

if ($filterType === 'income' || $filterType === 'expense') {
    $where .= ' AND t.type = ?';
    $types .= 's';
    $params[] = &$filterType;
}

if ($fromDate !== '') {
    $where .= ' AND t.transaction_date >= ?';
    $types .= 's';
    $params[] = &$fromDate;
}

if ($toDate !== '') {
    $where .= ' AND t.transaction_date <= ?';
    $types .= 's';
    $params[] = &$toDate;
}

$query = "SELECT t.id, t.title, t.amount, t.type, t.payment_method, t.note, t.transaction_date, c.name AS category_name
          FROM transactions t
          JOIN categories c ON c.id = t.category_id
          {$where}
          ORDER BY t.transaction_date DESC, t.created_at DESC
          LIMIT 10";

$transactions = [];
$stmt = $conn->prepare($query);
if ($stmt) {
    $bindParams = array_merge([$types], $params);
    $tmp = [];
    foreach ($bindParams as $key => $value) {
        $tmp[$key] = &$bindParams[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $tmp);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="app-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <div class="dashboard-grid">
            <div class="dashboard-intro">
                <div>
                    <p class="eyebrow">Transactions</p>
                    <h2><?php echo $editMode ? 'Edit Transaction' : 'Add Transaction'; ?></h2>
                    <p>Manage your income and expenses with precise control over categories, payment methods, and notes.</p>
                </div>
                <div class="dashboard-summary-pill">
                    <span><?php echo $editMode ? 'Edit mode' : 'Create new'; ?></span>
                    <strong><?php echo date('F j, Y'); ?></strong>
                </div>
            </div>

            <div class="chart-card">
                <?php if ($error): ?>
                    <div class="auth-message auth-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="auth-message auth-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="transaction.php" class="modal-form">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="transaction_id" value="<?php echo (int) $transaction['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($transaction['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" id="amount" name="amount" value="<?php echo htmlspecialchars($transaction['amount']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type" required>
                                <option value="income" <?php echo $transaction['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
                                <option value="expense" <?php echo $transaction['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $transaction['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <?php
                                $methods = ['cash' => 'Cash', 'bank' => 'Bank', 'credit_card' => 'Credit Card', 'debit_card' => 'Debit Card', 'mobile_payment' => 'Mobile Payment'];
                                foreach ($methods as $key => $label):
                                ?>
                                    <option value="<?php echo $key; ?>" <?php echo $transaction['payment_method'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transaction_date">Date</label>
                            <input type="date" id="transaction_date" name="transaction_date" value="<?php echo htmlspecialchars($transaction['transaction_date']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea id="note" name="note"><?php echo htmlspecialchars($transaction['note']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <button type="submit" class="auth-btn auth-btn-primary"><?php echo $editMode ? 'Update Transaction' : 'Add Transaction'; ?></button>
                        <?php if ($editMode): ?>
                            <a href="transaction.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <section class="transactions-card">
                <div class="transactions-header">
                    <div>
                        <p class="eyebrow">Rich filters</p>
                        <h3>Search & manage recent transactions</h3>
                    </div>
                    <a href="transaction.php" class="link-button">Reset filters</a>
                </div>

                <form method="GET" action="transaction.php" class="modal-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, category, method, amount">
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">All categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $filterCategory === (int) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <option value="">All</option>
                                <option value="income" <?php echo $filterType === 'income' ? 'selected' : ''; ?>>Income</option>
                                <option value="expense" <?php echo $filterType === 'expense' ? 'selected' : ''; ?>>Expense</option>
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
                        <button type="submit" class="auth-btn auth-btn-primary">Filter</button>
                        <a href="transaction.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                <?php if (empty($transactions)): ?>
                    <p>No transactions found.</p>
                <?php else: ?>
                    <div class="table-overflow">
                        <table class="transaction-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['transaction_date']); ?></td>
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td class="transaction-type <?php echo $item['type'] === 'income' ? 'type-income' : 'type-expense'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($item['type'])); ?>
                                        </td>
                                        <td class="amount <?php echo $item['type'] === 'income' ? 'amount-positive' : 'amount-negative'; ?>">
                                            <?php echo $item['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($item['amount'], 2); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['payment_method']); ?></td>
                                        <td>
                                            <a href="transaction.php?action=edit&id=<?php echo (int) $item['id']; ?>">Edit</a>
                                            <a href="transaction.php?action=delete&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this transaction?');">Delete</a>
                                        </td>
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
