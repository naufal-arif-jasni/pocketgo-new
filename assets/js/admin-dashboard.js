// admin-dashboard.js - Admin Dashboard Page Logic
Store.init().then(() => {
  requireAdminAuth();
  renderAdminTables();
});

function renderAdminTables() {
  const users = Store.adminUsers || [];
  const transactions = Store.adminTransactions || [];
  const reports = Store.reports || [];

  // Update statistics
  const totalBalance = users.reduce((sum, u) => sum + (u.balance || 0), 0);
  
  const totalUsersEl = document.getElementById('admin-total-users');
  if (totalUsersEl) totalUsersEl.textContent = users.length;
  
  const totalTxnsEl = document.getElementById('admin-total-txns');
  if (totalTxnsEl) totalTxnsEl.textContent = transactions.length;
  
  const totalBalanceEl = document.getElementById('admin-total-balance');
  if (totalBalanceEl) totalBalanceEl.textContent = 'RM ' + totalBalance.toFixed(2);
  
  const activeCardsEl = document.getElementById('admin-active-cards');
  if (activeCardsEl) activeCardsEl.textContent = users.filter(u => u.status === 'active').length;
  
  const openReportsEl = document.getElementById('admin-open-reports');
  if (openReportsEl) openReportsEl.textContent = reports.filter(r => r.status !== 'Resolved').length;

  // Render User Management table (limited to 5 items on dashboard)
  const userTbody = document.getElementById('admin-user-tbody');
  if (userTbody) {
    const limitUsers = users.slice(0, 5);
    userTbody.innerHTML = limitUsers.map(u => `
      <tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.child || '-'}</td>
        <td>RM ${(u.balance || 0).toFixed(2)}</td>
        <td><span class="${u.status === 'active' ? 'status-active' : 'status-inactive'}">${u.status}</span></td>
        <td>
          <div class="actions">
            <button class="action-btn edit" onclick="showAdminModal('user', 'edit', ${u.id})">Edit</button>
            <button class="action-btn delete" onclick="deleteUser(${u.id})">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Render User Management full table if present
  const userTbodyFull = document.getElementById('admin-user-tbody-full');
  if (userTbodyFull) {
    userTbodyFull.innerHTML = users.map(u => `
      <tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.phone || '-'}</td>
        <td>${u.child || '-'}</td>
        <td>RM ${(u.balance || 0).toFixed(2)}</td>
        <td><span class="${u.status === 'active' ? 'status-active' : 'status-inactive'}">${u.status}</span></td>
        <td>
          <div class="actions">
            <button class="action-btn edit" onclick="showAdminModal('user', 'edit', ${u.id})">Edit</button>
            <button class="action-btn delete" onclick="deleteUser(${u.id})">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Render Transactions table (limited to 5 items on dashboard)
  const txnTbody = document.getElementById('admin-txn-tbody');
  if (txnTbody) {
    const sortedTxns = [...transactions].sort((a,b) => b.id - a.id).slice(0, 5);
    txnTbody.innerHTML = sortedTxns.map(t => {
      const user = users.find(u => u.id === t.userId) || { name: 'Unknown User' };
      return `
        <tr>
          <td>${user.name}</td>
          <td>${t.description}</td>
          <td style="color:${t.amount >= 0 ? '#00ff88' : '#ff0040'}">${t.amount >= 0 ? '+' : ''}RM ${t.amount.toFixed(2)}</td>
          <td>${t.date}</td>
          <td style="text-transform:capitalize;">${t.type}</td>
          <td>
            <div class="actions">
              <button class="action-btn edit" onclick="showAdminModal('transaction', 'edit', ${t.id})">Edit</button>
              <button class="action-btn delete" onclick="deleteTransaction(${t.id})">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Render Transactions full table if present
  const txnTbodyFull = document.getElementById('admin-txn-tbody-full');
  if (txnTbodyFull) {
    const sortedTxns = [...transactions].sort((a,b) => b.id - a.id);
    txnTbodyFull.innerHTML = sortedTxns.map(t => {
      const user = users.find(u => u.id === t.userId) || { name: 'Unknown User' };
      return `
        <tr>
          <td>${user.name}</td>
          <td>${t.description}</td>
          <td style="color:${t.amount >= 0 ? '#00ff88' : '#ff0040'}">${t.amount >= 0 ? '+' : ''}RM ${t.amount.toFixed(2)}</td>
          <td>${t.date}</td>
          <td style="text-transform:capitalize;">${t.type}</td>
          <td>
            <div class="actions">
              <button class="action-btn edit" onclick="showAdminModal('transaction', 'edit', ${t.id})">Edit</button>
              <button class="action-btn delete" onclick="deleteTransaction(${t.id})">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Render Reports table (limited to 5 items on dashboard)
  const reportTbodyDashboard = document.getElementById('admin-report-tbody-dashboard');
  if (reportTbodyDashboard) {
    const reportLabels = { lost: 'Lost Card', damaged: 'Damaged Card', other: 'Other' };
    const sortedReports = [...reports].sort((a,b) => b.id - a.id).slice(0, 5);
    if (sortedReports.length === 0) {
      reportTbodyDashboard.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#6a6a8a;padding:20px;">No reports filed yet.</td></tr>`;
    } else {
      reportTbodyDashboard.innerHTML = sortedReports.map(r => `
        <tr>
          <td>${r.reporterName}</td>
          <td>${r.child || '-'}</td>
          <td>${reportLabels[r.type] || 'Other'}</td>
          <td>${r.subject}</td>
          <td>${r.createdAt}</td>
          <td><span style="color:${r.status === 'Resolved' ? '#00ff88' : r.status === 'In Progress' ? '#ffb300' : '#ff0040'}">${r.status}</span></td>
        </tr>
      `).join('');
    }
  }
}

function showAdminModal(type, action, id) {
  const modal = document.getElementById('admin-crud-modal');
  const title = document.getElementById('admin-modal-title');
  const content = document.getElementById('admin-modal-content');

  if (type === 'user') {
    const user = action === 'edit' ? Store.adminUsers.find(u => u.id === id) : null;
    title.innerHTML = action === 'create' ? `${getModernIcon('user')} Add New User` : `${getModernIcon('edit')} Edit User`;
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
    title.innerHTML = action === 'create' ? `${getModernIcon('transactions')} Add New Transaction` : `${getModernIcon('edit')} Edit Transaction`;
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

async function saveUser(action, id) {
  const name = document.getElementById('admin-user-name').value.trim();
  const email = document.getElementById('admin-user-email').value.trim();
  const phone = document.getElementById('admin-user-phone').value.trim();
  const child = document.getElementById('admin-user-child').value.trim();
  const balance = parseFloat(document.getElementById('admin-user-balance').value) || 0;
  const status = document.getElementById('admin-user-status').value;

  if (!name || !email || !child) { toast('Please fill in required fields.'); return; }

  toast('Saving user...');
  try {
    const res = await fetch('api.php?action=admin-save-user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, id, name, email, phone, child, balance, status })
    });
    if (res.ok) {
      toast('User saved successfully!');
      closeModal('admin-crud-modal');
      await Store.fetchAdminData();
      renderAdminTables();
    } else {
      toast('Save user failed.');
    }
  } catch (e) {
    console.error(e);
  }
}

async function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user?')) {
    toast('Deleting user...');
    try {
      const res = await fetch('api.php?action=admin-delete-user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      if (res.ok) {
        toast('User deleted successfully.');
        await Store.fetchAdminData();
        renderAdminTables();
      } else {
        toast('Action failed.');
      }
    } catch (e) {
      console.error(e);
    }
  }
}

async function saveTransaction(action, id) {
  const userId = parseInt(document.getElementById('admin-txn-user').value);
  const desc = document.getElementById('admin-txn-desc').value.trim();
  const amount = parseFloat(document.getElementById('admin-txn-amount').value) || 0;
  const type = document.getElementById('admin-txn-type').value;
  const dateVal = document.getElementById('admin-txn-date').value;
  const date = dateVal ? dateVal.replace('T', ' ') : new Date().toISOString().slice(0, 16).replace('T', ' ');

  if (!desc || !amount) { toast('Please fill in required fields.'); return; }

  toast('Saving transaction...');
  try {
    const res = await fetch('api.php?action=admin-save-transaction', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, id, userId, description: desc, amount, type, date })
    });
    if (res.ok) {
      toast('Transaction saved successfully!');
      closeModal('admin-crud-modal');
      await Store.fetchAdminData();
      renderAdminTables();
    } else {
      toast('Action failed.');
    }
  } catch (e) {
    console.error(e);
  }
}

async function deleteTransaction(id) {
  if (confirm('Are you sure you want to delete this transaction?')) {
    toast('Deleting transaction...');
    try {
      const res = await fetch('api.php?action=admin-delete-transaction', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      if (res.ok) {
        toast('Transaction deleted.');
        await Store.fetchAdminData();
        renderAdminTables();
      } else {
        toast('Action failed.');
      }
    } catch (e) {
      console.error(e);
    }
  }
}
