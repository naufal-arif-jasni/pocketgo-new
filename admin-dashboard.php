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
  <title>Admin Dashboard – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/admin-dashboard-style.css">
</head>
<body>

<div class="page page-active" id="page-admin-dashboard">
  <div class="topbar-admin">
    <div style="display:flex;align-items:center;">
      <div class="topbar-logo">Pocket<span>Go</span></div>
      <span class="admin-badge"><i class="bi bi-shield-lock-fill me-1"></i> ADMIN</span>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
      <span style="font-size:.75rem;color:#4a4a6a;">Welcome, <span style="color:#00d4ff;">Admin</span></span>
      <button class="btn btn-sm" style="background:#ff0040;color:#fff;padding:6px 14px;font-size:.7rem;" onclick="doLogout()">Logout</button>
    </div>
  </div>
  <div class="scroll-area-admin">
    <div style="max-width:1200px;margin:0 auto;">
      <div style="margin-bottom:24px;">
        <h1 style="color:#00d4ff;font-size:1.8rem;font-weight:800;" class="glow-text">Admin Dashboard</h1>
        <p style="color:#6a6a8a;font-size:.9rem;">System overview and management</p>
      </div>

      <div class="admin-dashboard-stats">
        <div class="admin-stat-card"><div class="stat-label">Total Users</div><div class="stat-value" id="admin-total-users">0</div><div class="stat-sub">Registered parents</div></div>
        <div class="admin-stat-card"><div class="stat-label">Total Transactions</div><div class="stat-value" id="admin-total-txns">0</div><div class="stat-sub">Across all cards</div></div>
        <div class="admin-stat-card"><div class="stat-label">Total Balance</div><div class="stat-value" style="color:#00ff88;" id="admin-total-balance">RM 0.00</div><div class="stat-sub">Across all cards</div></div>
        <div class="admin-stat-card"><div class="stat-label">Active Cards</div><div class="stat-value" id="admin-active-cards">0</div><div class="stat-sub">Cards sync active</div></div>
        <div class="admin-stat-card"><div class="stat-label">Open Reports</div><div class="stat-value" style="color:#ff0040;" id="admin-open-reports">0</div><div class="stat-sub">Lost/damaged/complaints</div></div>
      </div>

      <div class="admin-table-container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h3><i class="bi bi-people-fill me-1 text-info"></i> Recent Users</h3>
          <button class="btn btn-admin btn-sm" onclick="showAdminModal('user','create')"><i class="bi bi-plus-circle-fill me-1"></i> Add User</button>
        </div>
        <table class="admin-table" id="admin-user-table">
          <thead><tr><th>Name</th><th>Email</th><th>Child</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="admin-user-tbody"></tbody>
        </table>
      </div>

      <div class="admin-table-container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h3><i class="bi bi-bar-chart-line-fill me-1 text-warning"></i> Recent Transactions</h3>
          <button class="btn btn-admin btn-sm" onclick="showAdminModal('transaction','create')"><i class="bi bi-plus-circle-fill me-1"></i> Add Transaction</button>
        </div>
        <table class="admin-table" id="admin-txn-table">
          <thead><tr><th>User</th><th>Description</th><th>Amount</th><th>Date</th><th>Type</th><th>Actions</th></tr></thead>
          <tbody id="admin-txn-tbody"></tbody>
        </table>
      </div>

      <div class="admin-table-container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h3><i class="bi bi-clipboard-data-fill me-1 text-danger"></i> Recent Reports & Complaints</h3>
          <button class="btn btn-admin btn-sm" onclick="location.href='admin-reports.php'"><i class="bi bi-telephone-fill me-1"></i> Manage Reports</button>
        </div>
        <table class="admin-table" id="admin-report-table-dashboard">
          <thead><tr><th>Reporter</th><th>Child</th><th>Type</th><th>Subject</th><th>Submitted</th><th>Status</th></tr></thead>
          <tbody id="admin-report-tbody-dashboard"></tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="bottom-nav-admin">
    <div class="nav-item active" data-page="admin-dashboard" onclick="location.href='admin-dashboard.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">Dashboard</div></div>
    <div class="nav-item" data-page="admin-users" onclick="location.href='admin-users.php'"><div class="ni-icon"><i class="bi bi-people-fill"></i></div><div class="ni-label">Users</div></div>
    <div class="nav-item" data-page="admin-transactions" onclick="location.href='admin-transactions.php'"><div class="ni-icon"><i class="bi bi-cash-stack"></i></div><div class="ni-label">Transactions</div></div>
    <div class="nav-item" data-page="admin-reports" onclick="location.href='admin-reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
    <div class="nav-item" data-page="admin-settings" onclick="location.href='admin-settings.php'"><div class="ni-icon"><i class="bi bi-gear-fill"></i></div><div class="ni-label">Settings</div></div>
  </div>
</div>

<!-- ADMIN CRUD MODAL -->
<div class="modal-overlay admin-form-modal" id="admin-crud-modal" onclick="closeModal('admin-crud-modal')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title" id="admin-modal-title">Add New</div>
    <div id="admin-modal-content"></div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/admin-dashboard.js"></script>
</body>
</html>
