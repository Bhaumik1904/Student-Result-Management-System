<?php
/**
 * Add Marks
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Add Marks';
$activeNav = 'marks';
$errors    = [];

// Fetch dropdowns
$students = $pdo->query("SELECT id, roll_number, student_name, department FROM students ORDER BY student_name")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_code, subject_name, max_marks FROM subjects ORDER BY subject_code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $marks     = $_POST['marks_obtained'] ?? '';

    if (!$studentId)              $errors[] = 'Please select a student.';
    if (!$subjectId)              $errors[] = 'Please select a subject.';
    if ($marks === '' || !is_numeric($marks)) $errors[] = 'Please enter valid marks.';

    if (empty($errors)) {
        $marksVal = (float)$marks;
        // Get subject max marks
        $subStmt = $pdo->prepare("SELECT max_marks FROM subjects WHERE id = ?");
        $subStmt->execute([$subjectId]);
        $sub = $subStmt->fetch();

        if ($marksVal < 0)                      $errors[] = 'Marks cannot be negative.';
        if ($sub && $marksVal > $sub['max_marks']) $errors[] = "Marks cannot exceed {$sub['max_marks']}.";
    }

    // Duplicate check
    if (empty($errors)) {
        $dup = $pdo->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ?");
        $dup->execute([$studentId, $subjectId]);
        if ($dup->fetch()) $errors[] = 'Marks for this student and subject already exist. Use Edit to update.';
    }

    if (empty($errors)) {
        $pdo->prepare("INSERT INTO marks (student_id, subject_id, marks_obtained) VALUES (?,?,?)")
            ->execute([$studentId, $subjectId, $marksVal]);
        header('Location: view.php?success=added');
        exit;
    }
}

$topbarAction = '<a href="view.php" class="topbar-btn secondary"><i class="fa-solid fa-arrow-left fa-xs"></i> Back to Marks</a>';
require_once '../includes/header.php';
?>

<div style="max-width:620px;">
  <div class="mb-24">
    <h2 style="font-size:1.4rem;letter-spacing:-.03em;">Enter Marks</h2>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-top:4px;">Select a student and subject, then enter the marks obtained.</p>
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
    <div class="card-header"><div class="card-title">Marks Entry</div></div>
    <div class="card-body">
      <form method="POST" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
          <label class="form-label" for="student_id">Student *</label>
          <select id="student_id" name="student_id" class="form-control select2" required>
            <option value="">Search and select a student…</option>
            <?php foreach ($students as $s): ?>
              <option value="<?= $s['id'] ?>" <?= (($_POST['student_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['roll_number']) ?> — <?= htmlspecialchars($s['student_name']) ?> (<?= $s['department'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="subject_id">Subject *</label>
          <select id="subject_id" name="subject_id" class="form-control select2" required>
            <option value="">Search and select a subject…</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?= $sub['id'] ?>"
                data-max="<?= $sub['max_marks'] ?>"
                <?= (($_POST['subject_id'] ?? '') == $sub['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($sub['subject_code']) ?> — <?= htmlspecialchars($sub['subject_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="marks_obtained">
            Marks Obtained *
            <span id="max-marks-display" style="font-weight:400;color:var(--text-secondary);margin-left:8px;font-size:.78rem;"></span>
          </label>
          <input type="number" id="marks_obtained" name="marks_obtained" class="form-control"
            placeholder="Enter marks" min="0" step="0.5"
            value="<?= htmlspecialchars($_POST['marks_obtained'] ?? '') ?>" required>
          <div class="form-hint">Pass mark per subject is 35. Marks cannot be negative or exceed maximum.</div>
        </div>

        <div class="divider"></div>
        <div class="d-flex gap-12">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square fa-sm"></i> Save Marks
          </button>
          <a href="view.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
