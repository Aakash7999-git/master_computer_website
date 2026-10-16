<?php
/**
 * Master Computer Institute - Admin Branch Management Page
 */

$pageTitle = 'Branch Management';
$activeNav = 'branches';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-geo-alt-fill"></i> Institute Centers & Campuses</h3>
    <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openBranchModal()">
      <i class="bi bi-plus-lg"></i> Add New Center / Branch
    </button>
  </div>

  <div class="admin-card-body">
    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Branch Code</th>
            <th>Center Name & Tagline</th>
            <th>Full Address</th>
            <th>Director & Phone</th>
            <th>Enquiries</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="branchesTableBody">
          <tr><td colspan="7" style="text-align:center; padding:30px;">Loading branches...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let branchesData = [];

  async function loadBranches() {
    try {
      const res = await fetch('../api/branches.php?action=list');
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      branchesData = json.data.branches;
      renderBranchesTable();
    } catch (err) {
      document.getElementById('branchesTableBody').innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--admin-danger); padding:20px;">Error: ${err.message}</td></tr>`;
    }
  }

  function renderBranchesTable() {
    const tbody = document.getElementById('branchesTableBody');
    if (!branchesData || branchesData.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No branches found.</td></tr>`;
      return;
    }

    const rows = branchesData.map(b => `
      <tr>
        <td><span style="font-family:monospace; font-weight:700;">${escapeHtml(b.branch_code)}</span></td>
        <td>
          <strong>${escapeHtml(b.name)}</strong><br />
          <small style="color:var(--admin-text-muted);">${escapeHtml(b.tagline || 'Branch')}</small>
        </td>
        <td style="max-width:280px; white-space:normal;">
          <small>${escapeHtml(b.address)}</small>
        </td>
        <td>
          <span>${escapeHtml(b.director_name || 'Shivam Pandey')}</span><br />
          <small style="color:var(--admin-text-muted);"><i class="bi bi-telephone"></i> ${escapeHtml(b.phone || '—')}</small>
        </td>
        <td><span class="badge-status badge-contacted">${b.enquiry_count || 0} enquiries</span></td>
        <td>
          <span class="badge-status ${b.status === 'active' ? 'badge-active' : 'badge-inactive'}">
            ${b.status === 'active' ? 'Active' : 'Inactive'}
          </span>
        </td>
        <td style="text-align:right;">
          <div style="display:inline-flex; gap:6px;">
            <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" title="Toggle Status" onclick="toggleBranchStatus(${b.id}, '${b.status === 'active' ? 'inactive' : 'active'}')">
              <i class="bi ${b.status === 'active' ? 'bi-toggle-on text-success' : 'bi-toggle-off'}"></i>
            </button>
            <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="Edit Branch" onclick="openBranchModal(${b.id})">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteBranch(${b.id}, '${escapeHtml(b.name)}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');

    tbody.innerHTML = rows;
  }

  function openBranchModal(branchId = 0) {
    let b = {
      id: 0,
      branch_code: '',
      name: '',
      tagline: 'Main Center',
      address: '',
      city: 'Nawabganj',
      state: 'Uttar Pradesh',
      pincode: '271303',
      director_name: 'Shivam Pandey',
      phone: '+91 9696525287',
      email: 'mastercomputer@example.com',
      instagram: '@master.computer1',
      blog_url: 'https://mastercomputerinstitute.blogspot.com',
      status: 'active'
    };

    if (branchId > 0) {
      const found = branchesData.find(item => Number(item.id) === Number(branchId));
      if (found) b = found;
    }

    const title = branchId > 0 ? `Edit Branch — ${b.name}` : 'Add New Branch Center';
    const content = `
      <form id="branchForm" onsubmit="saveBranch(event)">
        <input type="hidden" name="id" value="${b.id}" />

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Branch Code *</label>
            <input type="text" name="branch_code" required value="${escapeHtml(b.branch_code)}" placeholder="e.g. MCI-BR01" />
          </div>
          <div class="form-field">
            <label>Center Name *</label>
            <input type="text" name="name" required value="${escapeHtml(b.name)}" placeholder="e.g. Master Computer Institute — Nawabganj" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Tagline / Badge</label>
            <input type="text" name="tagline" value="${escapeHtml(b.tagline)}" placeholder="e.g. Main Center" />
          </div>
          <div class="form-field">
            <label>Director Name</label>
            <input type="text" name="director_name" value="${escapeHtml(b.director_name)}" placeholder="Shivam Pandey" />
          </div>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Full Physical Address *</label>
          <textarea name="address" rows="2" required placeholder="Center address with landmark">${escapeHtml(b.address)}</textarea>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>City</label>
            <input type="text" name="city" value="${escapeHtml(b.city)}" />
          </div>
          <div class="form-field">
            <label>State</label>
            <input type="text" name="state" value="${escapeHtml(b.state)}" />
          </div>
          <div class="form-field">
            <label>Pincode</label>
            <input type="text" name="pincode" value="${escapeHtml(b.pincode)}" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Contact Phone</label>
            <input type="text" name="phone" value="${escapeHtml(b.phone || '')}" placeholder="+91 9696525287" />
          </div>
          <div class="form-field">
            <label>Contact Email</label>
            <input type="email" name="email" value="${escapeHtml(b.email || '')}" placeholder="center@example.com" />
          </div>
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Status</label>
          <select name="status">
            <option value="active" ${b.status === 'active' ? 'selected' : ''}>Active (Operational)</option>
            <option value="inactive" ${b.status === 'inactive' ? 'selected' : ''}>Inactive</option>
          </select>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Branch</button>
        </div>
      </form>
    `;

    openAdminModal(title, content);
  }

  async function saveBranch(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'save');

    try {
      const res = await fetch('../api/branches.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Branch saved successfully.');
      closeAdminModal();
      loadBranches();
    } catch (err) {
      showToast('Save error: ' + err.message, 'error');
    }
  }

  async function toggleBranchStatus(id, newStatus) {
    try {
      const formData = new FormData();
      formData.append('action', 'toggle_status');
      formData.append('id', id);
      formData.append('status', newStatus);

      const res = await fetch('../api/branches.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(`Branch status updated to ${newStatus}.`);
      loadBranches();
    } catch (err) {
      showToast('Error: ' + err.message, 'error');
    }
  }

  async function deleteBranch(id, name) {
    if (!confirm(`Are you sure you want to delete branch "${name}"?`)) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/branches.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Branch deleted.');
      loadBranches();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', loadBranches);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
