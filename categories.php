<?php
include 'auth_admin.php';

// categories.php
require_once 'includes/db.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch all categories
$stmt = $pdo->query("SELECT id, name, description FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optional message
$error = '';
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Categories — Inventory</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
    --danger:#ff4d4d;
    --card-shadow:rgba(0,0,0,0.6);
}

body{
    margin:0;
    font-family:"Poppins",sans-serif;
    background:var(--bg);
    color:#fff;
}

.page-container{
    margin-left:210px;
    padding:30px;
    margin-top:90px;
    width:calc(100% - 240px);
}

.card{
    background:var(--panel);
    border-radius:12px;
    padding:18px;
    box-shadow:0 8px 30px var(--card-shadow);
    width:95%;
}

.card h2{
    color:var(--accent);
    margin:0 0 16px 0;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:14px;
}

th, td{
    padding:12px 10px;
    border-bottom:1px solid #222;
    text-align:left;
}

th{
    color:var(--muted);
    font-weight:500;
}

.actions a{
    text-decoration:none;
    margin-right:12px;
    font-size:14px;
}

.actions .edit{
    color:var(--accent);
}

.actions .delete{
    color:var(--danger);
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
    text-decoration:none;
}

.messages .error{
    background:rgba(255,77,77,0.12);
    color:var(--danger);
    padding:10px;
    border-radius:8px;
    margin-bottom:12px;
}

/* Mobile */
@media(max-width:720px){
    .page-container{
        margin-left:0;
        padding:20px;
        margin-top:20px;
    }

    table, thead, tbody, th, td, tr{
        display:block;
    }

    th{
        display:none;
    }

    tr{
        margin-bottom:14px;
        border:1px solid #222;
        border-radius:8px;
        padding:10px;
    }

    td{
        border:none;
        padding:6px 0;
    }

    .actions{
        margin-top:8px;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
    <div class="card">
        <h2><i class="bi bi-tags"></i> Categories</h2>

        <?php if ($error): ?>
            <div class="messages">
                <div class="error"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <a href="add_category.php" class="btn">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>

        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3">No categories found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td><?= htmlspecialchars($cat['description']) ?></td>
                            <td class="actions">
                                <a href="edit_category.php?id=<?= $cat['id'] ?>" class="edit">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="delete_category.php?id=<?= $cat['id'] ?>"
                                   class="delete"
                                   onclick="return confirm('Are you sure you want to delete this category?');">
                                   <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
