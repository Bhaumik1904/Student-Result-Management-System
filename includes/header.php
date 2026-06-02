<?php
/**
 * Shared HTML Header
 * Include at the top of every page
 * @param string $title    Page title
 * @param string $active   Active nav item key
 */
if (!isset($pageTitle))  $pageTitle  = 'Dashboard';
if (!isset($activeNav))  $activeNav  = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ResultPro — Student Result Management System. Manage student marks, generate transcripts, and track academic performance.">
  <title><?= htmlspecialchars($pageTitle) ?> — ResultPro</title>

  <!-- Bootstrap 5.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <!-- App CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

  <!-- ── Sidebar ──────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">R</div>
      <div>
        <div class="logo-text">ResultPro</div>
        <div class="logo-sub">Academic Management</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>

      <a href="<?= BASE_URL ?>/dashboard/index.php"
         class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <i class="fa-solid fa-house-chimney"></i> Dashboard
      </a>

      <div class="nav-section-label">Management</div>

      <a href="<?= BASE_URL ?>/students/view.php"
         class="nav-item <?= $activeNav === 'students' ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i> Students
      </a>

      <a href="<?= BASE_URL ?>/subjects/view.php"
         class="nav-item <?= $activeNav === 'subjects' ? 'active' : '' ?>">
        <i class="fa-solid fa-book-open"></i> Subjects
      </a>

      <a href="<?= BASE_URL ?>/marks/view.php"
         class="nav-item <?= $activeNav === 'marks' ? 'active' : '' ?>">
        <i class="fa-solid fa-pen-to-square"></i> Marks
      </a>

      <div class="nav-section-label">Results</div>

      <a href="<?= BASE_URL ?>/results/search.php"
         class="nav-item <?= $activeNav === 'search' ? 'active' : '' ?>">
        <i class="fa-solid fa-magnifying-glass"></i> Search Result
      </a>

      <a href="<?= BASE_URL ?>/results/result-card.php"
         class="nav-item <?= $activeNav === 'result-card' ? 'active' : '' ?>">
        <i class="fa-solid fa-id-card"></i> Result Cards
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-badge">
        <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?></div>
        <div>
          <div class="admin-name"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></div>
          <div class="admin-role">Administrator</div>
        </div>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="ms-auto" title="Logout" style="color:var(--text-tertiary);">
          <i class="fa-solid fa-arrow-right-from-bracket fa-sm"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- ── Main ─────────────────────────────────────── -->
  <div class="main-content">
    <!-- Top Bar -->
    <header class="topbar">
      <div class="topbar-left">
        <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
        <div class="topbar-sub" id="topbar-date"></div>
      </div>
      <div class="topbar-right">
        <?php if (!empty($topbarAction)): ?>
          <?= $topbarAction ?>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="topbar-btn secondary">
          <i class="fa-solid fa-arrow-right-from-bracket fa-xs"></i> Logout
        </a>
      </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
