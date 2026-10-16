<?php
/**
 * Master Computer Institute - Admin Certificate Management Page
 */

$pageTitle = 'Certificate Management';
$activeNav = 'certificates';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-patch-check-fill"></i> Student Certificates</h3>
    <div style="display:flex; gap:10px;">
      <a href="../certificate-verification.php" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">
        <i class="bi bi-box-arrow-up-right"></i> Public Verification Page
      </a>
      <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openCertificateModal()">
        <i class="bi bi-plus-lg"></i> Issue New Certificate
      </button>
    </div>
  </div>

  <div class="admin-card-body">
    <div class="admin-toolbar">
      <div class="admin-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="certSearch" placeholder="Search certificate number, student name..." oninput="debounceCertSearch()" />
      </div>

      <div class="admin-filter-group">
        <select id="filterCertStatus" onchange="loadCertificates(1)">
          <option value="">All Statuses</option>
          <option value="Valid">Valid</option>
          <option value="Revoked">Revoked</option>
          <option value="Pending">Pending</option>
        </select>
        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadCertificates(1)">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
      </div>
    </div>

    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Certificate No.</th>
            <th>Student Name & Father</th>
            <th>Course & Duration</th>
            <th>Issue Date</th>
            <th>Grade / %</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="certificatesTableBody">
          <tr><td colspan="7" style="text-align:center; padding:30px;">Loading certificates...</td></tr>
        </tbody>
      </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; flex-wrap:wrap; gap:10px;">
      <div id="certCountInfo" style="font-size:13px; color:var(--admin-text-muted);">Showing 0 records</div>
      <div id="certPagination" style="display:flex; gap:6px;"></div>
    </div>
  </div>
</div>

