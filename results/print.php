<?php
/**
 * Print Result — Standalone printable page (no sidebar/topbar)
 * Access via: print.php?id=<student_id>
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$studentId = (int)($_GET['id'] ?? 0);
if (!$studentId) { header('Location: result-card.php'); exit; }

$result = getStudentResult($pdo, $studentId);
if (!$result || empty($result['marks'])) { header('Location: result-card.php?id=' . $studentId); exit; }

$s   = $result['student'];
$sum = $result['summary'];
$statusClass = $sum['status'] === 'PASS' ? 'pass' : 'fail';
$gradeClass  = 'grade-' . strtolower($sum['grade']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Result — <?= htmlspecialchars($s['roll_number']) ?> — ResultPro</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { background: #fff; margin: 0; padding: 0; }
    @media screen { .result-card-wrap { max-width: 820px; margin: 32px auto; padding: 0 24px; } }
    @media print  { .result-card-wrap { margin: 0; padding: 0; } .no-print { display: none; } }
  </style>
</head>
<body>

<div class="result-card-wrap">

  <!-- Print Controls -->
  <div class="no-print d-flex gap-12 mb-20" style="justify-content:flex-end;">
    <button onclick="window.print()" class="btn btn-primary">
      <i class="fa-solid fa-print"></i> Print / Save PDF
    </button>
    <button onclick="window.history.back()" class="btn btn-secondary">
      <i class="fa-solid fa-arrow-left"></i> Back
    </button>
  </div>

  <div class="result-card">
    <!-- Header -->
    <div class="result-card-header">
      <div>
        <div class="result-card-school">ResultPro Academic Management System</div>
        <div class="result-card-title">Official Academic Transcript</div>
        <div class="result-card-sub">Examination Report &mdash; <?= date('F Y') ?></div>
      </div>
      <span class="result-status-badge <?= $statusClass ?>">
        <?= $sum['status'] === 'PASS' ? '✓ PASS' : '✗ FAIL' ?>
      </span>
    </div>

    <!-- Student Info -->
    <div class="result-card-student">
      <div class="student-info-grid">
        <div>
          <div class="info-field-label">Roll Number</div>
          <div class="info-field-value" style="font-family:monospace;"><?= htmlspecialchars($s['roll_number']) ?></div>
        </div>
        <div>
          <div class="info-field-label">Student Name</div>
          <div class="info-field-value"><?= htmlspecialchars($s['student_name']) ?></div>
        </div>
        <div>
          <div class="info-field-label">Department</div>
          <div class="info-field-value"><?= htmlspecialchars($s['department']) ?></div>
        </div>
        <div>
          <div class="info-field-label">Mobile</div>
          <div class="info-field-value"><?= htmlspecialchars($s['mobile']) ?></div>
        </div>
      </div>
    </div>

    <!-- Marks Table -->
    <div class="result-card-marks">
      <table class="table marks-table" style="margin-bottom:0;">
        <thead>
          <tr>
            <th>Code</th>
            <th>Subject</th>
            <th style="text-align:center;">Max</th>
            <th style="text-align:center;">Obtained</th>
            <th style="text-align:center;">%</th>
            <th style="text-align:center;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result['marks'] as $m):
            $sp   = round(($m['marks_obtained']/$m['max_marks'])*100,1);
            $sOk  = (float)$m['marks_obtained'] >= 35;
          ?>
          <tr>
            <td style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($m['subject_code']) ?></td>
            <td style="font-weight:500;"><?= htmlspecialchars($m['subject_name']) ?></td>
            <td style="text-align:center;"><?= $m['max_marks'] ?></td>
            <td style="text-align:center;font-weight:700;"><?= (float)$m['marks_obtained'] ?></td>
            <td style="text-align:center;"><?= $sp ?>%</td>
            <td style="text-align:center;"><span class="badge <?= $sOk ? 'badge-green' : 'badge-red' ?>"><?= $sOk ? 'Pass' : 'Fail' ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background:var(--surface);">
            <td colspan="2" style="font-weight:700;">TOTAL</td>
            <td style="text-align:center;font-weight:700;"><?= $sum['total_max'] ?></td>
            <td style="text-align:center;font-weight:700;"><?= $sum['total_obtained'] ?></td>
            <td style="text-align:center;font-weight:700;"><?= $sum['percentage'] ?>%</td>
            <td style="text-align:center;"><span class="badge <?= $sum['status']==='PASS'?'badge-green':'badge-red' ?>" style="font-weight:700;"><?= $sum['status'] ?></span></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Summary -->
    <div class="result-card-summary">
      <div class="summary-grid">
        <div><div class="summary-item-label">Total</div><div class="summary-item-value"><?= $sum['total_obtained'] ?><span style="font-size:.85rem;font-weight:400;color:var(--text-secondary);">/<?= $sum['total_max'] ?></span></div></div>
        <div><div class="summary-item-label">Percentage</div><div class="summary-item-value"><?= $sum['percentage'] ?>%</div></div>
        <div><div class="summary-item-label">Grade</div><div class="summary-item-value"><span class="badge <?= $gradeClass ?>" style="font-size:1.1rem;padding:5px 14px;"><?= $sum['grade'] ?></span></div></div>
        <div><div class="summary-item-label">Result</div><div class="summary-item-value" style="font-size:1.1rem;color:<?= $sum['status']==='PASS'?'var(--success)':'var(--danger)' ?>;"><?= $sum['grade_label'] ?></div></div>
      </div>
    </div>

    <!-- Footer -->
    <div class="result-card-footer">
      <div>Generated: <?= date('d F Y, h:i A') ?> &mdash; ResultPro v1.0.0</div>
      <div>Pass Mark/Subject: 35 &mdash; Computer-generated document.</div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
  // Auto-trigger print if ?print=1
  if (new URLSearchParams(location.search).get('print') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
  }
</script>
</body>
</html>
