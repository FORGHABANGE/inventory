<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/db.php";

/* =========================
   VALIDATE PRODUCT ID
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID");
}
$product_id = (int) $_GET['id'];

/* =========================
   FETCH PRODUCT
========================= */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found");
}

/* =========================
   FETCH CATEGORIES
========================= */
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HANDLE UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sku             = trim($_POST['sku']);
    $name            = trim($_POST['name']);
    $category_id     = (int) $_POST['category_id'];
    $purchase_price = (float) $_POST['purchase_price'];
    $selling_price  = (float) $_POST['selling_price'];
    $quantity        = trim($_POST['quantity']); // ✅ alphanumeric
    $reorder_level   = (int) $_POST['reorder_level'];
    $is_active       = (int) $_POST['is_active'];

    /* =========================
       IMAGE HANDLING
    ========================= */
    $image_path = $product['image_path']; // keep old image

    /* Image via URL */
    if (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }

    /* Image via Upload */
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "uploads/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = "product_" . time() . "." . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_path = $targetPath;
        }
    }

    /* =========================
       UPDATE PRODUCT
    ========================= */
    $update = $pdo->prepare("
        UPDATE products SET
            sku = :sku,
            name = :name,
            category_id = :category_id,
            purchase_price = :purchase_price,
            selling_price = :selling_price,
            quantity = :quantity,
            reorder_level = :reorder_level,
            is_active = :is_active,
            image_path = :image_path
        WHERE id = :id
    ");

    $update->execute([
        ':sku' => $sku,
        ':name' => $name,
        ':category_id' => $category_id,
        ':purchase_price' => $purchase_price,
        ':selling_price' => $selling_price,
        ':quantity' => $quantity,
        ':reorder_level' => $reorder_level,
        ':is_active' => $is_active,
        ':image_path' => $image_path,
        ':id' => $product_id
    ]);

    header("Location: product.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body {
    background:#121212;
    color:#fff;
    font-family:Poppins, sans-serif;
    margin:0;
}
.page-container {
    margin-left:240px;
    padding:30px;
    margin-top:90px;
}
.form-box {
    background:#1a1a1a;
    padding:25px;
    border-radius:12px;
    max-width:900px;
}
label {
    color:#00ff9d;
    margin-top:12px;
    display:block;
}
input, select {
    width:100%;
    padding:10px;
    margin-top:5px;
    background:#2a2a2a;
    border:none;
    border-radius:6px;
    color:#fff;
}
img.preview {
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:8px;
    margin-bottom:10px;
}
button {
    background:#00ff9d;
    color:#000;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    font-weight:600;
    margin-top:20px;
    cursor:pointer;
}
button:hover {
    background:#00e68c;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; margin-top: 100px; padding: 15px; }
    .form-box { max-width: 100%; padding: 15px; }
    input, select { font-size: 14px; padding: 8px; }
    img.preview { width: 60px; height: 60px; }
    button { padding: 10px 16px; font-size: 14px; }
}

@media (max-width: 768px) {
    .page-container { padding: 12px; }
    .form-box { padding: 12px; }
    input, select { font-size: 13px; padding: 6px; }
    label { margin-top: 8px; font-size: 13px; }
    img.preview { width: 50px; height: 50px; }
    button { width: 100%; padding: 10px; font-size: 13px; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .form-box { padding: 10px; }
    h2 { font-size: 18px; }
    input, select { font-size: 12px; padding: 6px; }
    label { margin-top: 6px; font-size: 12px; }
    img.preview { width: 40px; height: 40px; }
    button { width: 100%; padding: 8px; font-size: 12px; margin-top: 12px; }
}
</head>

<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">
    <h2 style="color:#00ff9d;">
        <i class="bi bi-pencil-square"></i> Edit Product
    </h2>

    <div class="form-box">
        <form method="POST" enctype="multipart/form-data">

            <label>Current Image</label>
            <img src="<?= !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : 'assets/images/default.png' ?>" class="preview">

            <label>Upload New Image</label>
            <input type="file" name="image">

            <label>Or Image URL</label>
            <input type="text" name="image_url" placeholder="https://example.com/image.jpg">

            <label>SKU</label>
            <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']) ?>" required>

            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label>Category</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Purchase Price</label>
            <input type="number" step="0.01" name="purchase_price" value="<?= $product['purchase_price'] ?>">

            <label>Selling Price</label>
            <input type="number" step="0.01" name="selling_price" value="<?= $product['selling_price'] ?>">

            <label>Quantity (alphanumeric)</label>
            <input type="text" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>">

            <label>Reorder Level</label>
            <input type="number" name="reorder_level" value="<?= $product['reorder_level'] ?>">

            <label>Status</label>
            <select name="is_active">
                <option value="1" <?= $product['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $product['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button type="submit">
                <i class="bi bi-save"></i> Update Product
            </button>

        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
