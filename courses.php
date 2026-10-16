<?php
/**
 * Master Computer Institute - Courses Catalog Page
 */

$activePage = 'courses';
$pageTitle = 'Computer Courses in Nawabganj | CCC, DCA, ADCA, Tally | Master Computer Institute';
$pageDesc = 'Explore certified computer courses at Master Computer Institute Nawabganj including CCC, DCA, ADCA, Tally Prime GST, Advanced Excel, and Graphic Design.';
require_once __DIR__ . '/includes/header.php';

$courses = get_active_courses($pdo);
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Courses</span></div>
        <h1>Our Computer Courses</h1>
        <p>Practical computer courses designed for student learning, digital literacy, and job-ready skill building in Nawabganj.</p>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="card-grid">
          <?php if (empty($courses)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:50px; color:var(--text-muted);">
              <h3>No courses currently available.</h3>
              <p>Please contact our institute office for upcoming batch schedules.</p>
            </div>
          <?php else: ?>
            <?php foreach ($courses as $course): ?>
              <article class="course-card reveal">
                <img src="<?= base_url(e($course['image_url'] ?: 'assets/images/courses/ccc-course.jpg')) ?>"
                  alt="<?= e($course['name']) ?> practical training at Master Computer Institute" width="600" height="350" loading="lazy"
                  onerror="this.src='<?= base_url('assets/images/courses/ccc-course.jpg') ?>'" />
                <div class="course-body">
                  <span class="course-chip"><?= e($course['badge'] ?: $course['category']) ?></span>
                  <h3><?= e($course['name']) ?></h3>
                  <p><?= e($course['short_desc'] ?: substr(strip_tags($course['description'] ?? ''), 0, 140) . '...') ?></p>
                  <div class="meta-row">
                    <span><i class="bi bi-clock"></i> <?= e($course['duration']) ?></span>
                    <span><i class="bi bi-award"></i> <?= e($course['eligibility'] ?: 'All levels') ?></span>
                  </div>
                  <div class="meta-row">
                    <span><i class="bi bi-info-circle"></i> Practical Lab</span>
                    <span><i class="bi bi-file-earmark-check"></i> Certificate</span>
                  </div>
                  <div class="price"><?= format_inr($course['fee']) ?></div>
                  <div class="course-actions">
                    <a href="<?= base_url('course-details.php?id=' . $course['id']) ?>" class="btn btn-outline">View Details</a>
                    <button type="button" class="btn btn-primary" onclick="openEnquireModal('<?= addslashes($course['name']) ?>')">Enquire Now</button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section class="section-sm">
      <div class="container">
        <div class="cta-panel reveal">
          <div>
            <h3>Need guidance choosing the right course?</h3>
            <p>Our counselors will help you find the best program for your career path.</p>
          </div>
          <button type="button" class="btn btn-light" onclick="openEnquireModal()">Enquire Now</button>
        </div>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
