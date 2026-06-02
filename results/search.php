<?php
/**
 * Result Search — AJAX-powered
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Handle AJAX search POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $roll = strtoupper(trim($_POST['roll_number'] ?? ''));
    if (!$roll) { echo '<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i> Please enter a Roll Number.</div>'; exit; }

    $stmt = $pdo->prepare("SELECT id FROM students WHERE roll_number = ?");
    $stmt->execute([$roll]);
    $student = $stmt->fetch();

    if (!$student) {
        echo '
        <div style="text-align:center;padding:48px 24px;">
          <div style="font-size:3rem;margin-bottom:16px;color:var(--text-tertiary);">🔍</div>
          <h3 style="font-size:1.1rem;font-weight:600;margin-bottom:8px;">No Student Found</h3>
          <p style="color:var(--text-secondary);font-size:.875rem;">No student with Roll Number <strong>' . htmlspecialchars($roll) . '</strong> was found.</p>
        </div>';
        exit;
    }

    $result = getStudentResult($pdo, (int)$student['id']);
    if (!$result || empty($result['marks'])) {
        echo '
        <div style="text-align:center;padding:48px 24px;">
          <div style="font-size:3rem;margin-bottom:16px;color:var(--text-tertiary);">📋</div>
          <h3 style="font-size:1.1rem;font-weight:600;margin-bottom:8px;">No Marks Entered</h3>
          <p style="color:var(--text-secondary);font-size:.875rem;">Student found but no marks have been entered yet.</p>
        </div>';
        exit;
    }

    $s   = $result['student'];
    $sum = $result['summary'];
    $statusClass = $sum['status'] === 'PASS' ? 'pass' : 'fail';
    $gradeClass  = 'grade-' . strtolower($sum['grade']);

    // Render result card inline
    ob_start();
    include __DIR__ . '/result-card-partial.php';
    echo ob_get_clean();
    exit;
}

$pageTitle = 'Search Result';
$activeNav = 'search';
require_once '../includes/header.php';
?>

<!-- Search Hero -->
<div class="search-hero">
  <div style="display:inline-flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:99px;padding:6px 14px;font-size:.78rem;color:var(--text-secondary);margin-bottom:20px;font-weight:500;">
    <i class="fa-solid fa-bolt" style="color:var(--warning);"></i> Instant Result Lookup
  </div>
  <h1>Find Student Result</h1>
  <p>Enter the student's roll number to instantly retrieve their complete academic result and transcript.</p>

  <div class="search-input-wrap">
    <form id="result-search-form" style="display:flex;gap:12px;width:100%;">
      <input type="text" id="search-roll" class="form-control"
        placeholder="Enter Roll Number (e.g. CSE2024001)"
        style="font-size:1rem;padding:14px 18px;border-radius:var(--radius-md);text-transform:uppercase;"
        autocomplete="off">
      <button type="submit" class="btn btn-primary btn-lg" style="white-space:nowrap;border-radius:var(--radius-md);">
        <i class="fa-solid fa-magnifying-glass"></i> Search
      </button>
    </form>
  </div>
</div>

<!-- Spinner -->
<div id="search-spinner" style="display:none;justify-content:center;padding:40px;">
  <div style="display:flex;flex-direction:column;align-items:center;gap:16px;color:var(--text-secondary);">
    <div class="spinner" style="width:32px;height:32px;border-width:3px;"></div>
    <span style="font-size:.875rem;">Fetching result…</span>
  </div>
</div>

<!-- Result Output -->
<div id="result-output" class="result-card-wrap" style="max-width:900px;margin:0 auto;overflow-x:auto;"></div>

<script>
$(function() {
  const form   = document.getElementById('result-search-form');
  const input  = document.getElementById('search-roll');
  const output = document.getElementById('result-output');
  const spin   = document.getElementById('search-spinner');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const roll = input.value.trim();
    if (!roll) { toastr.warning('Please enter a Roll Number.'); return; }

    output.innerHTML = '';
    spin.style.display = 'flex';

    $.post(window.location.href, { roll_number: roll, ajax: 1 }, function(html) {
      spin.style.display = 'none';
      output.innerHTML = html;
    }).fail(function() {
      spin.style.display = 'none';
      toastr.error('Search failed. Please try again.');
    });
  });

  // Allow pressing Enter
  input.addEventListener('keydown', e => { if (e.key === 'Enter') form.dispatchEvent(new Event('submit')); });
});
</script>

<?php require_once '../includes/footer.php'; ?>
