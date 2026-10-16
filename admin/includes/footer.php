    </main>

    <footer style="padding: 16px 28px; background: #fff; border-top: 1px solid var(--admin-border); font-size: 13px; color: var(--admin-text-muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
      <div>© 2026 Master Computer Institute. Secure Admin System.</div>
      <div>Designed for Master Computer Institute • Nawabganj, UP</div>
    </footer>
  </div>

  <!-- Common Admin Modal Dialog -->
  <div class="admin-modal" id="adminModal" aria-hidden="true">
    <div class="admin-modal-card">
      <div class="admin-modal-header">
        <h3 id="adminModalTitle">Modal Title</h3>
        <button type="button" class="admin-modal-close" onclick="closeAdminModal()">&times;</button>
      </div>
      <div class="admin-modal-body" id="adminModalBody">
        Loading...
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div class="admin-toast-container" id="adminToastContainer"></div>

  <script src="../js/admin.js?v=1"></script>
  <script>
    function toggleAdminSidebar() {
      const sidebar = document.getElementById('adminSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
      } else {
        sidebar.classList.add('open');
        overlay.style.display = 'block';
      }
    }

    function showToast(message, type = 'success') {
      const container = document.getElementById('adminToastContainer');
      const toast = document.createElement('div');
      toast.className = `admin-toast admin-toast-${type}`;
      const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'error' ? 'bi-x-circle-fill' : 'bi-info-circle-fill');
      toast.innerHTML = `<i class="bi ${icon}"></i><span>${message}</span>`;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    function openAdminModal(title, contentHtml) {
      document.getElementById('adminModalTitle').textContent = title;
      document.getElementById('adminModalBody').innerHTML = contentHtml;
      const modal = document.getElementById('adminModal');
      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeAdminModal() {
      const modal = document.getElementById('adminModal');
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden', 'true');
    }

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeAdminModal();
    });
  </script>
</body>
</html>
