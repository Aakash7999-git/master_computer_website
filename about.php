<?php
/**
 * Master Computer Institute - About Us Page
 */

$activePage = 'about';
$pageTitle = 'About Us | Master Computer Institute';
$pageDesc = 'Learn about Master Computer Institute, our mission, vision and leadership under Director Shivam Pandey in Nawabganj, UP.';
require_once __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>About</span></div>
        <h1>About <?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></h1>
        <p>Professional computer learning, modern digital skills, and student-focused education in Nawabganj, UP.</p>
      </div>
    </section>

    <section class="section">
      <div class="container grid-2">
        <div class="about-text reveal">
          <p><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?> is a dedicated computer education center located in Nawabganj, Uttar Pradesh (PIN 271303). Our mission is to make technology and digital skills accessible, practical, and career-oriented for students and learners.</p>
          <p>We emphasize hands-on computer practice, foundational computer literacy, software tools, and guided learning to help students build confidence and real-world digital capabilities.</p>
          <ul class="points-list">
            <li><i class="bi bi-check-circle-fill"></i> Practical, career-oriented computer education</li>
            <li><i class="bi bi-check-circle-fill"></i> Supportive and structured learning environment</li>
            <li><i class="bi bi-check-circle-fill"></i> Located in Nawabganj, Uttar Pradesh (near Tripathi Hospital)</li>
            <li><i class="bi bi-check-circle-fill"></i> Dedicated computer lab sessions and personal guidance</li>
          </ul>
          <div style="margin-top:24px;">
            <button type="button" class="btn btn-primary" onclick="openEnquireModal()">Enquire Now</button>
          </div>
        </div>
        <div class="about-visual reveal">
          <img src="<?= base_url('assets/images/classroom/classroom-training-01.png') ?>"
            alt="Master Computer Institute classroom training with students" loading="lazy" />
        </div>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Director</span>
          <h2 class="section-title">Leadership</h2>
        </div>
        <div class="director-box reveal" style="max-width: 700px; margin: 0 auto;">
          <div class="director-avatar-placeholder"
            style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, #f59e0b, #d97706); display:flex; align-items:center; justify-content:center; color:#fff; font-size:26px; font-weight:700; flex-shrink:0;">
            SP
          </div>
          <div>
            <h4><?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></h4>
            <p>Director, <?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></p>
            <small style="color:var(--text-muted, #64748b);"><?= e($siteSettings['city'] ?? 'Nawabganj') ?>, <?= e($siteSettings['state'] ?? 'Uttar Pradesh') ?> - <?= e($siteSettings['pincode'] ?? '271303') ?></small>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Mission & Vision</span>
          <h2 class="section-title">Our guiding principles</h2>
        </div>
        <div class="feature-grid">
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-bullseye"></i></div>
            <h3>Mission</h3>
            <p>To deliver high-quality, practical computer education that helps students gain confidence, digital literacy, and real-world skills.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-eye"></i></div>
            <h3>Vision</h3>
            <p>To empower youth and learners in Nawabganj with modern computer and IT skills for educational and career advancement.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-star-fill"></i></div>
            <h3>Why choose us</h3>
            <p>We focus on direct computer lab practice, accessible teaching, and student-first guidance.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
