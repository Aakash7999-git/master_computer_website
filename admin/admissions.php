<?php
/**
 * Master Computer Institute - Admin Admission Management Page
 */

$pageTitle = 'Admission Management';
$activeNav = 'admissions';
require_once __DIR__ . '/includes/header.php';

$coursesList = $pdo->query("SELECT name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
$branchesList = $pdo->query("SELECT name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-mortarboard-fill"></i> Student Admissions & Applications</h3>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openAddAdmissionModal()">
        <i class="bi bi-person-plus-fill"></i> + Register Student / Add Admission
      </button>
      <button type="button" class="btn-admin btn-admin-success btn-admin-sm" onclick="exportAdmissionsCSV()">
        <i class="bi bi-file-earmark-excel"></i> Export CSV
      </button>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadAdmissions(1)">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>
  </div>

  <div class="admin-card-body">
    <!-- Toolbar -->
    <div class="admin-toolbar">
      <div class="admin-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="admissionSearch" placeholder="Search applicant, phone, code, student ID..." oninput="debounceAdmissionSearch()" />
      </div>

      <div class="admin-filter-group">
        <select id="filterAdmSource" onchange="loadAdmissions(1)" style="font-weight:600;">
          <option value="">All Sources</option>
          <option value="Website">Website</option>
          <option value="Offline">Offline (Walk-in)</option>
        </select>

        <select id="filterAdmStatus" onchange="loadAdmissions(1)">
          <option value="">All Statuses</option>
          <option value="New">New</option>
          <option value="Under Review">Under Review</option>
          <option value="Approved">Approved</option>
          <option value="Enrolled">Enrolled</option>
          <option value="Rejected">Rejected</option>
        </select>

        <select id="filterAdmCourse" onchange="loadAdmissions(1)">
          <option value="">All Courses</option>
          <?php foreach ($coursesList as $cName): ?>
            <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
          <?php endforeach; ?>
        </select>

        <select id="filterAdmBranch" onchange="loadAdmissions(1)">
          <option value="">All Branches</option>
          <?php foreach ($branchesList as $bName): ?>
            <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Application Code</th>
            <th>Student Reg Code</th>
            <th>Applicant Name</th>
            <th>Contact & Location</th>
            <th>Course & Center</th>
            <th>Batch & Date</th>
            <th>Source</th>
            <th>Status</th>
            <th>Recorded</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="admissionsTableBody">
          <tr><td colspan="10" style="text-align:center; padding:30px;">Loading applications...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; flex-wrap:wrap; gap:10px;">
      <div id="admissionsCountInfo" style="font-size:13px; color:var(--admin-text-muted);">Showing 0 records</div>
      <div id="admissionsPagination" style="display:flex; gap:6px;"></div>
    </div>
  </div>
</div>

<script>
  let admTimer = null;
  function debounceAdmissionSearch() {
    clearTimeout(admTimer);
    admTimer = setTimeout(() => loadAdmissions(1), 350);
  }

  async function loadAdmissions(page = 1) {
    const search = document.getElementById('admissionSearch').value;
    const source = document.getElementById('filterAdmSource')?.value || '';
    const status = document.getElementById('filterAdmStatus').value;
    const course = document.getElementById('filterAdmCourse').value;
    const branch = document.getElementById('filterAdmBranch').value;

    const url = new URL('../api/admissions.php', window.location.href);
    url.searchParams.set('action', 'list');
    url.searchParams.set('page', page);
    url.searchParams.set('search', search);
    url.searchParams.set('source', source);
    url.searchParams.set('status', status);
    url.searchParams.set('course', course);
    url.searchParams.set('branch', branch);

    try {
      const res = await fetch(url);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      renderAdmissionsTable(json.data.admissions);
      renderAdmPagination(json.data.pagination);
    } catch (err) {
      document.getElementById('admissionsTableBody').innerHTML = `<tr><td colspan="10" style="text-align:center; color:var(--admin-danger); padding:20px;">Error loading admissions: ${err.message}</td></tr>`;
    }
  }

  function renderAdmissionsTable(admissions) {
    const tbody = document.getElementById('admissionsTableBody');
    if (!admissions || admissions.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No admission applications found.</td></tr>`;
      return;
    }

    const rows = admissions.map(adm => {
      let badge = 'badge-new';
      if (adm.status === 'Approved' || adm.status === 'Enrolled') badge = 'badge-approved';
      else if (adm.status === 'Under Review') badge = 'badge-followup';
      else if (adm.status === 'Rejected') badge = 'badge-rejected';

      const isOffline = adm.source === 'Offline';
      const sourceBadge = isOffline
        ? `<span class="badge-source badge-source-offline"><i class="bi bi-person-walking"></i> Offline</span>`
        : `<span class="badge-source badge-source-website"><i class="bi bi-globe"></i> Website</span>`;

      const cleanPhone = (adm.mobile || '').replace(/[^0-9]/g, '');

      return `
        <tr>
          <td><span style="font-family:monospace; font-weight:700;">${adm.admission_code}</span></td>
          <td>
            ${adm.student_code ? `<span style="font-family:monospace; font-weight:700; color:#10b981;">${adm.student_code}</span>` : '<span style="color:var(--admin-text-muted); font-size:12px;">Not Enrolled</span>'}
          </td>
          <td>
            <strong>${escapeHtml(adm.full_name)}</strong>
            ${adm.gender ? `<br /><small style="color:var(--admin-text-muted);">${adm.gender} ${adm.dob ? `• DOB: ${adm.dob}` : ''}</small>` : ''}
          </td>
          <td>
            <span><i class="bi bi-telephone"></i> ${escapeHtml(adm.mobile)}</span><br />
            <small style="color:var(--admin-text-muted);">${escapeHtml(adm.city || 'Nawabganj')}</small>
          </td>
          <td>
            <span>${escapeHtml(adm.course_name)}</span><br />
            <small style="color:var(--admin-text-muted);">${escapeHtml(adm.branch_name)}</small>
          </td>
          <td>
            <span style="font-weight:500;">${escapeHtml(adm.batch || 'Morning')}</span><br />
            <small style="color:var(--admin-text-muted);">${adm.admission_date || formatDate(adm.created_at, 'dateOnly')}</small>
          </td>
          <td>${sourceBadge}</td>
          <td><span class="badge-status ${badge}">${adm.status}</span></td>
          <td>${formatDate(adm.created_at)}</td>
          <td style="text-align:right;">
            <div style="display:inline-flex; gap:6px;">
              <a href="tel:${cleanPhone}" class="btn-admin btn-admin-outline btn-admin-sm" title="Call"><i class="bi bi-telephone"></i></a>
              <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="Review Application" onclick="viewAdmissionDetails(${adm.id})"><i class="bi bi-pencil-square"></i></button>
              <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteAdmission(${adm.id}, '${escapeHtml(adm.admission_code)}')"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rows;
  }

  function renderAdmPagination(pg) {
    const info = document.getElementById('admissionsCountInfo');
    const container = document.getElementById('admissionsPagination');
    info.textContent = `Showing page ${pg.current_page} of ${pg.total_pages || 1} (${pg.total_records} applications)`;

    let pagesHtml = '';
    if (pg.current_page > 1) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadAdmissions(${pg.current_page - 1})">Prev</button>`;
    }
    for (let p = 1; p <= pg.total_pages; p++) {
      if (p === 1 || p === pg.total_pages || Math.abs(p - pg.current_page) <= 2) {
        pagesHtml += `<button type="button" class="btn-admin ${p === pg.current_page ? 'btn-admin-primary' : 'btn-admin-outline'} btn-admin-sm" onclick="loadAdmissions(${p})">${p}</button>`;
      }
    }
    if (pg.current_page < pg.total_pages) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadAdmissions(${pg.current_page + 1})">Next</button>`;
    }
    container.innerHTML = pagesHtml;
  }

  function exportAdmissionsCSV() {
    const search = document.getElementById('admissionSearch').value;
    const source = document.getElementById('filterAdmSource')?.value || '';
    const status = document.getElementById('filterAdmStatus').value;
    const course = document.getElementById('filterAdmCourse').value;
    const branch = document.getElementById('filterAdmBranch').value;

    const url = new URL('../api/admissions.php', window.location.href);
    url.searchParams.set('action', 'export_csv');
    url.searchParams.set('search', search);
    url.searchParams.set('source', source);
    url.searchParams.set('status', status);
    url.searchParams.set('course', course);
    url.searchParams.set('branch', branch);

    window.location.href = url.toString();
  }

  async function deleteAdmission(id, code) {
    if (!confirm(`Are you sure you want to permanently delete application ${code}?`)) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/admissions.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Application record deleted.');
      loadAdmissions();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  /**
   * Phase 1 & 3: Modal Form for Offline Student Admission Registration
   */
  function openAddAdmissionModal() {
    const coursesOptions = <?= json_encode($coursesList) ?>;
    const branchesOptions = <?= json_encode($branchesList) ?>;

    const courseSelectHtml = coursesOptions.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    const branchSelectHtml = branchesOptions.map(b => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`).join('');

    const todayDate = '<?= date('Y-m-d') ?>';

    const formHtml = `
      <form id="offlineAdmissionForm" onsubmit="submitOfflineAdmissionForm(event)" novalidate style="max-height:75vh; overflow-y:auto; padding-right:6px;">
        <p style="margin:0 0 16px; font-size:13px; color:var(--admin-text-muted);">
          Register a walk-in student taking admission directly at the institute. Select <strong>Enrolled</strong> to immediately issue an official Student Registration Number.
        </p>

        <!-- Section 1: Personal Information -->
        <h4 style="margin:0 0 12px; font-size:14px; color:var(--admin-primary-light); border-bottom:1px solid var(--admin-border); padding-bottom:6px;">
          <i class="bi bi-person-bounding-box"></i> 1. Personal Information
        </h4>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Student Full Name *</label>
            <input type="text" name="full_name" required placeholder="e.g. Priya Sharma" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>Primary Mobile Number *</label>
            <input type="tel" name="mobile" required placeholder="10-digit mobile number" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Alternate Mobile (Optional)</label>
            <input type="tel" name="alternate_mobile" placeholder="Guardian or secondary number" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>Email Address (Optional)</label>
            <input type="email" name="email" placeholder="student@example.com" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Date of Birth (Optional)</label>
            <input type="date" name="dob" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>Gender (Optional)</label>
            <select name="gender" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="">-- Select Gender --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:14px; margin-bottom:18px;">
          <div class="form-field">
            <label>Residential Address (Optional)</label>
            <input type="text" name="address" placeholder="Village / Street / Landmark" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>City (Optional)</label>
            <input type="text" name="city" value="Nawabganj" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <!-- Section 2: Academic and Course Details -->
        <h4 style="margin:0 0 12px; font-size:14px; color:var(--admin-primary-light); border-bottom:1px solid var(--admin-border); padding-bottom:6px;">
          <i class="bi bi-mortarboard"></i> 2. Academic & Course Registration
        </h4>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Enrolled Course *</label>
            <select name="course_name" required style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="">-- Choose Course --</option>
              ${courseSelectHtml}
            </select>
          </div>
          <div class="form-field">
            <label>Admission Branch / Center *</label>
            <select name="branch_name" required style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              ${branchSelectHtml}
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Batch Timing (Optional)</label>
            <select name="batch" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="Morning">Morning Batch (8:00 AM - 10:00 AM)</option>
              <option value="Afternoon">Afternoon Batch (12:00 PM - 2:00 PM)</option>
              <option value="Evening">Evening Batch (4:00 PM - 6:00 PM)</option>
              <option value="Weekend">Weekend Batch</option>
            </select>
          </div>
          <div class="form-field">
            <label>Previous Qualification (Optional)</label>
            <input type="text" name="qualification" placeholder="e.g. 10th / 12th / Graduate / BCA" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Admission Date *</label>
            <input type="date" name="admission_date" value="${todayDate}" required style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>Registration Status *</label>
            <select name="status" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; font-weight:600;">
              <option value="Enrolled" selected>Enrolled (Generate Student ID immediately)</option>
              <option value="Approved">Approved (Awaiting first class)</option>
              <option value="New">New / Under Review</option>
            </select>
          </div>
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Internal Administrator Remarks (Optional)</label>
          <textarea name="admin_notes" rows="2" placeholder="Fee structure details, ID proof submitted, special batch arrangements..." style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; box-sizing:border-box;"></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" id="btnSaveOfflineAdmission" class="btn-admin btn-admin-primary">
            <i class="bi bi-check-circle-fill"></i> Save Student Admission
          </button>
        </div>
      </form>
    `;

    openAdminModal('+ Register Student / Offline Admission', formHtml);
  }

  async function submitOfflineAdmissionForm(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSaveOfflineAdmission');

    const fullName = form.full_name.value.trim();
    const mobile = form.mobile.value.trim();
    const course = form.course_name.value;
    const branch = form.branch_name.value;

    if (!fullName) {
      alert('Please enter student full name.');
      form.full_name.focus();
      return;
    }
    const cleanMobile = mobile.replace(/[^0-9]/g, '');
    if (cleanMobile.length < 10) {
      alert('Please enter a valid 10-digit mobile number.');
      form.mobile.focus();
      return;
    }
    if (!course) {
      alert('Please select a course.');
      form.course_name.focus();
      return;
    }
    if (!branch) {
      alert('Please select an admission center.');
      form.branch_name.focus();
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Registering...';

    try {
      const formData = new FormData(form);
      formData.append('action', 'create_offline');

      const res = await fetch('../api/admissions.php', { method: 'POST', body: formData });
      const json = await res.json();

      if (!json.success) throw new Error(json.message);

      showToast(json.message || 'Offline student admission registered successfully!');
      closeAdminModal();
      loadAdmissions(1);

    } catch (err) {
      alert('Error registering student: ' + err.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Student Admission';
    }
  }

  document.addEventListener('DOMContentLoaded', () => loadAdmissions(1));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
