<?php
/**
 * Master Computer Institute - Admin Course Management Page
 */

$pageTitle = 'Course Management';
$activeNav = 'courses';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-journal-code"></i> Institute Courses & Training Programs</h3>
    <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openCourseModal()">
      <i class="bi bi-plus-lg"></i> Add New Course
    </button>
  </div>

  <div class="admin-card-body">
    <div class="admin-table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Course</th>
            <th>Category & Code</th>
            <th>Duration</th>
            <th>Fee (INR)</th>
            <th>Enquiries</th>
            <th>Admissions</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="coursesTableBody">
          <tr><td colspan="8" style="text-align:center; padding:30px;">Loading courses...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let coursesData = [];

  async function loadCourses() {
    try {
      const res = await fetch('../api/courses.php?action=list');
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      coursesData = json.data.courses;
      renderCoursesTable();
    } catch (err) {
      document.getElementById('coursesTableBody').innerHTML = `<tr><td colspan="8" style="text-align:center; color:var(--admin-danger); padding:20px;">Error: ${err.message}</td></tr>`;
    }
  }

  function renderCoursesTable() {
    const tbody = document.getElementById('coursesTableBody');
    if (!coursesData || coursesData.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No courses found. Click 'Add New Course' to create one.</td></tr>`;
      return;
    }

    const rows = coursesData.map(c => `
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:12px;">
            <img src="../${escapeHtml(c.image_url || 'assets/images/courses/ccc-course.jpg')}" alt="${escapeHtml(c.name)}" style="width:48px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--admin-border);" onerror="this.src='../assets/logos/mci-logo.svg'" />
            <div>
              <strong>${escapeHtml(c.name)}</strong><br />
              <small style="color:var(--admin-text-muted);">${escapeHtml(c.eligibility || 'Open to all')}</small>
            </div>
          </div>
        </td>
        <td>
          <span style="font-family:monospace; font-weight:700;">${escapeHtml(c.course_code)}</span><br />
          <small style="color:var(--admin-text-muted);">${escapeHtml(c.category)}</small>
        </td>
        <td><i class="bi bi-clock"></i> ${escapeHtml(c.duration)}</td>
        <td><strong style="color:var(--admin-primary-light);">₹${Number(c.fee).toLocaleString('en-IN')}</strong></td>
        <td><span class="badge-status badge-contacted">${c.enquiry_count || 0} enquiries</span></td>
        <td><span class="badge-status badge-approved">${c.admission_count || 0} applied</span></td>
        <td>
          <span class="badge-status ${c.status === 'active' ? 'badge-active' : 'badge-inactive'}">
            ${c.status === 'active' ? 'Active' : 'Inactive'}
          </span>
        </td>
        <td style="text-align:right;">
          <div style="display:inline-flex; gap:6px;">
            <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" title="Toggle Status" onclick="toggleCourseStatus(${c.id}, '${c.status === 'active' ? 'inactive' : 'active'}')">
              <i class="bi ${c.status === 'active' ? 'bi-toggle-on text-success' : 'bi-toggle-off'}"></i>
            </button>
            <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" title="Edit Course" onclick="openCourseModal(${c.id})">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteCourse(${c.id}, '${escapeHtml(c.name)}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');

    tbody.innerHTML = rows;
  }

  function openCourseModal(courseId = 0) {
    let c = {
      id: 0,
      course_code: '',
      name: '',
      category: 'Diploma',
      duration: '3 Months',
      fee: 3499,
      short_desc: '',
      description: '',
      eligibility: '10th / 12th',
      certification: 'Govt. Recognized MCI Certificate',
      badge: '',
      image_url: 'assets/images/courses/ccc-course.jpg',
      status: 'active'
    };

    if (courseId > 0) {
      const found = coursesData.find(item => Number(item.id) === Number(courseId));
      if (found) c = found;
    }

    const title = courseId > 0 ? `Edit Course — ${c.course_code}` : 'Add New Course';
    const content = `
      <form id="courseForm" onsubmit="saveCourse(event)">
        <input type="hidden" name="id" value="${c.id}" />

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Course Code *</label>
            <input type="text" name="course_code" required value="${escapeHtml(c.course_code)}" placeholder="e.g. ADCA, CCC, TALLY" />
          </div>
          <div class="form-field">
            <label>Course Title *</label>
            <input type="text" name="name" required value="${escapeHtml(c.name)}" placeholder="e.g. ADCA — Advanced Diploma" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Category</label>
            <input type="text" name="category" value="${escapeHtml(c.category)}" placeholder="e.g. Accounting, Diploma" />
          </div>
          <div class="form-field">
            <label>Duration *</label>
            <input type="text" name="duration" required value="${escapeHtml(c.duration)}" placeholder="e.g. 6 Months" />
          </div>
          <div class="form-field">
            <label>Fee (INR) *</label>
            <input type="number" step="1" min="0" name="fee" required value="${c.fee}" />
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:14px;">
          <div class="form-field">
            <label>Eligibility</label>
            <input type="text" name="eligibility" value="${escapeHtml(c.eligibility || '')}" placeholder="e.g. 10th / 12th / All Stream" />
          </div>
          <div class="form-field">
            <label>Badge / Ribbon</label>
            <input type="text" name="badge" value="${escapeHtml(c.badge || '')}" placeholder="e.g. Career Track, Govt. Recognized" />
          </div>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Image Asset Path</label>
          <input type="text" name="image_url" value="${escapeHtml(c.image_url || '')}" placeholder="assets/images/courses/..." />
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Short Summary</label>
          <input type="text" name="short_desc" value="${escapeHtml(c.short_desc || '')}" placeholder="Brief 1-sentence course overview" />
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Full Description & Syllabus</label>
          <textarea name="description" rows="4" placeholder="Detailed curriculum, modules, career scope...">${escapeHtml(c.description || '')}</textarea>
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Status</label>
          <select name="status">
            <option value="active" ${c.status === 'active' ? 'selected' : ''}>Active (Visible to public)</option>
            <option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>Inactive (Hidden)</option>
          </select>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Course</button>
        </div>
      </form>
    `;

    openAdminModal(title, content);
  }

  async function saveCourse(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'save');

    try {
      const res = await fetch('../api/courses.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(json.message || 'Course saved successfully.');
      closeAdminModal();
      loadCourses();
    } catch (err) {
      showToast('Save error: ' + err.message, 'error');
    }
  }

  async function toggleCourseStatus(id, newStatus) {
    try {
      const formData = new FormData();
      formData.append('action', 'toggle_status');
      formData.append('id', id);
      formData.append('status', newStatus);

      const res = await fetch('../api/courses.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(`Course status updated to ${newStatus}.`);
      loadCourses();
    } catch (err) {
      showToast('Status error: ' + err.message, 'error');
    }
  }

  async function deleteCourse(id, name) {
    if (!confirm(`Are you sure you want to delete course "${name}"? Existing enquiries for this course will remain.`)) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/courses.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Course deleted.');
      loadCourses();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', loadCourses);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
