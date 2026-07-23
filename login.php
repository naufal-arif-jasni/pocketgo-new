<?php
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Login – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/login-style.css">
</head>
<body>

<div class="page page-active" id="page-login">
  <div class="auth-body">
    <div class="auth-inner">
      <div class="auth-logo-box">
        <div class="logo-big">Pocket<span>Go</span></div>
        <p>SK Setia Alam Cashless Portal</p>
      </div>

      <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Select your login portal to continue</p>

        <!-- Role Selector Tabs -->
        <div style="display:flex;background:#f0f0f5;border-radius:12px;padding:4px;margin-bottom:16px;gap:2px;">
          <button id="login-role-parent" style="flex:1;border:none;padding:9px 6px;border-radius:10px;font-family:'Poppins',sans-serif;font-weight:600;font-size:.82rem;cursor:pointer;transition:all .2s;" onclick="setLoginRole('parent')"><i class="bi bi-people-fill me-1"></i> Parent Portal</button>
          <button id="login-role-admin" style="flex:1;border:none;padding:9px 6px;border-radius:10px;font-family:'Poppins',sans-serif;font-weight:600;font-size:.82rem;cursor:pointer;transition:all .2s;" onclick="setLoginRole('admin')"><i class="bi bi-shield-lock-fill me-1"></i> Admin Portal</button>
        </div>

        <!-- Demo Seed Credentials Box -->
        <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:10px 12px;margin-bottom:18px;font-size:0.78rem;color:#475569;">
          <div style="font-weight:700;margin-bottom:4px;color:#1e293b;"><i class="bi bi-key-fill text-warning me-1"></i> Quick Logins & Shortcuts:</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">
            <button type="button" style="background:#fff;border:1px solid #cbd5e1;border-radius:6px;padding:4px 8px;font-size:0.74rem;cursor:pointer;font-weight:600;" onclick="quickFill('ahmad@email.com','password123','parent')"><i class="bi bi-person me-1"></i> Parent</button>
            <button type="button" style="background:#fff;border:1px solid #cbd5e1;border-radius:6px;padding:4px 8px;font-size:0.74rem;cursor:pointer;font-weight:600;" onclick="quickFill('Admin1','12345','admin')"><i class="bi bi-shield-lock me-1"></i> Admin</button>
            <a href="pos.php" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;padding:4px 8px;font-size:0.74rem;font-weight:700;text-decoration:none;"><i class="bi bi-shop me-1"></i> POS Canteen (Direct Access)</a>
          </div>
        </div>

        <div class="form-group">
          <label>Email Address / ID</label>
          <input type="text" id="login-email" placeholder="parent@email.com">
        </div>

        <div class="form-group">
          <label>Password</label>
          <div class="password-container">
            <input type="password" id="login-pass" placeholder="••••••••">
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('login-pass', this)" aria-label="Toggle password visibility">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button class="btn btn-primary btn-full" style="margin-top:10px;" onclick="doLogin()">Log In Securely</button>

        <div class="divider">OR</div>

        <button class="btn btn-outline btn-full" onclick="location.href='index.php'">← Back to Home</button>

        <p class="auth-switch">Don't have an account? <a href="register.php">Register now</a></p>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/login.js"></script>
</body>
</html>
