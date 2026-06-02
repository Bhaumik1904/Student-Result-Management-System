<?php
/**
 * Student Dashboard
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Enforce student login
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION['student_id'];
$result = getStudentResult($pdo, $studentId);

if (!$result) {
    // Edge case if student was deleted while logged in
    session_destroy();
    header("Location: login.php");
    exit;
}

$s = $result['student'];
$sum = $result['summary'];
if ($sum) {
    $statusClass = $sum['status'] === 'PASS' ? 'pass' : 'fail';
    $gradeClass  = 'grade-' . strtolower($sum['grade']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Dashboard &mdash; ResultPro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .student-navbar {
      background: var(--black);
      padding: 16px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #fff;
    }
    .student-body {
      background: #f4f4f5;
      min-height: 100vh;
    }
    .student-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 24px;
    }
    
    @media print {
        .student-body { background: #fff; }
        .student-container { margin: 0 auto; padding: 0; }
    }
  </style>
</head>
<body class="student-body">

<nav class="student-navbar no-print">
  <div class="d-flex align-center gap-12">
    <div style="width:36px;height:36px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center;">
      <i class="fa-solid fa-graduation-cap" style="color:#fff;"></i>
    </div>
    <div>
      <div style="font-size:1rem;font-weight:700;letter-spacing:-.01em;">ResultPro</div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.05em;">Student Portal</div>
    </div>
  </div>
  <div class="d-flex align-center gap-16">
    <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.1);padding:6px 12px 6px 6px;border-radius:99px;">
      <div style="width:28px;height:28px;background:#fff;color:var(--black);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;">
        <?= strtoupper(substr($s['student_name'], 0, 1)) ?>
      </div>
      <span style="font-size:.85rem;font-weight:500;"><?= htmlspecialchars($s['student_name']) ?></span>
    </div>
    <a href="logout.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.2);padding:8px 16px;">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
    </a>
  </div>
</nav>

<div class="student-container">
  
  <?php if (empty($result['marks']) || !$sum): ?>
    <div class="empty-state card">
      <div class="empty-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
      <h3>Results Pending</h3>
      <p>Your marks have not been published yet. Please check back later.</p>
    </div>
  <?php else: ?>
    
    <div class="result-card-wrap">
      <!-- Render the partial card -->
      <?php 
        $hideFullResultButton = true;
        include '../results/result-card-partial.php'; 
      ?>
    </div>
    
    <div class="d-flex justify-center mt-24 no-print">
      <button onclick="window.print()" class="btn btn-primary btn-lg" style="border-radius:99px;padding:12px 32px;box-shadow:0 10px 25px rgba(0,0,0,.15);">
        <i class="fa-solid fa-print"></i> Download / Print Transcript
      </button>
    </div>

  <?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>
</html>
