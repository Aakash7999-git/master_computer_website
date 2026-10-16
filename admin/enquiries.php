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
    <div style="display:flex; gap:10px;">
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
            <th>Course & Branch</th>
            <th>Preferred Batch</th>
            <th>Status</th>
            <th>Follow-up</th>
            <th>Date Received</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="enquiriesTableBody">
          <tr><td colspan="8" style="text-align:center; padding:30px;">Loading enquiries from database...</td></tr>
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
    const status = document.getElementById('filterStatus').value;
    const course = document.getElementById('filterCourse').value;
    const branch = document.getElementById('filterBranch').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;

    const url = new URL('../api/enquiries.php', window.location.href);
    url.searchParams.set('action', 'list');
    url.searchParams.set('page', page);
    url.searchParams.set('search', search);
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
      document.getElementById('enquiriesTableBody').innerHTML = `<tr><td colspan="8" style="text-align:center; color:var(--admin-danger); padding:20px;">Error loading enquiries: ${err.message}</td></tr>`;
    }
  }

  function renderEnquiriesTable(enquiries) {
    const tbody = document.getElementById('enquiriesTableBody');
    if (!enquiries || enquiries.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No enquiry records found matching your filters.</td></tr>`;
      return;
    }

    const rowsHtml = enquiries.map(enq => {
      let badgeClass = 'badge-new';
      if (enq.status === 'Contacted') badgeClass = 'badge-contacted';
      else if (enq.status === 'Follow-up') badgeClass = 'badge-followup';
      else if (enq.status === 'Converted') badgeClass = 'badge-converted';
      else if (enq.status === 'Closed') badgeClass = 'badge-closed';

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
    const status = document.getElementById('filterStatus').value;
    const course = document.getElementById('filterCourse').value;
    const branch = document.getElementById('filterBranch').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;

    const url = new URL('../api/enquiries.php', window.location.href);
    url.searchParams.set('action', 'export_csv');
    url.searchParams.set('search', search);
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

  document.addEventListener('DOMContentLoaded', () => loadEnquiries(1));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
