<?php
/**
 * Edit Student
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: view.php'); exit; }

$student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student->execute([$id]);
$student = $student->fetch();
if (!$student) { header('Location: view.php'); exit; }

$pageTitle = 'Edit Student';
$activeNav = 'students';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll   = strtoupper(trim($_POST['roll_number'] ?? ''));
    $name   = trim($_POST['student_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $dept   = trim($_POST['department'] ?? '');

    if (!$roll)  $errors[] = 'Roll Number is required.';
    if (!$name)  $errors[] = 'Student Name is required.';
    if (!preg_match('/^\d{10}$/', $mobile)) $errors[] = 'Mobile must be exactly 10 digits.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!in_array($dept, ['CSE','ECE','EEE','Civil','Mechanical'])) $errors[] = 'Invalid department selected.';

    // Roll uniqueness (excluding current)
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM students WHERE roll_number = ? AND id != ?");
        $check->execute([$roll, $id]);
        if ($check->fetch()) $errors[] = 'Roll Number already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE students SET roll_number=?, student_name=?, mobile=?, email=?, department=? WHERE id=?");
        $stmt->execute([$roll, $name, $mobile, $email, $dept, $id]);
        header('Location: view.php?success=updated');
        exit;
    }

    // Repopulate with POSTed values
    $student = array_merge($student, compact('roll', 'name', 'mobile', 'email', 'dept'));
    $student['roll_number']   = $roll;
    $student['student_name']  = $name;
    $student['department']    = $dept;
}

$topbarAction = '<a href="view.php" class="topbar-btn secondary"><i class="fa-solid fa-arrow-left fa-xs"></i> Back to Students</a>';
require_once '../includes/header.php';
?>

<div style="max-width:680px;">
  <div class="mb-24">
    <h2 style="font-size:1.4rem;letter-spacing:-.03em;">Edit Student</h2>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-top:4px;">Update the details for <strong><?= htmlspecialchars($student['student_name']) ?></strong>.</p>
  </div>

  <?php if ($errors): ?>
  <div class="alert alert-danger mb-20">
    <i class="fa-solid fa-circle-exclamation"></i>
    <div><strong>Please fix the following:</strong><ul style="margin:6px 0 0 16px;padding:0;">
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul></div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Student Information</div>
      <span class="badge badge-gray">ID: <?= $id ?></span>
    </div>
    <div class="card-body">
      <form method="POST" id="student-form" novalidate>
        <?= csrfField() ?>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="roll_number">Roll Number *</label>
            <input type="text" id="roll_number" name="roll_number" class="form-control"
              value="<?= htmlspecialchars($student['roll_number']) ?>"
              style="text-transform:uppercase;" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="department">Department *</label>
            <select id="department" name="department" class="form-control select2" required>
              <?php foreach (['CSE','ECE','EEE','Civil','Mechanical'] as $d): ?>
                <option value="<?= $d ?>" <?= $student['department'] === $d ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="student_name">Full Name *</label>
          <input type="text" id="student_name" name="student_name" class="form-control"
            value="<?= htmlspecialchars($student['student_name']) ?>" required>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="mobile">Mobile Number *</label>
            <input type="tel" id="mobile" name="mobile" class="form-control"
              maxlength="10" value="<?= htmlspecialchars($student['mobile']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-control"
              value="<?= htmlspecialchars($student['email']) ?>" required>
          </div>
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
