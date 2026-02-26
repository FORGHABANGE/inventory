<?php
include 'auth_admin.php';

require_once 'includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$admin_id = 1;

$errors = [];
$success = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) die('Invalid category ID');

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute([':id'=>$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$category) die('Category not found');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if($name==='') $errors[]='Category name is required.';
    if(empty($errors)){
        try{
            $stmt = $pdo->prepare("UPDATE categories SET name=:name, description=:description WHERE id=:id");
            $stmt->execute([':name'=>$name, ':description'=>$description, ':id'=>$id]);
            $success = "Category updated successfully.";
            $category['name'] = $name;
            $category['description'] = $description;
        }catch(Exception $e){
            $errors[] = 'Database error: '.$e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Edit Category — Inventory</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{--bg:#121212;--panel:#1a1a1a;--muted:#bdbdbd;--accent:#00ff9d;--accent-strong:#00e68c;--danger:#ff4d4d;--card-shadow:rgba(0,0,0,0.6);}
body{margin:0;font-family:"Poppins",sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;padding:30px;margin-top:90px;width:calc(100% - 240px);}
.card{background:var(--panel);border-radius:12px;padding:18px;box-shadow:0 8px 30px var(--card-shadow);width:95%;max-width:none;margin:0;}
.card h2{color:var(--accent);margin:0 0 12px 0;}
label{display:block;margin-top:12px;color:var(--muted);font-size:14px;}
input[type="text"],textarea{width:100%;padding:10px 12px;margin-top:6px;background:#0f0f0f;border:1px solid #222;color:#fff;border-radius:8px;box-sizing:border-box;}
textarea{resize:vertical;}
button{display:inline-flex;align-items:center;gap:8px;background:var(--accent);color:#000;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:600;margin-top:14px;}
.btn{display:inline-flex;align-items:center;gap:8px;background:var(--accent);color:#000;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:600;margin-top:14px;}
.btn.secondary{background:transparent;color:var(--muted);border:1px solid #2a2a2a;}
.messages{margin-bottom:12px;}
.messages .error{background: rgba(255,77,77,0.12); color: var(--danger); padding:10px; border-radius:8px; margin-bottom:6px;}
.messages .success{background: rgba(0,255,157,0.12); color:#00ff9d; padding:10px; border-radius:8px; margin-bottom:6px;}
@media(max-width:720px){.page-container{margin-left:0;padding:20px;margin-top:20px;}}
/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; width: 100%; margin-top: 100px; padding: 15px; }
    .card { width: 100%; padding: 15px; }
    input, textarea { padding: 8px 10px; font-size: 14px; }
}

@media (max-width: 768px) {
    .page-container { margin-left: 0; width: 100%; padding: 12px; }
    .card { width: 100%; padding: 12px; }
    input, textarea { padding: 8px; font-size: 13px; }
    .btn { width: 100%; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .card { padding: 10px; }
    h2 { font-size: 18px; }
    label { font-size: 13px; margin-top: 8px; }
    input, textarea { padding: 6px; font-size: 12px; }
    .btn { width: 100%; padding: 10px; font-size: 12px; }
}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">
<h2><i class="bi bi-pencil-square"></i> Edit Category</h2>

<div class="messages">
<?php if(!empty($errors)): foreach($errors as $err): ?>
<div class="error"><?= htmlspecialchars($err) ?></div>
<?php endforeach; endif; ?>
<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
</div>

<form method="POST">
<label>Category Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
<label>Description</label>
<textarea name="description" rows="3"><?= htmlspecialchars($category['description']) ?></textarea>
<div style="display:flex; gap:12px; margin-top:14px;">
<button type="submit"><i class="bi bi-save"></i> Save Changes</button>
<a href="categories.php" class="btn secondary" style="text-decoration:none; padding:10px 14px;"><i class="bi bi-arrow-left"></i>Back</a>
</div>
</form>
</div>
</div>
<?php include 'layout/footer.php'; ?>
</body>
</html>
