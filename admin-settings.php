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
  <title>System Settings – PocketGo Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/admin-settings-style.css">
</head>
<body>

<div class="page page-active" id="page-admin-settings">
  <div class="topbar-admin">
    <div style="display:flex;align-items:center;">
      <button class="back-btn" onclick="location.href='admin-dashboard.php'">←</button>
      <div class="topbar-logo">Pocket<span>Go</span></div>
      <span class="admin-badge"><i class="bi bi-gear-fill me-1"></i> Settings</span>
    </div>
    <button class="btn btn-sm" style="background:#ff0040;color:#fff;padding:6px 14px;font-size:.7rem;" onclick="doLogout()">Logout</button>
  </div>
  <div class="scroll-area-admin">
    <div style="max-width:800px;margin:0 auto;">
      <h1 style="color:#00d4ff;font-size:1.8rem;font-weight:800;" class="glow-text">System Settings</h1>
      <p style="color:#6a6a8a;margin-bottom:24px;">Configure system parameters</p>

      <div class="admin-table-container">
        <h3><i class="bi bi-shield-lock-fill text-info me-1"></i> Security Settings</h3>
        <div style="display:flex;flex-direction:column;gap:16px;padding:12px 0;">
          <div style="display:flex;justify-content:space-between;align-items:center;color:#b0b0d0;"><span>Two-Factor Authentication</span><button class="btn btn-sm btn-success" onclick="toast('2FA enabled!')">Enable</button></div>
          <div style="display:flex;justify-content:space-between;align-items:center;color:#b0b0d0;"><span>Session Timeout (minutes)</span><input type="number" value="30" style="width:100px;padding:6px;background:rgba(255,255,255,.05);border:1px solid rgba(0,212,255,.2);color:#fff;border-radius:6px;"></div>
          <div style="display:flex;justify-content:space-between;align-items:center;color:#b0b0d0;"><span>Max Login Attempts</span><input type="number" value="5" style="width:100px;padding:6px;background:rgba(255,255,255,.05);border:1px solid rgba(0,212,255,.2);color:#fff;border-radius:6px;"></div>
        </div>
      </div>

      <div class="admin-table-container">
        <h3><i class="bi bi-credit-card-2-front-fill text-warning me-1"></i> Card Settings</h3>
        <div style="display:flex;flex-direction:column;gap:16px;padding:12px 0;">
          <div style="display:flex;justify-content:space-between;align-items:center;color:#b0b0d0;"><span>Default Daily Limit (RM)</span><input type="number" value="50" style="width:100px;padding:6px;background:rgba(255,255,255,.05);border:1px solid rgba(0,212,255,.2);color:#fff;border-radius:6px;"></div>
          <div style="display:flex;justify-content:space-between;align-items:center;color:#b0b0d0;"><span>Auto-topup Threshold (RM)</span><input type="number" value="20" style="width:100px;padding:6px;background:rgba(255,255,255,.05);border:1px solid rgba(0,212,255,.2);color:#fff;border-radius:6px;"></div>
        </div>
      </div>

      <div class="admin-table-container">
        <h3><i class="bi bi-cpu-fill text-danger me-1"></i> System Status</h3>
        <div style="display:flex;flex-direction:column;gap:12px;padding:12px 0;">
          <div style="display:flex;justify-content:space-between;color:#b0b0d0;font-size:.9rem;"><span>System Version</span><span style="color:#00d4ff;">v2.4.1</span></div>
          <div style="display:flex;justify-content:space-between;color:#b0b0d0;font-size:.9rem;"><span>Last Backup</span><span style="color:#00ff88;">Today, 02:00 AM</span></div>
          <div style="display:flex;justify-content:space-between;color:#b0b0d0;font-size:.9rem;"><span>Uptime</span><span style="color:#00ff88;">99.98%</span></div>
          <button class="btn btn-admin" onclick="toast('Backup initiated!')"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Run Backup Now</button>
        </div>
      </div>
    </div>
  </div>
  <div class="bottom-nav-admin">
    <div class="nav-item" data-page="admin-dashboard" onclick="location.href='admin-dashboard.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">Dashboard</div></div>
    <div class="nav-item" data-page="admin-users" onclick="location.href='admin-users.php'"><div class="ni-icon"><i class="bi bi-people-fill"></i></div><div class="ni-label">Users</div></div>
    <div class="nav-item" data-page="admin-transactions" onclick="location.href='admin-transactions.php'"><div class="ni-icon"><i class="bi bi-cash-stack"></i></div><div class="ni-label">Transactions</div></div>
    <div class="nav-item" data-page="admin-reports" onclick="location.href='admin-reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
    <div class="nav-item active" data-page="admin-settings" onclick="location.href='admin-settings.php'"><div class="ni-icon"><i class="bi bi-gear-fill"></i></div><div class="ni-label">Settings</div></div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/admin-settings.js"></script>
</body>
</html>
