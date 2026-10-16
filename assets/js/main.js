/* ============================================================
   ORLINA — Main JavaScript
   © 2026 Fahmida Islam
============================================================ */
'use strict';

// ── BASE PATH AUTO-DETECTION ─────────────────────────────────
const _BASE = window._BASE !== undefined ? window._BASE : (window.location.pathname.toLowerCase().startsWith('/orlina') ? window.location.pathname.slice(0, 7) : '');

// ── HAMBURGER / MOBILE NAV ───────────────────────────────────
(function () {
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('main-nav');
  if (hamburger && nav) {
    hamburger.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open);
    });
  }
})();

// ── HEADER SCROLL SHADOW ─────────────────────────────────────
window.addEventListener('scroll', () => {
  const header = document.getElementById('site-header');
  if (header) {
    header.style.boxShadow = window.scrollY > 10
      ? '0 4px 30px rgba(0,0,0,0.1)'
      : '0 2px 20px rgba(0,0,0,0.06)';
  }
}, { passive: true });

// ── FLASH AUTO-DISMISS ───────────────────────────────────────
document.querySelectorAll('.flash').forEach(f => {
  setTimeout(() => {
    f.style.transition = 'opacity 0.5s';
    f.style.opacity = '0';
    setTimeout(() => f.remove(), 500);
  }, 4500);
});

// ── CART AJAX ────────────────────────────────────────────────
function updateCartBadge(count) {
  const badge = document.getElementById('cart-badge');
  if (!badge) return;
  badge.textContent = count > 0 ? count : '';
  badge.style.transform = 'scale(1.4)';
  setTimeout(() => badge.style.transform = '', 200);
}

function showToast(message, type = 'success') {
  const existing = document.getElementById('orlina-toast');
  if (existing) existing.remove();
  const toast = document.createElement('div');
  toast.id = 'orlina-toast';
  toast.style.cssText = `
    position:fixed;bottom:28px;right:28px;z-index:9999;
    background:${type === 'success' ? 'linear-gradient(135deg,#ec4899,#8b5cf6)' : '#ef4444'};
    color:#fff;padding:14px 22px;border-radius:12px;
    font-family:'Outfit',sans-serif;font-weight:600;font-size:0.9rem;
    box-shadow:0 8px 32px rgba(0,0,0,0.18);
    display:flex;align-items:center;gap:10px;
    animation:slideUp 0.3s;max-width:340px;
  `;
  toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
  document.body.appendChild(toast);

  const style = document.createElement('style');
  style.textContent = '@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}';
  document.head.appendChild(style);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.4s';
    setTimeout(() => toast.remove(), 400);
  }, 3000);
}

// Add to Cart buttons
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-add-cart]');
  if (!btn) return;
  e.preventDefault();

  const productId = btn.dataset.addCart;
  const qtyInput  = document.getElementById('qty-input');
  const quantity   = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
  const csrf       = window.__csrf || '';

  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner" style="animation:spin 0.8s linear infinite"></i> Adding…';
  btn.disabled = true;

  try {
    const res = await fetch(_BASE + '/pages/cart_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=add&product_id=${productId}&quantity=${quantity}&csrf_token=${encodeURIComponent(csrf)}`
    });
    const data = await res.json();
    if (data.redirect) {
      showToast(data.message || 'Please login to continue.', 'error');
      setTimeout(() => { window.location.href = data.redirect; }, 900);
      return;
    }
    if (data.success) {
      updateCartBadge(data.cart_count);
      showToast('Added to cart!');
    } else {
      showToast(data.message || 'Could not add to cart.', 'error');
    }
  } catch {
    showToast('Connection error. Please try again.', 'error');
  } finally {
    btn.innerHTML = originalHtml;
    btn.disabled = false;
  }
});

// Remove from Cart (AJAX intercept for POST forms)
document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (!form.hasAttribute('data-remove-cart')) return;
  e.preventDefault();

  const formData = new FormData(form);
  try {
    const res = await fetch(_BASE + '/pages/cart_action.php', {
      method: 'POST',
      body: new URLSearchParams(formData)
    });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(data.cart_count);
      const pid = formData.get('product_id');
      const row = document.getElementById('cart-row-' + pid);
      if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity = '0';
        setTimeout(() => { row.remove(); updateCartTotals(data.cart_total, data.cart_count); }, 300);
      }
      showToast('Item removed from cart.');
    }
  } catch {
    form.submit(); // Fallback
  }
});

function updateCartTotals(total, count) {
  const totalEl = document.getElementById('cart-total');
  if (totalEl && total !== undefined) totalEl.textContent = '৳' + parseInt(total).toLocaleString();
  if (count === 0) {
    const tbody = document.getElementById('cart-body');
    if (tbody) {
      const container = tbody.closest('.cart-layout');
      if (container) container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-bag"></i><h3>Your cart is empty</h3><p>Go back and add some items.</p><a href="' + _BASE + '/pages/shop.php" class="btn btn-primary mt-4">Start Shopping</a></div>';
    }
  }
}

