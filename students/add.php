<?php
/**
 * Add Student
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Add Student';
$activeNav = 'students';
$errors    = [];
$success   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll   = strtoupper(trim($_POST['roll_number'] ?? ''));
    $name   = trim($_POST['student_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $dept   = trim($_POST['department'] ?? '');

    // Validation
    if (!$roll)  $errors[] = 'Roll Number is required.';
    if (!$name)  $errors[] = 'Student Name is required.';
    if (!preg_match('/^\d{10}$/', $mobile)) $errors[] = 'Mobile must be exactly 10 digits.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!in_array($dept, ['CSE','ECE','EEE','Civil','Mechanical'])) $errors[] = 'Invalid department selected.';

    // Check roll uniqueness
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM students WHERE roll_number = ?");
        $check->execute([$roll]);
        if ($check->fetch()) $errors[] = 'Roll Number already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO students (roll_number, student_name, mobile, email, department) VALUES (?,?,?,?,?)");
        $stmt->execute([$roll, $name, $mobile, $email, $dept]);
        header('Location: view.php?success=added');
        exit;
    }
}

$topbarAction = '<a href="view.php" class="topbar-btn secondary"><i class="fa-solid fa-arrow-left fa-xs"></i> Back to Students</a>';
require_once '../includes/header.php';
?>

<div style="max-width:680px;">
  <div class="mb-24">
    <h2 style="font-size:1.4rem;letter-spacing:-.03em;">Add New Student</h2>
    <p style="color:var(--text-secondary);font-size:.9rem;margin-top:4px;">Fill in the student details below. All fields are required.</p>
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
    </div>
    <div class="card-body">
      <form method="POST" id="student-form" novalidate>
        <?= csrfField() ?>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="roll_number">Roll Number *</label>
            <input type="text" id="roll_number" name="roll_number" class="form-control"
              placeholder="e.g. CSE2024001"
              value="<?= htmlspecialchars($_POST['roll_number'] ?? '') ?>"
              style="text-transform:uppercase;" required>
            <div class="form-hint">Must be unique across all students.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="department">Department *</label>
            <select id="department" name="department" class="form-control select2" required>
              <option value="">Select Department</option>
              <?php foreach (['CSE','ECE','EEE','Civil','Mechanical'] as $d): ?>
                <option value="<?= $d ?>" <?= (($_POST['department'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="student_name">Full Name *</label>
          <input type="text" id="student_name" name="student_name" class="form-control"
            placeholder="Enter student's full name"
            value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>" required>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label" for="mobile">Mobile Number *</label>
            <input type="tel" id="mobile" name="mobile" class="form-control"
              placeholder="10-digit mobile number" maxlength="10"
              value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required>
            <div class="form-hint">Exactly 10 digits, no spaces.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-control"
              placeholder="student@example.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="divider"></div>

        <div class="d-flex gap-12">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-user-plus fa-sm"></i> Add Student
          </button>
          <a href="view.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
