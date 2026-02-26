<?php
include 'auth_admin.php';

require_once 'includes/db.php';

// Load categories
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Helper: generate random SKU
function generate_sku($prefix = 'PRD') {
    return $prefix . '-' . strtoupper(substr(md5(uniqid((string)microtime(true), true)), 0, 6));
}

// Helper: generate **unique** SKU
function generate_unique_sku($pdo) {
    do {
        $sku = generate_sku();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = :sku");
        $stmt->execute([':sku' => $sku]);
        $exists = $stmt->fetchColumn();
    } while ($exists);
    return $sku;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitize
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $purchase_price = isset($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : 0;
    $selling_price = isset($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0;
    $reorder_level = isset($_POST['reorder_level']) ? (int)$_POST['reorder_level'] : 0;
    $quantity = trim($_POST['quantity'] ?? '0'); // allow alphanumeric
    $description = trim($_POST['description'] ?? '');

    // Validations
    if ($name === '') $errors[] = 'Product name is required.';
    if ($purchase_price < 0) $errors[] = 'Purchase price must be >= 0.';
    if ($selling_price < 0) $errors[] = 'Selling price must be >= 0.';
    if ($reorder_level < 0) $errors[] = 'Reorder level must be 0 or greater.';

    // Always generate unique SKU
    $sku = generate_unique_sku($pdo);

    // Image handling
    $imageFilename = null;
    $uploadDir = __DIR__ . '/uploads/products';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

    if (!empty($_FILES['image_file']['name'])) {
        $file = $_FILES['image_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $errors[] = 'Uploaded file must be an image (jpg, png, gif, webp).';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageFilename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $imageFilename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'Failed to move uploaded file.';
                    $imageFilename = null;
                }
            }
        } else {
            $errors[] = 'Error uploading file. Code: ' . $file['error'];
        }
    } elseif (!empty($_POST['image_path'])) {
        $image_url = trim($_POST['image_path']);
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            $headers = @get_headers($image_url, 1);
            $contentType = '';
            if ($headers !== false && isset($headers['Content-Type'])) {
                $contentType = is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'];
            }

            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($contentType, $allowed)) {
                $errors[] = 'Provided URL does not appear to be an allowed image type.';
            } else {
                $ext = match($contentType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
                $imageFilename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $imageFilename;

                $imageData = @file_get_contents($image_url);
                if ($imageData === false) {
                    $ch = curl_init($image_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $imageData = curl_exec($ch);
                    $curlErr = curl_error($ch);
                    curl_close($ch);
                    if ($imageData === false) {
                        $errors[] = 'Failed to download image from URL. ' . ($curlErr ?? '');
                        $imageFilename = null;
                    }
                }
                if ($imageFilename && $imageData !== false) {
                    if (@file_put_contents($targetPath, $imageData) === false) {
                        $errors[] = 'Failed to save downloaded image on server.';
                        $imageFilename = null;
                    }
                }
            }
        } else {
            $errors[] = 'Image URL is not valid.';
        }
    }

    // Insert into DB
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products 
                (sku, name, category_id, purchase_price, selling_price, reorder_level, quantity, description, image_path)
                VALUES
                (:sku, :name, :category_id, :purchase_price, :selling_price, :reorder_level, :quantity, :description, :image_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':sku' => $sku,
                ':name' => $name,
                ':category_id' => $category_id,
                ':purchase_price' => $purchase_price,
                ':selling_price' => $selling_price,
                ':reorder_level' => $reorder_level,
                ':quantity' => $quantity,
                ':description' => $description,
                ':image_path' => $imageFilename ? 'uploads/products/' . $imageFilename : null
            ]);

            $success = "Product added successfully with SKU: $sku";
            $_POST = [];
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            if (!empty($imageFilename) && file_exists($uploadDir . '/' . $imageFilename)) {
                @unlink($uploadDir . '/' . $imageFilename);
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Product — Inventory</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* ==== Keep your dark theme and design ==== */
:root{--bg:#121212;--panel:#1a1a1a;--muted:#bdbdbd;--accent:#00ff9d;--accent-strong:#00e68c;--danger:#ff4d4d;--card-shadow:rgba(0,0,0,0.6);}
body{margin:0;font-family:"Poppins",sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;padding:30px;margin-top:90px;width:calc(100% - 240px);}
.card{background:var(--panel);border-radius:12px;padding:18px;box-shadow:0 8px 30px var(--card-shadow);width:95%;max-width:none;margin:0;}
.card h2{color:var(--accent);margin:0 0 12px 0;}
label{display:block;margin-top:12px;color:var(--muted);font-size:14px;}
input[type="text"], input[type="number"], input[type="url"], select, textarea{width:100%;padding:10px 12px;margin-top:6px;background:#0f0f0f;border:1px solid #222;color:#fff;border-radius:8px;box-sizing:border-box;}
input[type="file"]{margin-top:8px;color:#fff;}
.two-cols{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.btn{display:inline-flex;align-items:center;gap:8px;background:var(--accent);color:#000;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:600;margin-top:14px;}
.btn.secondary{background:transparent;color:var(--muted);border:1px solid #2a2a2a;}
.note{font-size:13px;color:var(--muted);margin-top:12px;}
.preview{margin-top:10px;}
.preview img{max-width:140px;border-radius:8px;border:1px solid #222;}
.messages{margin-bottom:12px;}
.messages .error{background: rgba(255,77,77,0.12); color: var(--danger); padding:10px; border-radius:8px; margin-bottom:6px;}
.messages .success{background: rgba(0,255,157,0.12); color:#00ff9d; padding:10px; border-radius:8px; margin-bottom:6px;}
.field-row{display:flex;gap:12px;align-items:center;}
.small{font-size:13px;color:var(--muted);}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; width: 100%; margin-top: 100px; padding: 15px; }
    .two-cols { grid-template-columns: 1fr; gap: 10px; }
    .card { width: 100%; padding: 15px; }
    input, select, textarea { padding: 8px 10px; font-size: 14px; }
}

@media (max-width: 768px) {
    .page-container { margin-left: 0; width: 100%; padding: 12px; }
    .two-cols { grid-template-columns: 1fr; gap: 8px; }
    .card { width: 100%; padding: 12px; }
    .preview img { max-width: 100px; }
    input, select, textarea { padding: 8px; font-size: 13px; }
    .btn { width: 100%; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .card { padding: 10px; }
    h2 { font-size: 18px; }
    label { font-size: 13px; margin-top: 8px; }
    input, select, textarea { padding: 6px; font-size: 12px; }
    .field-row { flex-direction: column; gap: 6px; }
    .preview img { max-width: 80px; }
    .btn { width: 100%; padding: 10px; font-size: 12px; }
}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">
<h2><i class="bi bi-plus-circle"></i> Add New Product</h2>

<div class="messages">
<?php if(!empty($errors)): foreach($errors as $err): ?>
<div class="error"><?= htmlspecialchars($err) ?></div>
<?php endforeach; endif; ?>
<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" id="addProductForm">
<div class="two-cols">
<div>
<label>Product Image (upload or URL)</label>
<input type="file" name="image_file" accept="image/*" id="image_file">
<div class="note">Or paste an image URL below (the server will download it)</div>
<input type="url" name="image_path" placeholder="https://example.com/image.jpg" id="image_url">
<div class="preview" id="previewBox"></div>
</div>
<div>
<label>SKU (auto-generated)</label>
<input type="text" disabled value="<?= isset($sku)?htmlspecialchars($sku):'' ?>">
<label>Name</label>
<input type="text" name="name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
<label>Category</label>
<select name="category_id" required <?= empty($categories)?'disabled':'' ?>>
<option value="">Select category</option>
<?php if(!empty($categories)): foreach($categories as $cat):
$sel = (isset($_POST['category_id']) && $_POST['category_id']==$cat['id'])?'selected':''; ?>
<option value="<?= $cat['id'] ?>" <?= $sel ?>><?= htmlspecialchars($cat['name']) ?></option>
<?php endforeach; else: ?>
<option value="">No categories available</option>
<?php endif; ?>
</select>
</div>
</div>

<div class="two-cols" style="margin-top:12px;">
<div>
<label>Purchase Price</label>
<input type="number" name="purchase_price" step="0.01" min="0" required value="<?= isset($_POST['purchase_price'])?htmlspecialchars($_POST['purchase_price']):'' ?>">
</div>
<div>
<label>Selling Price</label>
<input type="number" name="selling_price" step="0.01" min="0" required value="<?= isset($_POST['selling_price'])?htmlspecialchars($_POST['selling_price']):'' ?>">
</div>
</div>

<div class="two-cols" style="margin-top:12px;">
<div>
<label>Quantity</label>
<input type="text" name="quantity" required value="<?= isset($_POST['quantity'])?htmlspecialchars($_POST['quantity']):'0' ?>">
</div>
<div>
<label>Reorder Level</label>
<input type="number" name="reorder_level" min="0" required value="<?= isset($_POST['reorder_level'])?htmlspecialchars($_POST['reorder_level']):'0' ?>">
</div>
</div>

<label style="margin-top:12px;">Description</label>
<textarea name="description" rows="3" style="width:100%; padding:10px; background:#0f0f0f; border:1px solid #222; color:#fff; border-radius:8px;"><?= isset($_POST['description'])?htmlspecialchars($_POST['description']):'' ?></textarea>

<div style="display:flex; gap:12px; margin-top:14px;">
<button type="submit" class="btn"><i class="bi bi-save"></i> Save Product</button>
<a href="product.php" class="btn secondary" style="text-decoration:none; padding:10px 14px;"><i class="bi bi-arrow-left"></i> Back to Products</a>
</div>
</form>
</div>
</div>

<script>
const fileInput = document.getElementById('image_file');
const urlInput = document.getElementById('image_url');
const preview = document.getElementById('previewBox');

fileInput.addEventListener('change', () => {
    const f = fileInput.files[0];
    if(!f){ preview.innerHTML=''; return; }
    const url = URL.createObjectURL(f);
    preview.innerHTML='<img src="'+url+'" alt="preview">';
});

urlInput.addEventListener('input', () => {
    const v = urlInput.value.trim();
    if(v===''){ if(!fileInput.files.length) preview.innerHTML=''; return; }
    preview.innerHTML='<img src="'+v+'" alt="preview" onerror="this.style.display=\'none\';">';
});

<?php if($success): ?>
document.getElementById('addProductForm').reset();
fileInput.value='';
urlInput.value='';
preview.innerHTML='';
<?php endif; ?>
</script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
