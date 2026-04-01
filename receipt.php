<?php
require_once 'includes/db.php';

if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) {
    die("Invalid sale selected.");
}
$sale_id = (int)$_GET['sale_id'];

// Fetch sale
$saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
$saleStmt->execute([$sale_id]);
$sale = $saleStmt->fetch(PDO::FETCH_ASSOC);
if (!$sale) die("Sale not found.");

// Fetch sale items
$itemsStmt = $pdo->prepare("
    SELECT si.id, p.name, si.quantity, si.unit_price, si.line_total
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$itemsStmt->execute([$sale_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$totalAmount = 0;
foreach ($items as $i) {
    $totalAmount += $i['line_total'];
}
$paidAmount = (float)$sale['paid_amount'];
$balance = $totalAmount - $paidAmount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Invoice <?= htmlspecialchars($sale['invoice_no']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
        }
        .details {
            margin-bottom: 20px;
        }
        .details div {
            margin-bottom: 5px;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items th {
            background: #f2f2f2;
        }
        .totals {
            text-align: right;
            margin-bottom: 20px;
        }
        .totals div {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        @media print {
            .print-btn {
                display: none;
            }
            body {
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Inventory Management System</h1>
            <p>Receipt</p>
        </div>
        <div class="details">
            <div><strong>Invoice No:</strong> <?= htmlspecialchars($sale['invoice_no']) ?></div>
            <div><strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($sale['created_at'])) ?></div>
            <?php if (!empty($sale['customer_name'])): ?>
                <div><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name']) ?></div>
            <?php endif; ?>
        </div>
        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>XAF <?= number_format($item['unit_price'], 2) ?></td>
                        <td>XAF <?= number_format($item['line_total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="totals">
            <div><strong>Total Amount:</strong> XAF <?= number_format($totalAmount, 2) ?></div>
            <div><strong>Paid Amount:</strong> XAF <?= number_format($paidAmount, 2) ?></div>
            <div><strong>Balance:</strong> XAF <?= number_format($balance, 2) ?></div>
        </div>
        <div class="footer">
            <p>&copy; 2026 Inventory Management System. All rights reserved.</p>
        </div>
    </div>
    <button class="print-btn" onclick="window.print()">Print Receipt</button>
</body>
</html>