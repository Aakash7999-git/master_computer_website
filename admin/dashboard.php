<?php
/**
 * Master Computer Institute - Admin Dashboard Overview
 */

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// Live statistics directly from database
$totalEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$websiteEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE source = 'Website'")->fetchColumn();
$offlineEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE source = 'Offline'")->fetchColumn();
$newEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
$followupEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Follow-up'")->fetchColumn();
$convertedEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Converted'")->fetchColumn();

$totalAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions")->fetchColumn();
$websiteAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE source = 'Website'")->fetchColumn();
$offlineAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE source = 'Offline'")->fetchColumn();
$newAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'New'")->fetchColumn();

$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();

$activeCourses = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
$activeBranches = (int)$pdo->query("SELECT COUNT(*) FROM branches WHERE status = 'active'")->fetchColumn();
$unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")->fetchColumn();
$validCerts = (int)$pdo->query("SELECT COUNT(*) FROM certificates WHERE status = 'Valid'")->fetchColumn();

// Fetch active courses and branches for walk-in modals
$coursesList = $pdo->query("SELECT name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
$branchesList = $pdo->query("SELECT name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Recent enquiries
$stmtRecentEnq = $pdo->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 5");
$recentEnquiries = $stmtRecentEnq->fetchAll() ?: [];

// Recent admissions
$stmtRecentAdm = $pdo->query("SELECT * FROM admissions ORDER BY id DESC LIMIT 5");
$recentAdmissions = $stmtRecentAdm->fetchAll() ?: [];
?>

<!-- Statistics Overview -->
<div class="admin-stats-grid">
  <!-- Total Enquiries -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-blue">
      <i class="bi bi-chat-left-quote-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Total Enquiries</h4>
      <div class="stat-value"><?= number_format($totalEnquiries) ?></div>
      <div style="font-size:12px; margin-top:4px; display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
        <span class="badge-source badge-source-website" style="font-size:10px; padding:1px 6px;"><i class="bi bi-globe"></i> <?= $websiteEnquiries ?> Web</span>
        <span class="badge-source badge-source-offline" style="font-size:10px; padding:1px 6px;"><i class="bi bi-building"></i> <?= $offlineEnquiries ?> Offline</span>
        <span style="color:var(--admin-primary-light); font-weight:600;">• <?= $newEnquiries ?> New</span>
      </div>
    </div>
  </div>

  <!-- Admission Submissions -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-purple">
      <i class="bi bi-mortarboard-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Admissions Registered</h4>
      <div class="stat-value"><?= number_format($totalAdmissions) ?></div>
      <div style="font-size:12px; margin-top:4px; display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
        <span class="badge-source badge-source-website" style="font-size:10px; padding:1px 6px;"><i class="bi bi-globe"></i> <?= $websiteAdmissions ?> Web</span>
        <span class="badge-source badge-source-offline" style="font-size:10px; padding:1px 6px;"><i class="bi bi-building"></i> <?= $offlineAdmissions ?> Offline</span>
        <span style="color:#8b5cf6; font-weight:600;">• <?= $newAdmissions ?> New</span>
      </div>
    </div>
  </div>

  <!-- Enrolled Students -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-green">
      <i class="bi bi-people-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Enrolled Students</h4>
      <div class="stat-value"><?= number_format($totalStudents) ?></div>
      <small style="color:var(--admin-success); font-weight:600;"><?= $activeStudents ?> Currently Active Learners</small>
    </div>
  </div>

  <!-- Follow-ups Pending -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-amber">
      <i class="bi bi-clock-history"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Pending Follow-ups</h4>
      <div class="stat-value"><?= number_format($followupEnquiries) ?></div>
      <small style="color:var(--admin-warning); font-weight:600;"><?= $convertedEnquiries ?> Converted to Admission</small>
    </div>
  </div>

  <!-- Active Courses & Branches -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-indigo">
      <i class="bi bi-journal-code"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Active Courses</h4>
      <div class="stat-value"><?= number_format($activeCourses) ?></div>
      <small style="color:var(--admin-primary); font-weight:600;"><?= $activeBranches ?> Training Centers</small>
    </div>
  </div>

  <!-- Contact Inquiries -->
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-red">
      <i class="bi bi-envelope-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Unread Messages</h4>
      <div class="stat-value"><?= number_format($unreadMessages) ?></div>
      <small style="color:var(--admin-danger); font-weight:600;">Website Contact Queries</small>
    </div>
  </div>
</div>

<!-- Quick Action Strip -->
<div style="background:#fff; border:1px solid var(--admin-border); border-radius:12px; padding:16px 24px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
  <div>
    <strong><i class="bi bi-lightning-charge-fill" style="color:var(--admin-accent);"></i> Quick Actions:</strong>
    <span style="color:var(--admin-text-muted); font-size:13px; margin-left:8px;">Streamline walk-in registrations and daily operations</span>
  </div>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openAddEnquiryModal()">
      <i class="bi bi-chat-plus-fill"></i> + Add Enquiry
    </button>
    <button type="button" class="btn-admin btn-admin-sm" style="background:#0ea5e9; color:#fff;" onclick="openRegisterStudentModal()">
      <i class="bi bi-person-plus-fill"></i> + Register Student
    </button>
    <a href="enquiries.php" class="btn-admin btn-admin-outline btn-admin-sm">
      <i class="bi bi-chat-left-quote"></i> View Enquiries
    </a>
    <a href="admissions.php" class="btn-admin btn-admin-outline btn-admin-sm">
      <i class="bi bi-mortarboard"></i> View Admissions
    </a>
    <a href="students.php" class="btn-admin btn-admin-outline btn-admin-sm">
      <i class="bi bi-people"></i> View Students
    </a>
  </div>
</div>

<!-- Two Columns Grid -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(450px, 1fr)); gap:24px;">
  
  <!-- Recent Course Enquiries Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3><i class="bi bi-chat-left-quote"></i> Recent Course Enquiries</h3>
      <div style="display:flex; gap:8px;">
        <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openAddEnquiryModal()">+ Add</button>
        <a href="enquiries.php" class="btn-admin btn-admin-outline btn-admin-sm">View All (<?= $totalEnquiries ?>)</a>
      </div>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-responsive" style="border:none; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Code / Name</th>
              <th>Course</th>
              <th>Source</th>
              <th>Status</th>
              <th>Received</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentEnquiries)): ?>
              <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--admin-text-muted);">No enquiries yet.</td></tr>
            <?php else: ?>
              <?php foreach ($recentEnquiries as $enq): ?>
                <tr>
                  <td>
                    <strong><?= e($enq['full_name']) ?></strong><br />
                    <small style="color:var(--admin-text-muted);"><?= e($enq['enquiry_code']) ?> • <?= e($enq['mobile']) ?></small>
                  </td>
                  <td>
                    <span style="font-weight:500;"><?= e($enq['course_name']) ?></span><br />
                    <small style="color:var(--admin-text-muted);"><?= e($enq['branch_name']) ?></small>
                  </td>
                  <td>
                    <?php if (($enq['source'] ?? 'Website') === 'Offline'): ?>
                      <span class="badge-source badge-source-offline"><i class="bi bi-building"></i> Offline</span>
                    <?php else: ?>
                      <span class="badge-source badge-source-website"><i class="bi bi-globe"></i> Website</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                      $badgeClass = 'badge-new';
                      if ($enq['status'] === 'Contacted') $badgeClass = 'badge-contacted';
                      elseif ($enq['status'] === 'Follow-up') $badgeClass = 'badge-followup';
                      elseif ($enq['status'] === 'Converted') $badgeClass = 'badge-converted';
                      elseif ($enq['status'] === 'Closed') $badgeClass = 'badge-closed';
                    ?>
                    <span class="badge-status <?= $badgeClass ?>"><?= e($enq['status']) ?></span>
                  </td>
                  <td><?= format_date($enq['created_at'], 'd M, h:i A') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Admission Submissions Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3><i class="bi bi-mortarboard"></i> Recent Admissions & Registrations</h3>
      <div style="display:flex; gap:8px;">
        <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openRegisterStudentModal()">+ Register</button>
        <a href="admissions.php" class="btn-admin btn-admin-outline btn-admin-sm">View All (<?= $totalAdmissions ?>)</a>
      </div>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-responsive" style="border:none; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Applicant</th>
              <th>Course / Center</th>
              <th>Source</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentAdmissions)): ?>
              <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--admin-text-muted);">No admission applications yet.</td></tr>
            <?php else: ?>
              <?php foreach ($recentAdmissions as $adm): ?>
                <tr>
                  <td>
                    <strong><?= e($adm['full_name']) ?></strong><br />
                    <small style="color:var(--admin-text-muted);"><?= e($adm['admission_code']) ?> • <?= e($adm['mobile']) ?></small>
                  </td>
                  <td>
                    <span><?= e($adm['course_name']) ?></span><br />
                    <small style="color:var(--admin-text-muted);"><?= e($adm['branch_name']) ?></small>
                  </td>
                  <td>
                    <?php if (($adm['source'] ?? 'Website') === 'Offline'): ?>
                      <span class="badge-source badge-source-offline"><i class="bi bi-building"></i> Offline</span>
                    <?php else: ?>
                      <span class="badge-source badge-source-website"><i class="bi bi-globe"></i> Website</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                      $admBadge = 'badge-new';
                      if ($adm['status'] === 'Approved' || $adm['status'] === 'Enrolled') $admBadge = 'badge-approved';
                      elseif ($adm['status'] === 'Under Review') $admBadge = 'badge-followup';
                      elseif ($adm['status'] === 'Rejected') $admBadge = 'badge-rejected';
                    ?>
                    <span class="badge-status <?= $admBadge ?>"><?= e($adm['status']) ?></span>
                  </td>
                  <td><?= format_date($adm['created_at'], 'd M, h:i A') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Quick Add Enquiry Modal -->
