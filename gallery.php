<?php
/**
 * Master Computer Institute - Gallery Page
 */

$activePage = 'gallery';
$pageTitle = 'Campus Gallery | Master Computer Institute';
$pageDesc = 'View gallery images of classrooms, events and activities at Master Computer Institute Nawabganj.';
require_once __DIR__ . '/includes/header.php';

$galleryStmt = $pdo->query("SELECT * FROM gallery_images WHERE status = 'active' ORDER BY sort_order ASC, id DESC");
$galleryItems = $galleryStmt->fetchAll() ?: [];
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Gallery</span></div>
        <h1>Institute Gallery</h1>
        <p>Snapshots of student activities, computer sessions, and institute moments in Nawabganj.</p>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div style="text-align:center; margin-bottom: 32px;" class="reveal">
          <p style="color:var(--text-muted, #64748b);">Explore more photos, event updates, and student achievements on our official Instagram 
            <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer" style="font-weight:600; color:var(--primary, #1e3a8a);">
              <i class="bi bi-instagram"></i> <?= e($siteSettings['instagram_handle'] ?? '@master.computer1') ?>
            </a>
          </p>
        </div>

        <div class="gallery-filters" role="group" aria-label="Gallery categories">
          <button class="gallery-filter is-active" type="button" data-gallery-filter="all">All</button>
          <button class="gallery-filter" type="button" data-gallery-filter="classroom">Classroom</button>
          <button class="gallery-filter" type="button" data-gallery-filter="certifications">Certifications</button>
          <button class="gallery-filter" type="button" data-gallery-filter="events">Events</button>
          <button class="gallery-filter" type="button" data-gallery-filter="activities">Activities</button>
          <button class="gallery-filter" type="button" data-gallery-filter="practical-training">Practical Training</button>
        </div>

        <div class="gallery-grid" data-gallery-grid>
          <?php foreach ($galleryItems as $item): ?>
            <div class="gallery-item reveal" data-gallery-category="<?= e($item['category']) ?>">
              <img src="<?= base_url(e($item['image_path'])) ?>" alt="<?= e($item['alt_text'] ?: $item['title']) ?>" loading="lazy" />
              <div class="gallery-overlay"><?= ucfirst(e($item['category'])) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
