<?php
/**
 * Partial Result Card
 * Included by search.php for AJAX response
 */
if (!isset($s, $sum, $statusClass, $gradeClass, $result)) {
    exit;
}
?>
<div class="result-card" style="box-shadow:var(--shadow-md);margin-top:20px;">
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
</div>
<div class="d-flex gap-12 mt-20" style="justify-content:center;">
  <a href="../results/result-card.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-lg">
    <i class="fa-solid fa-id-card"></i> Open Full Result Card
  </a>
</div>
