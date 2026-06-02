<?php
/**
 * Login Page
 * Student Result Management System
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

require_once '../config/db.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ../dashboard/index.php');
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ResultPro Admin Login — Secure access to Student Result Management System">
  <title>Sign In — ResultPro</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="auth-logo-icon">R</div>
      <div>
        <div style="font-size:1.1rem;font-weight:700;letter-spacing:-.02em;">ResultPro</div>
        <div style="font-size:.72rem;color:var(--text-secondary);">Academic Management</div>
      </div>
    </div>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your administrator account to continue.</p>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-24" id="login-error">
      <i class="fa-solid fa-circle-exclamation"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" id="login-form" novalidate>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          placeholder="Enter your username"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          autocomplete="username"
          required
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            autocomplete="current-password"
            style="padding-right:42px;"
            required
          >
          <button type="button" id="toggle-password"
            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-tertiary);padding:0;">
            <i class="fa-solid fa-eye fa-sm" id="eye-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg mt-8" id="login-btn">
        <span id="login-btn-text">Sign In</span>
        <span id="login-spinner" class="spinner" style="display:none;width:16px;height:16px;border-width:2px;border-color:rgba(255,255,255,.3);border-top-color:#fff;"></span>
      </button>
    </form>

    <div class="auth-divider">Default Credentials</div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px 16px;display:flex;justify-content:space-between;font-size:.8rem;">
      <div><span style="color:var(--text-secondary);">Username:</span> <strong>admin</strong></div>
      <div><span style="color:var(--text-secondary);">Password:</span> <strong>admin123</strong></div>
    </div>

    <p class="text-center text-sm mt-20" style="color:var(--text-tertiary);">
      ResultPro v1.0.0 &mdash; PHP Full Stack Internship Assessment
    </p>
  </div>
</div>

<script>
  // Toggle password visibility
  document.getElementById('toggle-password').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pwd.type === 'password') {
      pwd.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      pwd.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  });

  // Loading state on submit
  document.getElementById('login-form').addEventListener('submit', function() {
    document.getElementById('login-btn-text').style.display = 'none';
    document.getElementById('login-spinner').style.display = 'inline-block';
    document.getElementById('login-btn').disabled = true;
  });
</script>
</body>
</html>
