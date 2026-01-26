<?php
include 'auth_staff.php';

/* ===============================
   DATABASE
================================ */
require_once '../includes/db.php';

/* ===============================
   FETCH STOCK LEVELS
   - Active products only
   - Ordered by insertion (id ASC)
================================ */
$stmt = $pdo->query("
    SELECT id, sku, name, quantity, reorder_level
    FROM products
    WHERE is_active = 1
    ORDER BY id ASC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Stock Levels</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body{
    background:#121212;
    font-family:Poppins,sans-serif;
    color:#fff;
    margin:0;
}
.page-container{
    margin-left:240px;
    padding:25px;
    margin-top:90px;
}
.page-header{
    margin-bottom:25px;
}
.page-header h2{
    color:#00ff9d;
    margin:0;
}
.stock-table{
    width:100%;
    border-collapse:collapse;
    background:#1a1a1a;
    border-radius:10px;
    overflow:hidden;
}
.stock-table thead{
    background:#00ff9d;
    color:#000;
}
.stock-table th,
.stock-table td{
    padding:12px 15px;
    border-bottom:1px solid #333;
    text-align:center;
}
.badge-ok{
    color:#00ff9d;
    font-weight:600;
}
.badge-low{
    color:#ff4d4d;
    font-weight:600;
}
@media(max-width:768px){
    .page-container{
        margin-left:0;
        margin-top:20px;
    }
}
</style>
</head>

<body>

<?php include "layout/sidebar_staff.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">

    <div class="page-header">
        <h2><i class="bi bi-bar-chart-line"></i> Stock Levels</h2>
    </div>

    <table class="stock-table">
        <thead>
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Reorder Level</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($products): ?>
            <?php $i = 1; foreach ($products as $row): 

                $status = ($row['quantity'] <= $row['reorder_level'])
                    ? "<span class='badge-low'>Low</span>"
                    : "<span class='badge-ok'>OK</span>";
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($row['reorder_level']) ?></td>
                <td><?= $status ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="padding:20px;color:#bbb;">
                    No stock records found
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
