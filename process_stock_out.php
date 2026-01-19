<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $reference = trim($_POST['reference']);
    $note = trim($_POST['note']);
    $user_id = $_SESSION['user_id'];

    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be greater than zero.";
        header("Location: stock_out.php");
        exit;
    }

    // Check if product exists and has enough stock
    $stmt = $pdo->prepare("SELECT quantity, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: stock_out.php");
        exit;
    }

    if ($product['quantity'] < $quantity) {
        $_SESSION['error'] = "Not enough stock. Current stock: {$product['quantity']}.";
        header("Location: stock_out.php");
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // 1. Update product quantity
        $new_qty = $product['quantity'] - $quantity;
        $update = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $update->execute([$new_qty, $product_id]);

        // 2. Insert into stock_movements
        $insert = $pdo->prepare("
            INSERT INTO stock_movements (product_id, type, quantity, reference, note, user_id, created_at)
            VALUES (?, 'out', ?, ?, ?, ?, NOW())
        ");
        $insert->execute([$product_id, $quantity, $reference, $note, $user_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Stock Out successful for '{$product['name']}'.";
        header("Location: stock_out.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error processing Stock Out: " . $e->getMessage();
        header("Location: stock_out.php");
        exit;
    }
} else {
    // Redirect if not POST
    header("Location: stock_out.php");
    exit;
}
?>
