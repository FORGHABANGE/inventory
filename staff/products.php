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

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; margin-top: 100px; padding: 15px; }
    .product-table { font-size: 13px; }
    th, td { padding: 8px; }
}

@media (max-width: 768px) {
    .page-container { margin-left: 0; padding: 12px; }
    .product-table { font-size: 12px; }
    th, td { padding: 6px; }
    .product-img { width: 30px; height: 30px; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .product-table { display: block; overflow-x: auto; font-size: 11px; }
    th, td { padding: 5px; font-size: 11px; }
    .product-img { width: 25px; height: 25px; }
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
                    <a href="#" class="img-link" data-bs-toggle="modal" data-bs-target="#imgModal" data-img="<?= htmlspecialchars($img) ?>" data-alt="<?= htmlspecialchars($row['name']) ?>">
                        <img src="<?= htmlspecialchars($img) ?>" class="product-img">
                    </a>
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
<!-- Inline image popup (like admin) -->
<div id="imgPopup" class="img-popup" style="display:none; position:absolute; z-index:2000;">
    <div class="popup-card" style="background:#1a1a1a; color:#fff; border-radius:8px; box-shadow:0 8px 30px rgba(0,0,0,0.6); overflow:hidden; max-width:720px; min-width:320px; width:auto;">
        <div class="popup-close" style="position:absolute; right:8px; top:8px; z-index:2010; cursor:pointer; color:#fff; font-size:22px;">&times;</div>
        <div class="popup-image" style="padding:14px; text-align:center;">
            <img id="popupImgStaff" src="" alt="" style="max-width:100%; max-height:80vh; border-radius:6px;">
        </div>
        <div class="popup-body" style="padding:12px; border-top:1px solid #222;">
            <h4 id="popupTitleStaff" style="margin:0 0 8px 0; color:#00ff9d; font-size:16px;"></h4>
            <p id="popupDescStaff" style="margin:0; color:#bdbdbd; font-size:14px; white-space:pre-wrap;"></p>
        </div>
    </div>
</div>

<script>
(function(){
    const popup = document.getElementById('imgPopup');
    const card = popup.querySelector('.popup-card');
    const imgEl = document.getElementById('popupImgStaff');
    const titleEl = document.getElementById('popupTitleStaff');
    const descEl = document.getElementById('popupDescStaff');

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

<?php include '../layout/footer.php'; ?>
</body>
</html>
