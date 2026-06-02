<?php
/**
 * Edit Marks
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: view.php'); exit; }

$stmt = $pdo->prepare("
    SELECT m.*, s.student_name, s.roll_number, s.department,
           sub.subject_name, sub.subject_code, sub.max_marks
    FROM marks m
    JOIN students s   ON m.student_id  = s.id
    JOIN subjects sub ON m.subject_id  = sub.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$mark = $stmt->fetch();
if (!$mark) { header('Location: view.php'); exit; }

$pageTitle = 'Edit Marks';
$activeNav = 'marks';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marksVal = $_POST['marks_obtained'] ?? '';

    if ($marksVal === '' || !is_numeric($marksVal)) $errors[] = 'Please enter valid marks.';

    if (empty($errors)) {
        $marksVal = (float)$marksVal;
        if ($marksVal < 0)                         $errors[] = 'Marks cannot be negative.';
        if ($marksVal > (float)$mark['max_marks']) $errors[] = "Marks cannot exceed {$mark['max_marks']}.";
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE marks SET marks_obtained = ? WHERE id = ?")
            ->execute([$marksVal, $id]);
        header('Location: view.php?success=updated');
        exit;
    }

    $mark['marks_obtained'] = $marksVal;
}

$topbarAction = '<a href="view.php" class="topbar-btn secondary"><i class="fa-solid fa-arrow-left fa-xs"></i> Back to Marks</a>';
require_once '../includes/header.php';
?>

<div style="max-width:560px;">
  <div class="mb-24">
    <h2 style="font-size:1.4rem;letter-spacing:-.03em;">Edit Marks</h2>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-top:4px;">Update the marks for the entry below.</p>
  </div>

  <?php if ($errors): ?>
  <div class="alert alert-danger mb-20">
    <i class="fa-solid fa-circle-exclamation"></i>
    <div><strong>Please fix:</strong><ul style="margin:6px 0 0 16px;padding:0;">
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul></div>
  </div>
  <?php endif; ?>

  <!-- Info Card -->
  <div class="card mb-20">
    <div class="card-body" style="padding:18px 24px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
          <div class="form-hint mb-0" style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Student</div>
          <div style="font-weight:600;margin-top:3px;"><?= htmlspecialchars($mark['student_name']) ?></div>
          <div style="font-size:.78rem;color:var(--text-secondary);"><?= htmlspecialchars($mark['roll_number']) ?> &middot; <?= $mark['department'] ?></div>
        </div>
        <div>
          <div class="form-hint mb-0" style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Subject</div>
          <div style="font-weight:600;margin-top:3px;"><?= htmlspecialchars($mark['subject_name']) ?></div>
          <div style="font-size:.78rem;color:var(--text-secondary);"><?= $mark['subject_code'] ?> &middot; Max: <?= $mark['max_marks'] ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Update Marks</div></div>
    <div class="card-body">
      <form method="POST" novalidate>
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label" for="marks_obtained">
            Marks Obtained *
            <span style="font-weight:400;color:var(--text-secondary);margin-left:8px;font-size:.78rem;">Max: <?= $mark['max_marks'] ?></span>
          </label>
          <input type="number" id="marks_obtained" name="marks_obtained" class="form-control"
            value="<?= (float)$mark['marks_obtained'] ?>"
            min="0" max="<?= $mark['max_marks'] ?>" step="0.5"
            style="font-size:1.2rem;font-weight:600;padding:14px 16px;" required>
          <div class="form-hint">Pass mark is 35. Range: 0 – <?= $mark['max_marks'] ?>.</div>
        </div>
        <div class="divider"></div>
        <div class="d-flex gap-12">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk fa-sm"></i> Update Marks
          </button>
          <a href="view.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
