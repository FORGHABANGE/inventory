<?php
require_once '../includes/db.php';
session_start();
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role_name = $_POST['role'];

    try {
        // Get role_id based on role name
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$role_name]);
        $role = $stmt->fetch();

        if (!$role) {
            $message = "Invalid role selected.";
        } else {
            $role_id = $role['id'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (role_id, username, email, password_hash, full_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$role_id, $username, $email, $hashed_password, $full_name]);

            $message = " Account created successfully! You can now login.";
        }
    } catch (PDOException $e) {
        $message = " Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Inventory System</title>
 <link rel="stylesheet" href="../assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    body {
      background: #0e0e0e;
      color: #f1f1f1;
      font-family: 'Poppins', sans-serif;
    }
    .login-box {
     
      padding: 40px 30px;
      border-radius: 15px;
      width: 100%;
      max-width: 420px;
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
    .card {
      background: #1b1b1b;
      border: none;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,255,128,0.1);
      color: #f1f1f1;
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
    a {
      color: #00ff9d;
      text-decoration: none;
    }
    a:hover { text-decoration: underline; }
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
<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
  <div class="card p-4" style="width: 400px;">
   <div class="login-box">
    <div class="logo-circle">
        <!-- Logo placeholder for your cube/carton image -->
        📦
    </div>
    <h3>Inventory System</h3>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-info text-center py-2"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-2">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" name="full_name" required >
      </div>
      <div class="mb-2">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" id="username" required autocomplete="off">
      </div>
      <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
      </div>

      <div class="mb-3 position-relative">
  <label for="password" class="form-label">Password</label>
  <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
  <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
</div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
          <option value="">Select Role</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff</option>
        </select>
      </div>
      <button type="submit" class="btn btn-custom w-100">Sign Up</button>
    </form>

    <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
  </div>
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
