<?php
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Register Parenting Account – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/register-style.css">
</head>
<body>

<div class="page page-active" id="page-register">
  <div class="auth-body">
    <div class="auth-inner">
      <div class="auth-logo-box">
        <div class="logo-big">Pocket<span>Go</span></div>
        <p>SMK Setia Alam Parent Portal Registration</p>
      </div>

      <div class="auth-card">
        <h2>Register Account</h2>
        <p class="subtitle">Create a parenting profile to manage child e-wallet</p>

        <div class="form-group">
          <label>Full Name (as in NRIC)</label>
          <input type="text" id="reg-name" placeholder="Ahmad Bin Abdullah">
        </div>

        <div class="form-group">
          <label>NRIC Number</label>
          <input type="text" id="reg-ic" placeholder="850101-10-5432">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" id="reg-email" placeholder="ahmad@email.com">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" id="reg-phone" placeholder="012-3456789">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Password</label>
            <div class="password-container">
              <input type="password" id="reg-pass" placeholder="Min. 8 characters">
              <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-pass', this)" aria-label="Toggle password visibility">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <div class="password-container">
              <input type="password" id="reg-pass2" placeholder="Repeat password">
              <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('reg-pass2', this)" aria-label="Toggle password visibility">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Link student card section has been moved to the Card Section in parent portal -->

        <button class="btn btn-primary btn-full" style="margin-top:10px;" onclick="doRegister()">Create Parenting Account</button>

        <p class="auth-switch">Already registered? <a href="login.php">Log In here</a></p>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/register.js"></script>
</body>
</html>
