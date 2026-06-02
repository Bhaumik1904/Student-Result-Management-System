<?php
/**
 * View / List Students
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Students';
$activeNav = 'students';

$successMsg = '';
if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        'added'   => 'Student added successfully.',
        'updated' => 'Student updated successfully.',
        'deleted' => 'Student deleted successfully.',
        default   => '',
    };
}

// Handle inline AJAX delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
        jsonResponse(true, 'Student deleted successfully.');
    }
    jsonResponse(false, 'Invalid request.');
}

$students = $pdo->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll();

$topbarAction = '<a href="add.php" class="topbar-btn primary"><i class="fa-solid fa-plus fa-xs"></i> Add Student</a>';
require_once '../includes/header.php';
?>

<?php if ($successMsg): ?>
<div class="alert alert-success flash-message mb-20">
  <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<!-- Search Bar -->
<div class="d-flex align-center justify-between mb-20">
  <div>
    <h2 style="font-size:1.3rem;letter-spacing:-.02em;">All Students</h2>
    <p style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;"><?= count($students) ?> records</p>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:20px 24px;">
    <?php if (empty($students)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
        <h3>No Students Found</h3>
        <p>Get started by adding your first student.</p>
        <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus fa-sm"></i> Add Student</a>
      </div>
    <?php else: ?>
      <div class="table-wrapper" style="border:none;border-radius:0;box-shadow:none;">
        <table class="table" id="studentsTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Roll Number</th>
              <th>Student Name</th>
              <th>Department</th>
              <th>Mobile</th>
              <th>Email</th>
              <th>Added</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $i => $s): ?>
            <tr id="row-<?= $s['id'] ?>">
              <td style="color:var(--text-tertiary);font-size:.8rem;"><?= $i + 1 ?></td>
              <td>
                <span style="font-family:monospace;font-size:.82rem;background:var(--surface);padding:2px 8px;border-radius:4px;">
                  <?= htmlspecialchars($s['roll_number']) ?>
                </span>
              </td>
              <td style="font-weight:600;"><?= htmlspecialchars($s['student_name']) ?></td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($s['department']) ?></span></td>
              <td style="font-size:.85rem;"><?= htmlspecialchars($s['mobile']) ?></td>
              <td style="font-size:.82rem;color:var(--text-secondary);"><?= htmlspecialchars($s['email']) ?></td>
              <td style="font-size:.78rem;color:var(--text-tertiary);"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
              <td style="text-align:right;">
                <div class="d-flex gap-8" style="justify-content:flex-end;">
                  <a href="../results/result-card.php?id=<?= $s['id'] ?>" class="btn btn-outline btn-sm" title="View Result">
                    <i class="fa-solid fa-id-card fa-xs"></i>
                  </a>
                  <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                    <i class="fa-solid fa-pen fa-xs"></i>
                  </a>
                  <button class="btn btn-danger btn-sm btn-delete-student"
                    data-id="<?= $s['id'] ?>"
                    data-name="<?= htmlspecialchars($s['student_name']) ?>"
                    title="Delete">
                    <i class="fa-solid fa-trash fa-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
$(function() {
  window.__dataTable = initDataTable('#studentsTable', { order: [] });

  $(document).on('click', '.btn-delete-student', function() {
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
        } else {
          toastr.error(res.message);
        }
      });
    });
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>
