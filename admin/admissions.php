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
    <h3><i class="bi bi-mortarboard-fill"></i> Online Admission Applications</h3>
    <div style="display:flex; gap:10px;">
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
        <input type="text" id="admissionSearch" placeholder="Search applicant, phone, code..." oninput="debounceAdmissionSearch()" />
      </div>

      <div class="admin-filter-group">
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
            <th>Applicant Name</th>
            <th>Contact & City</th>
            <th>Course & Branch</th>
            <th>Qualification</th>
            <th>Batch</th>
            <th>Status</th>
            <th>Applied Date</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="admissionsTableBody">
          <tr><td colspan="9" style="text-align:center; padding:30px;">Loading applications...</td></tr>
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
    const status = document.getElementById('filterAdmStatus').value;
    const course = document.getElementById('filterAdmCourse').value;
    const branch = document.getElementById('filterAdmBranch').value;

    const url = new URL('../api/admissions.php', window.location.href);
    url.searchParams.set('action', 'list');
    url.searchParams.set('page', page);
    url.searchParams.set('search', search);
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
      document.getElementById('admissionsTableBody').innerHTML = `<tr><td colspan="9" style="text-align:center; color:var(--admin-danger); padding:20px;">Error loading admissions: ${err.message}</td></tr>`;
    }
  }

  function renderAdmissionsTable(admissions) {
    const tbody = document.getElementById('admissionsTableBody');
    if (!admissions || admissions.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No admission applications found.</td></tr>`;
      return;
    }

    const rows = admissions.map(adm => {
      let badge = 'badge-new';
      if (adm.status === 'Approved' || adm.status === 'Enrolled') badge = 'badge-approved';
      else if (adm.status === 'Under Review') badge = 'badge-followup';
      else if (adm.status === 'Rejected') badge = 'badge-rejected';

      const cleanPhone = (adm.mobile || '').replace(/[^0-9]/g, '');

      return `
        <tr>
          <td><span style="font-family:monospace; font-weight:700;">${adm.admission_code}</span></td>
          <td>
            <strong>${escapeHtml(adm.full_name)}</strong>
            ${adm.gender ? `<br /><small style="color:var(--admin-text-muted);">${adm.gender} ${adm.dob ? `• DOB: ${adm.dob}` : ''}</small>` : ''}
          </td>
          <td>
            <span><i class="bi bi-telephone"></i> ${escapeHtml(adm.mobile)}</span><br />
            <small style="color:var(--admin-text-muted);">${escapeHtml(adm.city)}</small>
          </td>
          <td>
            <span>${escapeHtml(adm.course_name)}</span><br />
            <small style="color:var(--admin-text-muted);">${escapeHtml(adm.branch_name)}</small>
          </td>
          <td>${escapeHtml(adm.qualification)}</td>
          <td><span style="font-weight:500;">${escapeHtml(adm.batch)}</span></td>
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
    const status = document.getElementById('filterAdmStatus').value;
    const course = document.getElementById('filterAdmCourse').value;
    const branch = document.getElementById('filterAdmBranch').value;

    const url = new URL('../api/admissions.php', window.location.href);
    url.searchParams.set('action', 'export_csv');
    url.searchParams.set('search', search);
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

  document.addEventListener('DOMContentLoaded', () => loadAdmissions(1));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
