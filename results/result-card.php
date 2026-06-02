<?php
/**
 * Result Card — view by student ID or roll number
 * Also serves as printable transcript
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$studentId = (int)($_GET['id'] ?? 0);

// Also support roll_number lookup
if (!$studentId && isset($_GET['roll'])) {
    $st = $pdo->prepare("SELECT id FROM students WHERE roll_number = ?");
    $st->execute([strtoupper(trim($_GET['roll']))]);
    $row = $st->fetch();
    if ($row) $studentId = (int)$row['id'];
}

$result = $studentId ? getStudentResult($pdo, $studentId) : null;

$pageTitle = 'Result Card';
$activeNav = 'result-card';

// All students for the dropdown
$students = $pdo->query("SELECT id, roll_number, student_name, department FROM students ORDER BY roll_number")->fetchAll();

$topbarAction = $result ? '
  <button onclick="window.print()" class="topbar-btn secondary no-print">
    <i class="fa-solid fa-print fa-xs"></i> Print Transcript
  </button>' : '';

require_once '../includes/header.php';
?>

<!-- Student Selector -->
<div class="card mb-24 no-print" style="max-width:820px;margin:0 auto 24px;">
  <div class="card-body" style="padding:18px 24px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
      <div style="flex:1;">
        <label class="form-label mb-8" style="margin-bottom:6px;">Select Student</label>
        <select name="id" class="form-control select2" style="width:100%;">
          <option value="">Choose a student…</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $s['id'] === $studentId ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['roll_number']) ?> — <?= htmlspecialchars($s['student_name']) ?> (<?= $s['department'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="height:40px;">
        <i class="fa-solid fa-id-card fa-sm"></i> View Result
      </button>
    </form>
  </div>
</div>

<?php if (!$studentId || !$result): ?>
<!-- Empty State -->
<div class="empty-state no-print" style="max-width:820px;margin:0 auto;">
  <div class="empty-icon"><i class="fa-solid fa-id-card"></i></div>
  <h3>Select a Student</h3>
  <p>Choose a student from the dropdown above to view their complete result card and academic transcript.</p>
</div>

<?php elseif (empty($result['marks'])): ?>
<div class="alert alert-warning no-print" style="max-width:820px;margin:0 auto;">
  <i class="fa-solid fa-triangle-exclamation"></i>
  No marks have been entered for <strong><?= htmlspecialchars($result['student']['student_name']) ?></strong> yet.
  <a href="../marks/add.php" style="font-weight:600;color:inherit;text-decoration:underline;">Add Marks</a>
</div>

<?php else:
    $s   = $result['student'];
    $sum = $result['summary'];
    $statusClass = $sum['status'] === 'PASS' ? 'pass' : 'fail';
    $gradeClass  = 'grade-' . strtolower($sum['grade']);
?>

<!-- Result Card -->
<div class="result-card-wrap">
  <div class="result-card">

    <!-- Header -->
    <div class="result-card-header">
      <div>
        <div class="result-card-school">ResultPro Academic Management System</div>
        <div class="result-card-title">Official Academic Transcript</div>
        <div class="result-card-sub">
          Examination Report &mdash; <?= date('F Y') ?>
        </div>
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
          <div class="info-field-label">Email</div>
          <div class="info-field-value" style="font-size:.8rem;"><?= htmlspecialchars($s['email']) ?></div>
        </div>
      </div>
    </div>

    <!-- Marks Table -->
    <div class="result-card-marks">
      <table class="table marks-table" style="margin-bottom:0;">
        <thead>
          <tr>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th style="text-align:center;">Max Marks</th>
            <th style="text-align:center;">Marks Obtained</th>
            <th style="text-align:center;">Percentage</th>
            <th style="text-align:center;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result['marks'] as $m):
            $subPct  = round(($m['marks_obtained'] / $m['max_marks']) * 100, 1);
            $subPass = (float)$m['marks_obtained'] >= 35;
          ?>
          <tr>
            <td style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($m['subject_code']) ?></td>
            <td style="font-weight:500;"><?= htmlspecialchars($m['subject_name']) ?></td>
            <td style="text-align:center;color:var(--text-secondary);"><?= $m['max_marks'] ?></td>
            <td style="text-align:center;font-weight:700;font-size:1rem;"><?= (float)$m['marks_obtained'] ?></td>
            <td style="text-align:center;"><?= $subPct ?>%</td>
            <td style="text-align:center;">
              <?php if ($subPass): ?>
                <span class="badge badge-green">Pass</span>
              <?php else: ?>
                <span class="badge badge-red">Fail</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background:var(--surface);">
            <td colspan="2" style="font-weight:700;font-size:.9rem;padding:14px 16px;">TOTAL</td>
            <td style="text-align:center;font-weight:700;"><?= $sum['total_max'] ?></td>
            <td style="text-align:center;font-weight:700;font-size:1.05rem;"><?= $sum['total_obtained'] ?></td>
            <td style="text-align:center;font-weight:700;"><?= $sum['percentage'] ?>%</td>
            <td style="text-align:center;">
              <span class="badge <?= $sum['status'] === 'PASS' ? 'badge-green' : 'badge-red' ?>" style="font-weight:700;">
                <?= $sum['status'] ?>
              </span>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Summary -->
    <div class="result-card-summary">
      <div class="summary-grid">
        <div>
          <div class="summary-item-label">Total Marks</div>
          <div class="summary-item-value"><?= $sum['total_obtained'] ?><span style="font-size:.9rem;font-weight:400;color:var(--text-secondary);">/<?= $sum['total_max'] ?></span></div>
        </div>
        <div>
          <div class="summary-item-label">Percentage</div>
          <div class="summary-item-value"><?= $sum['percentage'] ?>%</div>
        </div>
        <div>
          <div class="summary-item-label">Grade</div>
          <div class="summary-item-value">
            <span class="badge <?= $gradeClass ?>" style="font-size:1.2rem;padding:6px 14px;">
              <?= $sum['grade'] ?>
            </span>
          </div>
        </div>
        <div>
          <div class="summary-item-label">Grade Description</div>
          <div class="summary-item-value" style="font-size:1rem;font-weight:600;color:var(--text-secondary);">
            <?= $sum['grade_label'] ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="result-card-footer">
      <div>Generated on <?= date('d F Y, h:i A') ?> &mdash; ResultPro v1.0.0</div>
      <div>Pass Mark Per Subject: 35 &mdash; This is a computer-generated document.</div>
    </div>

  </div><!-- /.result-card -->

  <!-- Print Button (below card, no-print) -->
  <div class="d-flex gap-12 mt-20 no-print" style="justify-content:center;">
    <button onclick="window.print()" class="btn btn-primary btn-lg">
      <i class="fa-solid fa-print"></i> Print / Save as PDF
    </button>
    <a href="search.php" class="btn btn-secondary btn-lg">
      <i class="fa-solid fa-magnifying-glass"></i> Search Another
    </a>
  </div>

</div><!-- /.result-card-wrap -->
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
