<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) die("Invalid sale selected.");
$sale_id = (int)$_GET['sale_id'];

/* Fetch sale */
$saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
$saleStmt->execute([$sale_id]);
$sale = $saleStmt->fetch(PDO::FETCH_ASSOC);
if (!$sale) die("Sale not found.");

/* Fetch products */
$products = $pdo->query("
    SELECT id, name, selling_price, quantity 
    FROM products 
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['items'])) {
    $pdo->beginTransaction();
    try {
        foreach ($_POST['items'] as $item) {
            $product_id = (int)($item['product_id'] ?? 0);
            $quantityRaw = trim($item['quantity'] ?? '');
            $unit_price  = (float)($item['unit_price'] ?? 0);

            if ($product_id <= 0 || $quantityRaw === '' || $unit_price <= 0) {
                throw new Exception("Invalid product entry.");
            }

            // Extract numeric part of quantity (e.g. "16sachets" → 16)
            if (preg_match('/^\d+/', $quantityRaw, $matches)) {
                $quantity = (int)$matches[0];
            } else {
                throw new Exception("Quantity must start with a number (e.g., 16sachets).");
            }

            // Lock product row to ensure consistent stock deduction
            $stockStmt = $pdo->prepare("SELECT quantity FROM products WHERE id=? FOR UPDATE");
            $stockStmt->execute([$product_id]);
            $availableStock = $stockStmt->fetchColumn();

            if ($availableStock === false) throw new Exception("Product not found.");
            if ($availableStock < $quantity) {
                throw new Exception("Insufficient stock for selected product.");
            }

            $line_total = $quantity * $unit_price;

            // Insert sale item (store numeric quantity for calculations)
            $insertItem = $pdo->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                VALUES (?,?,?,?,?)
            ");
            $insertItem->execute([$sale_id, $product_id, $quantity, $unit_price, $line_total]);

            // Deduct stock
            $updateStock = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id=?");
            $updateStock->execute([$quantity, $product_id]);
        }

        // Calculate new total
        $totalStmt = $pdo->prepare("SELECT SUM(line_total) FROM sale_items WHERE sale_id=?");
        $totalStmt->execute([$sale_id]);
        $newTotal = (float)($totalStmt->fetchColumn() ?? 0);

        // Update sale: total_amount and paid_amount (equal since no credit)
        $updateSale = $pdo->prepare("UPDATE sales SET total_amount=?, paid_amount=? WHERE id=?");
        $updateSale->execute([$newTotal, $newTotal, $sale_id]);

        $pdo->commit();
        header("Location: sale_items.php?sale_id=".$sale_id);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Sale Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* ------------------ VARIABLES ------------------ */
:root{
  --bg:#121212; --panel:#1a1a1a; --muted:#bdbdbd;
  --accent:#00ff9d; --danger:#ff4d4d; --card-shadow: rgba(0,0,0,0.6);
  --radius:12px; --spacing:20px;
}

/* ------------------ BASE ------------------ */
body{margin:0;font-family:Poppins,sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;margin-top:90px;padding:var(--spacing);}

/* ------------------ COMPONENTS ------------------ */
.card{background:var(--panel);border-radius:var(--radius);padding:18px;box-shadow:0 8px 30px var(--card-shadow);}
h2{color:var(--accent);margin-bottom:18px;}
.table{width:100%;border-collapse:collapse;table-layout:fixed;}
.table th,.table td{padding:12px;border-bottom:1px solid #222;text-align:center;}
.table th{color:var(--muted);}
select,input{padding:8px;border-radius:8px;border:1px solid #222;background:#0f0f0f;color:#fff;width:100%;}
.btn{display:inline-flex;align-items:center;gap:6px;background:var(--accent);color:#000;padding:8px 14px;border-radius:8px;border:none;cursor:pointer;font-weight:600;text-decoration:none;}
.btn.danger{background:var(--danger);color:#fff;}
.btn.secondary{background:#2a2a2a;color:#bdbdbd;}
.alert{background:rgba(255,77,77,0.12);color:var(--danger);padding:10px;border-radius:8px;margin-bottom:10px;}
@media(max-width:720px){.page-container{margin-left:0;margin-top:20px;padding:20px}}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
  <div class="card">
    <h2><i class="bi bi-plus-circle"></i> Add Sale Items for Invoice <?= htmlspecialchars($sale['invoice_no']) ?></h2>

    <?php foreach($errors as $e): ?>
      <div class="alert"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty (alphanumeric)</th>
            <th>Unit Price (XAF)</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="items-container">
          <tr>
            <td>
              <select name="items[0][product_id]" onchange="setPrice(this)" required>
                <option value="">-- Select Product --</option>
                <?php foreach($products as $p): ?>
                  <option value="<?= $p['id'] ?>" data-price="<?= htmlspecialchars($p['selling_price']) ?>">
                    <?= htmlspecialchars($p['name']) ?> (Stock: <?= htmlspecialchars($p['quantity']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="text" name="items[0][quantity]" placeholder="e.g. 16sachets" required></td>
            <td><input type="number" step="0.01" name="items[0][unit_price]" min="0" required></td>
            <td><button type="button" class="btn danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
          </tr>
        </tbody>
      </table>

      <button type="button" class="btn" onclick="addRow()"><i class="bi bi-plus-circle"></i> Add Another Product</button>
      <br><br>
      <button type="submit" class="btn"><i class="bi bi-check-circle"></i> Save Items</button>
      <a href="sale_items.php?sale_id=<?= $sale_id ?>" class="btn secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </form>
  </div>
</div>

<script>
let rowCount = 1;

function addRow(){
  const container = document.getElementById('items-container');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>
      <select name="items[${rowCount}][product_id]" onchange="setPrice(this)" required>
        <option value="">-- Select Product --</option>
        <?php foreach($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-price="<?= htmlspecialchars($p['selling_price']) ?>">
            <?= htmlspecialchars($p['name']) ?> (Stock: <?= htmlspecialchars($p['quantity']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><input type="text" name="items[${rowCount}][quantity]" placeholder="e.g. 10boxes" required></td>
    <td><input type="number" step="0.01" name="items[${rowCount}][unit_price]" min="0" required></td>
    <td><button type="button" class="btn danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
  `;
  container.appendChild(row);
  rowCount++;
}

function removeRow(btn){
  btn.closest('tr').remove();
}

function setPrice(selectEl) {
  const price = selectEl.options[selectEl.selectedIndex]?.getAttribute('data-price');
  const row = selectEl.closest('tr');
  const priceInput = row.querySelector('input[name*="[unit_price]"]');
  if (price && priceInput) {
    priceInput.value = parseFloat(price).toFixed(2);
  }
}
</script>
</body>
</html>
