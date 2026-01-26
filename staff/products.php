<?php
include 'auth_staff.php';

/* ===============================
   DATABASE
================================ */
require_once '../includes/db.php';

/* ===============================
   FETCH CATEGORIES
================================ */
$catStmt = $pdo->query("SELECT id, name FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_KEY_PAIR);

/* ===============================
   FETCH PRODUCTS
================================ */
$stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products</title>

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
.product-table{
    width:100%;
    border-collapse:collapse;
    background:#1a1a1a;
    border-radius:10px;
    overflow:hidden;
}
.product-table thead{
    background:#00ff9d;
    color:#000;
}
.product-table th,
.product-table td{
    padding:12px 15px;
    border-bottom:1px solid #333;
    text-align:center;
}
.product-img{
    width:45px;
    height:45px;
    border-radius:6px;
    object-fit:cover;
    background:#333;
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
        <h2><i class="bi bi-box-seam"></i> Products</h2>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>SKU</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Selling Price</th>
                <th>Reorder Level</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($products): ?>
            <?php foreach ($products as $row): 

                $img = !empty($row['image_path'])
                    ? (filter_var($row['image_path'], FILTER_VALIDATE_URL) ? $row['image_path'] : "../" . $row['image_path'])
                    : "../uploads/products/default.png";

                $catName = $categories[$row['category_id']] ?? "Uncategorized";

                $reorderStatus = ($row['quantity'] <= $row['reorder_level'])
                    ? "<span class='badge-low'>Low</span>"
                    : "<span class='badge-ok'>OK</span>";

                $activeStatus = ($row['is_active'] == 1)
                    ? "<span class='badge-ok'>Active</span>"
                    : "<span class='badge-low'>Inactive</span>";
            ?>
            <tr>
                <td>
                    <img src="<?= htmlspecialchars($img) ?>" class="product-img">
                </td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($catName) ?></td>
                <td><?= number_format($row['selling_price']) ?></td>
                <td><?= htmlspecialchars($row['reorder_level']) ?> (<?= $reorderStatus ?>)</td>
                <td><?= $activeStatus ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="padding:20px;color:#bbb;">
                    No products found
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
