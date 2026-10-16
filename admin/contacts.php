<?php
/**
 * Master Computer Institute - Admin Contact Messages Page
 */

$pageTitle = 'Contact Messages';
$activeNav = 'contacts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-envelope-fill"></i> Inbound Contact Messages</h3>
    <div style="display:flex; gap:10px;">
      <select id="filterMsgStatus" onchange="loadMessages()" style="padding:7px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:13px;">
        <option value="">All Statuses</option>
        <option value="Unread">Unread Only</option>
        <option value="Read">Read</option>
        <option value="Replied">Replied</option>
      </select>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadMessages()">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>
  </div>

  <div class="admin-card-body">
    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Sender Name</th>
            <th>Email & Phone</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Date Sent</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="messagesTableBody">
          <tr><td colspan="6" style="text-align:center; padding:30px;">Loading messages...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let messagesData = [];

  async function loadMessages() {
    const status = document.getElementById('filterMsgStatus').value;
    try {
      const res = await fetch(`../api/admin-actions.php?action=list_contacts&status=${encodeURIComponent(status)}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      messagesData = json.data.messages;
      renderMessagesTable();
    } catch (err) {
      document.getElementById('messagesTableBody').innerHTML = `<tr><td colspan="6" style="text-align:center; color:var(--admin-danger); padding:20px;">Error: ${err.message}</td></tr>`;
    }
  }

  function renderMessagesTable() {
    const tbody = document.getElementById('messagesTableBody');
    if (!messagesData || messagesData.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No contact messages found.</td></tr>`;
      return;
    }

    const rows = messagesData.map(msg => {
      let badge = 'badge-unread';
      if (msg.status === 'Read') badge = 'badge-read';
      else if (msg.status === 'Replied') badge = 'badge-active';

      return `
        <tr style="${msg.status === 'Unread' ? 'font-weight:600; background:#f8fafc;' : ''}">
          <td>${escapeHtml(msg.name)}</td>
          <td>
            <a href="mailto:${escapeHtml(msg.email)}" style="color:var(--admin-primary-light); text-decoration:none;">${escapeHtml(msg.email)}</a><br />
            <small style="color:var(--admin-text-muted);"><i class="bi bi-telephone"></i> ${escapeHtml(msg.mobile)}</small>
          </td>
          <td>
            <span>${escapeHtml(msg.subject)}</span><br />
            <small style="color:var(--admin-text-muted); font-weight:normal;">${escapeHtml((msg.message || '').substring(0, 60))}...</small>
          </td>
          <td><span class="badge-status ${badge}">${msg.status}</span></td>
          <td>${formatDate(msg.created_at)}</td>
          <td style="text-align:right;">
            <div style="display:inline-flex; gap:6px;">
              <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="View Full Message" onclick="viewMessageDetails(${msg.id})">
                <i class="bi bi-envelope-open"></i> Read
              </button>
              <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteMessage(${msg.id})">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rows;
  }

  function viewMessageDetails(id) {
    const msg = messagesData.find(m => Number(m.id) === Number(id));
    if (!msg) return;

    // Automatically mark read if unread
    if (msg.status === 'Unread') {
      updateMessageStatus(id, 'Read', false);
      msg.status = 'Read';
    }

    const cleanPhone = (msg.mobile || '').replace(/[^0-9]/g, '');

    const content = `
      <div style="margin-bottom:18px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; border-bottom:1px solid var(--admin-border); padding-bottom:12px;">
          <div>
            <h4 style="margin:0 0 4px; font-size:16px;">${escapeHtml(msg.name)}</h4>
            <div style="font-size:13px; color:var(--admin-text-muted);">
              <span><i class="bi bi-envelope"></i> ${escapeHtml(msg.email)}</span> • 
              <span><i class="bi bi-telephone"></i> ${escapeHtml(msg.mobile)}</span>
            </div>
          </div>
          <span style="font-size:12px; color:var(--admin-text-muted);">${formatDate(msg.created_at)}</span>
        </div>

        <div style="margin-bottom:16px;">
          <strong style="font-size:13px; color:var(--admin-text-muted); display:block; margin-bottom:4px;">Subject:</strong>
          <div style="font-size:15px; font-weight:600;">${escapeHtml(msg.subject)}</div>
        </div>

        <div style="margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid var(--admin-border); font-size:14px; line-height:1.6; white-space:pre-wrap;">
${escapeHtml(msg.message)}
        </div>

        <div style="margin-bottom:18px;">
          <label style="font-size:13px; font-weight:600; display:block; margin-bottom:6px;">Update Message Status & Internal Notes</label>
          <div style="display:flex; gap:10px; margin-bottom:10px;">
            <select id="modalMsgStatus" style="padding:8px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:13px;">
              <option value="Unread" ${msg.status === 'Unread' ? 'selected' : ''}>Unread</option>
              <option value="Read" ${msg.status === 'Read' ? 'selected' : ''}>Read</option>
              <option value="Replied" ${msg.status === 'Replied' ? 'selected' : ''}>Replied</option>
              <option value="Archived" ${msg.status === 'Archived' ? 'selected' : ''}>Archived</option>
            </select>
          </div>
          <textarea id="modalMsgNotes" rows="2" style="width:100%; padding:8px 12px; border:1px solid var(--admin-border); border-radius:6px; font-size:13px; box-sizing:border-box;" placeholder="Optional internal admin notes...">${escapeHtml(msg.admin_notes || '')}</textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-top:1px solid var(--admin-border); padding-top:16px;">
          <div style="display:flex; gap:8px;">
            <a href="mailto:${escapeHtml(msg.email)}?subject=Re:%20${encodeURIComponent(msg.subject)}" class="btn-admin btn-admin-primary btn-admin-sm"><i class="bi bi-reply-fill"></i> Reply by Email</a>
            <a href="https://wa.me/91${cleanPhone}?text=Hello%20${encodeURIComponent(msg.name)}%2C%20regarding%20your%20message%20to%20Master%20Computer%20Institute." target="_blank" class="btn-admin btn-admin-success btn-admin-sm"><i class="bi bi-whatsapp"></i> WhatsApp</a>
          </div>
          <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="saveMessageStatusFromModal(${id})"><i class="bi bi-check-lg"></i> Save Status & Notes</button>
        </div>
      </div>
    `;

    openAdminModal(`Message from ${msg.name}`, content);
  }

  async function updateMessageStatus(id, status, reload = true) {
    try {
      const formData = new FormData();
      formData.append('action', 'update_contact_status');
      formData.append('id', id);
      formData.append('status', status);

      await fetch('../api/admin-actions.php', { method: 'POST', body: formData });
      if (reload) loadMessages();
    } catch (e) {}
  }

  async function saveMessageStatusFromModal(id) {
    const status = document.getElementById('modalMsgStatus').value;
    const notes = document.getElementById('modalMsgNotes').value;

    try {
      const formData = new FormData();
      formData.append('action', 'update_contact_status');
      formData.append('id', id);
      formData.append('status', status);
      formData.append('admin_notes', notes);

      const res = await fetch('../api/admin-actions.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Message status updated.');
      closeAdminModal();
      loadMessages();
    } catch (err) {
      showToast('Error: ' + err.message, 'error');
    }
  }

  async function deleteMessage(id) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete_contact');
      formData.append('id', id);

      const res = await fetch('../api/admin-actions.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Message deleted.');
      loadMessages();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', loadMessages);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
