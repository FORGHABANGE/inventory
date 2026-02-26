<?php
include 'auth_admin.php';

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

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .page-container {
            margin-left: 0;
            margin-top: 100px;
            padding: 12px;
        }

        .page-header {
            flex-direction: column;
            gap: 10px;
        }

        .add-btn {
            width: 100%;
        }

        .product-table {
            font-size: 13px;
        }

        .product-table th,
        .product-table td {
            padding: 8px 5px;
            font-size: 12px;
        }

        .product-img {
            width: 35px;
            height: 35px;
        }

        .actions i {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .product-table {
            font-size: 11px;
            display: block;
            overflow-x: auto;
        }

        .product-table th,
        .product-table td {
            padding: 6px 4px;
            font-size: 11px;
        }

        .product-img {
            width: 30px;
            height: 30px;
        }

        .actions i {
            font-size: 14px;
            padding: 4px;
        }
    }
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
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            foreach ($products as $row) {

                // FIX: Image path uses correct DB column
                $img = (!empty($row['image_path'])) 
                        ? (filter_var($row['image_path'], FILTER_VALIDATE_URL) ? $row['image_path'] : $row['image_path'])
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
                <td>
                    <a href="#" class="img-link" data-img="<?= htmlspecialchars($img) ?>" data-alt="<?= htmlspecialchars($row['name']) ?>" data-desc="<?= htmlspecialchars($row['description'] ?? '') ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="product-img" alt="<?= htmlspecialchars($row['name']) ?>">
                    </a>
                </td>
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
<!-- Image Modal -->
<!-- Inline image popup card (appears at click position) -->
<div id="imgPopup" class="img-popup" style="display:none; position:absolute; z-index:2000;">
    <div class="popup-card" style="background:#1a1a1a; color:#fff; border-radius:8px; box-shadow:0 8px 30px rgba(0,0,0,0.6); overflow:hidden; max-width:720px; min-width:420px; width:auto;">
        <div class="popup-close" style="position:absolute; right:8px; top:8px; z-index:2010; cursor:pointer; color:#fff; font-size:22px;">&times;</div>
        <div class="popup-image" style="padding:14px; text-align:center;">
        <img id="popupImg" src="" alt="" style="max-width:100%; max-height:80vh; border-radius:6px;">
        </div>
        <div class="popup-body" style="padding:12px; border-top:1px solid #222;">
            <h4 id="popupTitle" style="margin:0 0 8px 0; color:#00ff9d; font-size:16px;"></h4>
            <p id="popupDesc" style="margin:0; color:#bdbdbd; font-size:14px; white-space:pre-wrap;"></p>
        </div>
    </div>
</div>

<script>
(() => {
    const popup = document.getElementById('imgPopup');
    const card = popup.querySelector('.popup-card');
    const imgEl = document.getElementById('popupImg');
    const titleEl = document.getElementById('popupTitle');
    const descEl = document.getElementById('popupDesc');

    function hidePopup(){
        popup.style.display = 'none';
        imgEl.src = '';
        titleEl.textContent = '';
        descEl.textContent = '';
    }

    document.querySelectorAll('.img-link').forEach(el=>{
        el.addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            const src = this.getAttribute('data-img');
            const alt = this.getAttribute('data-alt') || '';
            const desc = this.getAttribute('data-desc') || '';

            imgEl.src = src;
            imgEl.alt = alt;
            titleEl.textContent = alt;
            descEl.textContent = desc;

            // temporarily show to measure
            popup.style.display = 'block';
            card.style.visibility = 'hidden';
            card.style.display = 'block';

            const rect = card.getBoundingClientRect();
            let left = e.pageX + 12;
            let top = e.pageY + 12;

            // adjust horizontal overflow
            const viewportRight = window.scrollX + window.innerWidth;
            if (left + rect.width > viewportRight) {
                left = e.pageX - rect.width - 12;
            }
            if (left < window.scrollX + 8) left = window.scrollX + 8;

            // adjust vertical overflow
            const viewportBottom = window.scrollY + window.innerHeight;
            if (top + rect.height > viewportBottom) {
                top = e.pageY - rect.height - 12;
            }
            if (top < window.scrollY + 8) top = window.scrollY + 8;

            popup.style.left = left + 'px';
            popup.style.top = top + 'px';
            card.style.visibility = 'visible';
        });
    });

    // close button
    popup.querySelector('.popup-close').addEventListener('click', function(e){
        e.stopPropagation();
        hidePopup();
    });

    // hide when clicking outside
    document.addEventListener('click', function(e){
        if (!popup.contains(e.target)) hidePopup();
    });

    // hide on escape
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') hidePopup(); });
})();
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>

<?php include 'layout/footer.php'; ?>
</body>
</html>
