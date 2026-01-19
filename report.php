<?php
// report.php
require_once 'includes/db.php';
if (session_status()===PHP_SESSION_NONE) session_start();

require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* ------------------ DATABASE QUERIES ------------------ */

// Products
$productStmt = $pdo->query("
    SELECT p.sku, p.name, p.quantity, c.name AS category,
           p.purchase_price, p.selling_price, p.reorder_level
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.name ASC
");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Daily sales
$salesStmt = $pdo->query("
    SELECT DATE(s.created_at) AS sale_date,
           SUM(s.total_amount) AS total_amount,
           SUM(s.paid_amount) AS paid_amount,
           SUM(s.total_amount - s.paid_amount) AS balance
    FROM sales s
    GROUP BY DATE(s.created_at)
    ORDER BY sale_date DESC
");
$sales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

// Invoices
$invoiceStmt = $pdo->query("
    SELECT invoice_no, customer_name, total_amount, paid_amount,
           (total_amount - paid_amount) AS balance, created_at
    FROM sales
    ORDER BY created_at DESC
");
$invoices = $invoiceStmt->fetchAll(PDO::FETCH_ASSOC);

/* ------------------ EXPORT TO EXCEL ------------------ */
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if ($type === 'products') {
        $sheet->setTitle('Products');
        $sheet->fromArray(['SKU','Product','Category','Quantity','Purchase Price','Selling Price','Reorder Level'], null, 'A1');
        $row = 2;
        foreach ($products as $p) {
            $sheet->fromArray([
                $p['sku'], $p['name'], $p['category'], $p['quantity'],
                $p['purchase_price'], $p['selling_price'], $p['reorder_level']
            ], null, "A{$row}");
            $row++;
        }
        $filename = 'products.xlsx';
    } elseif ($type === 'sales') {
        $sheet->setTitle('Daily Sales');
        $sheet->fromArray(['Date','Total Amount','Paid','Balance'], null, 'A1');
        $row = 2;
        foreach ($sales as $s) {
            $sheet->fromArray([$s['sale_date'], $s['total_amount'], $s['paid_amount'], $s['balance']], null, "A{$row}");
            $row++;
        }
        $filename = 'daily_sales.xlsx';
    } elseif ($type === 'invoices') {
        $sheet->setTitle('Invoices');
        $sheet->fromArray(['Invoice No','Customer','Total','Paid','Balance','Date'], null, 'A1');
        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->fromArray([
                $inv['invoice_no'], $inv['customer_name'], $inv['total_amount'],
                $inv['paid_amount'], $inv['balance'], $inv['created_at']
            ], null, "A{$row}");
            $row++;
        }
        $filename = 'invoices.xlsx';
    } else {
        http_response_code(400);
        exit('Invalid export type');
    }

    // Bold header row and auto-size
    $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ------------------ VARIABLES ------------------ */
:root {
  --bg: #121212;
  --panel: #1a1a1a;
  --muted: #bdbdbd;
  --accent: #00ff9d;
  --radius: 12px;
  --spacing: 20px;
}

/* ------------------ BASE ------------------ */
body {
  margin: 0;
  font-family: Poppins, sans-serif;
  background: var(--bg);
  color: #fff;
}

/* ------------------ LAYOUT ------------------ */
.page-container {
  margin-left: 210px;
  margin-top: 90px;
  padding: var(--spacing);
}

/* ------------------ TYPOGRAPHY ------------------ */
h2 {
  color: var(--accent);
  margin-bottom: var(--spacing);
  margin-left: 10px;
}
h3 {
  margin-bottom: 10px;
  color: var(--accent);
  text-align: center;
}

/* ------------------ COMPONENTS ------------------ */
.card {
  background: var(--panel);
  border-radius: var(--radius);
  padding: 18px;
  margin-bottom: var(--spacing);
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}       

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.table th, .table td {
  padding: 10px;
  border-bottom: 1px solid #222;
  text-align: center;
}
.table th {
  color: var(--muted);
  font-weight: 600;
}

.btn {
  background: var(--accent);
  color: #000;
  padding: 8px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  margin-right: 10px;
  transition: background 0.3s ease;
}
.btn:hover {
  background: #00e68a;
}

/* ------------------ UTILITIES ------------------ */
canvas {
  max-width: 100%;
  height: 400px;
}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
  <h2><i class="bi bi-bar-chart"></i> Reports Dashboard</h2>

  <!-- Export Buttons -->
  <div style="margin-bottom:var(--spacing);">
    <a href="report.php?export=products" class="btn">Export Products</a>
    <a href="report.php?export=sales" class="btn">Export Daily Sales</a>
    <a href="report.php?export=invoices" class="btn">Export Invoices</a>
  </div>

  <!-- Products Table -->
  <div class="card">
    <h3>Products</h3>
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th><th>Product</th><th>Category</th><th>Quantity</th>
          <th>Purchase Price</th><th>Selling Price</th><th>Reorder Level</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['sku']) ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['category']) ?></td>
          <td><?= htmlspecialchars($p['quantity']) ?></td>
          <td><?= number_format($p['purchase_price'],2) ?></td>
          <td><?= number_format($p['selling_price'],2) ?></td>
          <td><?= $p['reorder_level'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Daily Sales Chart -->
  <div class="card">
    <h3>Daily Sales Trend</h3>
    <canvas id="salesChart"></canvas>
  </div>

  <!-- Invoices Table -->
  <div class="card">
    <h3>Invoices</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Invoice No</th><th>Customer</th><th>Total (XAF)</th>
          <th>Paid (XAF)</th><th>Balance (XAF)</th><th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($invoices as $inv): ?>
        <tr>
          <td><?= htmlspecialchars($inv['invoice_no']) ?></td>
          <td><?= htmlspecialchars($inv['customer_name']) ?></td>
          <td><?= number_format($inv['total_amount'],2) ?></td>
          <td><?= number_format($inv['paid_amount'],2) ?></td>
          <td><?= number_format($inv['balance'],2) ?></td>
          <td><?= htmlspecialchars($inv['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Chart.js daily sales
const ctx = document.getElementById('salesChart').getContext('2d');
const labels = <?= json_encode(array_column($sales, 'sale_date')) ?>;
const totals = <?= json_encode(array_map('floatval', array_column($sales, 'total_amount'))) ?>;

new Chart(ctx, {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Total Amount (XAF)',
      data: totals,
      borderColor: '#00ff9d',
      backgroundColor: 'rgba(0,255,157,0.2)',
      fill: true,
      tension: 0.3
    }]
  }
});
</script>
</body>
</html>