<script>
  let certsData = [];
  let certTimer = null;

  function debounceCertSearch() {
    clearTimeout(certTimer);
    certTimer = setTimeout(() => loadCertificates(1), 350);
  }

  async function loadCertificates(page = 1) {
    const search = document.getElementById('certSearch').value;
    const status = document.getElementById('filterCertStatus').value;

    const url = new URL('../api/certificates.php', window.location.href);
    url.searchParams.set('action', 'list');
    url.searchParams.set('page', page);
    url.searchParams.set('search', search);
    url.searchParams.set('status', status);

    try {
      const res = await fetch(url);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      certsData = json.data.certificates;
      renderCertificatesTable();
      renderCertPagination(json.data.pagination);
    } catch (err) {
      document.getElementById('certificatesTableBody').innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--admin-danger); padding:20px;">Error: ${err.message}</td></tr>`;
    }
  }

  function renderCertificatesTable() {
    const tbody = document.getElementById('certificatesTableBody');
    if (!certsData || certsData.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No certificate records found.</td></tr>`;
      return;
    }

    const rows = certsData.map(c => {
      let badge = 'badge-active';
      if (c.status === 'Revoked') badge = 'badge-closed';
      else if (c.status === 'Pending') badge = 'badge-followup';

      return `
        <tr>
          <td><span style="font-family:monospace; font-weight:700; color:var(--admin-primary-light);">${escapeHtml(c.certificate_no)}</span></td>
          <td>
            <strong>${escapeHtml(c.student_name)}</strong>
            ${c.father_name ? `<br /><small style="color:var(--admin-text-muted);">S/o, D/o: ${escapeHtml(c.father_name)}</small>` : ''}
          </td>
          <td>
            <span>${escapeHtml(c.course_name)}</span><br />
            <small style="color:var(--admin-text-muted);"><i class="bi bi-clock"></i> ${escapeHtml(c.duration)}</small>
          </td>
          <td>${formatDate(c.issue_date, 'd M Y')}</td>
          <td>
            <strong>${escapeHtml(c.grade)}</strong>
            ${c.percentage ? `<small style="color:var(--admin-text-muted);"> (${escapeHtml(c.percentage)})</small>` : ''}
          </td>
          <td><span class="badge-status ${badge}">${c.status}</span></td>
          <td style="text-align:right;">
            <div style="display:inline-flex; gap:6px;">
              <button type="button" class="btn-admin ${c.status === 'Valid' ? 'btn-admin-danger' : 'btn-admin-success'} btn-admin-sm" title="${c.status === 'Valid' ? 'Revoke Certificate' : 'Activate Certificate'}" onclick="toggleCertStatus(${c.id}, '${c.status === 'Valid' ? 'Revoked' : 'Valid'}')">
                <i class="bi ${c.status === 'Valid' ? 'bi-slash-circle' : 'bi-check-circle'}"></i>
              </button>
              <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="Edit" onclick="openCertificateModal(${c.id})">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteCert(${c.id}, '${escapeHtml(c.certificate_no)}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rows;
  }

  function renderCertPagination(pg) {
    const info = document.getElementById('certCountInfo');
    const container = document.getElementById('certPagination');
    info.textContent = `Showing page ${pg.current_page} of ${pg.total_pages || 1} (${pg.total_records} certificates)`;

    let pagesHtml = '';
    if (pg.current_page > 1) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadCertificates(${pg.current_page - 1})">Prev</button>`;
    }
    for (let p = 1; p <= pg.total_pages; p++) {
      if (p === 1 || p === pg.total_pages || Math.abs(p - pg.current_page) <= 2) {
        pagesHtml += `<button type="button" class="btn-admin ${p === pg.current_page ? 'btn-admin-primary' : 'btn-admin-outline'} btn-admin-sm" onclick="loadCertificates(${p})">${p}</button>`;
      }
    }
    if (pg.current_page < pg.total_pages) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadCertificates(${pg.current_page + 1})">Next</button>`;
    }
    container.innerHTML = pagesHtml;
  }

  function openCertificateModal(certId = 0) {
    let c = {
      id: 0,
      certificate_no: 'MCI-CERT-',
      student_name: '',
      father_name: '',
      course_name: 'ADCA — Advanced Diploma in Computer Applications',
      duration: '12 Months',
      issue_date: new Date().toISOString().split('T')[0],
      grade: 'A+',
      percentage: '85%',
      center_name: 'Master Computer Institute, Nawabganj',
      status: 'Valid',
      remarks: ''
    };

    if (certId > 0) {
      const found = certsData.find(item => Number(item.id) === Number(certId));
      if (found) c = found;
    }

    const title = certId > 0 ? `Edit Certificate — ${c.certificate_no}` : 'Issue New Student Certificate';
    const content = `
      <form id="certForm" onsubmit="saveCertificate(event)">
        <input type="hidden" name="id" value="${c.id}" />

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Certificate Number *</label>
            <input type="text" name="certificate_no" required value="${escapeHtml(c.certificate_no)}" placeholder="e.g. MCI-CERT-10004" />
          </div>
          <div class="form-field">
            <label>Student Full Name *</label>
            <input type="text" name="student_name" required value="${escapeHtml(c.student_name)}" placeholder="Enter student name" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Father's / Guardian's Name</label>
            <input type="text" name="father_name" value="${escapeHtml(c.father_name || '')}" placeholder="Father's name" />
          </div>
          <div class="form-field">
            <label>Issue Date *</label>
            <input type="date" name="issue_date" required value="${c.issue_date}" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Course Name *</label>
            <input type="text" name="course_name" required value="${escapeHtml(c.course_name)}" placeholder="Course title" />
          </div>
          <div class="form-field">
            <label>Course Duration</label>
            <input type="text" name="duration" value="${escapeHtml(c.duration)}" placeholder="e.g. 12 Months" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Grade Awarded</label>
            <input type="text" name="grade" value="${escapeHtml(c.grade)}" placeholder="A+, A, B" />
          </div>
          <div class="form-field">
            <label>Percentage Marks</label>
            <input type="text" name="percentage" value="${escapeHtml(c.percentage || '')}" placeholder="e.g. 88%" />
          </div>
          <div class="form-field">
            <label>Status</label>
            <select name="status">
              <option value="Valid" ${c.status === 'Valid' ? 'selected' : ''}>Valid (Verified)</option>
              <option value="Revoked" ${c.status === 'Revoked' ? 'selected' : ''}>Revoked</option>
              <option value="Pending" ${c.status === 'Pending' ? 'selected' : ''}>Pending</option>
            </select>
          </div>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Issuing Center</label>
          <input type="text" name="center_name" value="${escapeHtml(c.center_name)}" />
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Remarks / Notes</label>
          <input type="text" name="remarks" value="${escapeHtml(c.remarks || '')}" placeholder="Optional performance remark" />
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Certificate</button>
        </div>
      </form>
    `;

    openAdminModal(title, content);
  }

  async function saveCertificate(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'save');

    try {
      const res = await fetch('../api/certificates.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(json.message || 'Certificate saved.');
      closeAdminModal();
      loadCertificates();
    } catch (err) {
      showToast('Save error: ' + err.message, 'error');
    }
  }

  async function toggleCertStatus(id, newStatus) {
    try {
      const formData = new FormData();
      formData.append('action', 'toggle_status');
      formData.append('id', id);
      formData.append('status', newStatus);

      const res = await fetch('../api/certificates.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(`Certificate status set to ${newStatus}.`);
      loadCertificates();
    } catch (err) {
      showToast('Status error: ' + err.message, 'error');
    }
  }

  async function deleteCert(id, certNo) {
    if (!confirm(`Are you sure you want to permanently delete certificate ${certNo}?`)) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/certificates.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Certificate record removed.');
      loadCertificates();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', () => loadCertificates(1));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
