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
  <title>Transaction History – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/history-style.css">
</head>
<body>

<div class="page page-active" id="page-history">
  <div class="topbar-parent">
    <div style="display:flex;align-items:center;">
      <button class="back-btn" onclick="location.href='dashboard.php'">←</button>
      <div class="topbar-logo">Pocket<span>Go</span></div>
    </div>
    <div style="font-weight:700;font-size:1rem;">Card History</div>
  </div>

  <div class="scroll-area pb">
    <!-- Card Balance Header on History Page -->
    <div style="background:#fff; padding:16px; border-radius:16px; margin:16px; border:1px solid #eaeaea; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
      <div>
        <div style="font-size:0.75rem; color:#666; font-weight:500;">Total Card Balance</div>
        <div style="font-size:1.3rem; font-weight:800; color:#C8102E; margin-top:2px;">RM <span id="history-total-balance">0.00</span></div>
      </div>
      <div id="history-card-pills" style="display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; max-width:60%;">
        <!-- Dynamic card quick info pills -->
      </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="filter-tabs">
      <button class="filter-tab active" onclick="filterTxns('all', this)"><i class="bi bi-file-earmark-text me-1"></i> All</button>
      <button class="filter-tab" onclick="filterTxns('topup', this)"><i class="bi bi-arrow-up-circle-fill me-1 text-success"></i> Top Ups</button>
      <button class="filter-tab" onclick="filterTxns('canteen', this)"><i class="bi bi-egg-fried me-1 text-warning"></i> Canteen</button>
      <button class="filter-tab" onclick="filterTxns('shop', this)"><i class="bi bi-book-half me-1 text-primary"></i> Bookshop</button>
    </div>

    <!-- History items container -->
    <div id="hist-container">
      <div style="text-align:center;padding:20px;color:#888;font-size:.9rem;">Loading logs...</div>
    </div>
  </div>

  <!-- Parent Bottom Navigation -->
  <div class="bottom-nav-parent">
    <div class="nav-item" data-page="dashboard" onclick="location.href='dashboard.php'"><div class="ni-icon"><i class="bi bi-house-door-fill"></i></div><div class="ni-label">Home</div></div>
    <div class="nav-item" data-page="topup" onclick="location.href='topup.php'"><div class="ni-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="ni-label">Top Up</div></div>
    <div class="nav-item active" data-page="history" onclick="location.href='history.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">History</div></div>
    <div class="nav-item" data-page="card" onclick="location.href='card.php'"><div class="ni-icon"><i class="bi bi-credit-card-2-front-fill"></i></div><div class="ni-label">My Card</div></div>
    <div class="nav-item" data-page="reports" onclick="location.href='reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/history.js"></script>
</body>
</html>
