<?php
/**
 * Master Computer Institute - Admin Gallery Management Page
 */

$pageTitle = 'Gallery Management';
$activeNav = 'gallery';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-images"></i> Institute Photo Gallery</h3>
    <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" onclick="openGalleryUploadModal()">
      <i class="bi bi-upload"></i> Upload / Add Image
    </button>
  </div>

  <div class="admin-card-body">
    <div class="admin-toolbar">
      <div class="admin-filter-group">
        <select id="galleryCategoryFilter" onchange="loadGallery()">
          <option value="all">All Categories</option>
          <option value="classroom">Classroom</option>
          <option value="events">Events</option>
          <option value="activities">Activities</option>
          <option value="practical-training">Practical Training</option>
          <option value="certifications">Certifications</option>
        </select>
        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" onclick="loadGallery()">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
      </div>
    </div>

    <div id="galleryGridContainer" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:20px;">
      <div style="grid-column: 1/-1; text-align:center; padding:30px; color:var(--admin-text-muted);">Loading gallery items...</div>
    </div>
  </div>
</div>

<script>
  let galleryItems = [];

  async function loadGallery() {
    const cat = document.getElementById('galleryCategoryFilter').value;
    try {
      const res = await fetch(`../api/gallery.php?action=list&category=${encodeURIComponent(cat)}`);
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      galleryItems = json.data.images;
      renderGallery();
    } catch (err) {
      document.getElementById('galleryGridContainer').innerHTML = `<div style="grid-column:1/-1; text-align:center; color:var(--admin-danger); padding:20px;">Error: ${err.message}</div>`;
    }
  }

  function renderGallery() {
    const container = document.getElementById('galleryGridContainer');
    if (!galleryItems || galleryItems.length === 0) {
      container.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:30px; color:var(--admin-text-muted);">No images found in this category.</div>`;
      return;
    }

    const cards = galleryItems.map(item => `
      <div style="background:#fff; border:1px solid var(--admin-border); border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); display:flex; flex-direction:column;">
        <div style="height:150px; background:#0f172a; position:relative; overflow:hidden;">
          <img src="../${escapeHtml(item.image_path)}" alt="${escapeHtml(item.alt_text || item.title)}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='../assets/images/classroom/classroom-training-01.png'" />
          <span style="position:absolute; top:8px; left:8px; background:rgba(15,23,42,0.8); color:#fff; font-size:10px; padding:3px 7px; border-radius:4px; text-transform:uppercase;">
            ${escapeHtml(item.category)}
          </span>
          <span style="position:absolute; top:8px; right:8px;" class="badge-status ${item.status === 'active' ? 'badge-active' : 'badge-inactive'}">
            ${item.status}
          </span>
        </div>
        <div style="padding:12px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
          <h4 style="margin:0 0 8px; font-size:13px; font-weight:600; line-height:1.3;">${escapeHtml(item.title)}</h4>
          <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--admin-border); padding-top:8px; margin-top:8px;">
            <button type="button" class="btn-admin btn-admin-outline btn-admin-sm" title="Toggle Active" onclick="toggleGalleryStatus(${item.id}, '${item.status === 'active' ? 'inactive' : 'active'}')">
              <i class="bi ${item.status === 'active' ? 'bi-eye-slash' : 'bi-eye'}"></i>
            </button>
            <button type="button" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete" onclick="deleteGalleryItem(${item.id})">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>
    `).join('');

    container.innerHTML = cards;
  }

  function openGalleryUploadModal() {
    const title = 'Upload / Add Photo to Gallery';
    const content = `
      <form id="galleryUploadForm" onsubmit="uploadGalleryImage(event)" enctype="multipart/form-data">
        <div class="form-field" style="margin-bottom:14px;">
          <label>Image Title *</label>
          <input type="text" name="title" required placeholder="e.g. Practical Lab Session, Student Certification" />
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Category *</label>
          <select name="category" required>
            <option value="classroom">Classroom</option>
            <option value="events">Events</option>
            <option value="activities">Activities</option>
            <option value="practical-training">Practical Training</option>
            <option value="certifications">Certifications</option>
          </select>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Upload Image File (JPG, PNG, WEBP, max 5MB)</label>
          <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/svg+xml" style="padding:8px;" />
          <small style="color:var(--admin-text-muted); display:block; margin-top:4px;">Or specify an existing asset path below:</small>
        </div>

        <div class="form-field" style="margin-bottom:14px;">
          <label>Existing Asset Path (Optional fallback)</label>
          <input type="text" name="image_path" placeholder="assets/images/gallery/..." />
        </div>

        <div class="form-field" style="margin-bottom:18px;">
          <label>Alt Text for SEO / Accessibility</label>
          <input type="text" name="alt_text" placeholder="Description of the photo" />
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-admin btn-admin-outline" onclick="closeAdminModal()">Cancel</button>
          <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-upload"></i> Upload & Add</button>
        </div>
      </form>
    `;

    openAdminModal(title, content);
  }

  async function uploadGalleryImage(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'upload');

    try {
      const res = await fetch('../api/gallery.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Image added to gallery.');
      closeAdminModal();
      loadGallery();
    } catch (err) {
      showToast('Upload error: ' + err.message, 'error');
    }
  }

  async function toggleGalleryStatus(id, newStatus) {
    try {
      const formData = new FormData();
      formData.append('action', 'toggle_status');
      formData.append('id', id);
      formData.append('status', newStatus);

      const res = await fetch('../api/gallery.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(`Image set to ${newStatus}.`);
      loadGallery();
    } catch (err) {
      showToast('Error: ' + err.message, 'error');
    }
  }

  async function deleteGalleryItem(id) {
    if (!confirm('Are you sure you want to remove this photo from the gallery?')) return;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', id);

      const res = await fetch('../api/gallery.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast('Photo removed.');
      loadGallery();
    } catch (err) {
      showToast('Delete error: ' + err.message, 'error');
    }
  }

  document.addEventListener('DOMContentLoaded', loadGallery);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
