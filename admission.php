<?php
/**
 * Master Computer Institute - Online Admission Enquiry Page
 */

$activePage = 'admission';
$pageTitle = 'Online Admission Enquiry | Master Computer Institute';
$pageDesc = 'Apply online for computer courses at Master Computer Institute. Fill the admission enquiry form for CCC, DCA, ADCA, Tally Prime GST, and Excel.';
require_once __DIR__ . '/includes/header.php';

$courses = get_active_courses($pdo);
$branches = get_active_branches($pdo);
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Admissions</span></div>
        <h1>Online Admission Enquiry</h1>
        <p>Begin your computer education with a simple and guided application process.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="promotion-grid reveal">
          <figure class="promotion-image">
            <img src="<?= base_url('assets/images/gallery/gallery-admissions-promotion.png') ?>"
              alt="Master Computer Institute admissions information poster" loading="lazy" />
            <figcaption>Admissions and course information from Master Computer Institute.</figcaption>
          </figure>
          <figure class="promotion-image">
            <img src="<?= base_url('assets/images/gallery/gallery-ccc-promotion.png') ?>"
              alt="Master Computer Institute CCC training information poster" loading="lazy" />
            <figcaption>CCC training information from Master Computer Institute.</figcaption>
          </figure>
        </div>

        <div class="form-shell reveal" style="max-width: 980px; margin: 0 auto;">
          <div style="margin-bottom: 24px;">
            <span class="section-tag">Admission Enquiry</span>
            <h2 class="section-title" style="margin-top:6px;">Submit your admission application</h2>
            <p style="color:var(--text-muted); font-size:14px; margin-top:4px;">Fill out the details below. Our admissions counselor will contact you to confirm batch timings and seat availability.</p>
          </div>

          <form id="onlineAdmissionPageForm" onsubmit="handleAdmissionPageSubmit(event)" novalidate>
            <div class="form-grid">
              <div class="form-field">
                <label for="admFullName">Full Name *</label>
                <input id="admFullName" name="fullName" type="text" required placeholder="Enter your full name" />
              </div>
              <div class="form-field">
                <label for="admDob">Date of Birth</label>
                <input id="admDob" name="dob" type="date" />
              </div>
              <div class="form-field">
                <label for="admGender">Gender</label>
                <select id="admGender" name="gender">
                  <option value="">Select Gender</option>
                  <option>Male</option>
                  <option>Female</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-field">
                <label for="admMobile">Mobile Number *</label>
                <input id="admMobile" name="mobile" type="tel" required placeholder="10-digit mobile number" />
              </div>
              <div class="form-field">
                <label for="admEmail">Email Address *</label>
                <input id="admEmail" name="email" type="email" required placeholder="name@example.com" />
              </div>
              <div class="form-field">
                <label for="admCity">City / Town *</label>
                <input id="admCity" name="city" type="text" required placeholder="e.g. Nawabganj, Gonda" />
              </div>
              <div class="form-field full">
                <label for="admAddress">Full Address *</label>
                <textarea id="admAddress" name="address" rows="3" required placeholder="Enter your complete street or village address"></textarea>
              </div>
              <div class="form-field">
                <label for="admCourse">Course Applied *</label>
                <select id="admCourse" name="course" required>
                  <option value="">Select course</option>
                  <?php foreach ($courses as $c): ?>
                    <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?> (<?= format_inr($c['fee']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="admBranch">Center Location *</label>
                <select id="admBranch" name="branch" required>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= e($b['name']) ?>"><?= e($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="admQualification">Education Qualification *</label>
                <input id="admQualification" name="qualification" type="text" required placeholder="e.g. 10th / 12th / Graduate" />
              </div>
              <div class="form-field">
                <label for="admBatch">Preferred Batch *</label>
                <select id="admBatch" name="batch" required>
                  <option value="Morning">Morning Batch</option>
                  <option value="Evening">Evening Batch</option>
                  <option value="Weekend">Weekend Batch</option>
                </select>
              </div>
              <div class="form-field full">
                <label for="admMessage">Message or Specific Requests</label>
                <textarea id="admMessage" name="message" rows="3" placeholder="Tell us about your learning goals or any questions"></textarea>
              </div>
            </div>

            <div class="form-actions" style="margin-top:24px;">
              <button type="submit" id="admPageSubmitBtn" class="btn btn-primary" style="padding:12px 28px;">
                <span>Enquire Now</span>
              </button>
              <button type="reset" class="btn btn-outline">Reset Form</button>
            </div>

            <div class="form-message" id="admPageMsg" style="display:none; margin-top:18px;" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script>
    async function handleAdmissionPageSubmit(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('admPageSubmitBtn');
      const msg = document.getElementById('admPageMsg');

      const name = form.fullName.value.trim();
      const mobile = form.mobile.value.trim();
      const email = form.email.value.trim();
      const course = form.course.value;
      const address = form.address.value.trim();

      if (!name || !mobile || !email || !course || !address) {
        msg.className = 'form-message error';
        msg.textContent = 'Please fill out all required fields marked with (*).';
        msg.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting Application...';

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
        btn.innerHTML = '<span>Enquire Now</span>';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
