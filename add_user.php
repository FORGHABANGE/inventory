<?php
require_once 'includes/db.php';
session_start();

$roles = $pdo->query("SELECT id, name FROM roles")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pdo->prepare("
        INSERT INTO users (role_id, username, email, password_hash, full_name, is_active)
        VALUES (?,?,?,?,?,?)
    ")->execute([
        $_POST['role_id'],
        $_POST['username'],
        $_POST['email'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['full_name'],
        $_POST['is_active']
    ]);
    header("Location: users.php");
    exit;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add User</title>
<style>
body{background:#121212;color:#fff;font-family:Poppins,sans-serif}
.page-container{margin-left:210px;margin-top:90px;padding:30px}
.card{background:#1a1a1a;padding:20px;border-radius:12px;max-width:500px}
input,select{
width:100%;padding:10px;margin-top:8px;
background:#0f0f0f;border:1px solid #222;color:#fff;border-radius:8px
}
.btn{margin-top:14px;background:#00ff9d;color:#000;padding:10px 16px;border-radius:8px;border:none;font-weight:600}
.btn.secondary{background:transparent;color:var(--muted);border:1px solid #2a2a2a;}
@media(max-width:720px){.page-container{margin-left:0;margin-top:20px}}
</style>
</head>

<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<h2 style="color:#00ff9d">Add User</h2>

<form method="post">
<input name="username" placeholder="Username" required>
<input name="full_name" placeholder="Full Name">
<input type="email" name="email" placeholder="Email">
<input type="password" name="password" placeholder="Password" required>

<select name="role_id" required>
<?php foreach($roles as $r): ?>
<option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
<?php endforeach; ?>
</select>

<select name="is_active">
<option value="1">Active</option>
<option value="0">Disabled</option>
</select>

<button class="btn">Save User</button>
<a href="users.php" class="btn secondary" style="text-decoration:none; padding:10px 14px;"><i class="bi bi-arrow-left"></i> Back to users</a>
</div>
</form>

</div>
</div>
</body>
</html>
