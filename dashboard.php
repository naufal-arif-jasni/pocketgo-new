<?php
require_once 'db_conn.php';
require_once 'auth.php';
// requireParent(); // Managed dynamically in client store.js, but included for authentic PHP
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Dashboard – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/dashboard-style.css">
</head>
<body>

<div class="page page-active" id="page-dashboard">
  <!-- Dynamic Topbar -->
  <div class="topbar-parent">
    <div style="display:flex;align-items:center;gap:12px;">
      <div class="avatar" id="dash-avatar">—</div>
      <div>
        <div style="font-size:.78rem;color:rgba(255,255,255,.75);font-weight:500;">Hello, Parent</div>
        <div style="font-weight:700;font-size:1.05rem;" id="dash-parent-name">—</div>
      </div>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
      <button class="btn btn-sm btn-ghost" style="padding:6px 14px;font-size:.7rem;" onclick="doLogout()">Logout</button>
    </div>
  </div>

  <div class="scroll-area pb">
    <!-- Date Header -->
    <div class="greeting">
      <h2 id="dash-greeting-title">SK Setia Alam Cashless</h2>
      <p id="dash-date">Today</p>
    </div>

    <!-- Central Wallet Card -->
    <div class="wallet-card">
      <div class="wc-label">Child Card Balance</div>
      <div class="wc-balance">RM <span id="dash-balance">0.00</span></div>
      <div class="wc-child">Linked to: <strong id="dash-child-name">—</strong> (<span id="dash-child-class">—</span>)</div>
      <div class="wc-actions">
        <button class="wc-btn" onclick="goTopUp()"><span class="wc-icon"><i class="bi bi-plus-circle-fill"></i></span>Top Up Card</button>
        <button class="wc-btn" onclick="location.href='card.php'"><span class="wc-icon"><i class="bi bi-gear-fill"></i></span>Card Settings</button>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="quick-stats">
      <div class="stat-card">
        <div class="stat-label">Total Topped Up</div>
        <div class="stat-value" id="dash-topup-stat">RM 0.00</div>
        <div class="stat-sub">This month</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Transactions</div>
        <div class="stat-value" id="dash-topup-count">0</div>
        <div class="stat-sub">This month</div>
      </div>
    </div>

    <!-- Linked NFC Card Row -->
    <div class="linked-card-row">
      <div class="card-visual">NFC</div>
      <div class="card-info" style="flex:1;">
        <h4>Student ID Card</h4>
        <p style="color:#666;">Contactless NFC Student Card</p>
      </div>
      <span class="tag tag-green">Active</span>
    </div>

    <!-- Recent transactions header -->
    <div class="section-header">
      <h3>Recent Transactions</h3>
      <a onclick="location.href='history.php'">View All History →</a>
    </div>

    <!-- Recent transactions list -->
    <div class="txn-list" id="dash-txn-list">
      <div style="text-align:center;padding:20px;color:#888;font-size:.9rem;">Loading transactions...</div>
    </div>
  </div>

  <!-- Parent Bottom Navigation -->
  <div class="bottom-nav-parent">
    <div class="nav-item active" data-page="dashboard" onclick="location.href='dashboard.php'"><div class="ni-icon"><i class="bi bi-house-door-fill"></i></div><div class="ni-label">Home</div></div>
    <div class="nav-item" data-page="topup" onclick="goTopUp()"><div class="ni-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="ni-label">Top Up</div></div>
    <div class="nav-item" data-page="history" onclick="location.href='history.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">History</div></div>
    <div class="nav-item" data-page="card" onclick="location.href='card.php'"><div class="ni-icon"><i class="bi bi-credit-card-2-front-fill"></i></div><div class="ni-label">My Card</div></div>
    <div class="nav-item" data-page="reports" onclick="location.href='reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
