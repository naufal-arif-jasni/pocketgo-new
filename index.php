<?php
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>PocketGo – Smart School E-Wallet</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/index-style.css">
</head>
<body>

<div class="page page-active" id="page-landing">
  <nav class="landing-nav">
    <div class="logo">Pocket<span>Go</span></div>
    <button class="btn btn-primary btn-sm" onclick="location.href='login.php'">Login</button>
  </nav>

  <div class="landing-scroll">
    <header class="hero">
      <div class="hero-badge"><i class="bi bi-credit-card-2-front-fill me-1 text-warning"></i> SMK SETIA ALAM OFFICIAL WALLET</div>
      <h1>The Smarter Way to<br>Manage <span>School Money</span></h1>
      <p>A secure e-wallet for students to buy food at the canteen and supplies at the bookshop. Fully controlled by parents.</p>
      <div class="hero-btns">
        <button class="btn btn-yellow" onclick="location.href='register.php'">Get Started Now</button>
        <button class="btn btn-ghost" onclick="location.href='login.php'">Parent Portal</button>
      </div>
      <div class="hero-mockup">
        <div class="mockup-balance">
          <div class="lbl">Parent Balance</div>
          <div class="amt">RM ***.**</div>
        </div>
        <div class="mockup-card">
          <div>
            <div class="cn" style="font-size:.7rem;color:#FFD700;">PocketGo Card</div>
            <div class="cn2">**** *****</div>
          </div>
          <div class="chip"></div>
        </div>
      </div>
    </header>

    <section class="features">
      <div class="section-title">
        <h2>Why Choose <span>PocketGo?</span></h2>
        <p>Seamless cashless payment built for schools</p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-lightning-charge text-danger"></i></div>
          <h3>Instant Top Up</h3>
          <p>Instantly reload your child's e-wallet via FPX or credit card.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-shield-check text-success"></i></div>
          <h3>Limit Control</h3>
          <p>Set daily maximum spending limits to prevent overspending.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-bar-chart text-info"></i></div>
          <h3>Realtime Logs</h3>
          <p>Get instant alerts and detailed logs of every purchase made.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i class="bi bi-shield-lock text-primary"></i></div>
          <h3>Safe & Secure</h3>
          <p>No cash lost, no stolen wallets. Secure NFC chip technology.</p>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <h2>Ready to go Cashless?</h2>
      <p>Register your parenting account in 2 minutes and link your child's card instantly.</p>
      <button class="btn btn-primary" onclick="location.href='register.php'">Create Free Account</button>
    </section>

    <footer class="footer">
      <p>&copy; 2026 <strong>PocketGo</strong>. Developed for SMK Setia Alam.</p>
    </footer>
  </div>
</div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/index.js"></script>
</body>
</html>
