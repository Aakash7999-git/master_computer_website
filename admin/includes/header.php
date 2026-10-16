<?php
/**
 * Master Computer Institute - Admin Dashboard Header
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin_auth();

$pdo = getDBConnection();
$currentAdmin = current_admin();
$pageTitle = $pageTitle ?? 'Admin Dashboard';
$activeNav = $activeNav ?? 'dashboard';

// Fetch live badge counts for sidebar
try {
    $newEnquiriesCount = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
    $newAdmissionsCount = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'New'")->fetchColumn();
    $unreadContactsCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")->fetchColumn();
    $activeStudentsCount = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();
} catch (Exception $e) {
    $newEnquiriesCount = 0;
    $newAdmissionsCount = 0;
    $unreadContactsCount = 0;
    $activeStudentsCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?> | Master Computer Institute</title>
  <link rel="icon" href="../assets/logos/mci-logo.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/admin.css" />
</head>
<body class="admin-body">

  <!-- Sidebar Overlay for mobile -->
  <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99;" onclick="toggleAdminSidebar()"></div>

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="dashboard.php" class="admin-sidebar-brand">
      <img src="../assets/logos/mci-logo.svg" alt="MCI Logo" />
      <div class="admin-sidebar-brand-text">
        MASTER COMPUTER<br /><span style="font-size:11px; font-weight:400; color:var(--admin-accent);">INSTITUTE PORTAL</span>
      </div>
    </a>

    <ul class="admin-sidebar-nav">
      <li class="admin-nav-item">
        <a href="dashboard.php" class="admin-nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="enquiries.php" class="admin-nav-link <?= $activeNav === 'enquiries' ? 'active' : '' ?>">
          <i class="bi bi-chat-left-quote-fill"></i>
          <span>Enquiries</span>
          <?php if ($newEnquiriesCount > 0): ?>
            <span class="admin-nav-badge"><?= $newEnquiriesCount ?></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="admissions.php" class="admin-nav-link <?= $activeNav === 'admissions' ? 'active' : '' ?>">
          <i class="bi bi-mortarboard-fill"></i>
          <span>Admissions</span>
          <?php if ($newAdmissionsCount > 0): ?>
            <span class="admin-nav-badge" style="background:var(--admin-primary-light);"><?= $newAdmissionsCount ?></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="students.php" class="admin-nav-link <?= $activeNav === 'students' ? 'active' : '' ?>">
          <i class="bi bi-people-fill"></i>
          <span>Students</span>
          <?php if ($activeStudentsCount > 0): ?>
            <span class="admin-nav-badge" style="background:#10b981;"><?= $activeStudentsCount ?></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="courses.php" class="admin-nav-link <?= $activeNav === 'courses' ? 'active' : '' ?>">
          <i class="bi bi-journal-code"></i>
          <span>Courses</span>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="branches.php" class="admin-nav-link <?= $activeNav === 'branches' ? 'active' : '' ?>">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Branches</span>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="contacts.php" class="admin-nav-link <?= $activeNav === 'contacts' ? 'active' : '' ?>">
          <i class="bi bi-envelope-fill"></i>
          <span>Messages</span>
          <?php if ($unreadContactsCount > 0): ?>
            <span class="admin-nav-badge" style="background:var(--admin-danger);"><?= $unreadContactsCount ?></span>
          <?php endif; ?>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="certificates.php" class="admin-nav-link <?= $activeNav === 'certificates' ? 'active' : '' ?>">
          <i class="bi bi-patch-check-fill"></i>
          <span>Certificates</span>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="gallery.php" class="admin-nav-link <?= $activeNav === 'gallery' ? 'active' : '' ?>">
          <i class="bi bi-images"></i>
          <span>Gallery</span>
        </a>
      </li>

      <li class="admin-nav-item">
        <a href="settings.php" class="admin-nav-link <?= $activeNav === 'settings' ? 'active' : '' ?>">
          <i class="bi bi-sliders"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>

    <div class="admin-sidebar-footer">
      <div class="admin-user-profile">
        <div class="admin-avatar">
          <?= strtoupper(substr($currentAdmin['name'], 0, 1)) ?>
        </div>
        <div class="admin-user-info">
          <div class="admin-user-name"><?= e($currentAdmin['name']) ?></div>
          <div class="admin-user-role"><?= ucfirst(e($currentAdmin['role'])) ?></div>
        </div>
        <a href="logout.php" title="Sign Out" style="color:var(--admin-sidebar-text); font-size:16px;">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <div class="admin-main-wrap">
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button class="admin-mobile-toggle" type="button" aria-label="Toggle Menu" onclick="toggleAdminSidebar()">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="admin-page-title"><?= e($pageTitle) ?></h1>
      </div>

      <div class="admin-topbar-right">
        <a href="../index.php" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">
          <i class="bi bi-box-arrow-up-right"></i> <span>View Website</span>
        </a>
        <a href="logout.php" class="btn-admin btn-admin-danger btn-admin-sm">
          <i class="bi bi-power"></i> <span>Logout</span>
        </a>
      </div>
    </header>

    <main class="admin-content-area">
