<?php
require_once 'db_conn.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Reports & Complaints – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/reports-style.css">
</head>
<body>

<div class="page page-active" id="page-reports">
  <div class="topbar-parent">
    <div style="display:flex;align-items:center;">
      <button class="back-btn" onclick="location.href='dashboard.php'">←</button>
      <div class="topbar-logo">Pocket<span>Go</span></div>
    </div>
    <div style="font-weight:700;font-size:1rem;">Support Desk</div>
  </div>

  <div class="scroll-area pb">
    <!-- Active Tickets list -->
    <div class="section-header">
      <h3>Active Support Reports</h3>
      <button class="btn btn-sm btn-primary" onclick="showModal('modal-new-report')"><i class="bi bi-plus-circle-fill"></i> File Report</button>
    </div>

    <div id="reports-container">
      <div style="text-align:center;padding:20px;color:#888;font-size:.9rem;">Loading support tickets...</div>
    </div>
  </div>

  <!-- Parent Bottom Navigation -->
  <div class="bottom-nav-parent">
    <div class="nav-item" data-page="dashboard" onclick="location.href='dashboard.php'"><div class="ni-icon"><i class="bi bi-house-door-fill"></i></div><div class="ni-label">Home</div></div>
    <div class="nav-item" data-page="topup" onclick="location.href='topup.php'"><div class="ni-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="ni-label">Top Up</div></div>
    <div class="nav-item" data-page="history" onclick="location.href='history.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">History</div></div>
    <div class="nav-item" data-page="card" onclick="location.href='card.php'"><div class="ni-icon"><i class="bi bi-credit-card-2-front-fill"></i></div><div class="ni-label">My Card</div></div>
    <div class="nav-item active" data-page="reports" onclick="location.href='reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
  </div>
</div>

<!-- MODAL: FILE NEW CARD ISSUE REPORT -->
<div class="modal-overlay" id="modal-new-report" onclick="closeModal('modal-new-report')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title"><i class="bi bi-telephone-fill text-danger me-1"></i> File Support Report</div>
    
    <div class="form-group">
      <label>Issue Type</label>
      <select id="rep-type">
        <option value="lost">Lost NFC Student Card</option>
        <option value="damaged">Damaged / Non-Scanning Card</option>
        <option value="other">Other System Complaint</option>
      </select>
    </div>

    <div class="form-group">
      <label>Card No. <span class="text-danger">*</span></label>
      <input type="text" id="rep-card-no" placeholder="e.g. 1012345678" maxlength="10">
    </div>

    <div class="form-group">
      <label>Brief Subject</label>
      <input type="text" id="rep-subject" placeholder="Canteen card terminal won't read">
    </div>

    <div class="form-group" style="margin-bottom:20px;">
      <label>Detailed Description</label>
      <textarea id="rep-desc" rows="4" placeholder="Please describe exactly what happened..." style="width:100%;border-radius:10px;padding:12px;border:1.5px solid #e0e0e0;font-family:inherit;outline:none;"></textarea>
    </div>

    <button class="btn btn-primary btn-full" onclick="submitReport()">Submit Support Ticket</button>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/reports.js"></script>
</body>
</html>
