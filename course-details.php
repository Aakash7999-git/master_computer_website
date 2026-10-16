<?php
/**
 * Master Computer Institute - Course Details Page
 */

$activePage = 'courses';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();

$courseId = (int)($_GET['id'] ?? 0);
$slug = sanitize_input($_GET['slug'] ?? '');

$course = null;
if ($courseId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
} elseif (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$slug]);
    $course = $stmt->fetch();
}

// Fallback to ADCA or first active course
if (!$course) {
    $stmt = $pdo->query("SELECT * FROM courses WHERE course_code = 'ADCA' LIMIT 1");
    $course = $stmt->fetch();
    if (!$course) {
        $stmt = $pdo->query("SELECT * FROM courses WHERE status = 'active' LIMIT 1");
        $course = $stmt->fetch();
    }
}

$pageTitle = ($course ? e($course['name']) . ' | Course Details' : 'Course Details') . ' | Master Computer Institute';
$pageDesc = $course ? substr(strip_tags($course['description'] ?? ''), 0, 150) : 'Explore certified computer courses at Master Computer Institute Nawabganj.';
require_once __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs">
          <a href="<?= base_url('index.php') ?>">Home</a> / 
          <a href="<?= base_url('courses.php') ?>">Courses</a> / 
          <span><?= e($course['course_code'] ?? 'Course Details') ?></span>
        </div>
        <h1><?= e($course['name']) ?></h1>
        <p>Duration: <?= e($course['duration']) ?> | Eligibility: <?= e($course['eligibility'] ?? '10th / 12th / All Stream Students') ?></p>
      </div>
    </section>

    <section class="section">
      <div class="container detail-grid">
        <div class="detail-panel reveal">
          <img src="<?= base_url(e($course['image_url'] ?: 'assets/images/courses/adca-course.jpg')) ?>"
            alt="<?= e($course['name']) ?> practical training at Master Computer Institute" width="600" height="350"
            onerror="this.src='<?= base_url('assets/images/courses/adca-course.jpg') ?>'" />
          
          <h2>Course Overview</h2>
          <div style="line-height:1.7; color:var(--text, #334155); margin-bottom:20px;">
            <p><?= nl2br(e($course['description'] ?: 'Comprehensive practical training curriculum designed to give students job-ready digital skills, office automation expertise, and software proficiency.')) ?></p>
          </div>

          <ul class="detail-list">
            <li><i class="bi bi-check-circle-fill"></i> Dedicated hands-on daily practical computer lab sessions</li>
            <li><i class="bi bi-check-circle-fill"></i> Comprehensive study materials, step-by-step guides, and notes</li>
            <li><i class="bi bi-check-circle-fill"></i> Continuous teacher mentorship and personal doubt-clearing assistance</li>
            <li><i class="bi bi-check-circle-fill"></i> Recognized educational certification upon successful course completion</li>
          </ul>
        </div>

        <aside class="detail-panel reveal">
          <div class="summary-box">
            <h3>Course Summary</h3>
            <ul>
              <li><span>Course Code</span><strong><?= e($course['course_code']) ?></strong></li>
              <li><span>Full Title</span><strong><?= e($course['name']) ?></strong></li>
              <li><span>Category</span><strong><?= e($course['category']) ?></strong></li>
              <li><span>Duration</span><strong><?= e($course['duration']) ?></strong></li>
              <li><span>Eligibility</span><strong><?= e($course['eligibility'] ?: 'Open to all') ?></strong></li>
              <li><span>Fees</span><strong style="color:var(--primary, #1e3a8a); font-size:16px;"><?= format_inr($course['fee']) ?></strong></li>
              <li><span>Certification</span><strong><?= e($course['certification'] ?: 'Govt. Registered Certificate') ?></strong></li>
              <li><span>Center</span><strong><?= e($siteSettings['city'] ?? 'Nawabganj') ?> Center</strong></li>
            </ul>
          </div>
          <div class="form-actions" style="margin-top: 24px;">
            <button type="button" class="btn btn-primary" style="width:100%; text-align:center;" onclick="openEnquireModal('<?= addslashes($course['name']) ?>')">
              Enquire Now
            </button>
          </div>
        </aside>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Key Highlights</span>
          <h2 class="section-title">What you will gain from this course</h2>
        </div>
        <div class="feature-grid">
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-laptop-fill"></i></div>
            <h3>100% Practical Training</h3>
            <p>Direct exposure to modern computer software and real workplace tasks.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-award-fill"></i></div>
            <h3>Recognized Certificate</h3>
            <p>Earn an authorized qualification that can be verified online anytime.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-briefcase-fill"></i></div>
            <h3>Career Readiness</h3>
            <p>Build skills suitable for office jobs, accounting, government exams, and business.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
