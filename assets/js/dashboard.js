// dashboard.js - Parent Dashboard Logic
Store.init().then(() => {
  requireParentAuth();
  updateDashboard();
});

function updateDashboard() {
  const user = Store.user;
  const firstName = user.name.split(' ')[0] || user.name;
  
  const parentNameEl = document.getElementById('dash-parent-name');
  if (parentNameEl) parentNameEl.textContent = firstName;
  
  const avatarEl = document.getElementById('dash-avatar');
  if (avatarEl) avatarEl.textContent = (firstName.charAt(0) || 'N').toUpperCase();
  
  const childNameEl = document.getElementById('dash-child-name');
  const childClassEl = document.getElementById('dash-child-class');
  const balanceEl = document.getElementById('dash-balance');
  
  const cards = user.cards || [];
  if (cards.length > 0) {
    const names = cards.map(c => c.student_name).join(', ');
    const classes = cards.map(c => c.class).join(' & ');
    const totalBalance = cards.reduce((sum, c) => sum + parseFloat(c.balance || 0), 0);
    
    if (childNameEl) childNameEl.textContent = names;
    if (childClassEl) childClassEl.textContent = classes;
    if (balanceEl) balanceEl.textContent = totalBalance.toFixed(2);
  } else {
    if (childNameEl) childNameEl.textContent = '—';
    if (childClassEl) childClassEl.textContent = '—';
    if (balanceEl) balanceEl.textContent = '0.00';
  }
  
  const topupStatEl = document.getElementById('dash-topup-stat');
  if (topupStatEl) topupStatEl.textContent = 'RM ' + (user.topupTotal || 0).toFixed(2);
  
  const topupCountEl = document.getElementById('dash-topup-count');
  if (topupCountEl) topupCountEl.textContent = (user.topupCount || 0) + ' transactions';

  const now = new Date();
  const dateEl = document.getElementById('dash-date');
  if (dateEl) dateEl.textContent = now.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

  renderRecentTxns();
}

function renderRecentTxns() {
  const list = document.getElementById('dash-txn-list');
  if (!list) return;
  const items = Store.historyItems.slice(0, 4);
  if (!items || items.length === 0) {
    list.innerHTML = '<div style="text-align:center;padding:20px;color:#888;font-size:.9rem;">No recent transactions</div>';
    return;
  }
  list.innerHTML = items.map(t => `
    <div class="txn-item">
      <div class="txn-icon ${t.cat === 'topup' || t.type === 'topup' ? 'topup' : 'spend'}">${getModernIcon(t.icon || '💸')}</div>
      <div class="txn-info"><h4>${t.title || t.description}</h4><p>${formatTxnSub(t)}</p></div>
      <div class="txn-amount ${t.amount >= 0 ? 'pos' : 'neg'}">${t.amount >= 0 ? '+' : '-'}RM ${Math.abs(t.amount).toFixed(2)}</div>
    </div>
  `).join('');
}

function goTopUp() {
  const cards = Store.user.cards || [];
  if (cards.length === 0) {
    toast('⚠️ Please register a student card before performing a top-up.');
    setTimeout(() => {
      location.href = 'card.php';
    }, 1200);
    return;
  }
  location.href = 'topup.php';
}

