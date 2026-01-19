<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

include "includes/db.php"; // DB connection

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => name
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - Inventory System</title>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
    body {
        background: #121212;
        font-family: "Poppins", sans-serif;
        color: #fff;
        margin: 0;
    }

    .page-container {
        margin-left: 240px;
        padding: 25px;
        margin-top: 90px;
    }

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

    .add-btn {
        background: #00ff9d;
        color: #000;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }

    .add-btn:hover {
        background: #00e68c;
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
        background: #1a1a1a;
        border-radius: 10px;
        overflow: hidden;
    }

    .product-table thead {
        background: #00ff9d;
        color: #000;
    }

    .product-table th, 
    .product-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #333;
        text-align: center;
        vertical-align: middle;
    }

    .product-img {
        width: 45px;
        height: 45px;
        border-radius: 6px;
        object-fit: cover;
        background: #333;
    }

    .actions i {
        font-size: 18px;
        cursor: pointer;
        padding: 6px;
    }

    .edit { color: #00ff9d; }
    .delete { color: #ff4d4d; }

    .badge-ok { color: #00ff9d; font-weight: 600; }
    .badge-low { color: #ff4d4d; font-weight: 600; }
</style>
</head>
<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">

    <div class="page-header">
        <h2><i class="bi bi-box-seam"></i> Products</h2>
        <button class="add-btn" onclick="window.location.href='add_product.php'">
            <i class="bi bi-plus-circle"></i> Add Product
        </button>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>SKU</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Reorder Level</th>
                <th>is_active</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            foreach ($products as $row) {

                // FIX: Image path uses correct DB column
                $img = (!empty($row['image_path'])) 
                        ? $row['image_path']
                        : "assets/images/default.png";

                // Category lookup
                $catName = $categories[$row['category_id']] ?? "Uncategorized";

                // FIX: Reorder level status
                if ($row['quantity'] <= $row['reorder_level']) {
                    $reorderStatus = "<span class='badge-low'>Low</span>";
                } else {
                    $reorderStatus = "<span class='badge-ok'>OK</span>";
                }

                // FIX: is_active formatting
                $activeStatus = ($row['is_active'] == 1)
                    ? "<span class='badge-ok'>Active</span>"
                    : "<span class='badge-low'>Inactive</span>";
        ?>

            <tr>
                <td><img src="<?= htmlspecialchars($img) ?>" class="product-img" alt="<?= htmlspecialchars($row['name']) ?>"></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($catName) ?></td>
                <td>XAF <?= number_format($row['purchase_price']) ?></td>
                <td>XAF <?= number_format($row['selling_price']) ?></td>
                <td><?= htmlspecialchars($row['reorder_level']) ?> (<?= $reorderStatus ?>)</td>
                <td><?= $activeStatus ?></td>

                <td class="actions">
                    <a href="edit_product.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil-square edit"></i></a>
                    <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to Delete this product?');">
                        <i class="bi bi-trash3 delete"></i>
                    </a>
                </td>
            </tr>

        <?php }
        } else { ?>
            <tr>
                <td colspan="10" style="padding: 20px; color: #bbb;">No products found</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>
