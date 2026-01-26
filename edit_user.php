<?php
require_once 'includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = (int) $_GET['id'];

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php");
    exit;
}

// Fetch roles
$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll();

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['password'])) {
        $sql = "UPDATE users 
                SET role_id=?, username=?, email=?, full_name=?, is_active=?, password_hash=? 
                WHERE id=?";
        $data = [
            $_POST['role_id'],
            $_POST['username'],
            $_POST['email'],
            $_POST['full_name'],
            $_POST['is_active'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $id
        ];
    } else {
        $sql = "UPDATE users 
                SET role_id=?, username=?, email=?, full_name=?, is_active=? 
                WHERE id=?";
        $data = [
            $_POST['role_id'],
            $_POST['username'],
            $_POST['email'],
            $_POST['full_name'],
            $_POST['is_active'],
            $id
        ];
    }

    $pdo->prepare($sql)->execute($data);
    header("Location: users.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit User</title>

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
    --danger:#ff4d4d;
    --shadow:rgba(0,0,0,.6);
}

body{
    margin:0;
    background:var(--bg);
    color:#fff;
    font-family:Poppins,sans-serif;
}

.page-container{
    margin-left:210px;
    margin-top:90px;
    padding:30px;
    width:calc(100% - 240px);
}

.card{
    background:var(--panel);
    padding:20px;
    border-radius:12px;
    box-shadow:0 8px 30px var(--shadow);
    max-width:520px;
}

label{
    display:block;
    margin-top:12px;
    color:var(--muted);
    font-size:14px;
}

input, select{
    width:100%;
    padding:10px 12px;
    margin-top:6px;
    background:#0f0f0f;
    border:1px solid #222;
    color:#fff;
    border-radius:8px;
    box-sizing:border-box;
}

.btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:var(--accent);
    color:#000;
    padding:10px 16px;
    border-radius:8px;
    border:none;
    cursor:pointer;
    font-weight:600;
    margin-top:16px;
}

.btn.secondary{
    background:transparent;
    color:var(--muted);
    border:1px solid #2a2a2a;
    text-decoration:none;
}

@media (max-width:720px){
    .page-container{
        margin-left:0;
        margin-top:20px;
        padding:20px;
        width:100%;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
    <div class="card">

        <h2 style="color:var(--accent); margin-top:0;">Edit User</h2>

        <form method="POST">

            <label>Username</label>
            <input type="text" name="username" required
                   value="<?= htmlspecialchars($user['username']) ?>">

            <label>Full Name</label>
            <input type="text" name="full_name"
                   value="<?= htmlspecialchars($user['full_name']) ?>">

            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>">

            <label>Role</label>
            <select name="role_id" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"
                        <?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Status</label>
            <select name="is_active">
                <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Disabled</option>
            </select>

            <label>New Password <span class="small">(leave empty to keep current)</span></label>
            <input type="password" name="password" placeholder="••••••••">

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn">Update User</button>
                <a href="users.php" class="btn secondary" style="text-decoration:none; padding:10px 14px;"><i class="bi bi-arrow-left"></i>Back to users</a>
            </div>

        </form>

    </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
