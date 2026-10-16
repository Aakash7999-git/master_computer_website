/**
 * Master Computer Institute - Admin Dashboard Interactions
 */

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatDate(dateStr, format = 'short') {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;

  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const day = String(d.getDate()).padStart(2, '0');
  const month = months[d.getMonth()];
  const year = d.getFullYear();
  let hours = d.getHours();
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;

  if (format === 'dateOnly') {
    return `${day} ${month} ${year}`;
  }
  return `${day} ${month} ${year}, ${hours}:${minutes} ${ampm}`;
}

async function viewEnquiryDetails(id) {
  try {
    const res = await fetch(`../api/enquiries.php?action=get&id=${id}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const enq = json.data.enquiry;
    const cleanPhone = (enq.mobile || '').replace(/[^0-9]/g, '');

    const content = `
      <div style="font-size:14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:14px;">
          <div>
            <span style="font-family:monospace; font-weight:700; color:var(--admin-primary-light); font-size:15px;">${enq.enquiry_code}</span>
            <h3 style="margin:4px 0 0; font-size:18px;">${escapeHtml(enq.full_name)}</h3>
          </div>
          <div style="text-align:right;">
            <span style="font-size:12px; color:var(--admin-text-muted);">Received:</span><br />
            <strong>${formatDate(enq.created_at)}</strong>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid var(--admin-border);">
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Mobile Contact</small>
            <div style="display:flex; align-items:center; gap:8px; margin-top:2px;">
              <strong>${escapeHtml(enq.mobile)}</strong>
              <a href="tel:${cleanPhone}" class="btn-admin btn-admin-outline btn-admin-sm" title="Call"><i class="bi bi-telephone"></i></a>
              <a href="https://wa.me/91${cleanPhone}?text=Hello%20${encodeURIComponent(enq.full_name)}%2C%20regarding%20your%20enquiry%20for%20${encodeURIComponent(enq.course_name)}%20at%20Master%20Computer%20Institute." target="_blank" class="btn-admin btn-admin-success btn-admin-sm" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
            </div>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Email Address</small>
            <div style="margin-top:4px;">
              ${enq.email ? `<a href="mailto:${escapeHtml(enq.email)}" style="color:var(--admin-primary-light);">${escapeHtml(enq.email)}</a>` : '<em>Not provided</em>'}
            </div>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Interested Course</small>
            <strong style="color:var(--admin-primary-light); font-size:15px;">${escapeHtml(enq.course_name)}</strong>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Center / Branch</small>
            <strong>${escapeHtml(enq.branch_name)}</strong>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">City / Location</small>
            <span>${escapeHtml(enq.city || 'Nawabganj')}</span>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Preferred Batch</small>
            <span>${escapeHtml(enq.preferred_batch || 'Morning')}</span>
          </div>
        </div>

        ${enq.message ? `
          <div style="margin-bottom:18px;">
            <small style="color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Applicant Message / Notes:</small>
            <div style="background:#fff; border:1px solid var(--admin-border); border-radius:6px; padding:10px 14px; font-style:italic;">
              "${escapeHtml(enq.message)}"
            </div>
          </div>
        ` : ''}

        <div style="margin-top:20px; border-top:1px solid var(--admin-border); padding-top:16px;">
          <h4 style="margin:0 0 12px; font-size:14px; color:var(--admin-primary-light);">Update Status & Follow-up</h4>
          
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
            <div class="form-field">
              <label>Enquiry Status *</label>
              <select id="modalEnquiryStatus" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
                <option value="New" ${enq.status === 'New' ? 'selected' : ''}>New</option>
                <option value="Contacted" ${enq.status === 'Contacted' ? 'selected' : ''}>Contacted</option>
                <option value="Follow-up" ${enq.status === 'Follow-up' ? 'selected' : ''}>Follow-up</option>
                <option value="Converted" ${enq.status === 'Converted' ? 'selected' : ''}>Converted</option>
                <option value="Closed" ${enq.status === 'Closed' ? 'selected' : ''}>Closed</option>
              </select>
            </div>
            <div class="form-field">
              <label>Next Follow-up Date</label>
              <input type="date" id="modalEnquiryFollowUp" value="${enq.follow_up_date || ''}" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;" />
            </div>
          </div>

          <div class="form-field" style="margin-bottom:16px;">
            <label>Internal Administrator Notes</label>
            <textarea id="modalEnquiryNotes" rows="3" placeholder="Record discussion details, fee negotiations, or call summaries..." style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; box-sizing:border-box;">${escapeHtml(enq.admin_notes || '')}</textarea>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Close</button>
            <button type="button" class="btn-admin btn-admin-primary" onclick="saveEnquiryModal(${enq.id})">
              <i class="bi bi-check2-circle"></i> Save Changes
            </button>
          </div>
        </div>
      </div>
    `;

    openAdminModal(`Enquiry Details — ${enq.enquiry_code}`, content);
  } catch (err) {
    showToast('Failed to load enquiry: ' + err.message, 'error');
  }
}

async function saveEnquiryModal(id) {
  const status = document.getElementById('modalEnquiryStatus').value;
  const followUp = document.getElementById('modalEnquiryFollowUp').value;
  const notes = document.getElementById('modalEnquiryNotes').value;

  try {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);
    formData.append('follow_up_date', followUp);
    formData.append('admin_notes', notes);

    const res = await fetch('../api/enquiries.php', { method: 'POST', body: formData });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    showToast('Enquiry record updated successfully.');
    closeAdminModal();
    if (typeof loadEnquiries === 'function') {
      loadEnquiries();
    } else {
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (err) {
    showToast('Update error: ' + err.message, 'error');
  }
}

async function viewAdmissionDetails(id) {
  try {
    const res = await fetch(`../api/admissions.php?action=get&id=${id}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const adm = json.data.admission;
    const cleanPhone = (adm.mobile || '').replace(/[^0-9]/g, '');

    const content = `
      <div style="font-size:14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:14px;">
          <div>
            <span style="font-family:monospace; font-weight:700; color:var(--admin-primary-light); font-size:15px;">${adm.admission_code}</span>
            <h3 style="margin:4px 0 0; font-size:18px;">${escapeHtml(adm.full_name)}</h3>
          </div>
          <div style="text-align:right;">
            <span style="font-size:12px; color:var(--admin-text-muted);">Submitted:</span><br />
            <strong>${formatDate(adm.created_at)}</strong>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid var(--admin-border);">
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Mobile</small>
            <div style="display:flex; align-items:center; gap:8px;">
              <strong>${escapeHtml(adm.mobile)}</strong>
              <a href="tel:${cleanPhone}" class="btn-admin btn-admin-outline btn-admin-sm"><i class="bi bi-telephone"></i></a>
            </div>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Email</small>
            <a href="mailto:${escapeHtml(adm.email)}" style="color:var(--admin-primary-light);">${escapeHtml(adm.email)}</a>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Date of Birth / Gender</small>
            <span>${escapeHtml(adm.dob || '—')} • ${escapeHtml(adm.gender || '—')}</span>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Education Qualification</small>
            <strong>${escapeHtml(adm.qualification)}</strong>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Applied Course</small>
            <strong style="color:var(--admin-primary-light);">${escapeHtml(adm.course_name)}</strong>
          </div>
          <div>
            <small style="color:var(--admin-text-muted); display:block;">Center & Preferred Batch</small>
            <span>${escapeHtml(adm.branch_name)} (${escapeHtml(adm.batch)})</span>
          </div>
          <div style="grid-column: 1 / -1;">
            <small style="color:var(--admin-text-muted); display:block;">Residential Address</small>
            <span>${escapeHtml(adm.address)}, ${escapeHtml(adm.city)}</span>
          </div>
        </div>

        ${adm.message ? `
          <div style="margin-bottom:16px;">
            <small style="color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Applicant Statement:</small>
            <div style="background:#fff; border:1px solid var(--admin-border); border-radius:6px; padding:10px 14px; font-style:italic;">
              "${escapeHtml(adm.message)}"
            </div>
          </div>
        ` : ''}

        <div style="border-top:1px solid var(--admin-border); padding-top:16px;">
          <h4 style="margin:0 0 12px; font-size:14px; color:var(--admin-primary-light);">Update Application Status</h4>
          
          <div class="form-field" style="margin-bottom:14px;">
            <label>Application Status</label>
            <select id="modalAdmissionStatus" style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px;">
              <option value="New" ${adm.status === 'New' ? 'selected' : ''}>New</option>
              <option value="Under Review" ${adm.status === 'Under Review' ? 'selected' : ''}>Under Review</option>
              <option value="Approved" ${adm.status === 'Approved' ? 'selected' : ''}>Approved</option>
              <option value="Enrolled" ${adm.status === 'Enrolled' ? 'selected' : ''}>Enrolled</option>
              <option value="Rejected" ${adm.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
            </select>
          </div>

          <div class="form-field" style="margin-bottom:16px;">
            <label>Admission Notes</label>
            <textarea id="modalAdmissionNotes" rows="3" placeholder="Enrollment details, document verification status..." style="width:100%; padding:9px 12px; border:1px solid var(--admin-border); border-radius:8px; box-sizing:border-box;">${escapeHtml(adm.admin_notes || '')}</textarea>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Close</button>
            <button type="button" class="btn-admin btn-admin-primary" onclick="saveAdmissionModal(${adm.id})">
              <i class="bi bi-check2-circle"></i> Save Changes
            </button>
          </div>
        </div>
      </div>
    `;

    openAdminModal(`Admission Review — ${adm.admission_code}`, content);
  } catch (err) {
    showToast('Failed to load admission: ' + err.message, 'error');
  }
}

async function saveAdmissionModal(id) {
  const status = document.getElementById('modalAdmissionStatus').value;
  const notes = document.getElementById('modalAdmissionNotes').value;

  try {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);
    formData.append('admin_notes', notes);

    const res = await fetch('../api/admissions.php', { method: 'POST', body: formData });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    showToast('Admission application updated.');
    closeAdminModal();
    if (typeof loadAdmissions === 'function') {
      loadAdmissions();
    } else {
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (err) {
    showToast('Update error: ' + err.message, 'error');
  }
}
