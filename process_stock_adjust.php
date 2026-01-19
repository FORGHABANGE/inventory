<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require "includes/db.php";

$errors = [];
$success = "";

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $adjust_type = trim($_POST['adjust_type'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $reference = trim($_POST['reference'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // Validation
    if ($product_id <= 0) $errors[] = "Please select a product.";
    if (!in_array($adjust_type, ['INCREASE', 'DECREASE'])) $errors[] = "Invalid adjustment type.";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than zero.";

    // Check product exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $errors[] = "Product not found.";
        } else {
            if ($adjust_type === "DECREASE" && $product['quantity'] < $quantity) {
                $errors[] = "Cannot decrease stock below zero. Available: {$product['quantity']}.";
            }
        }
    }

    // Apply the stock adjustment
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into stock_movements table
            $sql = "INSERT INTO stock_movements 
                (product_id, type, quantity, reference, note, user_id, created_at)
                VALUES
                (:product_id, :type, :quantity, :reference, :note, :user_id, NOW())";

            $movement = $pdo->prepare($sql);
            $movement->execute([
                ':product_id' => $product_id,
                ':type' => 'adjustment',
                ':quantity' => $quantity,
                ':reference' => $reference,
                ':note' => $note,
                ':user_id' => $_SESSION['user_id']
            ]);

            // Update product quantity
            if ($adjust_type === "INCREASE") {
                $update = $pdo->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :id");
            } else {
                $update = $pdo->prepare("UPDATE products SET quantity = quantity - :qty WHERE id = :id");
            }

            $update->execute([':qty' => $quantity, ':id' => $product_id]);

            $pdo->commit();

            $success = "Stock adjustment applied successfully.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // Store messages for display in stock_adjust.php
    $_SESSION['adjust_errors'] = $errors;
    $_SESSION['adjust_success'] = $success;

    header("Location: stock_adjust.php");
    exit;
}

?>
