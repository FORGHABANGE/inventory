<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

include "includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $reference = trim($_POST['reference']);
    $note = trim($_POST['note']);
    $user_id = $_SESSION['user_id'];

    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be greater than zero.";
        header("Location: stock_in.php");
        exit;
    }

    // Check product exists
    $stmt = $pdo->prepare("SELECT quantity, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: stock_in.php");
        exit;
    }

    $pdo->beginTransaction();

    try {
        // Update product quantity
        $new_qty = $product['quantity'] + $quantity;
        $update = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $update->execute([$new_qty, $product_id]);

        // Insert into stock_movements
        $insert = $pdo->prepare("
            INSERT INTO stock_movements (product_id, type, quantity, reference, note, user_id, created_at)
            VALUES (?, 'in', ?, ?, ?, ?, NOW())
        ");
        $insert->execute([$product_id, $quantity, $reference, $note, $user_id]);

        $pdo->commit();
        $_SESSION['success'] = "Stock In successful for '{$product['name']}'.";
        header("Location: stock_in.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error processing Stock In: " . $e->getMessage();
        header("Location: stock_in.php");
        exit;
    }
} else {
    header("Location: stock_in.php");
    exit;
}
?>
