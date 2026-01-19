<?php
require_once '../includes/db.php';
$message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = " Invalid or expired reset link.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = trim($_POST['password']);
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);
        $message = "Password has been reset successfully. <a href='login.php'>Login now</a>";
    }
} else {
    $message = " No reset token found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - Inventory System</title>
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
.position-relative { position: relative; }
.toggle-password {
  position: absolute;
  top: 70%;
  right: 15px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #00ff9d;
}
h3{
  color:#00ff9d;
}
.form-label{
  color: white;
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
        🔒
      </div>
       <h3>Reset Password</h3>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-info text-center"><?= $message ?></div>
    <?php endif; ?>

    <?php if (isset($user) && $user): ?>
    <form method="POST">
      <div class="mb-3 position-relative">
        <label class="form-label">New Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
      </div>
      <button type="submit" class="btn btn-custom w-100">Update Password</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

if(togglePassword){
  togglePassword.addEventListener('click', function(){
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
  });
}
</script>
</body>
</html>