// Cart quantity update on cart page
document.querySelectorAll('[data-cart-qty]').forEach(input => {
  let timeout;
  input.addEventListener('change', async () => {
    clearTimeout(timeout);
    timeout = setTimeout(async () => {
      const pid = input.dataset.cartQty;
      const qty = parseInt(input.value);
      if (qty < 1) return;
      const csrf = window.__csrf || '';
      try {
        const res = await fetch(_BASE + '/pages/cart_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=update&product_id=${pid}&quantity=${qty}&csrf_token=${encodeURIComponent(csrf)}`
        });
        const data = await res.json();
        if (data.success) {
          updateCartBadge(data.cart_count);
          const subtotalEl = document.querySelector('[data-subtotal="' + pid + '"]');
          if (subtotalEl && data.item_subtotal !== undefined) {
            subtotalEl.textContent = '৳' + parseInt(data.item_subtotal).toLocaleString();
          }
          updateCartTotals(data.cart_total, data.cart_count);
        }
      } catch {}
    }, 400);
  });
});

// ── WISHLIST ─────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-wishlist]');
  if (!btn) return;
  e.preventDefault();

  const productId = btn.dataset.wishlist;
  const csrf = window.__csrf || '';

  try {
    const res = await fetch(_BASE + '/pages/wishlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=toggle&product_id=${productId}&csrf_token=${encodeURIComponent(csrf)}`
    });
    const data = await res.json();
    if (data.redirect) {
      showToast(data.message || 'Please login to continue.', 'error');
      setTimeout(() => { window.location.href = data.redirect; }, 900);
      return;
    }
    if (data.success) {
      const icon = btn.querySelector('i');
      if (icon) {
        icon.className = data.wishlisted ? 'fas fa-heart' : 'far fa-heart';
      }
      btn.classList.toggle('wishlisted', !!data.wishlisted);
      showToast(data.wishlisted ? 'Added to wishlist!' : 'Removed from wishlist.');
    }
  } catch {
    showToast('Please login to use wishlist.', 'error');
  }
});

// ── PRODUCT GALLERY ──────────────────────────────────────────
document.querySelectorAll('.gallery-thumb').forEach(thumb => {
  thumb.addEventListener('click', () => {
    const mainImg = document.getElementById('gallery-main-img');
    if (mainImg) {
      mainImg.src = thumb.dataset.src;
      mainImg.style.transform = 'scale(0.96)';
      setTimeout(() => mainImg.style.transform = '', 200);
    }
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
  });
});

// ── PRODUCT TABS ─────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tabId = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + tabId);
    if (panel) panel.classList.add('active');
  });
});

// ── PRICE RANGE SLIDER ───────────────────────────────────────
const priceRange = document.getElementById('price-range');
const priceLabel = document.getElementById('price-max-label');
if (priceRange && priceLabel) {
  priceRange.addEventListener('input', () => {
    priceLabel.textContent = '৳' + parseInt(priceRange.value).toLocaleString();
  });
}

// ── COUNTDOWN TIMER ──────────────────────────────────────────
const countdownEnd = document.getElementById('countdown-end');
if (countdownEnd) {
  function updateCountdown() {
    const end = new Date(countdownEnd.dataset.end.replace(' ', 'T')).getTime();
    const now = new Date().getTime();
    const diff = end - now;
    if (diff <= 0) {
      document.getElementById('cd-hours').textContent = '00';
      document.getElementById('cd-mins').textContent  = '00';
      document.getElementById('cd-secs').textContent  = '00';
      return;
    }
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('cd-hours').textContent = pad(h);
    document.getElementById('cd-mins').textContent  = pad(m);
    document.getElementById('cd-secs').textContent  = pad(s);
  }
  updateCountdown();
  setInterval(updateCountdown, 1000);
}

// ── PAYMENT METHOD SELECTION ─────────────────────────────────
document.querySelectorAll('.payment-method').forEach(method => {
  method.addEventListener('click', () => {
    document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
    method.classList.add('selected');
    const radio = method.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
  });
});

// ── QUANTITY BUTTONS (product detail) ───────────────────────
const qtyMinus = document.getElementById('qty-minus');
const qtyPlus  = document.getElementById('qty-plus');
const qtyInput = document.getElementById('qty-input');
if (qtyMinus && qtyPlus && qtyInput) {
  qtyMinus.addEventListener('click', () => {
    const val = parseInt(qtyInput.value);
    if (val > 1) qtyInput.value = val - 1;
  });
  qtyPlus.addEventListener('click', () => {
    const max = parseInt(qtyInput.max) || 99;
    const val = parseInt(qtyInput.value);
    if (val < max) qtyInput.value = val + 1;
  });
}

// Cart page qty helpers
window.updateQty = function(pid, delta) {
  const input = document.getElementById('qty-' + pid);
  if (!input) return;
  const newVal = Math.max(1, parseInt(input.value) + delta);
  input.value = newVal;
  input.dispatchEvent(new Event('change'));
};
