<?php
/**
 * View / List Marks
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

$marks = $pdo->query("
    SELECT m.id, m.marks_obtained, m.updated_at,
           s.roll_number, s.student_name, s.department,
           sub.subject_code, sub.subject_name, sub.max_marks
    FROM marks m
    JOIN students s   ON m.student_id  = s.id
    JOIN subjects sub ON m.subject_id  = sub.id
    ORDER BY s.roll_number, sub.subject_code
")->fetchAll();

$topbarAction = '<a href="add.php" class="topbar-btn primary"><i class="fa-solid fa-plus fa-xs"></i> Add Marks</a>';
require_once '../includes/header.php';
?>

<?php if ($successMsg): ?>
<div class="alert alert-success flash-message mb-20">
  <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div class="d-flex align-center justify-between mb-20">
  <div>
    <h2 style="font-size:1.3rem;letter-spacing:-.02em;">All Marks</h2>
    <p style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;"><?= count($marks) ?> mark entries</p>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:20px 24px;">
    <?php if (empty($marks)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="fa-solid fa-pen-to-square"></i></div>
        <h3>No Marks Entered</h3>
        <p>Start by entering marks for students.</p>
        <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus fa-sm"></i> Add Marks</a>
      </div>
    <?php else: ?>
      <table class="table" id="marksTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Roll No.</th>
            <th>Student</th>
            <th>Dept</th>
            <th>Subject</th>
            <th>Obtained</th>
            <th>Max</th>
            <th>%</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($marks as $i => $m): ?>
          <?php
            $pct    = $m['max_marks'] > 0 ? round(($m['marks_obtained'] / $m['max_marks']) * 100, 1) : 0;
            $pass   = (float)$m['marks_obtained'] >= 35;
          ?>
          <tr>
            <td style="color:var(--text-tertiary);font-size:.8rem;"><?= $i + 1 ?></td>
            <td><span style="font-family:monospace;font-size:.8rem;background:var(--surface);padding:2px 7px;border-radius:4px;"><?= htmlspecialchars($m['roll_number']) ?></span></td>
            <td style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($m['student_name']) ?></td>
            <td><span class="badge badge-blue"><?= $m['department'] ?></span></td>
            <td>
              <div style="font-size:.85rem;"><?= htmlspecialchars($m['subject_name']) ?></div>
              <div style="font-size:.72rem;color:var(--text-tertiary);"><?= $m['subject_code'] ?></div>
            </td>
            <td style="font-weight:700;font-size:.95rem;"><?= (float)$m['marks_obtained'] ?></td>
            <td style="color:var(--text-secondary);font-size:.85rem;"><?= $m['max_marks'] ?></td>
            <td style="font-size:.85rem;"><?= $pct ?>%</td>
            <td>
              <?php if ($pass): ?>
                <span class="badge badge-green"><i class="fa-solid fa-check fa-xs"></i> Pass</span>
              <?php else: ?>
                <span class="badge badge-red"><i class="fa-solid fa-xmark fa-xs"></i> Fail</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right;">
              <div class="d-flex gap-8" style="justify-content:flex-end;">
                <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                  <i class="fa-solid fa-pen fa-xs"></i>
                </a>
                <button class="btn btn-danger btn-sm btn-delete-mark"
                  data-id="<?= $m['id'] ?>"
                  data-name="<?= htmlspecialchars($m['student_name'] . ' — ' . $m['subject_name']) ?>"
                  title="Delete">
                  <i class="fa-solid fa-trash fa-xs"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script>
$(function() {
  window.__dataTable = initDataTable('#marksTable', { order: [[1,'asc'],[4,'asc']] });

  $(document).on('click', '.btn-delete-mark', function() {
    const id   = $(this).data('id');
    const name = $(this).data('name');
    const row  = $(this).closest('tr');

    confirmDelete(name, function() {
      $.post(window.location.href, { action: 'delete', id: id }, function(res) {
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch(e) {} }
        if (res.success) {
          toastr.success(res.message);
          if (window.__dataTable) window.__dataTable.row(row).remove().draw();
          else row.fadeOut(300, () => row.remove());
        } else { toastr.error(res.message); }
      });
    });
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>
