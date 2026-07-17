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
  <title>Card Settings – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/card-style.css">
</head>
<body>

<div class="page page-active" id="page-card">
  <div class="topbar-parent">
    <div style="display:flex;align-items:center;">
      <button class="back-btn" onclick="location.href='dashboard.php'">←</button>
      <div class="topbar-logo">Pocket<span>Go</span></div>
    </div>
    <div style="font-weight:700;font-size:1rem;">Card Controls</div>
  </div>

  <div class="scroll-area pb">
    <!-- Empty State: Show when no card registered -->
    <div class="empty-card-state" id="empty-card-container" style="display: none;">
      <div class="empty-icon-circle"><i class="bi bi-credit-card-2-front text-muted fs-1"></i></div>
      <h3>No Linked Student Card</h3>
      <p>Link your child's physical school student NFC card to begin managing their e-wallet balance, setting daily spending limits, and monitoring canteen transactions.</p>
      <button class="btn btn-primary" onclick="showModal('modal-register-card')" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 30px; font-weight: 600; margin-top: 10px;">
        <i class="bi bi-plus-circle-fill"></i> Register Student Card
      </button>
    </div>

    <!-- Active Card: Show when card registered -->
    <div id="active-card-container" style="display: none;">
      <!-- Horizontal Carousel Container -->
      <div class="carousel-outer">
        <!-- Navigation Arrow Buttons -->
        <button class="carousel-arrow prev" id="carousel-prev-btn" onclick="navigateCarousel(-1)"><i class="bi bi-chevron-left"></i></button>
        <button class="carousel-arrow next" id="carousel-next-btn" onclick="navigateCarousel(1)"><i class="bi bi-chevron-right"></i></button>

        <div class="carousel-container" id="carousel-scroller">
          <div class="carousel-track" id="card-carousel-track">
            <!-- Dynamic Children Cards & Plus-Card Slide -->
          </div>
        </div>
        <div class="carousel-dots" id="carousel-dots-container">
          <!-- Dynamic Dot Indicators -->
        </div>
      </div>

      <!-- Card Settings Wrapper (dynamic visibility based on selected slide) -->
      <div id="card-settings-details">
        <!-- Main Card Settings -->
        <div class="white-card" style="padding:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h4 style="font-size:.92rem;font-weight:700;"><i class="bi bi-credit-card-2-front-fill text-danger me-1"></i> Card NFC Operations</h4>
            <span class="tag tag-green">Secured</span>
          </div>
          <div class="card-actions">
            <button class="card-action-btn" onclick="toast('Card locking requires physical school terminal sync')">
              <div class="ca-icon"><i class="bi bi-lock-fill text-danger"></i></div>
              <h4>Lock Card</h4>
              <p>Temporarily disable</p>
            </button>
            <button class="card-action-btn" onclick="location.href='reports.php'">
              <div class="ca-icon"><i class="bi bi-exclamation-octagon-fill text-warning"></i></div>
              <h4>Report Lost</h4>
              <p>Card replacement</p>
            </button>
          </div>
        </div>

        <!-- Daily Spend Limit Manager -->
        <div class="white-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h4 style="font-size:.92rem;font-weight:700;"><i class="bi bi-shield-fill-check text-success me-1"></i> Daily Spending Limit</h4>
            <button class="btn btn-sm btn-outline" style="padding:6px 14px;" onclick="showModal('modal-limit')">Change Limit</button>
          </div>
          
          <p style="font-size:.8rem;color:#666;margin-bottom:14px;">Controls the maximum amount your child can spend at school canteen and bookshop per day.</p>
          
          <div class="limit-bar">
            <div class="limit-fill" id="card-limit-fill" style="width: 0%;"></div>
          </div>
          
          <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#1a1a1a;font-weight:600;margin-top:10px;">
            <span>Spent: RM <span id="card-spent-val">0.00</span></span>
            <span>Daily Limit: RM <span id="card-limit-val">0.00</span></span>
          </div>
          <div style="font-size:.78rem;color:#1a9e5c;font-weight:600;margin-top:4px;text-align:right;">
            Remaining: RM <span id="card-rem-val">0.00</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Parent Bottom Navigation -->
  <div class="bottom-nav-parent">
    <div class="nav-item" data-page="dashboard" onclick="location.href='dashboard.php'"><div class="ni-icon"><i class="bi bi-house-door-fill"></i></div><div class="ni-label">Home</div></div>
    <div class="nav-item" data-page="topup" onclick="location.href='topup.php'"><div class="ni-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="ni-label">Top Up</div></div>
    <div class="nav-item" data-page="history" onclick="location.href='history.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">History</div></div>
    <div class="nav-item active" data-page="card" onclick="location.href='card.php'"><div class="ni-icon"><i class="bi bi-credit-card-2-front-fill"></i></div><div class="ni-label">My Card</div></div>
    <div class="nav-item" data-page="reports" onclick="location.href='reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
  </div>
</div>

<!-- MODAL: SET DAILY SPENDING LIMIT -->
<div class="modal-overlay" id="modal-limit" onclick="closeModal('modal-limit')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title"><i class="bi bi-shield-fill-check text-success me-1"></i> Set Daily Spending Limit</div>
    <p style="font-size:.85rem;color:#666;margin-bottom:18px;">Select a standard maximum daily spending limit or specify a custom threshold.</p>
    
    <div class="amount-grid" style="margin-bottom:14px;">
      <button class="amount-btn" onclick="selectLimit(5, this)">RM 5</button>
      <button class="amount-btn" onclick="selectLimit(10, this)">RM 10</button>
      <button class="amount-btn active" onclick="selectLimit(20, this)">RM 20</button>
      <button class="amount-btn" onclick="selectLimit(30, this)">RM 30</button>
      <button class="amount-btn" onclick="selectLimit(50, this)">RM 50</button>
      <button class="amount-btn" onclick="selectLimit(100, this)">RM 100</button>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
      <label>Or enter custom daily limit (RM)</label>
      <input type="number" id="card-custom-limit" placeholder="50.00" oninput="selectCustomLimit(this.value)">
    </div>

    <button class="btn btn-primary btn-full" onclick="saveLimit()">Save Limit Configuration</button>
  </div>
</div>

<!-- MODAL: REGISTER STUDENT CARD -->
<div class="modal-overlay" id="modal-register-card" onclick="closeModal('modal-register-card')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title"><i class="bi bi-credit-card-2-front-fill text-danger me-1"></i> Link Student NFC Card</div>
    <p style="font-size:.82rem;color:#666;margin-bottom:18px;">Enter the physical details of your child's PocketGo NFC card to synchronize with the school terminal.</p>
    
    <div class="form-group" style="margin-bottom:14px;">
      <label>10-digit Card Serial No.</label>
      <input type="text" id="reg-card-serial" maxlength="10" placeholder="e.g. 1029483756">
    </div>

    <div class="form-group" style="margin-bottom:14px;">
      <label>Student Full Name</label>
      <input type="text" id="reg-student-name" placeholder="e.g. Muhammad Faris">
    </div>

    <div class="form-group" style="margin-bottom:14px;">
      <label>Student NRIC (for ID)</label>
      <input type="text" id="reg-student-nric" placeholder="e.g. 120512-10-1234">
    </div>

    <div class="form-group" style="margin-bottom:20px;">
      <label>Class</label>
      <input type="text" id="reg-student-class" placeholder="e.g. 4 Amanah">
    </div>

    <button class="btn btn-primary btn-full" onclick="registerStudentCard()">Register & Link Card</button>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/card.js"></script>
</body>
</html>
