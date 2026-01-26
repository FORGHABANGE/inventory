<?php
include 'auth_admin.php';

// users.php
require_once 'includes/db.php';

$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.is_active, r.name AS role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    ORDER BY u.username ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --accent:#00ff9d;
    --danger:#ff4d4d;
    --muted:#bdbdbd;
}

body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:var(--bg);
    color:#fff;
}

.page-container{
    margin-left:210px;
    margin-top:90px;
    padding:30px;
}

.card{
    background:var(--panel);
    border-radius:12px;
    padding:20px;
}

h2{
    margin-bottom:15px;
    color:var(--accent);
}

/* ✅ TABLE FIX */
table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

th, td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:left;
    vertical-align:middle;
}

th{
    color:var(--muted);
    font-weight:500;
}

.status-active{ color:var(--accent); }
.status-disabled{ color:var(--danger); }

.actions a{
    margin-right:12px;
    text-decoration:none;
    font-weight:500;
}

.edit{ color:var(--accent); }
.delete{ color:var(--danger); }

.btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:var(--accent);
    color:#000;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    margin-bottom:15px;
}

/* 📱 MOBILE ONLY */
@media (max-width:720px){
    .page-container{
        margin-left:0;
        margin-top:20px;
        padding:20px;
    }

    table, thead, tbody, th, td, tr{
        display:block;
        width:100%;
    }

    thead{ display:none; }

    tr{
        margin-bottom:15px;
        border:1px solid #222;
        border-radius:8px;
        padding:12px;
    }

    td{
        border:none;
        padding:6px 0;
    }

    td::before{
        content: attr(data-label);
        display:block;
        color:var(--muted);
        font-size:13px;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<h2><i class="bi bi-people"></i> Users</h2>

<a href="add_user.php" class="btn">
<i class="bi bi-plus-circle"></i> Add User
</a>

<table>
<thead>
<tr>
<th>Username</th>
<th>Full Name</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td data-label="Username"><?= htmlspecialchars($u['username']) ?></td>
<td data-label="Full Name"><?= htmlspecialchars($u['full_name']) ?></td>
<td data-label="Role"><?= htmlspecialchars($u['role_name']) ?></td>
<td data-label="Status" class="<?= $u['is_active'] ? 'status-active':'status-disabled' ?>">
<?= $u['is_active'] ? 'Active' : 'Disabled' ?>
</td>
<td data-label="Actions" class="actions">
<a class="edit" href="edit_user.php?id=<?= $u['id'] ?>"><i class="bi bi-pencil-square"></i>Edit</a>
<a class="delete" href="delete_user.php?id=<?= $u['id'] ?>"
onclick="return confirm('Delete this user?')"><i class="bi bi-trash"></i>Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
