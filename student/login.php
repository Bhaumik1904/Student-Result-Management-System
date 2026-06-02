<?php
/**
 * Student Login Portal
 */
session_start();
require_once '../config/db.php';

// If already logged in as student, redirect to dashboard
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_number = trim($_POST['roll_number'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    if (!$roll_number || !$mobile) {
        $error = 'Both Roll Number and Mobile Number are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ? AND mobile = ?");
        $stmt->execute([$roll_number, $mobile]);
        $student = $stmt->fetch();

        if ($student) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['student_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Invalid Roll Number or Mobile Number. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Login &mdash; ResultPro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

  <div class="auth-card">
    <div class="auth-logo" style="justify-content:center;margin-bottom:24px;">
      <div class="auth-logo-icon" style="background:var(--accent);">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div>
        <div style="font-size:1.4rem;font-weight:700;line-height:1;">Student Portal</div>
        <div style="font-size:.75rem;color:var(--text-secondary);letter-spacing:.05em;text-transform:uppercase;margin-top:4px;">ResultPro</div>
      </div>
    </div>

    <div style="text-align:center;margin-bottom:32px;">
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle" style="margin-bottom:0;">Enter your credentials to view your result.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:24px;border-radius:var(--radius-md);display:flex;gap:12px;align-items:flex-start;">
        <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i>
        <span style="font-size:.875rem;"><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label" for="roll_number">Roll Number</label>
        <input type="text" id="roll_number" name="roll_number" class="form-control" placeholder="e.g. CSE2024001" required autocomplete="username">
      </div>
      
      <div class="form-group" style="margin-bottom:32px;">
        <label class="form-label" for="mobile">Mobile Number (PIN)</label>
        <input type="password" id="mobile" name="mobile" class="form-control" placeholder="10-digit mobile number" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;height:48px;font-size:1rem;border-radius:var(--radius-md);">
        Access Portal
      </button>
    </form>
    
    <div class="auth-divider" style="margin-top:32px;margin-bottom:24px;">Staff Access</div>
    
    <div style="text-align:center;">
      <a href="../auth/login.php" style="font-size:.875rem;font-weight:600;color:var(--text-secondary);">
        <i class="fa-solid fa-shield-halved" style="margin-right:6px;"></i> Are you an Administrator? Login here.
      </a>
    </div>

  </div>

</body>
</html>
