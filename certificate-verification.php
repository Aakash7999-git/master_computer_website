<?php
/**
 * Master Computer Institute - Public Certificate Verification Page
 */

$activePage = 'certificate-verification';
$pageTitle = 'Certificate Verification | Master Computer Institute';
$pageDesc = 'Verify official certificate issued by Master Computer Institute Nawabganj using registered certificate number.';
require_once __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="breadcrumbs"><a href="<?= base_url('index.php') ?>">Home</a> / <span>Certificate Verification</span></div>
        <h1>Certificate Verification</h1>
        <p>Validate an official certificate issued by Master Computer Institute, Nawabganj.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="verification-box reveal" style="max-width: 680px; margin: 0 auto;">
          <form class="form-shell" onsubmit="verifyCertificatePage(event)" novalidate>
            <div class="form-field">
              <label for="pageCertNumber" style="font-weight:600; font-size:15px;">Enter Official Certificate Number</label>
              <input id="pageCertNumber" name="certificateNumber" type="text" required placeholder="e.g. MCI-CERT-10001" style="font-size:16px; text-transform:uppercase;" />
              <small style="color:var(--text-muted); display:block; margin-top:6px;">Sample demo certificate: <code>MCI-CERT-10001</code></small>
            </div>
            <div class="form-actions" style="justify-content:center; margin-top:20px;">
              <button type="submit" id="certPageBtn" class="btn btn-primary" style="padding:12px 32px;">Verify Certificate</button>
            </div>
            <div class="form-message" id="certPageMsg" style="display:none; margin-top:16px;" aria-live="polite"></div>
          </form>

          <!-- Verified Result Card -->
          <div id="certDetailsCard" style="display:none; margin-top:28px; background:#fff; border:1px solid #22c55e; border-radius:14px; padding:28px; box-shadow:0 10px 25px rgba(34, 197, 94, 0.1);">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:16px;">
              <div style="width:54px; height:54px; border-radius:50%; background:rgba(34, 197, 94, 0.15); color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0;">
                <i class="bi bi-patch-check-fill"></i>
              </div>
              <div>
                <h3 style="margin:0 0 4px; font-size:20px; color:#15803d;">Authentic Certificate Verified</h3>
                <span style="font-size:13px; color:var(--text-muted);">Registered in Master Computer Institute Database</span>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; font-size:14px;">
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Certificate Number</span>
                <strong id="cardCertNo" style="font-family:monospace; font-size:15px; color:var(--primary, #1e3a8a);"></strong>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Student Name</span>
                <strong id="cardStudentName" style="font-size:15px;"></strong>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Course Completed</span>
                <strong id="cardCourseName"></strong>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Course Duration</span>
                <span id="cardDuration"></span>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Issue Date</span>
                <span id="cardIssueDate"></span>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:12px; display:block;">Grade / Performance</span>
                <strong id="cardGrade" style="color:#15803d;"></strong>
              </div>
              <div style="grid-column: 1/-1;">
                <span style="color:var(--text-muted); font-size:12px; display:block;">Issuing Center</span>
                <span id="cardCenter"></span>
              </div>
            </div>

            <div style="margin-top:20px; padding-top:14px; border-top:1px dashed var(--border); display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted);">
              <span><i class="bi bi-shield-lock"></i> Secure Verification System</span>
              <span id="cardTimestamp"></span>
            </div>
          </div>

          <div class="verification-info" style="margin-top: 28px; text-align: center; color: var(--text-muted, #64748b);">
            <p><i class="bi bi-shield-check"></i> Enter your registered certificate number above to verify student certification status with Master Computer Institute.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    async function verifyCertificatePage(e) {
      e.preventDefault();
      const form = e.target;
      const btn = document.getElementById('certPageBtn');
      const msg = document.getElementById('certPageMsg');
      const card = document.getElementById('certDetailsCard');
      const certNo = form.certificateNumber.value.trim();

      if (!certNo) {
        msg.className = 'form-message error';
        msg.textContent = 'Please enter a certificate number.';
        msg.style.display = 'block';
        card.style.display = 'none';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Verifying with Database...';
      msg.style.display = 'none';
      card.style.display = 'none';

      try {
        const res = await fetch(`<?= base_url('api/certificate.php') ?>?certificateNumber=${encodeURIComponent(certNo)}`);
        const json = await res.json();

        if (!json.success) throw new Error(json.message);

        const d = json.data;
        document.getElementById('cardCertNo').textContent = d.certificate_no;
        document.getElementById('cardStudentName').textContent = d.student_name;
        document.getElementById('cardCourseName').textContent = d.course_name;
        document.getElementById('cardDuration').textContent = d.duration;
        document.getElementById('cardIssueDate').textContent = d.issue_date;
        document.getElementById('cardGrade').textContent = d.grade;
        document.getElementById('cardCenter').textContent = d.center_name;
        document.getElementById('cardTimestamp').textContent = 'Verified: ' + d.verification_ts;

        card.style.display = 'block';
      } catch (err) {
        msg.className = 'form-message error';
        msg.textContent = err.message;
        msg.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Verify Certificate';
      }
    }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
