<?php
/**
 * Dashboard — Main Overview
 * Student Result Management System
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$stats   = getDashboardStats($pdo);
$toppers = getToppers($pdo, 5);
$recent  = getRecentStudents($pdo, 5);
$deptDist = getDepartmentDistribution($pdo);

$deptLabels = array_column($deptDist, 'department');
$deptData   = array_column($deptDist, 'count');

$topbarAction = '<a href="../students/add.php" class="topbar-btn primary"><i class="fa-solid fa-plus fa-xs"></i> Add Student</a>';
require_once '../includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
    <div>
      <div class="stat-value"><?= $stats['total_students'] ?></div>
      <div class="stat-label">Total Students</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa-solid fa-book-open"></i></div>
    <div>
      <div class="stat-value"><?= $stats['total_subjects'] ?></div>
      <div class="stat-label">Total Subjects</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div>
      <div class="stat-value"><?= $stats['pass_students'] ?></div>
      <div class="stat-label">Pass Students</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
    <div>
      <div class="stat-value"><?= $stats['fail_students'] ?></div>
      <div class="stat-label">Fail Students</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-solid fa-chart-line"></i></div>
    <div>
      <div class="stat-value"><?= $stats['avg_percentage'] ?>%</div>
      <div class="stat-label">Average Score</div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="charts-grid mb-28">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Pass vs Fail</div>
      <span class="badge badge-gray">All Students</span>
    </div>
    <div class="card-body" style="display:flex;align-items:center;justify-content:center;">
      <div class="chart-container" style="height:240px;width:100%;">
        <canvas id="passFailChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Department Distribution</div>
      <span class="badge badge-gray">By Branch</span>
    </div>
    <div class="card-body">
      <div class="chart-container" style="height:240px;width:100%;">
        <canvas id="deptChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Toppers + Recent Students -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Toppers -->
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-trophy" style="color:#B45309;margin-right:8px;"></i>Top Performers</div>
      <a href="../results/search.php" class="btn btn-outline btn-sm">View Results</a>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($toppers)): ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-trophy"></i></div>
          <h3>No Results Yet</h3>
          <p>Add marks to see top performers here.</p>
        </div>
      <?php else: ?>
        <table class="table" style="border-radius:0;">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student</th>
              <th>Dept</th>
              <th>Score</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($toppers as $i => $t): ?>
            <tr>
              <td>
                <div class="rank-badge <?= $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) ?>">
                  <?= $i + 1 ?>
                </div>
              </td>
              <td>
                <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($t['student_name']) ?></div>
                <div style="font-size:.72rem;color:var(--text-secondary);"><?= htmlspecialchars($t['roll_number']) ?></div>
              </td>
              <td><span class="badge badge-blue"><?= $t['department'] ?></span></td>
              <td>
                <div style="font-weight:700;font-size:.9rem;"><?= $t['percentage'] ?>%</div>
                <div class="progress-bar-wrap mt-8" style="width:80px;">
                  <div class="progress-bar-fill" style="width:<?= $t['percentage'] ?>%;background:<?= $t['percentage'] >= 80 ? 'var(--success)' : ($t['percentage'] >= 60 ? 'var(--accent)' : 'var(--warning)') ?>;"></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Students -->
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--accent);margin-right:8px;"></i>Recently Added</div>
      <a href="../students/view.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($recent)): ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
          <h3>No Students</h3>
          <p>Add your first student to get started.</p>
          <a href="../students/add.php" class="btn btn-primary btn-sm">Add Student</a>
        </div>
      <?php else: ?>
        <table class="table" style="border-radius:0;">
          <thead>
            <tr><th>Roll No.</th><th>Name</th><th>Dept</th><th>Added</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $s): ?>
            <tr>
              <td style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($s['roll_number']) ?></td>
              <td style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($s['student_name']) ?></td>
              <td><span class="badge badge-gray"><?= $s['department'] ?></span></td>
              <td style="font-size:.75rem;color:var(--text-secondary);"><?= date('M d', strtotime($s['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  if (typeof initDashboardCharts === 'function') {
    initDashboardCharts(
      <?= (int)$stats['pass_students'] ?>,
      <?= (int)$stats['fail_students'] ?>,
      <?= json_encode($deptLabels) ?>,
      <?= json_encode(array_map('intval', $deptData)) ?>
    );
  }
});
</script>

<?php require_once '../includes/footer.php'; ?>
