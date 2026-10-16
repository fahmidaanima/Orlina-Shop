/* Orlina Admin JS */
'use strict';

// Confirm deletes
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (form.dataset.confirm) {
    if (!confirm(form.dataset.confirm)) e.preventDefault();
  }
});

// Auto-close flash messages
const flashes = document.querySelectorAll('.flash');
flashes.forEach(f => {
  setTimeout(() => { f.style.opacity='0'; f.style.transition='opacity 0.5s'; setTimeout(()=>f.remove(),500); }, 4000);
});
