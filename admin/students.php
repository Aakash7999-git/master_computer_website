<?php
/**
 * Master Computer Institute - Admin Student Management Page
 */

$pageTitle = 'Student Management';
$activeNav = 'students';
require_once __DIR__ . '/includes/header.php';

$coursesList = $pdo->query("SELECT name FROM courses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
$branchesList = $pdo->query("SELECT name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [];
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-people-fill"></i> Enrolled Students Directory</h3>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openRegisterStudentModal()">
        <i class="bi bi-person-plus-fill"></i> + Register Student
      </button>
      <button type="button" class="btn-admin btn-admin-success btn-admin-sm" onclick="exportStudentsCSV()">
        <i class="bi bi-file-earmark-excel"></i> Export CSV
      </button>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadStudents(1)">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
    </div>
  </div>

  <div class="admin-card-body">
    <!-- Toolbar -->
    <div class="admin-toolbar">
      <div class="admin-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="studentSearch" placeholder="Search student name, mobile, student code, admission code..." oninput="debounceStudentSearch()" />
      </div>

      <div class="admin-filter-group">
        <select id="filterStuSource" onchange="loadStudents(1)" style="font-weight:600;">
          <option value="">All Sources</option>
          <option value="Offline">Offline (Walk-in)</option>
          <option value="Website">Website Application</option>
        </select>

        <select id="filterStuStatus" onchange="loadStudents(1)">
          <option value="">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Completed">Completed</option>
          <option value="Dropped">Dropped</option>
        </select>

        <select id="filterStuCourse" onchange="loadStudents(1)">
          <option value="">All Courses</option>
          <?php foreach ($coursesList as $cName): ?>
            <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
          <?php endforeach; ?>
        </select>

        <select id="filterStuBranch" onchange="loadStudents(1)">
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
            <th>Student Code</th>
            <th>Admission Ref</th>
            <th>Student Name</th>
            <th>Contact & City</th>
            <th>Course & Branch</th>
            <th>Batch Timing</th>
            <th>Enrollment Date</th>
            <th>Source</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="studentsTableBody">
          <tr><td colspan="10" style="text-align:center; padding:30px;">Loading student records...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; flex-wrap:wrap; gap:10px;">
      <div id="studentsCountInfo" style="font-size:13px; color:var(--admin-text-muted);">Showing 0 records</div>
      <div id="studentsPagination" style="display:flex; gap:6px;"></div>
    </div>
  </div>
</div>

<script>
  let stuTimer = null;
  function debounceStudentSearch() {
    clearTimeout(stuTimer);
    stuTimer = setTimeout(() => loadStudents(1), 350);
  }

  async function loadStudents(page = 1) {
    const search = document.getElementById('studentSearch').value;
    const source = document.getElementById('filterStuSource').value;
    const status = document.getElementById('filterStuStatus').value;
    const course = document.getElementById('filterStuCourse').value;
    const branch = document.getElementById('filterStuBranch').value;

    const url = new URL('../api/students.php', window.location.href);
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

      renderStudentsTable(json.data.students);
      renderStuPagination(json.data.pagination);
    } catch (err) {
      document.getElementById('studentsTableBody').innerHTML = `<tr><td colspan="10" style="text-align:center; color:var(--admin-danger); padding:20px;">Error loading students: ${err.message}</td></tr>`;
    }
  }

  function renderStudentsTable(students) {
    const tbody = document.getElementById('studentsTableBody');
    if (!students || students.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:30px; color:var(--admin-text-muted);">No enrolled students found matching the selected criteria.</td></tr>`;
      return;
    }

    const rows = students.map(s => {
      let badgeClass = 'badge-active';
      if (s.status === 'Completed') badgeClass = 'badge-completed';
      else if (s.status === 'Dropped') badgeClass = 'badge-dropped';

      const isOffline = s.source === 'Offline';
      const sourceBadge = isOffline
        ? `<span class="badge-source badge-source-offline" title="Offline Walk-in Registration"><i class="bi bi-building"></i> Offline</span>`
        : `<span class="badge-source badge-source-website" title="Online Website Application"><i class="bi bi-globe"></i> Website</span>`;

      const admCodeHtml = s.admission_code
        ? `<a href="admissions.php?search=${encodeURIComponent(s.admission_code)}" style="color:var(--admin-primary); font-weight:600; text-decoration:none;"><i class="bi bi-link-45deg"></i> ${escapeHtml(s.admission_code)}</a>`
        : `<span style="color:var(--admin-text-muted); font-size:12px;">Direct</span>`;

      return `
        <tr>
          <td><strong style="color:var(--admin-accent); font-family:monospace; font-size:13px;">${escapeHtml(s.student_code)}</strong></td>
          <td>${admCodeHtml}</td>
          <td>
            <div style="font-weight:600; color:var(--admin-text);">${escapeHtml(s.full_name)}</div>
            ${s.gender ? `<span style="font-size:11px; color:var(--admin-text-muted);">${escapeHtml(s.gender)}</span>` : ''}
            ${s.dob ? `<span style="font-size:11px; color:var(--admin-text-muted);"> &bull; DOB: ${escapeHtml(s.dob)}</span>` : ''}
          </td>
          <td>
            <div><i class="bi bi-telephone-fill" style="font-size:11px; color:var(--admin-text-muted);"></i> ${escapeHtml(s.mobile)}</div>
            ${s.email ? `<div style="font-size:12px; color:var(--admin-text-muted);"><i class="bi bi-envelope"></i> ${escapeHtml(s.email)}</div>` : ''}
            <div style="font-size:11px; color:var(--admin-text-muted);"><i class="bi bi-geo-alt"></i> ${escapeHtml(s.city || 'Nawabganj')}</div>
          </td>
          <td>
            <span class="badge" style="background:var(--admin-bg); color:var(--admin-text); border:1px solid var(--admin-border);">${escapeHtml(s.course_name)}</span>
            <div style="font-size:11px; color:var(--admin-text-muted); margin-top:3px;"><i class="bi bi-geo"></i> ${escapeHtml(s.branch_name)}</div>
          </td>
          <td>${escapeHtml(s.batch_timing || 'Regular')}</td>
          <td>
            <div>${formatDate(s.enrollment_date || s.admission_date)}</div>
            <div style="font-size:11px; color:var(--admin-text-muted);">Adm: ${formatDate(s.admission_date)}</div>
          </td>
          <td>${sourceBadge}</td>
          <td><span class="badge ${badgeClass}">${escapeHtml(s.status)}</span></td>
          <td style="text-align:right; white-space:nowrap;">
            <button class="btn-admin btn-admin-outline btn-admin-sm" title="View Student Profile" onclick="viewStudentDetails(${s.id})">
              <i class="bi bi-eye"></i> View
            </button>
            <button class="btn-admin btn-admin-primary btn-admin-sm" title="Edit Student" onclick="editStudentDetails(${s.id})">
              <i class="bi bi-pencil"></i>
            </button>
          </td>
        </tr>
      `;
    }).join('');

    tbody.innerHTML = rows;
  }

  function renderStuPagination(pagination) {
    const { total_records, total_pages, current_page } = pagination;
    document.getElementById('studentsCountInfo').textContent = `Showing page ${current_page} of ${total_pages || 1} (${total_records} students)`;

    const pagDiv = document.getElementById('studentsPagination');
    if (total_pages <= 1) {
      pagDiv.innerHTML = '';
      return;
    }

    let html = '';
    if (current_page > 1) {
      html += `<button class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadStudents(${current_page - 1})">&laquo; Prev</button>`;
    }
    for (let i = 1; i <= total_pages; i++) {
      if (i === 1 || i === total_pages || (i >= current_page - 1 && i <= current_page + 1)) {
        html += `<button class="btn-admin btn-admin-sm ${i === current_page ? 'btn-admin-primary' : 'btn-admin-outline'}" onclick="loadStudents(${i})">${i}</button>`;
      } else if (i === current_page - 2 || i === current_page + 2) {
        html += `<span style="padding:4px 6px;">...</span>`;
      }
    }
    if (current_page < total_pages) {
      html += `<button class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadStudents(${current_page + 1})">Next &raquo;</button>`;
    }
    pagDiv.innerHTML = html;
  }

  function exportStudentsCSV() {
    const search = document.getElementById('studentSearch').value;
    const source = document.getElementById('filterStuSource').value;
    const status = document.getElementById('filterStuStatus').value;
    const course = document.getElementById('filterStuCourse').value;
    const branch = document.getElementById('filterStuBranch').value;

    const url = new URL('../api/students.php', window.location.href);
    url.searchParams.set('action', 'export_csv');
    url.searchParams.set('search', search);
    url.searchParams.set('source', source);
    url.searchParams.set('status', status);
    url.searchParams.set('course', course);
    url.searchParams.set('branch', branch);

    window.location.href = url.toString();
  }

  async function viewStudentDetails(id) {
    try {
      const res = await fetch(`../api/students.php?action=get&id=${id}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      const s = json.data.student;
      document.getElementById('viewStudentCode').textContent = s.student_code;
      document.getElementById('viewStudentName').textContent = s.full_name;
      document.getElementById('viewStudentAdmissionRef').innerHTML = s.admission_code
        ? `<a href="admissions.php?search=${encodeURIComponent(s.admission_code)}" target="_blank" style="color:var(--admin-primary); font-weight:600;"><i class="bi bi-link-45deg"></i> ${escapeHtml(s.admission_code)}</a>`
        : `<span style="color:var(--admin-text-muted);">Direct Enrollment</span>`;
      
      document.getElementById('viewStudentSource').innerHTML = s.source === 'Offline'
        ? `<span class="badge-source badge-source-offline"><i class="bi bi-building"></i> Offline Walk-in</span>`
        : `<span class="badge-source badge-source-website"><i class="bi bi-globe"></i> Website Application</span>`;

      document.getElementById('viewStudentStatus').innerHTML = `<span class="badge ${s.status === 'Active' ? 'badge-active' : (s.status === 'Completed' ? 'badge-completed' : 'badge-dropped')}">${escapeHtml(s.status)}</span>`;

      document.getElementById('viewStudentMobile').innerHTML = `<a href="tel:${escapeHtml(s.mobile)}" style="color:inherit; font-weight:600;"><i class="bi bi-telephone-fill" style="color:var(--admin-primary);"></i> ${escapeHtml(s.mobile)}</a>`;
      document.getElementById('viewStudentAltMobile').textContent = s.alternate_mobile || '—';
      document.getElementById('viewStudentEmail').textContent = s.email || '—';
      document.getElementById('viewStudentDob').textContent = s.dob ? formatDate(s.dob) : '—';
      document.getElementById('viewStudentGender').textContent = s.gender || '—';
      document.getElementById('viewStudentCity').textContent = s.city || 'Nawabganj';
      document.getElementById('viewStudentAddress').textContent = s.address || '—';
      document.getElementById('viewStudentQualification').textContent = s.qualification || '—';

      document.getElementById('viewStudentCourse').textContent = s.course_name;
      document.getElementById('viewStudentBranch').textContent = s.branch_name;
      document.getElementById('viewStudentBatch').textContent = s.batch_timing || 'Standard Timing';
      document.getElementById('viewStudentAdmissionDate').textContent = formatDate(s.admission_date);
      document.getElementById('viewStudentEnrollDate').textContent = formatDate(s.enrollment_date);
      document.getElementById('viewStudentCreatedBy').textContent = s.created_by_name ? `${s.created_by_name} (${s.created_by_username})` : 'System Administrator';
      document.getElementById('viewStudentRemarks').textContent = s.remarks || 'No internal remarks recorded.';

      document.getElementById('btnEditFromView').onclick = () => {
        closeViewStudentModal();
        editStudentDetails(s.id);
      };

      document.getElementById('viewStudentModal').style.display = 'flex';
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  function closeViewStudentModal() {
    document.getElementById('viewStudentModal').style.display = 'none';
  }

  async function editStudentDetails(id) {
    try {
      const res = await fetch(`../api/students.php?action=get&id=${id}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      const s = json.data.student;
      document.getElementById('editStudentId').value = s.id;
      document.getElementById('editStudentCodeBadge').textContent = s.student_code;
      document.getElementById('editStudentName').value = s.full_name;
      document.getElementById('editStudentMobile').value = s.mobile;
      document.getElementById('editStudentAltMobile').value = s.alternate_mobile || '';
      document.getElementById('editStudentEmail').value = s.email || '';
      document.getElementById('editStudentDob').value = s.dob || '';
      document.getElementById('editStudentGender').value = s.gender || '';
      document.getElementById('editStudentCity').value = s.city || 'Nawabganj';
      document.getElementById('editStudentAddress').value = s.address || '';
      document.getElementById('editStudentQualification').value = s.qualification || '';
      document.getElementById('editStudentCourse').value = s.course_name;
      document.getElementById('editStudentBranch').value = s.branch_name;
      document.getElementById('editStudentBatch').value = s.batch_timing || '';
      document.getElementById('editStudentStatus').value = s.status || 'Active';
      document.getElementById('editStudentRemarks').value = s.remarks || '';

      document.getElementById('editStudentModal').style.display = 'flex';
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  function closeEditStudentModal() {
    document.getElementById('editStudentModal').style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadStudents(1);

    // Edit form submission
    document.getElementById('editStudentForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const submitBtn = form.querySelector('button[type="submit"]');
      const origText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

      try {
        const formData = new FormData(form);
        const res = await fetch('../api/students.php?action=update', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        showToast(json.message, 'success');
        closeEditStudentModal();
        loadStudents(1);
      } catch (err) {
        showToast(err.message, 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;
      }
    });

    // Register Student Modal Form Submission
    document.getElementById('modalRegisterStudentForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const submitBtn = form.querySelector('button[type="submit"]');
      const origText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registering Student...';

      try {
        const formData = new FormData(form);
        formData.append('csrf_token', '<?= get_csrf_token() ?>');
        formData.append('status', 'Enrolled'); // Auto-enroll walk-in student

        const res = await fetch('../api/admissions.php?action=create_offline', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        showToast(json.message, 'success');
        closeRegisterStudentModal();
        form.reset();
        document.getElementById('reg_admission_date').value = new Date().toISOString().split('T')[0];
        loadStudents(1);
      } catch (err) {
        showToast(err.message, 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origText;
      }
    });
  });

  function openRegisterStudentModal() {
    const modal = document.getElementById('registerStudentModal');
    if (modal) {
      document.getElementById('modalRegisterStudentForm').reset();
      document.getElementById('reg_admission_date').value = new Date().toISOString().split('T')[0];
      modal.style.display = 'flex';
    }
  }

  function closeRegisterStudentModal() {
    const modal = document.getElementById('registerStudentModal');
    if (modal) modal.style.display = 'none';
  }
</script>

<!-- View Student Modal -->
<div id="viewStudentModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:16px;">
  <div style="background:var(--admin-surface); width:100%; max-width:760px; max-height:90vh; overflow-y:auto; border-radius:12px; border:1px solid var(--admin-border); box-shadow:var(--admin-shadow-lg); padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--admin-border); padding-bottom:14px; margin-bottom:18px;">
      <div>
        <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-person-badge-fill" style="color:var(--admin-primary);"></i>
          <span id="viewStudentName">Student Profile</span>
          <span id="viewStudentCode" style="font-family:monospace; font-size:14px; padding:2px 8px; border-radius:4px; background:var(--admin-bg); color:var(--admin-accent); border:1px solid var(--admin-border);"></span>
        </h3>
      </div>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="closeViewStudentModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:18px;">
      <div style="background:var(--admin-bg); padding:12px 14px; border-radius:8px; border:1px solid var(--admin-border);">
        <label style="font-size:11px; text-transform:uppercase; color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Admission Ref</label>
        <div id="viewStudentAdmissionRef" style="font-size:14px;"></div>
      </div>
      <div style="background:var(--admin-bg); padding:12px 14px; border-radius:8px; border:1px solid var(--admin-border);">
        <label style="font-size:11px; text-transform:uppercase; color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Registration Source</label>
        <div id="viewStudentSource" style="font-size:14px;"></div>
      </div>
      <div style="background:var(--admin-bg); padding:12px 14px; border-radius:8px; border:1px solid var(--admin-border);">
        <label style="font-size:11px; text-transform:uppercase; color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Student Status</label>
        <div id="viewStudentStatus" style="font-size:14px;"></div>
      </div>
    </div>

    <h4 style="font-size:14px; margin:16px 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
      <i class="bi bi-person-lines-fill"></i> Personal & Contact Details
    </h4>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:16px; font-size:13px;">
      <div><strong style="color:var(--admin-text-muted);">Mobile:</strong> <span id="viewStudentMobile"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Alternate Mobile:</strong> <span id="viewStudentAltMobile"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Email:</strong> <span id="viewStudentEmail"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Date of Birth:</strong> <span id="viewStudentDob"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Gender:</strong> <span id="viewStudentGender"></span></div>
      <div><strong style="color:var(--admin-text-muted);">City / District:</strong> <span id="viewStudentCity"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Qualification:</strong> <span id="viewStudentQualification"></span></div>
      <div style="grid-column:1 / -1;"><strong style="color:var(--admin-text-muted);">Address:</strong> <span id="viewStudentAddress"></span></div>
    </div>

    <h4 style="font-size:14px; margin:16px 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
      <i class="bi bi-book-half"></i> Course & Academic Details
    </h4>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:16px; font-size:13px;">
      <div><strong style="color:var(--admin-text-muted);">Enrolled Course:</strong> <span id="viewStudentCourse" style="font-weight:600;"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Training Branch:</strong> <span id="viewStudentBranch"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Batch Timing:</strong> <span id="viewStudentBatch"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Admission Date:</strong> <span id="viewStudentAdmissionDate"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Enrollment Date:</strong> <span id="viewStudentEnrollDate"></span></div>
      <div><strong style="color:var(--admin-text-muted);">Recorded By:</strong> <span id="viewStudentCreatedBy"></span></div>
    </div>

    <div style="background:var(--admin-bg); padding:12px; border-radius:8px; border:1px solid var(--admin-border); margin-top:10px;">
      <label style="font-size:11px; text-transform:uppercase; color:var(--admin-text-muted); font-weight:600; display:block; margin-bottom:4px;">Internal Remarks</label>
      <div id="viewStudentRemarks" style="font-size:13px; color:var(--admin-text);"></div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--admin-border); padding-top:14px;">
      <button type="button" class="btn-admin btn-admin-outline" onclick="closeViewStudentModal()">Close</button>
      <button type="button" class="btn-admin btn-admin-primary" id="btnEditFromView">
        <i class="bi bi-pencil"></i> Edit Student
      </button>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:16px;">
  <div style="background:var(--admin-surface); width:100%; max-width:760px; max-height:90vh; overflow-y:auto; border-radius:12px; border:1px solid var(--admin-border); box-shadow:var(--admin-shadow-lg); padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--admin-border); padding-bottom:14px; margin-bottom:18px;">
      <div>
        <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-pencil-square" style="color:var(--admin-primary);"></i> Edit Student Information
          <span id="editStudentCodeBadge" style="font-family:monospace; font-size:13px; padding:2px 8px; border-radius:4px; background:var(--admin-bg); color:var(--admin-accent); border:1px solid var(--admin-border);"></span>
        </h3>
      </div>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="closeEditStudentModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="editStudentForm">
      <input type="hidden" name="id" id="editStudentId" />

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Full Name <span style="color:var(--admin-danger);">*</span></label>
          <input type="text" name="full_name" id="editStudentName" class="admin-input" required />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Primary Mobile <span style="color:var(--admin-danger);">*</span></label>
          <input type="tel" name="mobile" id="editStudentMobile" class="admin-input" required maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Alternate Mobile</label>
          <input type="tel" name="alternate_mobile" id="editStudentAltMobile" class="admin-input" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email Address</label>
          <input type="email" name="email" id="editStudentEmail" class="admin-input" />
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Date of Birth</label>
          <input type="date" name="dob" id="editStudentDob" class="admin-input" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Gender</label>
          <select name="gender" id="editStudentGender" class="admin-input">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">City / District</label>
          <input type="text" name="city" id="editStudentCity" class="admin-input" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Qualification</label>
          <input type="text" name="qualification" id="editStudentQualification" class="admin-input" />
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Full Residential Address</label>
        <input type="text" name="address" id="editStudentAddress" class="admin-input" />
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Course <span style="color:var(--admin-danger);">*</span></label>
          <select name="course_name" id="editStudentCourse" class="admin-input" required>
            <?php foreach ($coursesList as $cName): ?>
              <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Branch / Center <span style="color:var(--admin-danger);">*</span></label>
          <select name="branch_name" id="editStudentBranch" class="admin-input" required>
            <?php foreach ($branchesList as $bName): ?>
              <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Batch / Timing</label>
          <input type="text" name="batch_timing" id="editStudentBatch" class="admin-input" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Student Status <span style="color:var(--admin-danger);">*</span></label>
          <select name="status" id="editStudentStatus" class="admin-input" required style="font-weight:600;">
            <option value="Active">Active</option>
            <option value="Completed">Completed</option>
            <option value="Dropped">Dropped</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Staff Remarks / Academic Notes</label>
        <textarea name="remarks" id="editStudentRemarks" rows="2" class="admin-input" placeholder="Notes on student progress, batch timing, certificates, etc."></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--admin-border); padding-top:14px;">
        <button type="button" class="btn-admin btn-admin-outline" onclick="closeEditStudentModal()">Cancel</button>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-check2"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Register Student Offline Modal -->
<div id="registerStudentModal" class="admin-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding:16px;">
  <div style="background:var(--admin-surface); width:100%; max-width:760px; max-height:90vh; overflow-y:auto; border-radius:12px; border:1px solid var(--admin-border); box-shadow:var(--admin-shadow-lg); padding:24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--admin-border); padding-bottom:14px; margin-bottom:18px;">
      <div>
        <h3 style="margin:0; font-size:18px; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-person-plus-fill" style="color:var(--admin-primary);"></i>
          Register Student (Offline Walk-in)
        </h3>
        <p style="margin:4px 0 0; font-size:12px; color:var(--admin-text-muted);">Directly admits and creates an enrolled student record in the Master Computer database.</p>
      </div>
      <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="closeRegisterStudentModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <form id="modalRegisterStudentForm">
      <!-- Personal Information -->
      <h4 style="font-size:14px; margin:0 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
        <i class="bi bi-person-lines-fill"></i> Personal Information
      </h4>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Student Full Name <span style="color:var(--admin-danger);">*</span></label>
          <input type="text" name="full_name" class="admin-input" required placeholder="e.g. Rahul Sharma" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mobile Number <span style="color:var(--admin-danger);">*</span></label>
          <input type="tel" name="mobile" class="admin-input" required placeholder="10-digit mobile" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Alternate Mobile</label>
          <input type="tel" name="alternate_mobile" class="admin-input" placeholder="Parent / Guardian mobile" maxlength="15" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email Address</label>
          <input type="email" name="email" class="admin-input" placeholder="student@example.com" />
        </div>
      </div>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Date of Birth</label>
          <input type="date" name="dob" class="admin-input" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Gender</label>
          <select name="gender" class="admin-input">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">City / Town</label>
          <input type="text" name="city" class="admin-input" value="Nawabganj" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Previous Qualification</label>
          <input type="text" name="qualification" class="admin-input" placeholder="e.g. 10th / 12th / Graduate" />
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Full Residential Address</label>
        <input type="text" name="address" class="admin-input" placeholder="Village / Ward, Post, Tehsil, District" />
      </div>

      <!-- Course & Academic Information -->
      <h4 style="font-size:14px; margin:16px 0 10px; color:var(--admin-primary); border-bottom:1px dashed var(--admin-border); padding-bottom:6px;">
        <i class="bi bi-book-half"></i> Academic & Course Information
      </h4>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:14px;">
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Selected Course <span style="color:var(--admin-danger);">*</span></label>
          <select name="course" class="admin-input" required>
            <option value="">-- Choose Course --</option>
            <?php foreach ($coursesList as $cName): ?>
              <option value="<?= e($cName) ?>"><?= e($cName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Admission Branch <span style="color:var(--admin-danger);">*</span></label>
          <select name="branch" class="admin-input" required>
            <option value="">-- Choose Branch --</option>
            <?php foreach ($branchesList as $bName): ?>
              <option value="<?= e($bName) ?>"><?= e($bName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Preferred Batch / Timing</label>
          <input type="text" name="batch" class="admin-input" placeholder="e.g. Morning 08:00 AM - 10:00 AM" />
        </div>
        <div>
          <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Admission Date</label>
          <input type="date" name="admission_date" id="reg_admission_date" class="admin-input" value="<?= date('Y-m-d') ?>" />
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Internal Remarks / Notes</label>
        <textarea name="remarks" rows="2" class="admin-input" placeholder="Optional staff notes, batch preference, fee discussion, etc."></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--admin-border); padding-top:14px;">
        <button type="button" class="btn-admin btn-admin-outline" onclick="closeRegisterStudentModal()">Cancel</button>
        <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-person-check-fill"></i> Save & Enroll Student</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
