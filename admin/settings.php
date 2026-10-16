<?php
/**
 * Master Computer Institute - Admin Website Settings Page
 */

$pageTitle = 'Website Settings';
$activeNav = 'settings';
require_once __DIR__ . '/includes/header.php';

$settings = get_all_settings($pdo);
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3><i class="bi bi-sliders"></i> Master Computer Institute Configuration</h3>
    <span style="font-size:12px; color:var(--admin-text-muted);">Content updates take effect on the public website immediately</span>
  </div>

  <div class="admin-card-body">
    <form id="settingsForm" onsubmit="saveSettings(event)">
      
      <div style="margin-bottom:24px;">
        <h4 style="font-size:15px; margin:0 0 14px; border-bottom:1px solid var(--admin-border); padding-bottom:8px; color:var(--admin-primary-light);">
          <i class="bi bi-building"></i> General Institute Identity
        </h4>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
          <div class="form-field">
            <label>Institute Name</label>
            <input type="text" name="settings[institute_name]" value="<?= e($settings['institute_name'] ?? 'Master Computer Institute') ?>" required />
          </div>
          <div class="form-field">
            <label>Tagline / Motto</label>
            <input type="text" name="settings[tagline]" value="<?= e($settings['tagline'] ?? 'Premier Computer Education • Nawabganj') ?>" />
          </div>
          <div class="form-field">
            <label>Director Name</label>
            <input type="text" name="settings[director_name]" value="<?= e($settings['director_name'] ?? 'Shivam Pandey') ?>" required />
          </div>
          <div class="form-field">
            <label>Operating Hours / Timing</label>
            <input type="text" name="settings[timing]" value="<?= e($settings['timing'] ?? 'Monday to Saturday: 8:00 AM - 6:00 PM') ?>" />
          </div>
        </div>
      </div>

      <div style="margin-bottom:24px;">
        <h4 style="font-size:15px; margin:0 0 14px; border-bottom:1px solid var(--admin-border); padding-bottom:8px; color:var(--admin-primary-light);">
          <i class="bi bi-geo-alt"></i> Location & Contact Numbers
        </h4>
        <div class="form-field" style="margin-bottom:14px;">
          <label>Full Physical Address</label>
          <input type="text" name="settings[address]" value="<?= e($settings['address'] ?? 'Center near Tripathi Hospital, Nawabganj, Uttar Pradesh - 271303, India') ?>" required />
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
          <div class="form-field">
            <label>City / Town</label>
            <input type="text" name="settings[city]" value="<?= e($settings['city'] ?? 'Nawabganj') ?>" />
          </div>
          <div class="form-field">
            <label>State</label>
            <input type="text" name="settings[state]" value="<?= e($settings['state'] ?? 'Uttar Pradesh') ?>" />
          </div>
          <div class="form-field">
            <label>PIN Code</label>
            <input type="text" name="settings[pincode]" value="<?= e($settings['pincode'] ?? '271303') ?>" />
          </div>
          <div class="form-field">
            <label>Helpline / Phone</label>
            <input type="text" name="settings[phone]" value="<?= e($settings['phone'] ?? '+91 9696525287') ?>" required />
          </div>
          <div class="form-field">
            <label>WhatsApp Number (without +)</label>
            <input type="text" name="settings[whatsapp]" value="<?= e($settings['whatsapp'] ?? '919696525287') ?>" />
          </div>
          <div class="form-field">
            <label>Inquiry Email Address</label>
            <input type="email" name="settings[email]" value="<?= e($settings['email'] ?? 'info@mastercomputer.edu') ?>" />
          </div>
        </div>
      </div>

      <div style="margin-bottom:28px;">
        <h4 style="font-size:15px; margin:0 0 14px; border-bottom:1px solid var(--admin-border); padding-bottom:8px; color:var(--admin-primary-light);">
          <i class="bi bi-share"></i> Social Links & Official Channels
        </h4>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
          <div class="form-field">
            <label>Instagram Handle</label>
            <input type="text" name="settings[instagram_handle]" value="<?= e($settings['instagram_handle'] ?? '@master.computer1') ?>" />
          </div>
          <div class="form-field">
            <label>Instagram Profile URL</label>
            <input type="url" name="settings[instagram_url]" value="<?= e($settings['instagram_url'] ?? 'https://www.instagram.com/master.computer1/') ?>" />
          </div>
          <div class="form-field">
            <label>Official Blog URL</label>
            <input type="url" name="settings[blog_url]" value="<?= e($settings['blog_url'] ?? 'https://mastercomputerinstitute.blogspot.com') ?>" />
          </div>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding:10px 24px; font-size:14px;">
          <i class="bi bi-check2-circle"></i> Save Settings
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  async function saveSettings(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'save_settings');

    try {
      const res = await fetch('../api/admin-actions.php', { method: 'POST', body: formData });
      const json = await res.json();
      if (!json.success) throw new Error(json.message);

      showToast(json.message || 'Website settings saved.');
    } catch (err) {
      showToast('Save error: ' + err.message, 'error');
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
