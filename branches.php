<?php
/**
 * Master Computer Institute - Branches & Centers Page
 */

$activePage = 'branches';
$pageTitle = 'Centers & Branches | Master Computer Institute';
$pageDesc = 'Explore the training centers of Master Computer Institute with contact details, timings, and course availability in Nawabganj, UP.';
require_once __DIR__ . '/includes/header.php';

$branches = get_active_branches($pdo);
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Location</span></div>
        <h1>Institute Centers & Branches</h1>
        <p>Dedicated computer education and training facilities in Nawabganj, Uttar Pradesh.</p>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container branch-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); max-width: 960px; margin: 0 auto; gap:28px;">
        <?php foreach ($branches as $branch): ?>
          <article class="branch-card reveal">
            <div class="branch-header">
              <h3><?= e($branch['name']) ?></h3>
              <span class="branch-badge"><?= e($branch['tagline']) ?></span>
            </div>
            <ul>
              <li><i class="bi bi-geo-alt-fill"></i> <?= e($branch['address']) ?></li>
              <li><i class="bi bi-person-badge-fill"></i> Director: <?= e($branch['director_name']) ?></li>
              <li><i class="bi bi-telephone-fill"></i> Phone: <?= e($branch['phone'] ?: $siteSettings['phone'] ?? '+91 9696525287') ?></li>
              <li><i class="bi bi-instagram"></i> Instagram: <a href="<?= e($branch['instagram']) ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($branch['instagram']) ?></a></li>
              <li><i class="bi bi-globe"></i> Blog: <a href="<?= e($branch['blog_url']) ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($branch['blog_url']) ?></a></li>
              <li><i class="bi bi-mortarboard-fill"></i> Category: Education / Computer Education</li>
            </ul>
            <div class="map-placeholder"><?= e($branch['name']) ?> • <?= e($branch['city']) ?> (<?= e($branch['pincode']) ?>)</div>
            <div style="display:flex; gap:12px; margin-top:16px;">
              <a href="<?= base_url('contact.php') ?>" class="btn btn-primary" style="flex:1; text-align:center;">Contact Center</a>
              <button type="button" class="btn btn-outline" style="flex:1;" onclick="openEnquireModal()">Enquire Now</button>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
