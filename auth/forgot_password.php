<?php
require_once '../includes/db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // For now, display the link (you can later replace this with an email send function)
        $reset_link = "http://localhost/inventory/auth/reset_password.php?token=$token";
        $message = "Password reset link: <br><a href='$reset_link' target='_blank'>$reset_link</a>";
    } else {
        $message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - Inventory System</title>
<link rel="stylesheet" href="../assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body {
  background: #0e0e0e;
  color: #f1f1f1;
  font-family: 'Poppins', sans-serif;
}
.card {
  background: #1b1b1b;
  border: none;
  border-radius: 12px;
  box-shadow: 0 0 15px rgba(0,255,128,0.1);
}
.btn-custom {
  background: #00ff9d;
  color: #000;
  font-weight: 600;
  transition: 0.3s;
}
.btn-custom:hover {
  background: #00cc7d;
}
a { color: #00ff9d; text-decoration: none; }
a:hover { text-decoration: underline; }
.form-label{
  color:white;
}
h3{
  color:#00ff9d;
}
/* Hide browser’s built-in password reveal icon (Chrome/Edge) */
input::-ms-reveal,
input::-ms-clear {
  display: none;
}

input[type="password"]::-webkit-credentials-auto-fill-button,
input[type="password"]::-webkit-textfield-decoration-container,
input[type="password"]::-webkit-clear-button,
input[type="password"]::-webkit-inner-spin-button {
  display: none !important;
  appearance: none;
}

</style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
  <div class="card p-4" style="width:400px;">
    <div class="text-center mb-3">
      <div style="width:80px;height:80px;border-radius:50%;background:#00ff9d20;display:flex;justify-content:center;align-items:center;margin:auto;">
        📦
      </div>
      <h3>Forgot Password</h3>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-info text-center"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label ">Enter your email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <button type="submit" class="btn btn-custom w-100">Send Reset Link</button>
    </form>

    <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
  </div>
</div>
</body>
</html>
