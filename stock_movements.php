<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

include "includes/db.php";

// Debug: Check if $pdo exists
if (!isset($pdo)) {
    die('Database connection failed. Check includes/db.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movements - Inventory System</title>

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

        .actions-group button {
            background: #00ff9d;
            color: #000;
            border: none;
            padding: 10px 18px;
            margin-left: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .actions-group button:hover {
            background: #00e68c;
        }

        .movement-table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 10px;
            overflow: hidden;
        }

        .movement-table thead {
            background: #00ff9d;
            color: #000;
        }

        .movement-table th,
        .movement-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            text-align: center;
        }

        .type-in {
            color: #00ff9d;
            font-weight: 600;
        }

        .type-out {
            color: #ff4d4d;
            font-weight: 600;
        }

        .type-adjust {
            color: #ffd700;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">

    <div class="page-header">
        <h2><i class="bi bi-arrow-left-right"></i> Stock Movements</h2>

        <div class="actions-group">
            <button onclick="window.location.href='stock_in.php'">
                <i class="bi bi-plus-circle"></i> Stock In
            </button>

            <button onclick="window.location.href='stock_out.php'">
                <i class="bi bi-dash-circle"></i> Stock Out
            </button>

            <button onclick="window.location.href='stock_adjust.php'">
                <i class="bi bi-sliders"></i> Adjust Stock
            </button>
        </div>
    </div>

    <table class="movement-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Reference</th>
                <th>Note</th>
                <th>User</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $query = "
            SELECT sm.*, p.name AS product_name, u.name AS user_name
            FROM stock_movements sm
            LEFT JOIN products p ON sm.product_id = p.id
            LEFT JOIN users u ON sm.user_id = u.id
            ORDER BY sm.created_at DESC
        ";

        try {
            $stmt = $pdo->query($query);
            $movements = $stmt->fetchAll();
        } catch (Exception $e) {
            $movements = [];
        }

        // Debug: Check number of movements
        echo "<!-- Debug: Found " . count($movements) . " movements -->";

         if ($movements) {
             foreach ($movements as $m) {
                 $typeLower = strtolower($m['type']);
                 $typeClass = ($typeLower == 'in') ? 'type-in' :
                              (($typeLower == 'out') ? 'type-out' : 'type-adjust');
                $typeDisplay = ($typeLower == 'in') ? 'Stock In' :
                               (($typeLower == 'out') ? 'Stock Out' : 'Adjustment');
                $productName = $m['product_name'] ? htmlspecialchars($m['product_name']) : '—';
                $userName = $m['user_name'] ? htmlspecialchars($m['user_name']) : 'System';
                $reference = $m['reference'] ? htmlspecialchars($m['reference']) : '-';
                $note = $m['note'] ? htmlspecialchars($m['note']) : '-';
                $date = isset($m['created_at']) ? date("Y-m-d H:i", strtotime($m['created_at'])) : '-';
                $qty = htmlspecialchars($m['quantity']);
                $type = htmlspecialchars($typeDisplay);
         ?>
             <tr>
                <td><?= $date ?></td>
                <td><?= $productName ?></td>
                <td class="<?= $typeClass ?>"><?= $type ?></td>
                <td><?= $qty ?></td>
                <td><?= $reference ?></td>
                <td><?= $note ?></td>
                <td><?= $userName ?></td>
             </tr>
         <?php }
         } else { ?>
            <tr>
                <td colspan="7" style="padding: 20px; color: #aaa;">No stock movement records found</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>
