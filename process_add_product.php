<?php
// Load PDO database connection
require_once 'includes/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect all fields safely
    $product_name  = trim($_POST['product_name']);
    $category_id   = $_POST['category_id'];
    $quantity      = $_POST['quantity'];
    $buy_price     = $_POST['buy_price'];
    $sell_price    = $_POST['sell_price'];
    $reorder_level = $_POST['reorder_level'];
    $sku           = trim($_POST['sku']);
    $description   = trim($_POST['description']);

    // -----------------------------
    // IMAGE UPLOAD HANDLING
    // -----------------------------
    $image_name = null;  // default if no image is uploaded

    if (!empty($_FILES['product_image']['name'])) {

        $upload_dir = "uploads/";

        // Ensure uploads folder exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Extract file info
        $file_name  = $_FILES['product_image']['name'];
        $file_tmp   = $_FILES['product_image']['tmp_name'];
        $file_size  = $_FILES['product_image']['size'];
        $file_error = $_FILES['product_image']['error'];

        // Allowed image formats
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate image
        if ($file_error === 0) {

            if (!in_array($file_ext, $allowed_ext)) {
                die("<h2 style='color:#ff4444; font-family:Arial'>❌Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WEBP.</h2>");
            }

            if ($file_size > 2 * 1024 * 1024) {
                die("<h2 style='color:#ff4444; font-family:Arial'>❌ File too large. Max size is 2MB.</h2>");
            }

            // Create unique filename to avoid overwriting
            $image_name = uniqid("IMG_", true) . "." . $file_ext;

            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                die("<h2 style='color:#ff4444; font-family:Arial'>❌ Failed to upload image.</h2>");
            }

        } else {
            die("<h2 style='color:#ff4444; font-family:Arial'>❌ Image upload error.</h2>");
        }
    }

    // -----------------------------------
    // INSERT INTO DATABASE
    // -----------------------------------

    try {
        $query = "INSERT INTO products 
                (sku, name, category_id, quantity, buy_price, sell_price, reorder_level, description, image, status)
                  VALUES 
                (:sku, :name, :cat, :qty, :buy, :sell, :reorder, :descr, :image, :status)";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ':sku'     => $sku,
            ':name'    => $product_name,
            ':cat'     => $category_id,
            ':qty'     => $quantity,
            ':buy'     => $buy_price,
            ':sell'    => $sell_price,
            ':reorder' => $reorder_level,
            ':descr'   => $description,
            ':image'   => $image_name,
            ':status'  => 'active'
        ]);

        echo "<h2 style='color:#4CAF50; font-family:Arial'>✅ Product added successfully!</h2>";
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'product.php';
                }, 1500);
              </script>";

    } catch (Exception $e) {
        die("<h2 style='color:#ff4444; font-family:Arial'>❌ Database Error: " . $e->getMessage() . "</h2>");
    }
} else {
    echo "<h2 style='color:#ff4444; font-family:Arial'>❌ Invalid request.</h2>";
}
?>
