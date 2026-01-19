<?php
require_once '../includes/db.php';
session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        // Store session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];

        // REDIRECTION LOGIC
        if ($user['role_id'] == 1) {
            // Admin
            header("Location: ../admin_dashboard.php");
            exit();
        } elseif ($user['role_id'] == 2) {
            // Normal User
            header("Location: ../staff/user_dashboard.php");
            exit();
        } else {
            // If the role is unknown
            $message = "Your role is not recognized.";
        }

    } else {
        $message = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Inventory System</title>
 <link rel="stylesheet" href="../assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    body {
      background: #0d0d0d;
      color: #f1f1f1;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      background: #1a1a1a;
      padding: 40px 30px;
      border-radius: 15px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 0 20px rgba(0,255,128,0.08);
    }

    .logo-circle {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background-color: #00ff9d20;
      margin: 0 auto 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #00ff9d;
      font-size: 30px;
    }

    h3 {
      text-align: center;
      font-weight: 600;
      color: #00ff9d;
      margin-bottom: 15px;
    }

    .form-label {
      margin-top: 10px;
      color: #ccc;
      font-weight: 500;
    }

    .btn-login {
      background-color: #00ff9d;
      color: #000;
      font-weight: 600;
      width: 100%;
      margin-top: 20px;
      border: none;
      transition: 0.3s;
    }

    .btn-login:hover {
      background-color: #00cc7d;
    }

    .extra-links {
      text-align: center;
      margin-top: 20px;
    }

    a {
      color: #00ff9d;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .alert {
      text-align: center;
      padding: 8px;
      font-size: 0.9rem;
    }
    .btn-outline-secondary {
  background: #1b1b1b;
  color: #00ff9d;
  border: 1px solid #00ff9d50;
}
/*.btn-outline-secondary:hover {
  background: #00ff9d;
  color: #000;
}
*/
.position-relative {
  position: relative;
}

.toggle-password {
  position: absolute;
  top: 70%;
  right: 15px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #333;
  font-size: 1.2rem;
}

.toggle-password:hover {
  color: #00ff9d;
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

<div class="login-box">
    <div class="logo-circle">
        <!-- Logo placeholder for your cube/carton image -->
        📦
    </div>
    <h3>Inventory System</h3>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required autocomplete="off">
        </div>

       <div class="mb-3 position-relative">
  <label for="password" class="form-label">Password</label>
  <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
  <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
</div>


        <div class="text-end mb-2">
            <a href="forgot_password.php">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-login">Login</button>

        <div class="extra-links">
            <p class="mt-3">Don’t have an account yet? <a href="register.php">Sign up for free</a></p>
        </div>
    </form>
</div>
<script>
  const togglePassword = document.querySelector('#togglePassword');
  const password = document.querySelector('#password');

  togglePassword.addEventListener('click', function() {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
  });
</script>
</body>
</html>
