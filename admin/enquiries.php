<?php
/**
 * Master Computer Institute - Admin Enquiry Management Page
 */

$pageTitle = 'Enquiry Management';
$activeNav = 'enquiries';
require_once __DIR__ . '/includes/header.php';

// Fetch distinct courses and branches for filter dropdowns
$coursesList = $pdo->query("SELECT name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
$branchesList = $pdo->query("SELECT name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-chat-left-quote-fill"></i> Student Enquiries</h3>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openAddEnquiryModal()">
        <i class="bi bi-plus-circle-fill"></i> + Add Enquiry
      </button>
      <button type="button" class="btn-admin btn-admin-success btn-admin-sm" onclick="exportEnquiriesCSV()">
        <i class="bi bi-file-earmark-excel"></i> Export CSV
      </button>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadEnquiries(1)">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>
  </div>

  <div class="admin-card-body">
    <!-- Filter Toolbar -->
    <div class="admin-toolbar">
      <div class="admin-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="enquirySearch" placeholder="Search name, mobile, email, ID..." oninput="debounceSearch()" />
      </div>

      <div class="admin-filter-group">
        <select id="filterSource" onchange="loadEnquiries(1)" style="font-weight:600;">
          <option value="">All Sources</option>
          <option value="Website">Website</option>
          <option value="Offline">Offline (Walk-in)</option>
        </select>

        <select id="filterStatus" onchange="loadEnquiries(1)">
          <option value="">All Statuses</option>
          <option value="New">New</option>
          <option value="Contacted">Contacted</option>
          <option value="Follow-up">Follow-up</option>
          <option value="Converted">Converted</option>
          <option value="Closed">Closed</option>
        </select>

        <select id="filterCourse" onchange="loadEnquiries(1)">
          <option value="">All Courses</option>
          <?php foreach ($coursesList as $cName): ?>
            <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
          <?php endforeach; ?>
        </select>

        <select id="filterBranch" onchange="loadEnquiries(1)">
          <option value="">All Branches</option>
          <?php foreach ($branchesList as $bName): ?>
            <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
          <?php endforeach; ?>
        </select>

        <input type="date" id="filterDateFrom" title="From Date" onchange="loadEnquiries(1)" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;" />
        <input type="date" id="filterDateTo" title="To Date" onchange="loadEnquiries(1)" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:8px; font-size:13px;" />
      </div>
    </div>

    <!-- Enquiries Table -->
    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name & Contact</th>
            <th>Course & Center</th>
            <th>Preferred Batch</th>
            <th>Source</th>
            <th>Status</th>
            <th>Follow-up</th>
            <th>Date Received</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="enquiriesTableBody">
          <tr><td colspan="9" style="text-align:center; padding:30px;">Loading enquiries from database...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; flex-wrap:wrap; gap:10px;">
      <div id="enquiriesCountInfo" style="font-size:13px; color:var(--admin-text-muted);">Showing 0 records</div>
      <div id="enquiriesPagination" style="display:flex; gap:6px;"></div>
    </div>
  </div>
</div>

<script>
  let searchTimer = null;
  function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadEnquiries(1), 350);
  }

  async function loadEnquiries(page = 1) {
    const search = document.getElementById('enquirySearch').value;
    const source = document.getElementById('filterSource')?.value || '';
    const status = document.getElementById('filterStatus').value;
    const course = document.getElementById('filterCourse').value;
    const branch = document.getElementById('filterBranch').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;

    const url = new URL('../api/enquiries.php', window.location.href);
    url.searchParams.set('action', 'list');
    url.searchParams.set('page', page);
    url.searchParams.set('search', search);
    url.searchParams.set('source', source);
    url.searchParams.set('status', status);
    url.searchParams.set('course', course);
    url.searchParams.set('branch', branch);
    url.searchParams.set('date_from', dateFrom);
    url.searchParams.set('date_to', dateTo);

    try {
      const res = await fetch(url);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      renderEnquiriesTable(json.data.enquiries);
      renderPagination(json.data.pagination);
    } catch (err) {
      document.getElementById('enquiriesTableBody').innerHTML = `<tr><td colspan="9" style="text-align:center; color:var(--admin-danger); padding:20px;">Error loading enquiries: ${err.message}</td></tr>`;
    }
  }

  function renderEnquiriesTable(enquiries) {
    const tbody = document.getElementById('enquiriesTableBody');
    if (!enquiries || enquiries.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No enquiry records found matching your filters.</td></tr>`;
      return;
    }

    const rowsHtml = enquiries.map(enq => {
      let badgeClass = 'badge-new';
      if (enq.status === 'Contacted') badgeClass = 'badge-contacted';
      else if (enq.status === 'Follow-up') badgeClass = 'badge-followup';
      else if (enq.status === 'Converted') badgeClass = 'badge-converted';
      else if (enq.status === 'Closed') badgeClass = 'badge-closed';

      const isOffline = enq.source === 'Offline';
      const sourceBadge = isOffline
        ? `<span class="badge-source badge-source-offline"><i class="bi bi-person-walking"></i> Offline</span>`
        : `<span class="badge-source badge-source-website"><i class="bi bi-globe"></i> Website</span>`;

      const cleanPhone = (enq.mobile || '').replace(/[^0-9]/g, '');

      return `
        <tr>
          <td><span style="font-family:monospace; font-weight:700;">${enq.enquiry_code}</span></td>
          <td>
            <strong>${escapeHtml(enq.full_name)}</strong><br />
            <span style="color:var(--admin-text-muted); font-size:12px;">
              <i class="bi bi-telephone"></i> ${escapeHtml(enq.mobile)}
              ${enq.email ? ` • <i class="bi bi-envelope"></i> ${escapeHtml(enq.email)}` : ''}
            </span>
          </td>
          <td>
            <span>${escapeHtml(enq.course_name)}</span><br />
            <small style="color:var(--admin-text-muted);">${escapeHtml(enq.branch_name)}</small>
          </td>
          <td>${escapeHtml(enq.preferred_batch || '—')}</td>
          <td>${sourceBadge}</td>
          <td>
            <span class="badge-status ${badgeClass}">${enq.status}</span>
          </td>
          <td>${enq.follow_up_date || '—'}</td>
          <td>${formatDate(enq.created_at)}</td>
          <td style="text-align:right;">
            <div style="display:inline-flex; gap:6px;">
              <a href="tel:${cleanPhone}" class="btn-admin btn-admin-outline btn-admin-sm" title="Call"><i class="bi bi-telephone-outbound"></i></a>
              <a href="https://wa.me/91${cleanPhone}?text=Hello%20${encodeURIComponent(enq.full_name)}%2C%20regarding%20your%20enquiry%20for%20${encodeURIComponent(enq.course_name)}%20at%20Master%20Computer%20Institute." target="_blank" class="btn-admin btn-admin-success btn-admin-sm" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
              <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="View & Edit" onclick="viewEnquiryDetails(${enq.id})"><i class="bi bi-pencil-square"></i></button>
              <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteEnquiry(${enq.id}, '${escapeHtml(enq.enquiry_code)}')"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rowsHtml;
  }

  function renderPagination(pg) {
    const info = document.getElementById('enquiriesCountInfo');
    const container = document.getElementById('enquiriesPagination');
    info.textContent = `Showing page ${pg.current_page} of ${pg.total_pages || 1} (${pg.total_records} total enquiries)`;

    let pagesHtml = '';
    if (pg.current_page > 1) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadEnquiries(${pg.current_page - 1})">Prev</button>`;
    }
    for (let p = 1; p <= pg.total_pages; p++) {
      if (p === 1 || p === pg.total_pages || Math.abs(p - pg.current_page) <= 2) {
        pagesHtml += `<button type="button" class="btn-admin ${p === pg.current_page ? 'btn-admin-primary' : 'btn-admin-outline'} btn-admin-sm" onclick="loadEnquiries(${p})">${p}</button>`;
      }
    }
    if (pg.current_page < pg.total_pages) {
      pagesHtml += `<button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadEnquiries(${pg.current_page + 1})">Next</button>`;
    }
    container.innerHTML = pagesHtml;
  }

  function exportEnquiriesCSV() {
    const search = document.getElementById('enquirySearch').value;
    const source = document.getElementById('filterSource')?.value || '';
    const status = document.getElementById('filterStatus').value;
    const course = document.getElementById('filterCourse').value;
    const branch = document.getElementById('filterBranch').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;

    const url = new URL('../api/enquiries.php', window.location.href);
    url.searchParams.set('action', 'export_csv');
    url.searchParams.set('search', search);
    url.searchParams.set('source', source);
    url.searchParams.set('status', status);
    url.searchParams.set('course', course);
    url.searchParams.set('branch', branch);
    url.searchParams.set('date_from', dateFrom);
    url.searchParams.set('date_to', dateTo);

    window.location.href = url.toString();
  }

  async function deleteEnquiry(id, code) {
    if (!confirm(`Are you sure you want to permanently delete enquiry record ${code}? This action cannot be undone.`)) {
      return;
    }
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/enquiries.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Enquiry deleted successfully.');
      loadEnquiries();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  /**
   * Phase 1 & 2: Modal Form for Offline Walk-in Enquiry Entry
   */
  function openAddEnquiryModal() {
    const coursesOptions = <?= json_encode($coursesList) ?>;
    const branchesOptions = <?= json_encode($branchesList) ?>;

    const courseSelectHtml = coursesOptions.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');
    const branchSelectHtml = branchesOptions.map(b => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`).join('');

    const formHtml = `
      <form id="offlineEnquiryForm" onsubmit="submitOfflineEnquiryForm(event)" novalidate>
        <p style="margin:0 0 16px; font-size:13px; color:var(--admin-text-muted);">
          Record a walk-in or offline enquiry from a visitor at the institute. This creates a real MySQL record marked with source <strong>Offline</strong>.
        </p>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Visitor Full Name *</label>
            <input type="text" name="full_name" required placeholder="e.g. Ramesh Kumar" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>Primary Mobile Number *</label>
            <input type="tel" name="mobile" required placeholder="10-digit mobile number" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Email Address (Optional)</label>
            <input type="email" name="email" placeholder="name@example.com" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
          <div class="form-field">
            <label>City / Area (Optional)</label>
            <input type="text" name="city" value="Nawabganj" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Course of Interest *</label>
            <select name="course_name" required style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="">-- Choose Course --</option>
              ${courseSelectHtml}
            </select>
          </div>
          <div class="form-field">
            <label>Institute Center / Branch *</label>
            <select name="branch_name" required style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              ${branchSelectHtml}
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
          <div class="form-field">
            <label>Preferred Batch (Optional)</label>
            <select name="preferred_batch" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="Morning">Morning Batch (8:00 AM - 10:00 AM)</option>
              <option value="Evening">Evening Batch (4:00 PM - 6:00 PM)</option>
              <option value="Weekend">Weekend Batch</option>
              <option value="Flexible">Flexible / Customized Timing</option>
            </select>
          </div>
          <div class="form-field">
            <label>Next Follow-up Date (Optional)</label>
            <input type="date" name="follow_up_date" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
          </div>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Enquiry Questions / Visitor Questions (Optional)</label>
          <textarea name="message" rows="2" placeholder="Fee questions, installment inquiries, syllabus details requested..." style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; box-sizing:border-box;"></textarea>
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Internal Staff Notes (Optional)</label>
          <textarea name="admin_notes" rows="2" placeholder="Counsellor notes: walk-in timing, discounts discussed, promised callback..." style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; box-sizing:border-box;"></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" id="btnSaveOfflineEnquiry" class="btn-admin btn-admin-primary">
            <i class="bi bi-check-circle-fill"></i> Save Offline Enquiry
          </button>
        </div>
      </form>
    `;

    openAdminModal('+ Add Offline Walk-in Enquiry', formHtml);
  }

  async function submitOfflineEnquiryForm(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSaveOfflineEnquiry');

    const fullName = form.full_name.value.trim();
    const mobile = form.mobile.value.trim();
    const course = form.course_name.value;
    const branch = form.branch_name.value;

    if (!fullName) {
      alert('Please enter visitor full name.');
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
      alert('Please select a course of interest.');
      form.course_name.focus();
      return;
    }
    if (!branch) {
      alert('Please select an institute branch.');
      form.branch_name.focus();
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

    try {
      const formData = new FormData(form);
      formData.append('action', 'create_offline');

      const res = await fetch('../api/enquiries.php', { method: 'POST', body: formData });
      const json = await res.json();

      if (!json.success) throw new Error(json.message);

      showToast(json.message || 'Offline enquiry recorded successfully!');
      closeAdminModal();
      loadEnquiries(1);

    } catch (err) {
      alert('Error saving enquiry: ' + err.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Save Offline Enquiry';
    }
  }

  document.addEventListener('DOMContentLoaded', () => loadEnquiries(1));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
