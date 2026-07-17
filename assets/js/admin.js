/* ============================================================
   admin.js
   Shared admin-panel logic (user & transaction CRUD, table
   rendering, stats). Used by admin-dashboard.html,
   admin-users.html and admin-transactions.html.
   Requires store.js + common.js to be loaded first.
   ============================================================ */

function renderAdminTables() {
  // User table on dashboard (short list)
  const tbody = document.getElementById('admin-user-tbody');
  if (tbody) {
    tbody.innerHTML = Store.adminUsers.map(u => `
      <tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.child}</td>
        <td>RM ${u.balance.toFixed(2)}</td>
        <td><span class="${u.status === 'active' ? 'status-active' : 'status-inactive'}">${u.status}</span></td>
        <td>
          <div class="actions">
            <button class="action-btn edit" onclick="showAdminModal('user','edit',${u.id})">Edit</button>
            <button class="action-btn delete" onclick="deleteUser(${u.id})">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // User table full page
  const tbodyFull = document.getElementById('admin-user-tbody-full');
  if (tbodyFull) {
    tbodyFull.innerHTML = Store.adminUsers.map(u => `
      <tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.phone}</td>
        <td>${u.child}</td>
        <td>RM ${u.balance.toFixed(2)}</td>
        <td><span class="${u.status === 'active' ? 'status-active' : 'status-inactive'}">${u.status}</span></td>
        <td>
          <div class="actions">
            <button class="action-btn edit" onclick="showAdminModal('user','edit',${u.id})">Edit</button>
            <button class="action-btn delete" onclick="deleteUser(${u.id})">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Transaction table on dashboard (top 5)
  const txnTbody = document.getElementById('admin-txn-tbody');
  if (txnTbody) {
    txnTbody.innerHTML = Store.adminTransactions.slice(0, 5).map(t => {
      const u = Store.adminUsers.find(u => u.id === t.userId);
      return `
        <tr>
          <td>${u ? u.name : 'Unknown'}</td>
          <td>${t.description}</td>
          <td style="color:${t.amount >= 0 ? '#00ff88' : '#ff0040'}">${t.amount >= 0 ? '+' : ''}RM ${Math.abs(t.amount).toFixed(2)}</td>
          <td>${t.date}</td>
          <td><span class="${t.type === 'topup' ? 'status-active' : 'status-inactive'}">${t.type}</span></td>
          <td>
            <div class="actions">
              <button class="action-btn edit" onclick="showAdminModal('transaction','edit',${t.id})">Edit</button>
              <button class="action-btn delete" onclick="deleteTransaction(${t.id})">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Transaction table full page
  const txnTbodyFull = document.getElementById('admin-txn-tbody-full');
  if (txnTbodyFull) {
    txnTbodyFull.innerHTML = Store.adminTransactions.map(t => {
      const u = Store.adminUsers.find(u => u.id === t.userId);
      return `
        <tr>
          <td>${u ? u.name : 'Unknown'}</td>
          <td>${t.description}</td>
          <td style="color:${t.amount >= 0 ? '#00ff88' : '#ff0040'}">${t.amount >= 0 ? '+' : ''}RM ${Math.abs(t.amount).toFixed(2)}</td>
          <td>${t.date}</td>
          <td><span class="${t.type === 'topup' ? 'status-active' : 'status-inactive'}">${t.type}</span></td>
          <td>
            <div class="actions">
              <button class="action-btn edit" onclick="showAdminModal('transaction','edit',${t.id})">Edit</button>
              <button class="action-btn delete" onclick="deleteTransaction(${t.id})">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Stats (only present on the dashboard page)
  const totalUsersEl = document.getElementById('admin-total-users');
  if (totalUsersEl) {
    const totalBalance = Store.adminUsers.reduce((sum, u) => sum + u.balance, 0);
    totalUsersEl.textContent = Store.adminUsers.length;
    document.getElementById('admin-total-txns').textContent = Store.adminTransactions.length;
    document.getElementById('admin-total-balance').textContent = 'RM ' + totalBalance.toFixed(2);
    document.getElementById('admin-active-cards').textContent = Store.adminUsers.filter(u => u.status === 'active').length;
    const openReportsEl = document.getElementById('admin-open-reports');
    if (openReportsEl) openReportsEl.textContent = Store.reports.filter(r => r.status !== 'Resolved').length;
  }

  renderReportsTable();
}

// ── REPORTS ──
const REPORT_TYPE_LABEL = { lost: 'Lost Card', damaged: 'Damaged Card', other: 'Other' };

function reportStatusClass(status) {
  if (status === 'Resolved') return 'status-active';
  if (status === 'In Progress') return 'status-inactive'; // reuse existing color, styled amber below
  return 'status-inactive';
}

function renderReportsTable() {
  const tbody = document.getElementById('admin-report-tbody');
  if (!tbody) return;
  const sorted = [...Store.reports].sort((a, b) => b.createdAt.localeCompare(a.createdAt));
  tbody.innerHTML = sorted.map(r => `
    <tr>
      <td>${r.reporterName}</td>
      <td>${r.child || '-'}</td>
      <td>${REPORT_TYPE_LABEL[r.type] || 'Other'}</td>
      <td>${r.subject}</td>
      <td>${r.createdAt}</td>
      <td><span style="color:${r.status === 'Resolved' ? '#00ff88' : r.status === 'In Progress' ? '#ffb300' : '#ff0040'}">${r.status}</span></td>
      <td>
        <div class="actions">
          <button class="action-btn edit" onclick="showReportModal(${r.id})">View / Update</button>
          <button class="action-btn delete" onclick="deleteReport(${r.id})">Delete</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function showReportModal(id) {
  const modal = document.getElementById('admin-crud-modal');
  const title = document.getElementById('admin-modal-title');
  const content = document.getElementById('admin-modal-content');
  const r = Store.reports.find(r => r.id === id);
  if (!r) return;

  title.textContent = '📞 Report Details';
  content.innerHTML = `
    <div class="form-group"><label>Reporter</label><input type="text" value="${r.reporterName}" disabled/></div>
    <div class="form-group"><label>Child</label><input type="text" value="${r.child || '-'}" disabled/></div>
    <div class="form-group"><label>Type</label><input type="text" value="${REPORT_TYPE_LABEL[r.type] || 'Other'}" disabled/></div>
    <div class="form-group"><label>Subject</label><input type="text" value="${r.subject}" disabled/></div>
    <div class="form-group"><label>Description</label><textarea rows="4" disabled>${r.description}</textarea></div>
    <div class="form-group"><label>Status</label>
      <select id="admin-report-status">
        <option value="Open" ${r.status === 'Open' ? 'selected' : ''}>Open</option>
        <option value="In Progress" ${r.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
        <option value="Resolved" ${r.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
      </select>
    </div>
    <button class="btn btn-admin btn-full" onclick="saveReportStatus(${r.id})">Update Status</button>
  `;
  modal.classList.add('show');
}

function saveReportStatus(id) {
  const status = document.getElementById('admin-report-status').value;
  const r = Store.reports.find(r => r.id === id);
  if (r) {
    r.status = status;
    Store.save();
    toast('Report status updated!');
  }
  closeModal('admin-crud-modal');
  renderReportsTable();
}

function deleteReport(id) {
  if (confirm('Are you sure you want to delete this report?')) {
    Store.reports = Store.reports.filter(r => r.id !== id);
    Store.save();
    renderReportsTable();
    toast('Report deleted successfully!');
  }
}

function showAdminModal(type, action, id) {
  const modal = document.getElementById('admin-crud-modal');
  const title = document.getElementById('admin-modal-title');
  const content = document.getElementById('admin-modal-content');

  if (type === 'user') {
    const user = action === 'edit' ? Store.adminUsers.find(u => u.id === id) : null;
    title.textContent = action === 'create' ? '👤 Add New User' : '✏️ Edit User';
    content.innerHTML = `
      <div class="form-group"><label>Full Name</label><input type="text" id="admin-user-name" value="${user ? user.name : ''}"/></div>
      <div class="form-group"><label>Email</label><input type="email" id="admin-user-email" value="${user ? user.email : ''}"/></div>
      <div class="form-group"><label>Phone</label><input type="text" id="admin-user-phone" value="${user ? user.phone : ''}"/></div>
      <div class="form-group"><label>Child's Name</label><input type="text" id="admin-user-child" value="${user ? user.child : ''}"/></div>
      <div class="form-group"><label>Balance (RM)</label><input type="number" id="admin-user-balance" step="0.01" value="${user ? user.balance : '0.00'}"/></div>
      <div class="form-group"><label>Status</label>
        <select id="admin-user-status">
          <option value="active" ${user && user.status === 'active' ? 'selected' : ''}>Active</option>
          <option value="inactive" ${user && user.status === 'inactive' ? 'selected' : ''}>Inactive</option>
        </select>
      </div>
      <button class="btn btn-admin btn-full" onclick="saveUser('${action}', ${id || 'null'})">${action === 'create' ? 'Create' : 'Update'} User</button>
    `;
  } else if (type === 'transaction') {
    const txn = action === 'edit' ? Store.adminTransactions.find(t => t.id === id) : null;
    title.textContent = action === 'create' ? '💰 Add New Transaction' : '✏️ Edit Transaction';
    content.innerHTML = `
      <div class="form-group"><label>User</label>
        <select id="admin-txn-user">
          ${Store.adminUsers.map(u => `<option value="${u.id}" ${txn && txn.userId === u.id ? 'selected' : ''}>${u.name}</option>`).join('')}
        </select>
      </div>
      <div class="form-group"><label>Description</label><input type="text" id="admin-txn-desc" value="${txn ? txn.description : ''}"/></div>
      <div class="form-group"><label>Amount (RM)</label><input type="number" id="admin-txn-amount" step="0.01" value="${txn ? Math.abs(txn.amount) : ''}"/></div>
      <div class="form-group"><label>Type</label>
        <select id="admin-txn-type">
          <option value="topup" ${txn && txn.type === 'topup' ? 'selected' : ''}>Top Up</option>
          <option value="spend" ${txn && txn.type === 'spend' ? 'selected' : ''}>Spend</option>
        </select>
      </div>
      <div class="form-group"><label>Date & Time</label><input type="datetime-local" id="admin-txn-date" value="${txn ? txn.date.replace(' ', 'T') : ''}"/></div>
      <button class="btn btn-admin btn-full" onclick="saveTransaction('${action}', ${id || 'null'})">${action === 'create' ? 'Create' : 'Update'} Transaction</button>
    `;
  }

  modal.classList.add('show');
}

function saveUser(action, id) {
  const name = document.getElementById('admin-user-name').value.trim();
  const email = document.getElementById('admin-user-email').value.trim();
  const phone = document.getElementById('admin-user-phone').value.trim();
  const child = document.getElementById('admin-user-child').value.trim();
  const balance = parseFloat(document.getElementById('admin-user-balance').value) || 0;
  const status = document.getElementById('admin-user-status').value;

  if (!name || !email || !child) { toast('Please fill in required fields.'); return; }

  if (action === 'create') {
    Store.adminUsers.push({ id: Store.adminNextId++, name, email, phone, child, balance, status });
    toast('User created successfully!');
  } else {
    const user = Store.adminUsers.find(u => u.id === id);
    if (user) {
      user.name = name; user.email = email; user.phone = phone; user.child = child;
      user.balance = balance; user.status = status;
      toast('User updated successfully!');
    }
  }
  Store.save();
  closeModal('admin-crud-modal');
  renderAdminTables();
}

function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    Store.adminUsers = Store.adminUsers.filter(u => u.id !== id);
    Store.adminTransactions = Store.adminTransactions.filter(t => t.userId !== id);
    Store.save();
    renderAdminTables();
    toast('User deleted successfully!');
  }
}

function saveTransaction(action, id) {
  const userId = parseInt(document.getElementById('admin-txn-user').value);
  const desc = document.getElementById('admin-txn-desc').value.trim();
  const amount = parseFloat(document.getElementById('admin-txn-amount').value) || 0;
  const type = document.getElementById('admin-txn-type').value;
  const date = document.getElementById('admin-txn-date').value.replace('T', ' ');

  if (!desc || !amount) { toast('Please fill in all fields.'); return; }

  const txnAmount = type === 'topup' ? Math.abs(amount) : -Math.abs(amount);

  if (action === 'create') {
    Store.adminTransactions.push({ id: Store.txnNextId++, userId, description: desc, amount: txnAmount, date: date || new Date().toISOString().slice(0, 16).replace('T', ' '), type });
    const user = Store.adminUsers.find(u => u.id === userId);
    if (user) user.balance += txnAmount;
    toast('Transaction created successfully!');
  } else {
    const txn = Store.adminTransactions.find(t => t.id === id);
    if (txn) {
      const oldUser = Store.adminUsers.find(u => u.id === txn.userId);
      if (oldUser) oldUser.balance -= txn.amount;
      txn.userId = userId; txn.description = desc; txn.amount = txnAmount;
      txn.date = date || txn.date; txn.type = type;
      const newUser = Store.adminUsers.find(u => u.id === userId);
      if (newUser) newUser.balance += txnAmount;
      toast('Transaction updated successfully!');
    }
  }
  Store.save();
  closeModal('admin-crud-modal');
  renderAdminTables();
}

function deleteTransaction(id) {
  if (confirm('Are you sure you want to delete this transaction?')) {
    const txn = Store.adminTransactions.find(t => t.id === id);
    if (txn) {
      const user = Store.adminUsers.find(u => u.id === txn.userId);
      if (user) user.balance -= txn.amount;
    }
    Store.adminTransactions = Store.adminTransactions.filter(t => t.id !== id);
    Store.save();
    renderAdminTables();
    toast('Transaction deleted successfully!');
  }
}
