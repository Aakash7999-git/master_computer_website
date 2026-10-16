<?php
/**
 * Master Computer Institute - Admin Dashboard Overview
 */

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// Live statistics directly from database
$totalEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$newEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
$followupEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Follow-up'")->fetchColumn();
$convertedEnquiries = (int)$pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Converted'")->fetchColumn();

$totalAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions")->fetchColumn();
$newAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'New'")->fetchColumn();

$activeCourses = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
$activeBranches = (int)$pdo->query("SELECT COUNT(*) FROM branches WHERE status = 'active'")->fetchColumn();
$unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'Unread'")->fetchColumn();
$validCerts = (int)$pdo->query("SELECT COUNT(*) FROM certificates WHERE status = 'Valid'")->fetchColumn();

// Recent enquiries
$stmtRecentEnq = $pdo->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 5");
$recentEnquiries = $stmtRecentEnq->fetchAll() ?: [];

// Recent admissions
$stmtRecentAdm = $pdo->query("SELECT * FROM admissions ORDER BY id DESC LIMIT 5");
$recentAdmissions = $stmtRecentAdm->fetchAll() ?: [];
?>

<!-- Statistics Overview -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-blue">
      <i class="bi bi-chat-left-quote-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Total Enquiries</h4>
      <div class="stat-value"><?= number_format($totalEnquiries) ?></div>
      <small style="color:var(--admin-primary-light); font-weight:600;"><?= $newEnquiries ?> New / Unprocessed</small>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-amber">
      <i class="bi bi-clock-history"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Follow-ups Pending</h4>
      <div class="stat-value"><?= number_format($followupEnquiries) ?></div>
      <small style="color:var(--admin-warning); font-weight:600;"><?= $convertedEnquiries ?> Converted Admissions</small>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-purple">
      <i class="bi bi-mortarboard-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Admission Submissions</h4>
      <div class="stat-value"><?= number_format($totalAdmissions) ?></div>
      <small style="color:#8b5cf6; font-weight:600;"><?= $newAdmissions ?> Pending Review</small>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-green">
      <i class="bi bi-journal-code"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Active Courses</h4>
      <div class="stat-value"><?= number_format($activeCourses) ?></div>
      <small style="color:var(--admin-success); font-weight:600;"><?= $activeBranches ?> Institute Branch(es)</small>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-red">
      <i class="bi bi-envelope-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Unread Messages</h4>
      <div class="stat-value"><?= number_format($unreadMessages) ?></div>
      <small style="color:var(--admin-danger); font-weight:600;">Contact Inquiries</small>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-icon stat-indigo">
      <i class="bi bi-patch-check-fill"></i>
    </div>
    <div class="admin-stat-info">
      <h4>Verified Certificates</h4>
      <div class="stat-value"><?= number_format($validCerts) ?></div>
      <small style="color:#6366f1; font-weight:600;">Active in Database</small>
    </div>
  </div>
</div>

<!-- Quick Action Strip -->
<div style="background:#fff; border:1px solid var(--admin-border); border-radius:12px; padding:16px 24px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
  <div>
    <strong><i class="bi bi-lightning-charge-fill" style="color:var(--admin-accent);"></i> Quick Actions:</strong>
    <span style="color:var(--admin-text-muted); font-size:13px; margin-left:8px;">Streamline institute operations</span>
  </div>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a href="enquiries.php" class="btn-admin btn-admin-primary btn-admin-sm"><i class="bi bi-eye"></i> Manage Enquiries</a>
    <a href="courses.php?action=new" class="btn-admin btn-admin-outline btn-admin-sm"><i class="bi bi-plus-lg"></i> Add Course</a>
    <a href="certificates.php?action=new" class="btn-admin btn-admin-outline btn-admin-sm"><i class="bi bi-award"></i> Issue Certificate</a>
    <a href="../api/enquiries.php?action=export_csv" class="btn-admin btn-admin-success btn-admin-sm"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
  </div>
</div>

<!-- Two Columns Grid -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(450px, 1fr)); gap:24px;">
  
  <!-- Recent Course Enquiries Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3><i class="bi bi-chat-left-quote"></i> Recent Course Enquiries</h3>
      <a href="enquiries.php" class="btn-admin btn-admin-outline btn-admin-sm">View All (<?= $totalEnquiries ?>)</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-responsive" style="border:none; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Code / Name</th>
              <th>Course</th>
              <th>Status</th>
              <th>Received</th>
              <th>Action</th>
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
                  <td>
                    <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="viewEnquiryDetails(<?= (int)$enq['id'] ?>)">
                      <i class="bi bi-eye"></i>
                    </button>
                  </td>
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
      <h3><i class="bi bi-mortarboard"></i> Recent Online Admissions</h3>
      <a href="admissions.php" class="btn-admin btn-admin-outline btn-admin-sm">View All (<?= $totalAdmissions ?>)</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-responsive" style="border:none; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Applicant</th>
              <th>Course / Batch</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Action</th>
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
                    <small style="color:var(--admin-text-muted);"><?= e($adm['batch']) ?> Batch</small>
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
                  <td>
                    <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="viewAdmissionDetails(<?= (int)$adm['id'] ?>)">
                      <i class="bi bi-eye"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
