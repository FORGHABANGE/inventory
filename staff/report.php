<?php
// staff_report.php
include 'auth_staff.php';

require_once '../includes/db.php';

require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$staff_id = $_SESSION['user_id'];

/* ================= DATABASE QUERIES ================= */

// Staff daily sales
$salesStmt = $pdo->prepare("
    SELECT DATE(s.created_at) AS sale_date,
           SUM(s.total_amount) AS total_amount,
           SUM(s.paid_amount) AS paid_amount,
           SUM(s.total_amount - s.paid_amount) AS balance
    FROM sales s
    WHERE s.user_id = ?
    GROUP BY DATE(s.created_at)
    ORDER BY sale_date DESC
");
$salesStmt->execute([$staff_id]);
$sales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

// Staff invoices
$invoiceStmt = $pdo->prepare("
    SELECT invoice_no, customer_name, total_amount, paid_amount,
           (total_amount - paid_amount) AS balance, created_at
    FROM sales
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$invoiceStmt->execute([$staff_id]);
$invoices = $invoiceStmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= EXPORT TO EXCEL ================= */
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if ($type === 'sales') {
        $sheet->setTitle('My Daily Sales');
        $sheet->fromArray(['Date','Total Amount','Paid','Balance'], null, 'A1');
        $row = 2;
        foreach ($sales as $s) {
            $sheet->fromArray([$s['sale_date'], $s['total_amount'], $s['paid_amount'], $s['balance']], null, "A{$row}");
            $row++;
        }
        $filename = 'my_daily_sales.xlsx';

    } elseif ($type === 'invoices') {
        $sheet->setTitle('My Invoices');
        $sheet->fromArray(['Invoice No','Customer','Total','Paid','Balance','Date'], null, 'A1');
        $row = 2;
        foreach ($invoices as $inv) {
            $sheet->fromArray([
                $inv['invoice_no'], 
                $inv['customer_name'], 
                $inv['total_amount'],
                $inv['paid_amount'], 
                $inv['balance'], 
                $inv['created_at']
            ], null, "A{$row}");
            $row++;
        }
        $filename = 'my_invoices.xlsx';

    } else {
        http_response_code(400);
        exit('Invalid export type');
    }

    // Style header
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
<title>Staff Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root {
  --bg: #121212;
  --panel: #1a1a1a;
  --muted: #bdbdbd;
  --accent: #00ff9d;
  --radius: 12px;
  --spacing: 20px;
}

/* Base */
body {
  margin: 0;
  font-family: Poppins, sans-serif;
  background: var(--bg);
  color: #fff;
}

/* Layout */
.page-container {
  margin-left: 210px;
  margin-top: 90px;
  padding: var(--spacing);
}

/* Titles */
h2 {
  color: var(--accent);
  margin-bottom: var(--spacing);
}
h3 {
  margin-bottom: 10px;
  color: var(--accent);
  text-align: center;
}

/* Cards */
.card {
  background: var(--panel);
  border-radius: var(--radius);
  padding: 18px;
  margin-bottom: var(--spacing);
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
}

/* Tables */
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

/* Buttons */
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

/* Chart */
canvas {
  max-width: 100%;
  height: 400px;
}
</style>
</head>
<body>

<?php include 'layout/sidebar_staff.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
  <h2><i class="bi bi-graph-up"></i> My Sales Report</h2>

  <!-- Export Buttons -->
  <div style="margin-bottom:var(--spacing);">
    <a href="staff_report.php?export=sales" class="btn">Export My Sales</a>
    <a href="staff_report.php?export=invoices" class="btn">Export My Invoices</a>
    <button onclick="window.print()" class="btn">Print</button>
  </div>

  <!-- Daily Sales Chart -->
  <div class="card">
    <h3>My Daily Sales Trend</h3>
    <canvas id="salesChart"></canvas>
  </div>

  <!-- Daily Sales Table -->
  <div class="card">
    <h3>My Daily Sales</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Total (XAF)</th>
          <th>Paid (XAF)</th>
          <th>Balance (XAF)</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($sales)): ?>
          <tr><td colspan="4">No sales records found</td></tr>
        <?php else: ?>
          <?php foreach($sales as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['sale_date']) ?></td>
            <td><?= number_format($s['total_amount'], 2) ?></td>
            <td><?= number_format($s['paid_amount'], 2) ?></td>
            <td><?= number_format($s['balance'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Invoices Table -->
  <div class="card">
    <h3>My Invoices</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Invoice No</th>
          <th>Customer</th>
          <th>Total (XAF)</th>
          <th>Paid (XAF)</th>
          <th>Balance (XAF)</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($invoices)): ?>
          <tr><td colspan="6">No invoices found</td></tr>
        <?php else: ?>
          <?php foreach($invoices as $inv): ?>
          <tr>
            <td><?= htmlspecialchars($inv['invoice_no']) ?></td>
            <td><?= htmlspecialchars($inv['customer_name']) ?></td>
            <td><?= number_format($inv['total_amount'], 2) ?></td>
            <td><?= number_format($inv['paid_amount'], 2) ?></td>
            <td><?= number_format($inv['balance'], 2) ?></td>
            <td><?= htmlspecialchars($inv['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Chart.js daily sales (same logic as admin)
const labels = <?= json_encode(array_reverse(array_column($sales, 'sale_date'))) ?>;
const totals = <?= json_encode(array_reverse(array_map('floatval', array_column($sales, 'total_amount')))) ?>;

const ctx = document.getElementById('salesChart').getContext('2d');

new Chart(ctx, {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'My Sales (XAF)',
      data: totals,
      borderColor: '#00ff9d',
      backgroundColor: 'rgba(0,255,157,0.15)',
      fill: true,
      tension: 0.35,
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        labels: { color: '#fff' }
      }
    },
    scales: {
      x: {
        ticks: { color: '#bdbdbd' },
        grid: { color: '#222' }
      },
      y: {
        ticks: { color: '#bdbdbd' },
        grid: { color: '#222' }
      }
    }
  }
});
</script>
<?php include '../layout/footer.php'; ?>
</body>
</html>