<div id="addEnquiryModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:16px;">
  <div style="background:var(--admin-surface); width:100%; max-width:640px; max-height:90vh; overflow-y:auto; border-radius:12px; border:1px solid var(--admin-border); box-shadow:var(--admin-shadow-lg); padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--admin-border); padding-bottom:14px; margin-bottom:18px;">
      <div>
        <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-person-lines-fill" style="color:var(--admin-primary);"></i>
          Record Offline Enquiry (Walk-in)
        </h3>
        <p style="margin:4px 0 0; font-size:12px; color:var(--admin-text-muted);">Quickly record walk-in visitor details directly into the enquiry management system.</p>
      </div>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="closeAddEnquiryModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="dashAddEnquiryForm">
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Full Name <span style="color:var(--admin-danger);">*</span></label>
          <input type="text" name="full_name" class="admin-input" required placeholder="Visitor / Student Full Name" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mobile Number <span style="color:var(--admin-danger);">*</span></label>
          <input type="tel" name="mobile" class="admin-input" required placeholder="10-digit mobile number" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email Address</label>
          <input type="email" name="email" class="admin-input" placeholder="visitor@example.com (optional)" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">City / Area</label>
          <input type="text" name="city" class="admin-input" placeholder="e.g. Nawabganj" value="Nawabganj" />
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Course of Interest <span style="color:var(--admin-danger);">*</span></label>
          <select name="course" class="admin-input" required>
            <option value="">-- Select Course --</option>
            <?php foreach ($coursesList as $cName): ?>
              <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Preferred Branch <span style="color:var(--admin-danger);">*</span></label>
          <select name="branch" class="admin-input" required>
            <option value="">-- Select Branch --</option>
            <?php foreach ($branchesList as $bName): ?>
              <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Preferred Batch / Timing</label>
          <input type="text" name="batch" class="admin-input" placeholder="e.g. Morning 09:00 AM - 11:00 AM" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Next Follow-up Date</label>
          <input type="date" name="follow_up_date" class="admin-input" min="<?= date('Y-m-d') ?>" />
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Visitor Enquiry / Interest Details</label>
        <textarea name="message" rows="2" class="admin-input" placeholder="Visitor questions regarding fees, duration, batch timings..."></textarea>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Internal Staff Notes</label>
        <textarea name="staff_notes" rows="2" class="admin-input" placeholder="Private internal notes regarding counseling or fee quotation..."></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--admin-border); padding-top:14px;">
        <button type="button" class="btn-admin btn-admin-outline" onclick="closeAddEnquiryModal()">Cancel</button>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save2"></i> Save Enquiry</button>
      </div>
    </form>
  </div>
