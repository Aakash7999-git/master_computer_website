<?php
/**
 * Master Computer Institute - Public Site Header
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$siteSettings = get_all_settings($pdo);

$currentActivePage = $activePage ?? 'home';
$pageTitle = $pageTitle ?? 'Master Computer Institute | Computer Courses & Training';
$pageDesc = $pageDesc ?? 'Master Computer Institute offers practical computer education, professional courses, admissions, and certificate verification in Nawabganj, Uttar Pradesh.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= e($pageDesc) ?>" />
  <title><?= e($pageTitle) ?></title>
  <link rel="icon" href="<?= base_url('assets/logos/mci-logo.svg') ?>" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/swiper/swiper-bundle.min.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('css/style.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('css/responsive.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('css/animations.css') ?>" />
  <style>
    /* Enquire Modal Styling matching existing aesthetics */
    .mci-enquire-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(4px);
      z-index: 10000;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .mci-enquire-modal.is-active {
      display: flex;
    }
    .mci-modal-dialog {
      background: #ffffff;
      border-radius: 16px;
      width: 100%;
      max-width: 540px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
      animation: modalFadeIn 0.3s ease;
    }
    @keyframes modalFadeIn {
      from { transform: scale(0.95); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    .mci-modal-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border, #e2e8f0);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f8fafc;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
    }
    .mci-modal-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: var(--text, #0f172a);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .mci-modal-close {
      background: none;
      border: none;
      font-size: 24px;
      color: var(--text-muted, #64748b);
      cursor: pointer;
      line-height: 1;
      padding: 4px;
    }
    .mci-modal-body {
      padding: 24px;
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="container topbar-inner">
      <div><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?> • <?= e($siteSettings['city'] ?? 'Nawabganj') ?>, <?= e($siteSettings['state'] ?? 'Uttar Pradesh') ?> (<?= e($siteSettings['pincode'] ?? '271303') ?>)</div>
      <div class="topbar-links">
        <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer">
          <i class="bi bi-instagram"></i> <?= e($siteSettings['instagram_handle'] ?? '@master.computer1') ?>
        </a>
        <a href="<?= e($siteSettings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" target="_blank" rel="noopener noreferrer">
          <i class="bi bi-globe"></i> Official Blog
        </a>
      </div>
    </div>
  </div>

  <header class="navbar">
    <div class="container navbar-inner">
      <a class="nav-logo" href="<?= base_url('index.php') ?>" aria-label="Master Computer Institute home">
        <span class="nav-logo-mark"><img src="<?= base_url('assets/logos/mci-logo.svg') ?>" alt="Master Computer Institute Logo" /></span>
        <span class="nav-logo-text">MASTER<br />COMPUTER<br />INSTITUTE</span>
      </a>

      <nav aria-label="Main navigation">
        <ul class="nav-menu">
          <li><a class="<?= $currentActivePage === 'home' ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">Home</a></li>
          <li><a class="<?= $currentActivePage === 'about' ? 'active' : '' ?>" href="<?= base_url('about.php') ?>">About</a></li>
          <li><a class="<?= $currentActivePage === 'courses' ? 'active' : '' ?>" href="<?= base_url('courses.php') ?>">Courses</a></li>
          <li><a class="<?= $currentActivePage === 'branches' ? 'active' : '' ?>" href="<?= base_url('branches.php') ?>">Branches</a></li>
          <li><a class="<?= $currentActivePage === 'admission' ? 'active' : '' ?>" href="<?= base_url('admission.php') ?>">Admissions</a></li>
          <li><a class="<?= $currentActivePage === 'gallery' ? 'active' : '' ?>" href="<?= base_url('gallery.php') ?>">Gallery</a></li>
          <li><a class="<?= $currentActivePage === 'certificate-verification' ? 'active' : '' ?>" href="<?= base_url('certificate-verification.php') ?>">Certificate Verification</a></li>
          <li><a class="<?= $currentActivePage === 'contact' ? 'active' : '' ?>" href="<?= base_url('contact.php') ?>">Contact</a></li>
        </ul>
      </nav>

      <div class="nav-actions" id="authNavActions">
        <button type="button" class="btn btn-primary" onclick="openEnquireModal()">Enquire Now</button>
      </div>

      <button class="mobile-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
        <i class="bi bi-list"></i>
      </button>
    </div>
  </header>
