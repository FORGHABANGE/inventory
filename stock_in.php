<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/db.php";

$errors = [];
$success = '';

/* ===============================
   HANDLE FORM SUBMISSION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = (int)($_POST['quantity'] ?? 0);
    $reference  = trim($_POST['reference'] ?? '');
    $note       = trim($_POST['note'] ?? '');
    $user_id    = $_SESSION['user_id'];

    if ($product_id <= 0) $errors[] = "Please select a product.";
    if ($quantity <= 0)   $errors[] = "Quantity must be greater than zero.";

    if (empty($errors)) {

        try {
            $pdo->beginTransaction();

            /* Lock product row */
            $lock = $pdo->prepare("SELECT quantity FROM products WHERE id = ? FOR UPDATE");
            $lock->execute([$product_id]);

            if (!$lock->fetch()) {
                throw new Exception("Selected product does not exist.");
            }

            /* Insert stock movement */
            $insertMovement = $pdo->prepare("
                INSERT INTO stock_movements
                (product_id, type, quantity, reference, note, user_id)
                VALUES (?,?,?,?,?,?)
            ");
            $insertMovement->execute([
                $product_id,
                'IN',
                $quantity,
                $reference ?: 'ADMIN-STOCK-IN',
                $note,
                $user_id
            ]);

            /* Update product quantity */
            $updateProduct = $pdo->prepare("
                UPDATE products
                SET quantity = quantity + ?
                WHERE id = ?
            ");
            $updateProduct->execute([$quantity, $product_id]);

            $pdo->commit();
            $success = "Stock added successfully.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

/* ===============================
   FETCH PRODUCTS
================================ */
$products = $pdo->query("
    SELECT id, name
    FROM products
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock In - Inventory System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* =========================
   Base & Body Styles
========================= */
body {
    background: #121212;
    font-family: "Poppins", sans-serif;
    color: #fff;
    margin: 0;
}

/* =========================
   Page Layout
========================= */
.page-container {
    margin-left: 240px; /* accommodate sidebar */
    padding: 25px;
    margin-top: 90px; /* below header */
}

/* =========================
   Header
========================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h2 {
    color: #00ff9d;
    margin: 0;
}

/* =========================
   Messages (Success/Error)
========================= */
.messages {
    margin-bottom: 20px;
}

.messages .error {
    background: #ff4d4d33;
    border-left: 4px solid #ff4d4d;
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    color: #fff;
}

.messages .success {
    background: #00ff9d33;
    border-left: 4px solid #00ff9d;
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    color: #000;
}

/* =========================
   Form Container
========================= */
.card {
    background: #1a1a1a;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

/* =========================
   Form Elements
========================= */
form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #00ff9d;
}

form input[type="number"],
form input[type="text"],
form select,
form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: 1px solid #333;
    border-radius: 8px;
    background: #121212;
    color: #fff;
    font-size: 14px;
}

form textarea {
    resize: vertical;
}

/* =========================
   Buttons
========================= */
.btn {
    background: #00ff9d;
    color: #000;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    background: #00e68c;
}

.btn.secondary {
    background: #333;
    color: #fff;
}

.btn.secondary:hover {
    background: #444;
}

/* Buttons group in header */
.actions-group button {
    background: #00ff9d;
    color: #000;
    border: none;
    padding: 10px 18px;
    margin-left: 10px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.actions-group button:hover {
    background: #00e68c;
}

/* =========================
   Table Styles
========================= */
.movement-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a1a1a;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 25px;
}

.movement-table thead {
    background: #00ff9d;
    color: #000;
}

.movement-table th,
.movement-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #333;
    text-align: center;
}

.type-in {
    color: #00ff9d;
    font-weight: 600;
}

.type-out {
    color: #ff4d4d;
    font-weight: 600;
}

.type-adjust {
    color: #ffd700;
    font-weight: 600;
}

/* =========================
   Responsive Design
========================= */
@media (max-width: 720px) {
    .page-container {
        margin-left: 0;
        padding: 15px;
        margin-top: 70px;
    }

    .actions-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .actions-group button {
        margin-left: 0;
        width: 100%;
    }

    .movement-table th,
    .movement-table td {
        padding: 8px 10px;
        font-size: 12px;
    }
}
</style>
</head>
<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">
    <div class="card">
        <h2><i class="bi bi-plus-circle"></i> Stock In</h2>

        <div class="messages">
            <?php if (!empty($errors)): ?>
                <?php foreach($errors as $err): ?>
                    <div class="error"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
        </div>

        <form method="POST">
            <label>Product</label>
            <select name="product_id" required>
                <option value="">Select product</option>
                <?php foreach($products as $p): 
                    $sel = (isset($_POST['product_id']) && $_POST['product_id'] == $p['id']) ? 'selected' : '';
                ?>
                    <option value="<?= $p['id'] ?>" <?= $sel ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Quantity</label>
            <input type="number" name="quantity" min="1" required value="<?= $_POST['quantity'] ?? '' ?>">

            <label>Reference</label>
            <input type="text" name="reference" value="<?= $_POST['reference'] ?? '' ?>">

            <label>Note</label>
            <textarea name="note" rows="3"><?= $_POST['note'] ?? '' ?></textarea>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn"><i class="bi bi-save"></i> Add Stock</button>
                <a href="stock_movements.php" class="btn secondary"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