</div>

<!-- Quick Register Student Modal -->
<div id="registerStudentModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:16px;">
  <div style="background:var(--admin-surface); width:100%; max-width:760px; max-height:90vh; overflow-y:auto; border-radius:12px; border:1px solid var(--admin-border); box-shadow:var(--admin-shadow-lg); padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--admin-border); padding-bottom:14px; margin-bottom:18px;">
      <div>
        <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-person-plus-fill" style="color:var(--admin-primary);"></i>
          Register Student (Offline Walk-in Admission)
        </h3>
        <p style="margin:4px 0 0; font-size:12px; color:var(--admin-text-muted);">Saves admission record and immediately registers student profile.</p>
      </div>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="closeRegisterStudentModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="dashRegisterStudentForm">
      <!-- Personal Information -->
      <h4 style="font-size:14px; margin:0 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
        <i class="bi bi-person-lines-fill"></i> Personal Information
      </h4>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Student Full Name <span style="color:var(--admin-danger);">*</span></label>
          <input type="text" name="full_name" class="admin-input" required placeholder="Full Name" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mobile Number <span style="color:var(--admin-danger);">*</span></label>
          <input type="tel" name="mobile" class="admin-input" required placeholder="10-digit mobile" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Alternate Mobile</label>
          <input type="tel" name="alternate_mobile" class="admin-input" placeholder="Parent / Guardian" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email Address</label>
          <input type="email" name="email" class="admin-input" placeholder="student@example.com" />
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Date of Birth</label>
          <input type="date" name="dob" class="admin-input" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Gender</label>
          <select name="gender" class="admin-input">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">City / Town</label>
          <input type="text" name="city" class="admin-input" value="Nawabganj" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Previous Qualification</label>
          <input type="text" name="qualification" class="admin-input" placeholder="e.g. 10th / 12th / Grad" />
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Full Residential Address</label>
        <input type="text" name="address" class="admin-input" placeholder="Address / Tehsil / District" />
      </div>

      <!-- Course & Academic Information -->
      <h4 style="font-size:14px; margin:16px 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
        <i class="bi bi-book-half"></i> Academic & Course Information
      </h4>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Selected Course <span style="color:var(--admin-danger);">*</span></label>
          <select name="course" class="admin-input" required>
            <option value="">-- Choose Course --</option>
            <?php foreach ($coursesList as $cName): ?>
              <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Admission Branch <span style="color:var(--admin-danger);">*</span></label>
          <select name="branch" class="admin-input" required>
            <option value="">-- Choose Branch --</option>
            <?php foreach ($branchesList as $bName): ?>
              <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Preferred Batch / Timing</label>
          <input type="text" name="batch" class="admin-input" placeholder="e.g. Morning 08:00 AM - 10:00 AM" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Admission Date</label>
          <input type="date" name="admission_date" class="admin-input" value="<?= date('Y-m-d') ?>" />
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Internal Remarks / Notes</label>
        <textarea name="remarks" rows="2" class="admin-input" placeholder="Staff notes, fee discussion, concessions, etc."></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--admin-border); padding-top:14px;">
        <button type="button" class="btn-admin btn-admin-outline" onclick="closeRegisterStudentModal()">Cancel</button>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-person-check-fill"></i> Save & Enroll</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openAddEnquiryModal() {
    const modal = document.getElementById('addEnquiryModal');
    if (modal) {
      document.getElementById('dashAddEnquiryForm').reset();
      modal.style.display = 'flex';
    }
  }

  function closeAddEnquiryModal() {
    const modal = document.getElementById('addEnquiryModal');
    if (modal) modal.style.display = 'none';
  }

  function openRegisterStudentModal() {
    const modal = document.getElementById('registerStudentModal');
    if (modal) {
      document.getElementById('dashRegisterStudentForm').reset();
      modal.style.display = 'flex';
    }
  }

  function closeRegisterStudentModal() {
    const modal = document.getElementById('registerStudentModal');
    if (modal) modal.style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Dash Add Enquiry
    const enquiryForm = document.getElementById('dashAddEnquiryForm');
    if (enquiryForm) {
      enquiryForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = enquiryForm.querySelector('button[type="submit"]');
        const origText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

        try {
          const formData = new FormData(enquiryForm);
          formData.append('csrf_token', '<?= get_csrf_token() ?>');

          const res = await fetch('../api/enquiries.php?action=create_offline', {
            method: 'POST',
            body: formData
          });
          const json = await res.json();
          if (!json.success) throw new Error(json.message);

          showToast(json.message + ' (Ref: ' + (json.data.enquiry_code || '') + ')', 'success');
          closeAddEnquiryModal();
          setTimeout(() => window.location.reload(), 1200);
        } catch (err) {
          showToast(err.message, 'error');
        } finally {
          submitBtn.disabled = false;
          submitBtn.innerHTML = origText;
        }
      });
    }

    // Dash Register Student
    const regForm = document.getElementById('dashRegisterStudentForm');
    if (regForm) {
      regForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = regForm.querySelector('button[type="submit"]');
        const origText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registering...';

        try {
          const formData = new FormData(regForm);
          formData.append('csrf_token', '<?= get_csrf_token() ?>');
          formData.append('status', 'Enrolled');

          const res = await fetch('../api/admissions.php?action=create_offline', {
            method: 'POST',
            body: formData
          });
          const json = await res.json();
          if (!json.success) throw new Error(json.message);

          const studentCode = json.data.student_code ? ` (Student: ${json.data.student_code})` : '';
          showToast(json.message + studentCode, 'success');
          closeRegisterStudentModal();
          setTimeout(() => window.location.reload(), 1200);
        } catch (err) {
          showToast(err.message, 'error');
        } finally {
          submitBtn.disabled = false;
          submitBtn.innerHTML = origText;
        }
      });
    }
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
