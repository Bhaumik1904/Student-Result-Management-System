<?php
/**
 * Edit Subject
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: view.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch();
if (!$subject) { header('Location: view.php'); exit; }

$pageTitle = 'Edit Subject';
$activeNav = 'subjects';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code     = strtoupper(trim($_POST['subject_code'] ?? ''));
    $name     = trim($_POST['subject_name'] ?? '');
    $maxMarks = (int)($_POST['max_marks'] ?? 0);

    if (!$code)          $errors[] = 'Subject Code is required.';
    if (!$name)          $errors[] = 'Subject Name is required.';
    if ($maxMarks <= 0)  $errors[] = 'Max Marks must be greater than 0.';
    if ($maxMarks > 500) $errors[] = 'Max Marks cannot exceed 500.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM subjects WHERE subject_code = ? AND id != ?");
        $check->execute([$code, $id]);
        if ($check->fetch()) $errors[] = 'Subject Code already exists.';
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE subjects SET subject_code=?, subject_name=?, max_marks=? WHERE id=?")
            ->execute([$code, $name, $maxMarks, $id]);
        header('Location: view.php?success=updated');
        exit;
    }

    $subject['subject_code'] = $code;
    $subject['subject_name'] = $name;
    $subject['max_marks']    = $maxMarks;
}

$topbarAction = '<a href="view.php" class="topbar-btn secondary"><i class="fa-solid fa-arrow-left fa-xs"></i> Back to Subjects</a>';
require_once '../includes/header.php';
?>

<div style="max-width:560px;">
  <div class="mb-24">
    <h2 style="font-size:1.4rem;letter-spacing:-.03em;">Edit Subject</h2>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-top:4px;">
      Updating <strong><?= htmlspecialchars($subject['subject_name']) ?></strong>.
    </p>
  </div>

  <?php if ($errors): ?>
  <div class="alert alert-danger mb-20">
    <i class="fa-solid fa-circle-exclamation"></i>
    <div><strong>Please fix:</strong><ul style="margin:6px 0 0 16px;padding:0;">
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul></div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Subject Details</div>
      <span class="badge badge-gray">ID: <?= $id ?></span>
    </div>
    <div class="card-body">
      <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="subject_code">Subject Code *</label>
            <input type="text" id="subject_code" name="subject_code" class="form-control"
              value="<?= htmlspecialchars($subject['subject_code']) ?>"
              style="text-transform:uppercase;" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="max_marks">Maximum Marks *</label>
            <input type="number" id="max_marks" name="max_marks" class="form-control"
              value="<?= (int)$subject['max_marks'] ?>" min="1" max="500" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="subject_name">Subject Name *</label>
          <input type="text" id="subject_name" name="subject_name" class="form-control"
            value="<?= htmlspecialchars($subject['subject_name']) ?>" required>
        </div>
        <div class="divider"></div>
        <div class="d-flex gap-12">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk fa-sm"></i> Save Changes
          </button>
          <a href="view.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
