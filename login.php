<?php
/**
 * Master Computer Institute - Student Login Page
 */

$activePage = 'login';
$pageTitle = 'Student Login | Master Computer Institute';
$pageDesc = 'Student portal access for Master Computer Institute learners in Nawabganj.';
require_once __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="section">
      <div class="container">
        <div class="login-shell reveal" style="max-width:480px; margin:0 auto;">
          <div style="text-align:center; margin-bottom:20px;">
            <div style="width:54px; height:54px; border-radius:50%; background:rgba(30, 58, 138, 0.1); color:var(--primary, #1e3a8a); display:flex; align-items:center; justify-content:center; font-size:26px; margin:0 auto 12px;">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <h3 style="margin:0 0 6px;">Student Portal Login</h3>
            <p style="color:var(--text-muted); font-size:13px; margin:0;">Access your learning schedule and course certificates</p>
          </div>

          <form id="studentLoginForm" onsubmit="handleStudentLogin(event)" novalidate>
            <div class="form-grid" style="grid-template-columns: 1fr;">
              <div class="form-field">
                <label for="studentUser">Student ID or Email</label>
                <input id="studentUser" name="studentUser" type="text" required placeholder="e.g. demo.student@example.com" />
              </div>
              <div class="form-field">
                <label for="studentPassword">Password</label>
                <input id="studentPassword" name="studentPassword" type="password" required placeholder="Enter password" />
              </div>
            </div>

            <div class="form-actions" style="margin-top:20px;">
              <button type="submit" id="studentLoginBtn" class="btn btn-primary" style="width:100%;">Sign In as Student</button>
            </div>

            <div class="form-message" id="studentLoginMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>

            <div class="auth-links" style="margin-top:18px; display:flex; justify-content:space-between; font-size:13px;">
              <a href="<?= base_url('certificate-verification.php') ?>"><i class="bi bi-patch-check"></i> Verify Certificate</a>
              <a href="<?= base_url('admin/login.php') ?>" style="color:var(--admin-accent, #f59e0b); font-weight:600;"><i class="bi bi-shield-lock"></i> Admin Login</a>
            </div>
          </form>

          <div style="margin-top:24px; padding:14px; background:#f8fafc; border-radius:8px; border:1px solid var(--border); font-size:12px; color:var(--text-muted);">
            <strong><i class="bi bi-info-circle"></i> Institute Notice:</strong> Newly enrolled students receive their portal credentials directly upon center admission confirmation. If you haven't received your credentials yet, please contact the center office.
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    function handleStudentLogin(e) {
      e.preventDefault();
      const form = e.target;
      const user = form.studentUser.value.trim();
      const pass = form.studentPassword.value.trim();
      const msg = document.getElementById('studentLoginMsg');

      if (!user || !pass) {
        msg.className = 'form-message error';
        msg.textContent = 'Please enter both your student ID/email and password.';
        msg.style.display = 'block';
        return;
      }

      // Demo student credentials
      if (user.toLowerCase() === 'demo.student@example.com' || user.toLowerCase() === 'student') {
        msg.className = 'form-message success';
        msg.textContent = 'Student authentication verified. Welcome to Master Computer Institute student resources!';
        msg.style.display = 'block';
      } else {
        msg.className = 'form-message error';
        msg.textContent = 'Invalid student credentials. Please check your Student ID or contact the institute office.';
        msg.style.display = 'block';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
