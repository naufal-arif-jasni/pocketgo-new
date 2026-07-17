// reports.js - Reports Management
Store.init().then(() => {
  requireParentAuth();
  renderReportsPage();
});

function renderReportsPage() {
  const u = Store.user;
  const repChild = document.getElementById('rep-child');
  if (repChild) {
    repChild.textContent = u.child || '—';
  }
  
  const container = document.getElementById('reports-container');
  if (!container) return;

  const myReports = Store.reports;
  if (myReports.length === 0) {
    container.innerHTML = `
      <div style="text-align:center;padding:40px 20px;color:#888;">
        ${getModernIcon('📋')}
        No active reports or complaints.
      </div>
    `;
    return;
  }

  const sorted = [...myReports].sort((a,b) => b.id - a.id);
  container.innerHTML = sorted.map(r => `
    <div class="report-item">
      <div class="report-icon">${getModernIcon('📞')}</div>
      <div class="report-info">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
          <span class="tag ${reportStatusClass(r.status)}">${r.status}</span>
          <button class="report-delete" onclick="deleteMyReport(${r.id})">Cancel</button>
        </div>
        <h4>${r.subject}</h4>
        <p>${r.description}</p>
        <div class="report-meta">Type: ${r.type === 'lost' ? 'Lost Card' : r.type === 'damaged' ? 'Damaged Card' : 'Other'} • Filed on ${r.createdAt}</div>
      </div>
    </div>
  `).join('');
}

function reportStatusClass(status) {
  if (status === 'Resolved') return 'tag-green';
  if (status === 'In Progress') return 'tag-yellow';
  return 'tag-red';
}

async function submitReport() {
  const type = document.getElementById('rep-type').value;
  const cardNo = document.getElementById('rep-card-no').value.trim();
  const subject = document.getElementById('rep-subject').value.trim();
  const desc = document.getElementById('rep-desc').value.trim();

  if (!cardNo) { toast('Please enter Card No.'); return; }
  if (cardNo.length !== 10 || isNaN(cardNo)) { toast('Card No. must be exactly 10 digits.'); return; }
  if (!subject || !desc) { toast('Please fill up all fields.'); return; }

  const finalDesc = `Card No: ${cardNo}\n\n${desc}`;

  toast('Submitting report...');
  try {
    const res = await fetch('api.php?action=create-report', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: Store.user.email, type, subject, description: finalDesc })
    });
    if (res.ok) {
      const data = await res.json();
      Store.reports = data.reports;
      Store.save();
      closeModal('modal-new-report');
      toast('Report submitted successfully!');
      
      // Clear fields
      document.getElementById('rep-card-no').value = '';
      document.getElementById('rep-subject').value = '';
      document.getElementById('rep-desc').value = '';
      
      renderReportsPage();
    } else {
      toast('Submission failed.');
    }
  } catch (e) {
    console.error(e);
    toast('Error submitting report.');
  }
}

function showConfirmDialog(title, message, onConfirm) {
  let modal = document.getElementById('custom-confirm-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'custom-confirm-modal';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
      <div class="modal-sheet" onclick="event.stopPropagation()">
        <div class="modal-handle"></div>
        <div class="modal-title" id="custom-confirm-title" style="color: #c8102e;"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Action</div>
        <div style="font-size: 0.95rem; color: #555; margin-bottom: 24px; line-height: 1.5;" id="custom-confirm-msg"></div>
        <div style="display: flex; gap: 12px;">
          <button class="btn btn-outline" style="flex: 1; padding: 12px;" onclick="closeModal('custom-confirm-modal')">No, Go Back</button>
          <button class="btn btn-primary" id="custom-confirm-ok-btn" style="flex: 1; padding: 12px; background: #c8102e; border-color: #c8102e;">Yes, Cancel</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', () => closeModal('custom-confirm-modal'));
  }

  document.getElementById('custom-confirm-title').innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> ${title}`;
  document.getElementById('custom-confirm-msg').textContent = message;

  const okBtn = document.getElementById('custom-confirm-ok-btn');
  const newOkBtn = okBtn.cloneNode(true);
  okBtn.parentNode.replaceChild(newOkBtn, okBtn);

  newOkBtn.addEventListener('click', () => {
    closeModal('custom-confirm-modal');
    onConfirm();
  });

  showModal('custom-confirm-modal');
}

async function deleteMyReport(id) {
  showConfirmDialog(
    'Cancel Support Report?',
    'Are you sure you want to cancel and delete this support report?',
    async () => {
      toast('Cancelling report...');
      try {
        const res = await fetch('api.php?action=delete-report', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
        if (res.ok) {
          Store.reports = Store.reports.filter(r => r.id !== id);
          Store.save();
          toast('Report cancelled successfully.');
          renderReportsPage();
        } else {
          toast('Action failed.');
        }
      } catch (e) {
        console.error(e);
        toast('Network error.');
      }
    }
  );
}
