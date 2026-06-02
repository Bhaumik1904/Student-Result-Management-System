<?php
/**
 * View / List Marks (Card Layout)
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Marks';
$activeNav = 'marks';

$successMsg = '';
if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        'added'   => 'Marks added successfully.',
        'updated' => 'Marks updated successfully.',
        'deleted' => 'Marks record deleted successfully.',
        default   => '',
    };
}

// AJAX delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM marks WHERE id = ?")->execute([$id]);
        jsonResponse(true, 'Marks record deleted successfully.');
    }
    jsonResponse(false, 'Invalid request.');
}

// Fetch all marks
$marks = $pdo->query("
    SELECT m.id, m.marks_obtained, m.updated_at,
           s.id as student_id, s.roll_number, s.student_name, s.department,
           sub.subject_code, sub.subject_name, sub.max_marks
    FROM marks m
    JOIN students s   ON m.student_id  = s.id
    JOIN subjects sub ON m.subject_id  = sub.id
    ORDER BY s.roll_number, sub.subject_code
")->fetchAll();

// Group by student
$grouped = [];
foreach ($marks as $m) {
    $roll = $m['roll_number'];
    if (!isset($grouped[$roll])) {
        $grouped[$roll] = [
            'student_id'   => $m['student_id'],
            'student_name' => $m['student_name'],
            'roll_number'  => $m['roll_number'],
            'department'   => $m['department'],
            'marks'        => []
        ];
    }
    $grouped[$roll]['marks'][] = $m;
}

$topbarAction = '<a href="add.php" class="topbar-btn primary"><i class="fa-solid fa-plus fa-xs"></i> Add Marks</a>';
require_once '../includes/header.php';
?>

<?php if ($successMsg): ?>
<div class="alert alert-success flash-message mb-20">
  <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div class="d-flex align-center justify-between mb-24">
  <div>
    <h2 style="font-size:1.3rem;letter-spacing:-.02em;">Marks Overview</h2>
    <p style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;">Grouped by Student &mdash; <?= count($grouped) ?> students graded</p>
  </div>
  
  <div class="search-bar" style="width:280px;background:var(--bg);">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="cardSearch" placeholder="Search by name or roll no...">
  </div>
</div>

<?php if (empty($grouped)): ?>
  <div class="empty-state card">
    <div class="empty-icon"><i class="fa-solid fa-pen-to-square"></i></div>
    <h3>No Marks Entered</h3>
    <p>Start by entering marks for students.</p>
    <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus fa-sm"></i> Add Marks</a>
  </div>
<?php else: ?>
  <div class="student-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(340px, 1fr));gap:24px;">
    <?php foreach ($grouped as $roll => $group): ?>
      <div class="card student-mark-card" data-search="<?= strtolower(htmlspecialchars($group['student_name'] . ' ' . $group['roll_number'])) ?>">
        
        <!-- Card Header -->
        <div class="card-header" style="flex-direction:column;align-items:flex-start;gap:8px;padding:24px;border-bottom:1px solid var(--border-light);">
          <div class="d-flex align-center justify-between w-100">
            <div style="font-weight:700;font-size:1.1rem;letter-spacing:-.01em;"><?= htmlspecialchars($group['student_name']) ?></div>
            <span class="badge badge-blue"><?= htmlspecialchars($group['department']) ?></span>
          </div>
          <div style="font-family:monospace;font-size:.85rem;color:var(--text-secondary);">
            <?= htmlspecialchars($group['roll_number']) ?>
          </div>
        </div>
        
        <!-- Card Body (Marks Table) -->
        <div class="card-body" style="padding:0;">
          <table class="table" style="margin:0;border-radius:0;">
            <thead>
              <tr>
                <th style="padding-left:24px;">Subject</th>
                <th style="text-align:center;">Score</th>
                <th style="text-align:right;padding-right:24px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($group['marks'] as $m): 
                  $pass = (float)$m['marks_obtained'] >= 35;
              ?>
              <tr>
                <td style="padding-left:24px;">
                  <div style="font-size:.85rem;font-weight:500;color:var(--text-primary);"><?= htmlspecialchars($m['subject_name']) ?></div>
                  <div style="font-size:.7rem;color:var(--text-tertiary);"><?= htmlspecialchars($m['subject_code']) ?></div>
                </td>
                <td style="text-align:center;">
                  <div style="font-weight:700;font-size:.95rem;color:<?= $pass ? 'var(--text-primary)' : 'var(--danger)' ?>;">
                    <?= (float)$m['marks_obtained'] ?>
                  </div>
                  <div style="font-size:.7rem;color:var(--text-secondary);">/ <?= $m['max_marks'] ?></div>
                </td>
                <td style="text-align:right;padding-right:24px;">
                  <div class="d-flex gap-8" style="justify-content:flex-end;">
                    <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-secondary btn-icon" style="width:28px;height:28px;padding:0;" title="Edit">
                      <i class="fa-solid fa-pen" style="font-size:.7rem;"></i>
                    </a>
                    <button class="btn btn-danger btn-icon btn-delete-mark" style="width:28px;height:28px;padding:0;" 
                      data-id="<?= $m['id'] ?>" 
                      data-name="<?= htmlspecialchars($group['student_name'] . ' — ' . $m['subject_name']) ?>" 
                      title="Delete">
                      <i class="fa-solid fa-trash" style="font-size:.7rem;"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <!-- Card Footer (Summary) -->
        <div class="card-footer" style="padding:16px 24px;display:flex;justify-content:space-between;align-items:center;background:var(--surface);border-top:1px solid var(--border-light);">
           <a href="../results/result-card.php?id=<?= $group['student_id'] ?>" class="btn btn-outline btn-sm">Full Transcript</a>
           <?php
              $tObtained = array_sum(array_column($group['marks'], 'marks_obtained'));
              $tMax = array_sum(array_column($group['marks'], 'max_marks'));
              $pct = $tMax > 0 ? round(($tObtained/$tMax)*100, 1) : 0;
           ?>
           <div style="font-size:.85rem;font-weight:600;color:var(--text-secondary);">
              Overall: <span style="color:var(--text-primary);font-weight:700;"><?= $pct ?>%</span>
           </div>
        </div>
        
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
$(function() {
  // Live search filtering
  $('#cardSearch').on('input', function() {
    const term = $(this).val().toLowerCase();
    $('.student-mark-card').each(function() {
      const searchData = $(this).data('search');
      if (searchData.includes(term)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // AJAX Delete handler
  $(document).on('click', '.btn-delete-mark', function() {
    const id   = $(this).data('id');
    const name = $(this).data('name');
    const row  = $(this).closest('tr');
    const tableBody = $(this).closest('tbody');
    const card = $(this).closest('.student-mark-card');

    confirmDelete(name, function() {
      $.post(window.location.href, { action: 'delete', id: id }, function(res) {
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch(e) {} }
        if (res.success) {
          toastr.success(res.message);
          
          // Remove the row dynamically
          row.fadeOut(300, () => {
             row.remove();
             // If this was the last subject for the student, remove the whole card
             if (tableBody.children('tr').length === 0) {
                 card.fadeOut(300, () => card.remove());
             }
          });
        } else { toastr.error(res.message); }
      });
    });
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>
