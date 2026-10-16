/* Orlina Admin JS */
'use strict';

// Auto-close flash messages
document.querySelectorAll('.flash').forEach(f => {
  setTimeout(() => {
    f.style.transition = 'opacity 0.5s';
    f.style.opacity = '0';
    setTimeout(() => f.remove(), 500);
  }, 4000);
});

// Confirm deletes
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (form.dataset.confirm) {
    if (!confirm(form.dataset.confirm)) e.preventDefault();
  }
});

// Mobile sidebar toggle
const sidebarToggle = document.getElementById('sidebar-toggle');
const adminSidebar  = document.getElementById('admin-sidebar');
if (sidebarToggle && adminSidebar) {
  sidebarToggle.addEventListener('click', () => {
    adminSidebar.classList.toggle('open');
  });
}

// Image preview on file input
document.querySelectorAll('input[type="file"]').forEach(input => {
  input.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const preview = e.target.closest('.admin-form-group')?.querySelector('.img-preview');
    if (preview) {
      const reader = new FileReader();
      reader.onload = ev => preview.src = ev.target.result;
      reader.readAsDataURL(file);
    }
  });
});
