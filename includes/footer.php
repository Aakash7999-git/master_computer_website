<?php
/**
 * Master Computer Institute - Public Site Footer
 * Includes "Admin Dashboard" footer link, Enquire Now modal, and AJAX integration
 */
$footerCourses = get_active_courses($pdo);
$footerBranches = get_active_branches($pdo);
?>
  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <div class="footer-brand" style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
          <img src="<?= base_url('assets/logos/mci-logo.svg') ?>" alt="Master Computer Institute Logo" />
          <h4><?= e($siteSettings['institute_name'] ?? 'Master Computer Institute') ?></h4>
        </div>
        <p>Professional computer education and career-ready skill development in Nawabganj, UP.</p>
        <div style="margin-top:14px; font-size:13px; color:var(--text-muted, #94a3b8);">
          <i class="bi bi-clock"></i> <?= e($siteSettings['timing'] ?? 'Mon - Sat: 8:00 AM - 6:00 PM') ?>
        </div>
      </div>

      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= base_url('index.php') ?>">Home</a></li>
          <li><a href="<?= base_url('about.php') ?>">About</a></li>
          <li><a href="<?= base_url('courses.php') ?>">Courses</a></li>
          <li><a href="<?= base_url('gallery.php') ?>">Gallery</a></li>
          <li><a href="<?= base_url('admission.php') ?>">Admissions</a></li>
          <li><a href="<?= base_url('branches.php') ?>">Branches</a></li>
          <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>
        </ul>
      </div>

      <div>
        <h4>Student & Admin</h4>
        <ul>
          <li><a href="<?= base_url('login.php') ?>">Student Login</a></li>
          <li><a href="<?= base_url('certificate-verification.php') ?>">Certificate Verification</a></li>
          <li><a href="<?= base_url('admin/login.php') ?>" style="color:var(--admin-accent, #f59e0b); font-weight:600;"><i class="bi bi-shield-lock"></i> Admin Dashboard</a></li>
        </ul>
      </div>

      <div>
        <h4>Location</h4>
        <ul>
          <li><a href="<?= base_url('branches.php') ?>"><?= e($siteSettings['city'] ?? 'Nawabganj') ?> Center</a></li>
          <li><a href="<?= base_url('contact.php') ?>">Contact Us</a></li>
          <li><a href="tel:<?= preg_replace('/[^0-9+]/', '', $siteSettings['phone'] ?? '+91 9696525287') ?>"><i class="bi bi-telephone"></i> <?= e($siteSettings['phone'] ?? '+91 9696525287') ?></a></li>
        </ul>
      </div>
    </div>

    <div class="container footer-bottom">
      <div>© 2026 Master Computer Institute. All Rights Reserved.</div>
      <div>Director: <?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></div>
      <div class="socials">
        <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="<?= e($siteSettings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" target="_blank" rel="noopener noreferrer" aria-label="Official Blog">
          <i class="bi bi-globe"></i>
        </a>
      </div>
    </div>
  </footer>

  <!-- Gallery Lightbox Modal -->
  <div class="gallery-modal" aria-hidden="true">
    <button type="button" class="gallery-modal-close" aria-label="Close image">&times;</button>
    <button type="button" class="gallery-modal-control gallery-modal-prev" aria-label="Previous image">&#8249;</button>
    <img alt="Selected gallery" />
    <button type="button" class="gallery-modal-control gallery-modal-next" aria-label="Next image">&#8250;</button>
  </div>

  <!-- Global "Enquire Now" Modal -->
  <div class="mci-enquire-modal" id="enquireModal" aria-hidden="true" role="dialog" aria-labelledby="enquireModalTitle">
    <div class="mci-modal-dialog">
      <div class="mci-modal-header">
        <h3 id="enquireModalTitle"><i class="bi bi-chat-left-quote-fill" style="color:var(--primary, #1e3a8a);"></i> Course Enquiry</h3>
        <button type="button" class="mci-modal-close" onclick="closeEnquireModal()" aria-label="Close modal">&times;</button>
      </div>

      <div class="mci-modal-body">
        <p style="margin:0 0 18px; font-size:13px; color:var(--text-muted, #64748b);">
          Fill in your details below to enquire about courses, batch timings, and fee installment plans at Master Computer Institute.
        </p>

        <form id="publicEnquiryModalForm" onsubmit="submitEnquiryForm(event)" novalidate>
          <div class="form-grid">
            <div class="form-field full">
              <label for="modalEnqFullName">Your Full Name *</label>
              <input id="modalEnqFullName" name="fullName" type="text" required placeholder="Enter your full name" />
            </div>

            <div class="form-field">
              <label for="modalEnqMobile">Mobile Number *</label>
              <input id="modalEnqMobile" name="mobile" type="tel" required placeholder="10-digit mobile number" />
            </div>

            <div class="form-field">
              <label for="modalEnqEmail">Email Address</label>
              <input id="modalEnqEmail" name="email" type="email" placeholder="name@example.com" />
            </div>

            <div class="form-field full">
              <label for="modalEnqCourse">Selected Course *</label>
              <select id="modalEnqCourse" name="course" required>
                <option value="">-- Choose Course --</option>
                <?php foreach ($footerCourses as $fc): ?>
                  <option value="<?= e($fc['name']) ?>"><?= e($fc['name']) ?> (<?= e($fc['duration']) ?> - <?= format_inr($fc['fee']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-field">
              <label for="modalEnqBranch">Center Location</label>
              <select id="modalEnqBranch" name="branch">
                <?php foreach ($footerBranches as $fb): ?>
                  <option value="<?= e($fb['name']) ?>"><?= e($fb['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-field">
              <label for="modalEnqBatch">Preferred Batch</label>
              <select id="modalEnqBatch" name="batch">
                <option value="Morning">Morning Batch</option>
                <option value="Evening">Evening Batch</option>
                <option value="Weekend">Weekend Batch</option>
              </select>
            </div>

            <div class="form-field full">
              <label for="modalEnqMessage">Message or Questions</label>
              <textarea id="modalEnqMessage" name="message" rows="3" placeholder="Tell us what you would like to know..."></textarea>
            </div>
          </div>

          <div class="form-message" id="modalEnquiryMessage" style="display:none; margin:16px 0;" aria-live="polite"></div>

          <div class="form-actions" style="margin-top:20px;">
            <button type="submit" id="modalEnqSubmitBtn" class="btn btn-primary" style="width:100%; display:flex; justify-content:center; align-items:center; gap:8px;">
              <span>Submit Enquiry</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="<?= base_url('assets/vendor/swiper/swiper-bundle.min.js') ?>"></script>
  <script src="<?= base_url('js/main.js?v=5') ?>"></script>
  <script src="<?= base_url('js/animations.js') ?>"></script>

  <script>
    // Global Enquire Modal Script
    function openEnquireModal(courseName = '') {
      const modal = document.getElementById('enquireModal');
      const courseSelect = document.getElementById('modalEnqCourse');
      const msgBox = document.getElementById('modalEnquiryMessage');

      if (msgBox) {
        msgBox.style.display = 'none';
        msgBox.className = 'form-message';
      }

      if (courseSelect && courseName) {
        // Try matching course option
        let matched = false;
        for (let opt of courseSelect.options) {
          if (opt.value.toLowerCase().includes(courseName.toLowerCase()) || courseName.toLowerCase().includes(opt.value.toLowerCase())) {
            opt.selected = true;
            matched = true;
            break;
          }
        }
      }

      if (modal) {
        modal.classList.add('is-active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeEnquireModal() {
      const modal = document.getElementById('enquireModal');
      if (modal) {
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }
    }

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeEnquireModal();
    });

    document.getElementById('enquireModal')?.addEventListener('click', (e) => {
      if (e.target === document.getElementById('enquireModal')) {
        closeEnquireModal();
      }
    });

    // AJAX Form submission for modal
    async function submitEnquiryForm(e) {
      e.preventDefault();
      const form = e.target;
      const submitBtn = document.getElementById('modalEnqSubmitBtn');
      const msgBox = document.getElementById('modalEnquiryMessage');

      const name = form.fullName.value.trim();
      const mobile = form.mobile.value.trim();
      const course = form.course.value;

      if (!name) {
        showFormMsg(msgBox, 'Please enter your full name.', 'error');
        form.fullName.focus();
        return;
      }
      const cleanMobile = mobile.replace(/[^0-9]/g, '');
      if (cleanMobile.length < 10) {
        showFormMsg(msgBox, 'Please enter a valid 10-digit mobile number.', 'error');
        form.mobile.focus();
        return;
      }
      if (!course) {
        showFormMsg(msgBox, 'Please select a course.', 'error');
        form.course.focus();
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

      try {
        const formData = new FormData(form);
        formData.append('source_page', window.location.pathname.split('/').pop() || 'website');

        const res = await fetch('<?= base_url('api/enquiry.php') ?>', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();

        if (!json.success) {
          throw new Error(json.message || 'Submission failed.');
        }

        showFormMsg(msgBox, json.message, 'success');
        form.reset();

        setTimeout(() => {
          closeEnquireModal();
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<span>Submit Enquiry</span>';
        }, 3000);

      } catch (err) {
        showFormMsg(msgBox, err.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Submit Enquiry</span>';
      }
    }

    function showFormMsg(el, msg, type) {
      if (!el) return;
      el.className = `form-message ${type}`;
      el.textContent = msg;
      el.style.display = 'block';
    }
  </script>
</body>
</html>
