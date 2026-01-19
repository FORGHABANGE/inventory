<?php
require_once 'includes/db.php';

$id = $_GET['id'];

$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

header("Location: users.php");
exit;
