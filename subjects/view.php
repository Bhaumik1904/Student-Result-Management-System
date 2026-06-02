<?php
/**
 * View / List Subjects
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Subjects';
$activeNav = 'subjects';

$successMsg = '';
if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        'added'   => 'Subject added successfully.',
        'updated' => 'Subject updated successfully.',
        'deleted' => 'Subject deleted successfully.',
        default   => '',
    };
}

// AJAX delete handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([$id]);
        jsonResponse(true, 'Subject deleted successfully.');
    }
    jsonResponse(false, 'Invalid request.');
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_code")->fetchAll();

$topbarAction = '<a href="add.php" class="topbar-btn primary"><i class="fa-solid fa-plus fa-xs"></i> Add Subject</a>';
require_once '../includes/header.php';
?>

<?php if ($successMsg): ?>
<div class="alert alert-success flash-message mb-20">
  <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div class="d-flex align-center justify-between mb-20">
  <div>
    <h2 style="font-size:1.3rem;letter-spacing:-.02em;">All Subjects</h2>
    <p style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;"><?= count($subjects) ?> subjects configured</p>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:20px 24px;">
    <?php if (empty($subjects)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="fa-solid fa-book-open"></i></div>
        <h3>No Subjects Found</h3>
        <p>Add subjects to begin entering marks.</p>
        <a href="add.php" class="btn btn-primary"><i class="fa-solid fa-plus fa-sm"></i> Add Subject</a>
      </div>
    <?php else: ?>
      <table class="table" id="subjectsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Max Marks</th>
            <th>Pass Mark</th>
            <th>Added</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($subjects as $i => $sub): ?>
          <tr>
            <td style="color:var(--text-tertiary);font-size:.8rem;"><?= $i + 1 ?></td>
            <td>
              <span style="font-family:monospace;font-size:.82rem;background:var(--surface);padding:2px 8px;border-radius:4px;">
                <?= htmlspecialchars($sub['subject_code']) ?>
              </span>
            </td>
            <td style="font-weight:600;"><?= htmlspecialchars($sub['subject_name']) ?></td>
            <td>
              <span class="badge badge-blue"><?= $sub['max_marks'] ?></span>
            </td>
            <td>
              <span class="badge badge-orange">35</span>
            </td>
            <td style="font-size:.78rem;color:var(--text-tertiary);"><?= date('M d, Y', strtotime($sub['created_at'])) ?></td>
            <td style="text-align:right;">
              <div class="d-flex gap-8" style="justify-content:flex-end;">
                <a href="edit.php?id=<?= $sub['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                  <i class="fa-solid fa-pen fa-xs"></i>
                </a>
                <button class="btn btn-danger btn-sm btn-delete-subject"
                  data-id="<?= $sub['id'] ?>"
                  data-name="<?= htmlspecialchars($sub['subject_name']) ?>"
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
  window.__dataTable = initDataTable('#subjectsTable', { order: [[1, 'asc']] });

  $(document).on('click', '.btn-delete-subject', function() {
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
