/* ============================================================
   common.js
   Shared UI helpers used across every page: toast, modal
   show/hide, logout, and simple auth guards.
   Requires store.js to be loaded first.
   ============================================================ */

// ── TOAST ──
function toast(msg) {
  const el = document.getElementById('toast-el');
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

// ── MODAL ──
function showModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('show');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}

// ── AUTH GUARDS ──
// Call at the top of every parent-only page.
function requireParentAuth() {
  if (!Store.loggedIn) {
    window.location.href = 'login.php';
  }
}

// Call at the top of every admin-only page.
function requireAdminAuth() {
  if (!Store.isAdmin) {
    window.location.href = 'login.php';
  }
}

// ── LOGOUT ──
function doLogout() {
  Store.reset();
}

// ── BOTTOM NAV ACTIVE-STATE HELPER ──
// Marks a bottom-nav item active based on the current file name,
// so we don't have to hand-edit "active" classes per page.
function markActiveNav(navSelector, page) {
  document.querySelectorAll(navSelector + ' .nav-item').forEach(item => {
    item.classList.toggle('active', item.dataset.page === page);
  });
}

// ── MODERN ICON TRANSLATION HELPER ──
function getModernIcon(emojiOrCat) {
  const mapping = {
    // Categories/Identifiers
    'home': '<i class="bi bi-house-door"></i>',
    'topup': '<i class="bi bi-plus-circle"></i>',
    'history': '<i class="bi bi-clock-history"></i>',
    'card': '<i class="bi bi-credit-card-2-front"></i>',
    'reports': '<i class="bi bi-exclamation-triangle"></i>',
    'canteen': '<i class="bi bi-egg-fried"></i>',
    'shop': '<i class="bi bi-book-half"></i>',
    'lock': '<i class="bi bi-lock"></i>',
    'lost': '<i class="bi bi-exclamation-octagon"></i>',
    'limit': '<i class="bi bi-shield-check"></i>',
    'settings': '<i class="bi bi-gear"></i>',
    'search': '<i class="bi bi-search"></i>',
    'user': '<i class="bi bi-person"></i>',
    'users': '<i class="bi bi-people"></i>',
    'admin': '<i class="bi bi-shield-lock"></i>',
    'fpx': '<i class="bi bi-bank"></i>',
    'wallet': '<i class="bi bi-wallet2"></i>',
    'success': '<i class="bi bi-check-circle"></i>',
    'chevron-left': '<i class="bi bi-chevron-left"></i>',
    'chevron-right': '<i class="bi bi-chevron-right"></i>',
    'dashboard': '<i class="bi bi-speedometer2"></i>',
    'transactions': '<i class="bi bi-cash-stack"></i>',
    'delete': '<i class="bi bi-trash"></i>',
    'edit': '<i class="bi bi-pencil-square"></i>',
    'plus': '<i class="bi bi-plus-lg"></i>',
    'checkmark': '<i class="bi bi-check-lg"></i>',

    // Exact emoji matches
    '🏠': '<i class="bi bi-house-door-fill"></i>',
    '➕': '<i class="bi bi-plus-circle-fill"></i>',
    '📊': '<i class="bi bi-bar-chart-line-fill"></i>',
    '💳': '<i class="bi bi-credit-card-2-front-fill"></i>',
    '📞': '<i class="bi bi-telephone-fill"></i>',
    '🍱': '<i class="bi bi-egg-fried text-warning"></i>',
    '📚': '<i class="bi bi-book-half text-primary"></i>',
    '⬆️': '<i class="bi bi-arrow-up-circle-fill text-success"></i>',
    '👨‍👩‍👧': '<i class="bi bi-people-fill"></i>',
    '🔐': '<i class="bi bi-shield-lock-fill"></i>',
    '🔒': '<i class="bi bi-lock-fill"></i>',
    '🚨': '<i class="bi bi-exclamation-octagon-fill text-danger"></i>',
    '🛡️': '<i class="bi bi-shield-fill-check"></i>',
    '⚙️': '<i class="bi bi-gear-fill"></i>',
    '📋': '<i class="bi bi-clipboard-data-fill"></i>',
    '👥': '<i class="bi bi-people-fill"></i>',
    '💰': '<i class="bi bi-cash-stack"></i>',
    '🏦': '<i class="bi bi-bank"></i>',
    '📱': '<i class="bi bi-phone"></i>',
    '✓': '<i class="bi bi-check-lg"></i>',
    '✅': '<i class="bi bi-check-circle-fill text-success" style="font-size:3rem;display:block;margin:0 auto 15px;"></i>',
    '🔍': '<i class="bi bi-search text-muted" style="font-size:3rem;display:block;margin:0 auto 15px;"></i>',
    '💸': '<i class="bi bi-cash"></i>'
  };

  const key = emojiOrCat ? emojiOrCat.toString().trim() : '';
  return mapping[key] || mapping[key.toLowerCase()] || `<i class="bi bi-arrow-right"></i>`;
}

// ── TIMESTAMP & TRANSACTION SUBTITLE FORMATTER ──
function formatTxnSub(t) {
  if (!t) return '';
  let prefix = '';
  let rawTime = '';

  if (t.sub && typeof t.sub === 'string') {
    const parts = t.sub.split('·');
    if (parts.length > 1) {
      prefix = parts[0].trim() + ' · ';
      rawTime = parts[1].trim();
    } else {
      if (!t.sub.match(/\d{4}-\d{2}-\d{2}/) && !t.sub.match(/^\d{1,2}:\d{2}/)) {
        prefix = t.sub.trim() + ' · ';
      } else {
        rawTime = t.sub.trim();
      }
    }
  }

  if (rawTime && (rawTime.includes('AM') || rawTime.includes('PM'))) {
    return prefix + rawTime;
  }

  const dateToParse = rawTime || t.date || '';
  if (!dateToParse) return prefix ? prefix.replace(/ · $/, '') : '';

  const match = dateToParse.match(/(\d{1,2}):(\d{2})/);
  if (match) {
    let hrs = parseInt(match[1], 10);
    const mins = match[2];
    const ampm = hrs >= 12 ? 'PM' : 'AM';
    hrs = hrs % 12 || 12;
    return prefix + `${hrs}:${mins} ${ampm}`;
  }

  const d = new Date(dateToParse.replace(' ', 'T'));
  if (!isNaN(d.getTime())) {
    const formatted = d.toLocaleTimeString('en-MY', { hour: 'numeric', minute: '2-digit', hour12: true });
    return prefix + formatted;
  }

  return t.sub || t.date || '';
}

function getDateLabel(dateStr) {
  if (!dateStr) return 'Today';
  
  let datePart = dateStr.trim();
  if (datePart.includes('T')) {
    datePart = datePart.split('T')[0];
  } else if (datePart.includes(' ')) {
    datePart = datePart.split(' ')[0];
  }

  const today = new Date().toISOString().slice(0, 10);
  const yesterday = new Date(Date.now() - 86400000).toISOString().slice(0, 10);

  if (datePart === today) return 'Today';
  if (datePart === yesterday) return 'Yesterday';

  const parts = datePart.split('-');
  if (parts.length === 3) {
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const day = parseInt(parts[2], 10);
    const d = new Date(year, month, day);
    if (!isNaN(d.getTime())) {
      return d.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' });
    }
  }

  return datePart;
}

// ── PASSWORD VISIBILITY TOGGLE ──
function togglePasswordVisibility(inputId, btnEl) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const icon = btnEl.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) {
      icon.className = 'bi bi-eye-slash';
    }
  } else {
    input.type = 'password';
    if (icon) {
      icon.className = 'bi bi-eye';
    }
  }
}

