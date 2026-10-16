<?php
/**
 * Master Computer Institute - Official Homepage
 * Dynamic PHP with MySQL integration and AJAX-enabled forms
 */

$activePage = 'home';
$pageTitle = 'Master Computer Institute | Premier Computer Education • Nawabganj';
$pageDesc = 'Master Computer Institute offers practical computer education, professional courses (CCC, DCA, ADCA, Tally Prime GST), admissions, and certificate verification in Nawabganj, UP.';
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic content from database
$courses = get_active_courses($pdo);
$branches = get_active_branches($pdo);
$mainBranch = !empty($branches) ? $branches[0] : null;

// Fetch active gallery images
$galleryStmt = $pdo->query("SELECT * FROM gallery_images WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT 8");
$galleryItems = $galleryStmt->fetchAll() ?: [];
?>

  <main>
    <!-- Hero Section -->
    <section class="hero" id="home">
      <div class="hero-decor-1" aria-hidden="true"></div>
      <div class="hero-decor-2" aria-hidden="true"></div>
      <div class="container hero-grid">
        <div class="hero-copy reveal">
          <span class="hero-kicker"><i class="bi bi-mortarboard-fill"></i> <?= e($siteSettings['tagline'] ?? 'Premier Computer Education • Nawabganj') ?></span>
          <h1>Build Your Future With <span class="hero-brand-highlight"><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></span></h1>
          <p>Learn practical computer skills through industry-relevant courses designed to build confidence, knowledge, and career-ready skills in a modern hands-on learning environment.</p>
          <div class="hero-actions">
            <a href="#courses" class="btn btn-primary btn-lg"><i class="bi bi-grid-fill"></i> Explore Courses</a>
            <button type="button" class="btn btn-outline btn-lg" onclick="openEnquireModal()"><i class="bi bi-chat-dots-fill"></i> Enquire Now</button>
          </div>
          <div class="hero-points">
            <span><i class="bi bi-check-circle-fill"></i> Practical Learning</span>
            <span><i class="bi bi-check-circle-fill"></i> Career-Focused Courses</span>
            <span><i class="bi bi-check-circle-fill"></i> Experienced Guidance</span>
          </div>
        </div>

        <div class="hero-visual reveal" data-tilt-scene>
          <div class="hero-image-wrapper">
            <img src="<?= base_url('assets/images/hero/holographic-dashboard-hero.png') ?>"
              alt="Master Computer Institute technology and computer education dashboard"
              width="800" height="540" fetchpriority="high" />
            <div class="hero-floating-card hero-float-top">
              <div class="floating-icon"><i class="bi bi-laptop"></i></div>
              <div class="floating-content">
                <strong>100% Practical</strong>
                <small>Dedicated Computer Lab</small>
              </div>
            </div>
            <div class="hero-floating-card hero-float-bottom">
              <div class="floating-icon"><i class="bi bi-patch-check-fill"></i></div>
              <div class="floating-content">
                <strong>Certified Courses</strong>
                <small>Government Recognized</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Key Statistics -->
    <section class="stats">
      <div class="container stats-grid reveal">
        <div class="stat-item">
          <div class="stat-number"><i class="bi bi-pc-display-horizontal"></i></div>
          <div class="stat-label">Practical Computer Training</div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><i class="bi bi-mortarboard-fill"></i></div>
          <div class="stat-label">Career-Focused Education</div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><i class="bi bi-geo-alt-fill"></i></div>
          <div class="stat-label"><?= e($siteSettings['city'] ?? 'Nawabganj') ?>, UP - <?= e($siteSettings['pincode'] ?? '271303') ?></div>
        </div>
        <div class="stat-item">
          <div class="stat-number"><i class="bi bi-patch-check-fill"></i></div>
          <div class="stat-label">Skill Certifications</div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="section" id="about">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">About us</span>
          <h2 class="section-title">A trusted learning environment for modern career success</h2>
        </div>

        <div class="grid-2 reveal">
          <div class="about-text">
            <p><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?> is dedicated to helping students and professionals build practical digital skills in a structured, supportive learning environment. Our mission is to make technology learning accessible, relevant and career-focused through hands-on training and guided instruction.</p>
            <p>With a strong emphasis on real-world applications, our institute focuses on foundational computing skills, software proficiency, professional growth, and job-readiness. We maintain a learner-first culture that supports each student through every step of their educational journey.</p>

            <div class="director-box">
              <div class="director-avatar-placeholder"
                style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, #f59e0b, #d97706); display:flex; align-items:center; justify-content:center; color:#fff; font-size:26px; font-weight:700; flex-shrink:0;">
                SP
              </div>
              <div>
                <h4><?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></h4>
                <p>Director, <?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></p>
                <small style="color:var(--text-muted, #64748b);"><?= e($siteSettings['city'] ?? 'Nawabganj') ?>, <?= e($siteSettings['state'] ?? 'Uttar Pradesh') ?></small>
              </div>
            </div>

            <ul class="points-list">
              <li><i class="bi bi-check-circle-fill"></i> Focus on practical, career-oriented learning</li>
              <li><i class="bi bi-check-circle-fill"></i> Structured courses designed for beginners and professionals</li>
              <li><i class="bi bi-check-circle-fill"></i> Professional guidance from a dedicated team</li>
            </ul>
          </div>

          <div class="about-visual reveal">
            <img src="<?= base_url('assets/images/classroom/classroom-training-01.png') ?>"
              alt="Master Computer Institute classroom training with students studying" loading="lazy" />
          </div>
        </div>
      </div>
    </section>

    <!-- Dynamic Courses Section -->
    <section class="section section-alt" id="courses">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Popular courses</span>
          <h2 class="section-title">Professional courses designed for real-world outcomes</h2>
        </div>

        <div class="card-grid">
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
                <div class="price"><?= format_inr($course['fee']) ?></div>
                <div class="course-actions">
                  <a href="<?= base_url('course-details.php?id=' . $course['id']) ?>" class="btn btn-outline">View Details</a>
                  <button type="button" class="btn btn-primary" onclick="openEnquireModal('<?= addslashes($course['name']) ?>')">Enquire Now</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Center Location Section -->
    <section class="section" id="branches">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Center Location</span>
          <h2 class="section-title"><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></h2>
        </div>

        <div class="branch-grid" style="grid-template-columns: 1fr; max-width: 760px; margin: 0 auto;">
          <?php if ($mainBranch): ?>
            <article class="branch-card reveal">
              <div class="branch-header">
                <h3><?= e($mainBranch['name']) ?></h3>
                <span class="branch-badge"><?= e($mainBranch['tagline']) ?></span>
              </div>
              <ul>
                <li><i class="bi bi-geo-alt-fill"></i> <?= e($mainBranch['address']) ?></li>
                <li><i class="bi bi-person-badge-fill"></i> Director: <?= e($mainBranch['director_name']) ?></li>
                <li><i class="bi bi-telephone-fill"></i> Phone: <?= e($mainBranch['phone'] ?: $siteSettings['phone'] ?? '+91 9696525287') ?></li>
                <li><i class="bi bi-instagram"></i> <a href="<?= e($mainBranch['instagram']) ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($mainBranch['instagram']) ?></a></li>
                <li><i class="bi bi-globe"></i> <a href="<?= e($mainBranch['blog_url']) ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($mainBranch['blog_url']) ?></a></li>
                <li><i class="bi bi-bookmark-check-fill"></i> Category: Education / Computer Education</li>
              </ul>
              <div class="map-placeholder"><?= e($mainBranch['name']) ?> • <?= e($mainBranch['city']) ?> (<?= e($mainBranch['pincode']) ?>)</div>
              <a href="<?= base_url('contact.php') ?>" class="btn btn-primary">Contact Center</a>
            </article>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section section-alt">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Why choose us</span>
          <h2 class="section-title">Built around student growth and practical learning</h2>
        </div>

        <div class="feature-grid">
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
            <h3>Experienced Trainers</h3>
            <p>Learn under guidance that focuses on clarity, discipline, and results.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-laptop-fill"></i></div>
            <h3>Practical Learning</h3>
            <p>Every topic is connected to live use cases, tools and real tasks.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-pc-display"></i></div>
            <h3>Modern Computer Labs</h3>
            <p>Train in well-maintained labs with the right hardware and systems.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-briefcase-fill"></i></div>
            <h3>Industry Relevant Courses</h3>
            <p>Course content is aligned with practical workplace skills and trends.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <h3>Flexible Batches</h3>
            <p>Options for different schedules help students and professionals learn better.</p>
          </article>
          <article class="feature-card reveal">
            <div class="feature-icon"><i class="bi bi-award-fill"></i></div>
            <h3>Certificate Programs</h3>
            <p>Earn a recognized certificate that supports your skill-building journey.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- Dynamic Gallery Section -->
    <section class="section" id="gallerySection">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Gallery</span>
          <h2 class="section-title">Campus life, activities, and learning moments</h2>
        </div>

        <div class="gallery-grid">
          <?php foreach ($galleryItems as $g): ?>
            <div class="gallery-item reveal" data-gallery-category="<?= e($g['category']) ?>">
              <img src="<?= base_url(e($g['image_path'])) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy" />
              <div class="gallery-overlay"><?= ucfirst(e($g['category'])) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:24px;">
          <a href="<?= base_url('gallery.php') ?>" class="btn btn-outline">View Complete Gallery</a>
        </div>
      </div>
    </section>

    <!-- Institute Pillars Carousel -->
    <section class="section section-alt">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Institute Pillars</span>
          <h2 class="section-title">Our Learning Environment</h2>
        </div>

        <div class="testimonial-wrap swiper testimonial-swiper reveal">
          <div class="swiper-wrapper">
            <article class="testimonial-item swiper-slide">
              <p>“Dedicated hands-on computer sessions designed to build confidence in core digital and office technologies.”</p>
              <div class="testimonial-author">
                <div style="width:48px; height:48px; border-radius:50%; background:var(--primary, #1e3a8a); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="bi bi-laptop"></i>
                </div>
                <div><strong>Practical Lab Sessions</strong><br />Interactive Training</div>
              </div>
            </article>
            <article class="testimonial-item swiper-slide">
              <p>“Step-by-step guidance from foundational computer literacy to professional skill applications.”</p>
              <div class="testimonial-author">
                <div style="width:48px; height:48px; border-radius:50%; background:var(--primary, #1e3a8a); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="bi bi-people"></i>
                </div>
                <div><strong>Guided Mentorship</strong><br />Personalized Attention</div>
              </div>
            </article>
            <article class="testimonial-item swiper-slide">
              <p>“Focused on real-world capabilities to support academic, personal, and career-oriented digital proficiency.”</p>
              <div class="testimonial-author">
                <div style="width:48px; height:48px; border-radius:50%; background:var(--primary, #1e3a8a); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="bi bi-award"></i>
                </div>
                <div><strong>Skill Certification</strong><br />Course Completion</div>
              </div>
            </article>
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
      </div>
    </section>

    <!-- Final CTA Panel with Enquire Now -->
    <section class="section-sm">
      <div class="container">
        <div class="cta-panel reveal">
          <div>
            <h3>Ready to begin your learning journey?</h3>
            <p>Enquire today and explore the right course for your career goals.</p>
          </div>
          <button type="button" class="btn btn-light" onclick="openEnquireModal()">Enquire Now</button>
        </div>
      </div>
    </section>

    <!-- Online Admission Enquiry Section -->
    <section class="section" id="admission">
      <div class="container contact-grid">
        <div class="form-shell reveal">
          <div class="section-heading" style="margin-bottom: 24px;">
            <span class="section-tag">Admission Enquiry</span>
            <h2 class="section-title">Apply online</h2>
          </div>

          <form id="homeAdmissionForm" onsubmit="handleHomeAdmission(event)" novalidate>
            <div class="form-grid">
              <div class="form-field">
                <label for="fullName">Full Name *</label>
                <input id="fullName" name="fullName" type="text" required placeholder="Enter your full name" />
              </div>
              <div class="form-field">
                <label for="dob">Date of Birth</label>
                <input id="dob" name="dob" type="date" />
              </div>
              <div class="form-field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                  <option value="">Select</option>
                  <option>Male</option>
                  <option>Female</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-field">
                <label for="mobile">Mobile Number *</label>
                <input id="mobile" name="mobile" type="tel" required placeholder="10-digit mobile number" />
              </div>
              <div class="form-field">
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" required placeholder="name@example.com" />
              </div>
              <div class="form-field">
                <label for="city">City *</label>
                <input id="city" name="city" type="text" required placeholder="Your city" />
              </div>
              <div class="form-field full">
                <label for="address">Address *</label>
                <textarea id="address" name="address" rows="3" required placeholder="Enter your full residential address"></textarea>
              </div>
              <div class="form-field">
                <label for="courseSelect">Course *</label>
                <select id="courseSelect" name="course" required>
                  <option value="">Select course</option>
                  <?php foreach ($courses as $c): ?>
                    <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="branchSelect">Center</label>
                <select id="branchSelect" name="branch" required>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= e($b['name']) ?>"><?= e($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="qualification">Education Qualification *</label>
                <input id="qualification" name="qualification" type="text" required placeholder="e.g. 10th / 12th / Graduate" />
              </div>
              <div class="form-field">
                <label for="batchSelect">Preferred Batch *</label>
                <select id="batchSelect" name="batch" required>
                  <option value="Morning">Morning</option>
                  <option value="Evening">Evening</option>
                  <option value="Weekend">Weekend</option>
                </select>
              </div>
              <div class="form-field full">
                <label for="admissionMessage">Message (Optional)</label>
                <textarea id="admissionMessage" name="message" rows="3" placeholder="Tell us about your learning goals"></textarea>
              </div>
            </div>

            <div class="form-actions" style="margin-top:20px;">
              <button type="submit" id="homeAdmSubmitBtn" class="btn btn-primary">Enquire Now</button>
              <button type="reset" class="btn btn-outline">Reset</button>
            </div>
            <div class="form-message" id="homeAdmMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>
          </form>
        </div>

        <aside class="contact-card reveal">
          <h3>Institute Information</h3>
          <ul class="contact-list">
            <li><i class="bi bi-geo-alt-fill"></i> <?= e($siteSettings['address'] ?? 'Center near Tripathi Hospital, Nawabganj, Uttar Pradesh - 271303, India') ?></li>
            <li><i class="bi bi-person-fill"></i> Director: <?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></li>
            <li><i class="bi bi-telephone-fill"></i> Helpline: <?= e($siteSettings['phone'] ?? '+91 9696525287') ?></li>
            <li><i class="bi bi-instagram"></i> <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($siteSettings['instagram_handle'] ?? '@master.computer1') ?></a></li>
            <li><i class="bi bi-globe"></i> <a href="<?= e($siteSettings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($siteSettings['blog_url'] ?? 'mastercomputerinstitute.blogspot.com') ?></a></li>
          </ul>
          <div class="map-placeholder" style="margin-top: 24px;"><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?> • Nawabganj, UP</div>
        </aside>
      </div>
    </section>

    <!-- Certificate Verification Section -->
    <section class="section section-alt" id="certificate-verification">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Certificate verification</span>
          <h2 class="section-title">Verify a certificate online</h2>
        </div>

        <div class="verification-box reveal">
          <form class="form-shell" onsubmit="handleCertificateVerify(event)" novalidate>
            <div class="form-field">
              <label for="homeCertNumber">Enter Certificate Number</label>
              <input id="homeCertNumber" name="certificateNumber" type="text" required placeholder="e.g. MCI-CERT-10001" />
            </div>
            <div class="form-actions" style="justify-content:center; margin-top:18px;">
              <button type="submit" id="homeCertVerifyBtn" class="btn btn-primary">Verify Certificate</button>
            </div>
            <div class="form-message" id="homeCertMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>
          </form>

          <!-- Verified Results Box -->
          <div id="homeCertResultBox" style="display:none; margin-top:20px; background:#fff; border:1px solid var(--border); border-radius:12px; padding:24px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
              <div style="width:48px; height:48px; border-radius:50%; background:rgba(34, 197, 94, 0.15); color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:24px;">
                <i class="bi bi-patch-check-fill"></i>
              </div>
              <div>
                <h3 style="margin:0; font-size:18px; color:#16a34a;">Certificate Verified & Valid</h3>
                <small style="color:var(--text-muted);">Official student record registered in database</small>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; font-size:14px;">
              <div><strong>Certificate No:</strong> <span id="resCertNo"></span></div>
              <div><strong>Student Name:</strong> <span id="resStudentName"></span></div>
              <div><strong>Course:</strong> <span id="resCourseName"></span></div>
              <div><strong>Duration:</strong> <span id="resDuration"></span></div>
              <div><strong>Issue Date:</strong> <span id="resIssueDate"></span></div>
              <div><strong>Grade / Performance:</strong> <span id="resGrade"></span></div>
              <div style="grid-column:1/-1;"><strong>Issuing Center:</strong> <span id="resCenter"></span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form Section -->
    <section class="section" id="contact">
      <div class="container">
        <div class="section-heading reveal">
          <span class="section-tag">Contact</span>
          <h2 class="section-title">Get in touch with us</h2>
        </div>

        <div class="contact-grid">
          <form class="form-shell reveal" onsubmit="handleHomeContact(event)" novalidate>
            <div class="form-grid">
              <div class="form-field">
                <label for="contactName">Name *</label>
                <input id="contactName" name="contactName" type="text" required placeholder="Your name" />
              </div>
              <div class="form-field">
                <label for="contactEmail">Email *</label>
                <input id="contactEmail" name="contactEmail" type="email" required placeholder="your@email.com" />
              </div>
              <div class="form-field">
                <label for="contactMobile">Mobile *</label>
                <input id="contactMobile" name="contactMobile" type="tel" required placeholder="+91 98765 43210" />
              </div>
              <div class="form-field">
                <label for="contactSubject">Subject *</label>
                <input id="contactSubject" name="contactSubject" type="text" required placeholder="How can we help?" />
              </div>
              <div class="form-field full">
                <label for="contactMessage">Message *</label>
                <textarea id="contactMessage" name="contactMessage" rows="5" required placeholder="Write your message here"></textarea>
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" id="homeContactBtn" class="btn btn-primary">Send Message</button>
            </div>
            <div class="form-message" id="homeContactMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>
          </form>

          <aside class="contact-card reveal">
            <h3>Center details</h3>
            <ul class="contact-list">
              <li><i class="bi bi-geo-alt-fill"></i> <?= e($siteSettings['address'] ?? 'Nawabganj, Uttar Pradesh - 271303, India') ?></li>
              <li><i class="bi bi-hospital-fill"></i> Center near Tripathi Hospital</li>
              <li><i class="bi bi-person-fill"></i> Director: <?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></li>
              <li><i class="bi bi-telephone-fill"></i> Phone: <?= e($siteSettings['phone'] ?? '+91 9696525287') ?></li>
              <li><i class="bi bi-instagram"></i> <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($siteSettings['instagram_handle'] ?? '@master.computer1') ?></a></li>
              <li><i class="bi bi-globe"></i> <a href="<?= e($siteSettings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;"><?= e($siteSettings['blog_url'] ?? 'mastercomputerinstitute.blogspot.com') ?></a></li>
            </ul>
          </aside>
        </div>
      </div>
    </section>
  </main>

  <script>
    // AJAX for Online Admission Enquiry Form on Homepage
    async function handleHomeAdmission(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('homeAdmSubmitBtn');
      const msg = document.getElementById('homeAdmMsg');

      const name = form.fullName.value.trim();
      const mobile = form.mobile.value.trim();
      const email = form.email.value.trim();
      const course = form.course.value;
      const address = form.address.value.trim();

      if (!name || !mobile || !email || !course || !address) {
        msg.className = 'form-message error';
        msg.textContent = 'Please fill out all required fields.';
        msg.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Submitting...';

      try {
        const formData = new FormData(form);
        const res = await fetch('<?= base_url('api/admission.php') ?>', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();

        if (!json.success) throw new Error(json.message);

        msg.className = 'form-message success';
        msg.textContent = json.message;
        msg.style.display = 'block';
        form.reset();
      } catch (err) {
        msg.className = 'form-message error';
        msg.textContent = err.message;
        msg.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Enquire Now';
      }
    }

    // AJAX for Certificate Verification on Homepage
    async function handleCertificateVerify(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('homeCertVerifyBtn');
      const msg = document.getElementById('homeCertMsg');
      const resultBox = document.getElementById('homeCertResultBox');
      const certNo = form.certificateNumber.value.trim();

      if (!certNo) {
        msg.className = 'form-message error';
        msg.textContent = 'Please enter a certificate number.';
        msg.style.display = 'block';
        resultBox.style.display = 'none';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Verifying...';
      msg.style.display = 'none';
      resultBox.style.display = 'none';

      try {
        const res = await fetch(`<?= base_url('api/certificate.php') ?>?certificateNumber=${encodeURIComponent(certNo)}`);
        const json = await res.json();

        if (!json.success) throw new Error(json.message);

        const data = json.data;
        document.getElementById('resCertNo').textContent = data.certificate_no;
        document.getElementById('resStudentName').textContent = data.student_name;
        document.getElementById('resCourseName').textContent = data.course_name;
        document.getElementById('resDuration').textContent = data.duration;
        document.getElementById('resIssueDate').textContent = data.issue_date;
        document.getElementById('resGrade').textContent = data.grade;
        document.getElementById('resCenter').textContent = data.center_name;

        resultBox.style.display = 'block';
      } catch (err) {
        msg.className = 'form-message error';
        msg.textContent = err.message;
        msg.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Verify Certificate';
      }
    }

    // AJAX for Contact Form on Homepage
    async function handleHomeContact(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('homeContactBtn');
      const msg = document.getElementById('homeContactMsg');

      const name = form.contactName.value.trim();
      const email = form.contactEmail.value.trim();
      const message = form.contactMessage.value.trim();

      if (!name || !email || !message) {
        msg.className = 'form-message error';
        msg.textContent = 'Please fill out your name, email and message.';
        msg.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Sending...';

      try {
        const formData = new FormData(form);
        const res = await fetch('<?= base_url('api/contact.php') ?>', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();

        if (!json.success) throw new Error(json.message);

        msg.className = 'form-message success';
        msg.textContent = json.message;
        msg.style.display = 'block';
        form.reset();
      } catch (err) {
        msg.className = 'form-message error';
        msg.textContent = err.message;
        msg.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send Message';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
