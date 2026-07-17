// admin-reports.js
Store.init().then(() => {
  requireAdminAuth();
  renderReportsTable();
});

const REPORT_TYPE_LABEL = { lost: 'Lost Card', damaged: 'Damaged Card', other: 'Other' };

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

  title.innerHTML = `${getModernIcon('reports')} Report Details`;
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

async function saveReportStatus(id) {
  const status = document.getElementById('admin-report-status').value;
  toast('Updating report status...');
  try {
    const res = await fetch('api.php?action=admin-update-report-status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    });
    if (res.ok) {
      toast('Report status updated successfully!');
      closeModal('admin-crud-modal');
      await Store.fetchAdminData();
      renderReportsTable();
    } else {
      toast('Update failed.');
    }
  } catch (e) {
    console.error(e);
  }
}

async function deleteReport(id) {
  if (confirm('Are you sure you want to delete this report?')) {
    toast('Deleting report...');
    try {
      const res = await fetch('api.php?action=admin-delete-report', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      if (res.ok) {
        toast('Report deleted successfully!');
        await Store.fetchAdminData();
        renderReportsTable();
      } else {
        toast('Action failed.');
      }
    } catch (e) {
      console.error(e);
    }
  }
}
