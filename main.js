/* Orlina — Main JS */
'use strict';

const _BASE = window._BASE !== undefined ? window._BASE : (window.location.pathname.toLowerCase().startsWith('/orlina') ? window.location.pathname.slice(0, 7) : '');

// ── Hamburger Menu ─────────────────────────────
const ham = document.getElementById('hamburger');
const nav = document.getElementById('main-nav');
if (ham && nav) {
  ham.addEventListener('click', () => {
    nav.classList.toggle('open');
    ham.classList.toggle('active');
  });
}

// ── Cart Badge Update ─────────────────────────
function updateCartBadge(count) {
  const badge = document.getElementById('cart-badge');
  if (badge) {
    badge.textContent = count > 0 ? count : '';
  }
}

// ── Add to Cart ───────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-add-cart]');
  if (!btn) return;
  e.preventDefault();
  const pid  = btn.dataset.addCart;
  const qty  = document.getElementById('qty-input') ? document.getElementById('qty-input').value : 1;
  btn.disabled = true;
  const orig = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding…';
  try {
    const res  = await fetch(_BASE + '/pages/cart_action.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body   : `action=add&product_id=${pid}&quantity=${qty}&csrf_token=${window.__csrf}`
    });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(data.cart_count);
      showToast(data.message, 'success');
    } else {
      showToast(data.message || 'Error', 'error');
    }
  } catch (_) {
    showToast('Network error', 'error');
  }
  btn.disabled = false;
  btn.innerHTML = orig;
});

// ── Remove from Cart ──────────────────────────
document.addEventListener('submit', async (e) => {
  const form = e.target.closest('[data-remove-cart]');
  if (!form) return;
  e.preventDefault();
  try {
    const res  = await fetch(_BASE + '/pages/cart_action.php', {
      method : 'POST',
      body   : new FormData(form)
    });
    const data = await res.json();
    if (data.success) {
      const row = form.closest('tr') || form.closest('.cart-item');
      if (row) row.remove();
      updateCartBadge(data.cart_count);
      if (data.cart_total !== undefined) updateCartTotal(data.cart_total);
      showToast(data.message, 'success');
    }
  } catch (_) {
    form.submit(); // fallback
  }
});

// ── Update Qty in Cart ────────────────────────
document.addEventListener('change', async (e) => {
  const input = e.target.closest('[data-cart-qty]');
  if (!input) return;
  const pid = input.dataset.cartQty;
  const qty = input.value;
  try {
    const res  = await fetch(_BASE + '/pages/cart_action.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body   : `action=update&product_id=${pid}&quantity=${qty}&csrf_token=${window.__csrf}`
    });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(data.cart_count);
      if (data.cart_total !== undefined) updateCartTotal(data.cart_total);
      const subEl = document.querySelector(`[data-subtotal="${pid}"]`);
      if (subEl && data.item_subtotal !== undefined) subEl.textContent = data.item_subtotal;
    }
  } catch (_) {}
});

function updateCartTotal(total) {
  const el = document.getElementById('cart-total');
  if (el) el.textContent = total;
}

// ── Wishlist Toggle ───────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-wishlist]');
  if (!btn) return;
  e.preventDefault();
  const pid = btn.dataset.wishlist;
  try {
    const res  = await fetch(_BASE + '/pages/wishlist.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body   : `action=toggle&product_id=${pid}&csrf_token=${window.__csrf}`
    });
    const data = await res.json();
    if (data.success) {
      btn.classList.toggle('wishlisted', data.wishlisted);
      const icon = btn.querySelector('i');
      if (icon) { icon.className = data.wishlisted ? 'fas fa-heart' : 'far fa-heart'; }
      showToast(data.message, 'success');
    }
  } catch (_) {}
});

// ── Product Gallery ───────────────────────────
const thumbs = document.querySelectorAll('.gallery-thumb');
const mainImg = document.getElementById('gallery-main-img');
thumbs.forEach(t => {
  t.addEventListener('click', () => {
    thumbs.forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    if (mainImg) mainImg.src = t.dataset.src;
  });
});

// ── Quantity Buttons ──────────────────────────
const qtyMinus = document.getElementById('qty-minus');
const qtyPlus  = document.getElementById('qty-plus');
const qtyInput = document.getElementById('qty-input');
if (qtyMinus && qtyPlus && qtyInput) {
  qtyMinus.addEventListener('click', () => {
    const v = parseInt(qtyInput.value);
    if (v > 1) qtyInput.value = v - 1;
  });
  qtyPlus.addEventListener('click', () => {
    const max = parseInt(qtyInput.max) || 99;
    const v   = parseInt(qtyInput.value);
    if (v < max) qtyInput.value = v + 1;
  });
}

// ── Tabs ──────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + target);
    if (panel) panel.classList.add('active');
  });
});

// ── Star Rating Picker ────────────────────────
const starInputs = document.querySelectorAll('.star-picker input');
starInputs.forEach(input => {
  input.addEventListener('change', () => {
    document.querySelectorAll('.star-picker label').forEach((l, i) => {
      l.style.color = i < parseInt(input.value) ? 'var(--accent)' : '#d1d5db';
    });
  });
});

// ── Price Range Filter ────────────────────────
const priceRange = document.getElementById('price-range');
const priceMax   = document.getElementById('price-max-label');
if (priceRange && priceMax) {
  priceRange.addEventListener('input', () => {
    priceMax.textContent = '৳' + parseInt(priceRange.value).toLocaleString();
  });
}

// ── Countdown Timer ───────────────────────────
function startCountdown(endTime) {
  const update = () => {
    const now  = new Date();
    const diff = endTime - now;
    if (diff <= 0) return;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const set = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(val).padStart(2, '0');
    };
    set('cd-hours', h);
    set('cd-mins', m);
    set('cd-secs', s);
  };
  update();
  setInterval(update, 1000);
}
const cdEnd = document.getElementById('countdown-end');
if (cdEnd) startCountdown(new Date(cdEnd.dataset.end));

// ── Toast ─────────────────────────────────────
function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
  document.body.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}

// Toast CSS (injected)
const toastStyle = document.createElement('style');
toastStyle.textContent = `
.toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 22px; border-radius: 14px; font-size: 0.9rem; font-weight: 500;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  transform: translateY(20px); opacity: 0; transition: all 0.3s ease;
  font-family: 'Outfit', sans-serif;
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast-success { background: #fff; color: #059669; border-left: 4px solid #059669; }
.toast-error   { background: #fff; color: #dc2626; border-left: 4px solid #dc2626; }
.toast-info    { background: #fff; color: #2563eb; border-left: 4px solid #2563eb; }
`;
document.head.appendChild(toastStyle);

// ── Image lazy load fallback ──────────────────
document.querySelectorAll('img[data-src]').forEach(img => {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { img.src = img.dataset.src; io.disconnect(); } });
  });
  io.observe(img);
});
