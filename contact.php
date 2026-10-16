<?php
/**
 * Master Computer Institute - Contact Us Page
 */

$activePage = 'contact';
$pageTitle = 'Contact Us | Master Computer Institute';
$pageDesc = 'Contact Master Computer Institute for computer course admissions, center details, and student counseling in Nawabganj, UP.';
require_once __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Contact</span></div>
        <h1>Contact Us</h1>
        <p>Reach out to Master Computer Institute for computer courses, training, and guidance.</p>
      </div>
    </section>

    <section class="section">
      <div class="container contact-grid">
        <form class="form-shell reveal" onsubmit="handleContactPageSubmit(event)" novalidate>
          <div class="form-grid">
            <div class="form-field">
              <label for="pageContactName">Your Name *</label>
              <input id="pageContactName" name="contactName" type="text" required placeholder="Your name" />
            </div>
            <div class="form-field">
              <label for="pageContactEmail">Email Address *</label>
              <input id="pageContactEmail" name="contactEmail" type="email" required placeholder="your@email.com" />
            </div>
            <div class="form-field">
              <label for="pageContactMobile">Mobile Number *</label>
              <input id="pageContactMobile" name="contactMobile" type="tel" required placeholder="+91 98765 43210" />
            </div>
            <div class="form-field">
              <label for="pageContactSubject">Subject *</label>
              <input id="pageContactSubject" name="contactSubject" type="text" required placeholder="How can we help?" />
            </div>
            <div class="form-field full">
              <label for="pageContactMessage">Message *</label>
              <textarea id="pageContactMessage" name="contactMessage" rows="5" required placeholder="Write your message or inquiry here"></textarea>
            </div>
          </div>
          <div class="form-actions" style="margin-top:20px;">
            <button type="submit" id="contactPageSubmitBtn" class="btn btn-primary" style="padding:12px 32px;">Send Message</button>
          </div>
          <div class="form-message" id="contactPageMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>
        </form>

        <aside class="contact-card reveal">
          <h3>Center details</h3>
          <ul class="contact-list">
            <li><i class="bi bi-geo-alt-fill"></i> <?= e($siteSettings['address'] ?? 'Center near Tripathi Hospital, Nawabganj, Uttar Pradesh - 271303, India') ?></li>
            <li><i class="bi bi-hospital-fill"></i> Center near Tripathi Hospital</li>
            <li><i class="bi bi-person-fill"></i> Director: <?= e($siteSettings['director_name'] ?? 'Shivam Pandey') ?></li>
            <li><i class="bi bi-telephone-fill"></i> Helpline: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $siteSettings['phone'] ?? '+919696525287') ?>" style="color:inherit;"><?= e($siteSettings['phone'] ?? '+91 9696525287') ?></a></li>
            <li><i class="bi bi-instagram"></i> Instagram: <a href="<?= e($siteSettings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit; text-decoration:underline;"><?= e($siteSettings['instagram_handle'] ?? '@master.computer1') ?></a></li>
            <li><i class="bi bi-globe"></i> Blog: <a href="<?= e($siteSettings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" target="_blank" rel="noopener noreferrer" style="color:inherit; text-decoration:underline;"><?= e($siteSettings['blog_url'] ?? 'mastercomputerinstitute.blogspot.com') ?></a></li>
            <li><i class="bi bi-bookmark-check-fill"></i> Category: Education / Computer Education</li>
          </ul>
        </aside>
      </div>
    </section>
  </main>

  <script>
    async function handleContactPageSubmit(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('contactPageSubmitBtn');
      const msg = document.getElementById('contactPageMsg');

      const name = form.contactName.value.trim();
      const email = form.contactEmail.value.trim();
      const mobile = form.contactMobile.value.trim();
      const message = form.contactMessage.value.trim();

      if (!name || !email || !mobile || !message) {
        msg.className = 'form-message error';
        msg.textContent = 'Please fill out all required fields.';
        msg.style.display = 'block';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Sending Message...';

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
