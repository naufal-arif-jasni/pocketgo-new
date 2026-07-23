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
  <title>Top Up – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/topup-style.css">
</head>
<body>

<div class="page page-active" id="page-topup">
  <div class="topbar-parent">
    <div style="display:flex;align-items:center;">
      <button class="back-btn" onclick="location.href='dashboard.php'">←</button>
      <div class="topbar-logo">Pocket<span>Go</span></div>
    </div>
    <div style="font-weight:700;font-size:1rem;">Top Up Wallet</div>
  </div>

  <div class="scroll-area pb">
    <!-- Unlinked Block State: Hidden by default, shown in topup.js if 0 cards -->
    <div class="empty-card-state" id="tu-no-card-block" style="display: none; margin: 20px 16px;">
      <div class="empty-icon-circle"><i class="bi bi-shield-lock-fill fs-2 text-muted"></i></div>
      <h3>Top Up Locked</h3>
      <p>You must link at least one PocketGo school student NFC card to your account before you can perform any wallet top-up operations.</p>
      <button class="btn btn-primary" onclick="location.href='card.php'" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 30px; font-weight: 600; margin-top: 10px;">
        Go to Card Settings
      </button>
    </div>

    <div id="tu-active-container">
      <div class="white-card">
        <div style="font-size:.8rem;color:#666;font-weight:600;margin-bottom:8px;">TOPPING UP WALLET FOR</div>
        <select id="tu-card-selector" style="width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid #e0e0e0; font-weight: 600; font-family: 'Poppins', sans-serif; background: #fff;">
          <!-- Options loaded dynamically -->
        </select>
      </div>

      <div class="white-card">
        <h4 style="font-size:.9rem;font-weight:700;margin-bottom:12px;">1. Select Amount (RM)</h4>
        <div class="amount-grid">
          <button class="amount-btn active" onclick="selectAmount(10, this)">10</button>
          <button class="amount-btn" onclick="selectAmount(20, this)">20</button>
          <button class="amount-btn" onclick="selectAmount(50, this)">50</button>
          <button class="amount-btn" onclick="selectAmount(100, this)">100</button>
          <button class="amount-btn" onclick="selectAmount(200, this)">200</button>
          <button class="amount-btn" onclick="selectAmount(500, this)">500</button>
        </div>
        <div style="margin-top:14px;">
          <label>Or enter custom amount (RM)</label>
          <input type="number" id="tu-custom-amount" placeholder="0.00" oninput="selectCustomAmount(this.value)">
        </div>
      </div>

      <div class="white-card payment-gateway-card">
        <h3 class="pg-title">Select Payment Gateway</h3>
        <p class="pg-subtitle">Powered by real Malaysian payment providers</p>
        
        <!-- Category Tabs -->
        <div class="pg-tabs-container">
          <button type="button" class="pg-tab-pill active" id="tab-banking" onclick="changePaymentTab('banking')">
            <span class="pg-tab-icon">🏦</span> Online Banking
          </button>
          <button type="button" class="pg-tab-pill" id="tab-ewallet" onclick="changePaymentTab('ewallet')">
            <span class="pg-tab-icon">📱</span> E-Wallet
          </button>
          <button type="button" class="pg-tab-pill" id="tab-card" onclick="changePaymentTab('card')">
            <span class="pg-tab-icon">💳</span> Card
          </button>
        </div>

        <!-- Powered By Badges -->
        <div class="pg-powered-by">
          <span class="powered-label">Powered by</span>
          <span class="powered-badge billplz">Billplz</span>
          <span class="powered-badge ipay88">iPay88</span>
          <span class="powered-badge molpay">MOLPay</span>
        </div>

        <!-- Informational Banner -->
        <div class="pg-info-banner" id="pg-banner-el">
          <span class="banner-icon">🔓</span> 
          <span class="banner-text">FPX — You'll be securely redirected to your bank's portal to authorise payment.</span>
        </div>

        <!-- Inline Selector Section for Banks -->
        <div class="pg-content-sec" id="sec-banking">
          <h4 class="sec-heading">Select Bank</h4>
          <div class="bank-grid">
            <button type="button" class="bank-btn active" onclick="selectBank('Maybank2u', this)">
              <span class="bank-logo-sq" style="background:#ffcc00;color:#000;">M</span>
              <div class="ew-text-container">
                <span class="ew-title">Maybank2u</span>
                <span class="ew-desc">Pay via Maybank FPX portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('CIMB Clicks', this)">
              <span class="bank-logo-sq" style="background:#c00000;color:#fff;">C</span>
              <div class="ew-text-container">
                <span class="ew-title">CIMB Clicks</span>
                <span class="ew-desc">Pay via CIMB Clicks FPX portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('RHB Now', this)">
              <span class="bank-logo-sq" style="background:#0055b3;color:#fff;">R</span>
              <div class="ew-text-container">
                <span class="ew-title">RHB Now</span>
                <span class="ew-desc">Pay via RHB Now FPX portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('Public Bank', this)">
              <span class="bank-logo-sq" style="background:#0033aa;color:#fff;">P</span>
              <div class="ew-text-container">
                <span class="ew-title">Public Bank</span>
                <span class="ew-desc">Pay via Public Bank FPX portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('Hong Leong', this)">
              <span class="bank-logo-sq" style="background:#e5b800;color:#fff;">H</span>
              <div class="ew-text-container">
                <span class="ew-title">Hong Leong Bank</span>
                <span class="ew-desc">Pay via Hong Leong Connect</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('AmBank', this)">
              <span class="bank-logo-sq" style="background:#f37021;color:#fff;">A</span>
              <div class="ew-text-container">
                <span class="ew-title">AmBank</span>
                <span class="ew-desc">Pay via AmOnline portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('BSN', this)">
              <span class="bank-logo-sq" style="background:#00875a;color:#fff;">B</span>
              <div class="ew-text-container">
                <span class="ew-title">BSN</span>
                <span class="ew-desc">Pay via myBSN portal</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('Bank Islam', this)">
              <span class="bank-logo-sq" style="background:#005f3f;color:#fff;">BI</span>
              <div class="ew-text-container">
                <span class="ew-title">Bank Islam</span>
                <span class="ew-desc">Pay via Bank Islam Internet Banking</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('Alliance', this)">
              <span class="bank-logo-sq" style="background:#c8102e;color:#fff;">A</span>
              <div class="ew-text-container">
                <span class="ew-title">Alliance Bank</span>
                <span class="ew-desc">Pay via Alliance Online</span>
              </div>
            </button>
            <button type="button" class="bank-btn" onclick="selectBank('Bank Rakyat', this)">
              <span class="bank-logo-sq" style="background:#009c95;color:#fff;">BR</span>
              <div class="ew-text-container">
                <span class="ew-title">Bank Rakyat</span>
                <span class="ew-desc">Pay via iRakyat portal</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Inline Selector Section for E-Wallet -->
        <div class="pg-content-sec hidden" id="sec-ewallet">
          <h4 class="sec-heading">Select E-Wallet</h4>
          <div class="ew-grid">
            <button type="button" class="ew-btn" id="ew-tng-inline" onclick="selectEwallet('Touch n Go', 'ew-tng-inline')">
              <span class="ew-logo-sq" style="background:#0051ba;color:#fff;font-size:.7rem;font-weight:800;">TnG</span>
              <div class="ew-text-container">
                <span class="ew-title">Touch 'n Go eWallet</span>
                <span class="ew-desc">Pay instantly via TNG app</span>
              </div>
            </button>
            <button type="button" class="ew-btn" id="ew-grab-inline" onclick="selectEwallet('GrabPay', 'ew-grab-inline')">
              <span class="ew-logo-sq" style="background:#00b14f;color:#fff;font-size:.7rem;font-weight:800;">Grab</span>
              <div class="ew-text-container">
                <span class="ew-title">GrabPay</span>
                <span class="ew-desc">Earn GrabRewards points</span>
              </div>
            </button>
            <button type="button" class="ew-btn" id="ew-boost-inline" onclick="selectEwallet('Boost', 'ew-boost-inline')">
              <span class="ew-logo-sq" style="background:#ff0033;color:#fff;font-size:.7rem;font-weight:800;">B</span>
              <div class="ew-text-container">
                <span class="ew-title">Boost Wallet</span>
                <span class="ew-desc">Pay securely with red Boost</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Inline Selector Section for Cards -->
        <div class="pg-content-sec hidden" id="sec-card">
          <!-- Populated dynamically with Saved Card OR New Card Input Form -->
          <div id="dynamic-card-container">
            <!-- Loading or loaded state -->
          </div>
        </div>
      </div>

      <button class="btn btn-primary btn-full" style="margin-top:20px; font-weight:700; padding:14px; border-radius:30px; font-size:1.05rem; box-shadow: 0 4px 15px rgba(200, 16, 46, 0.25);" onclick="doTopUp()">Confirm Top Up</button>
    </div>
  </div>

  <!-- Parent Bottom Navigation -->
  <div class="bottom-nav-parent">
    <div class="nav-item" data-page="dashboard" onclick="location.href='dashboard.php'"><div class="ni-icon"><i class="bi bi-house-door-fill"></i></div><div class="ni-label">Home</div></div>
    <div class="nav-item active" data-page="topup" onclick="location.href='topup.php'"><div class="ni-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="ni-label">Top Up</div></div>
    <div class="nav-item" data-page="history" onclick="location.href='history.php'"><div class="ni-icon"><i class="bi bi-bar-chart-line-fill"></i></div><div class="ni-label">History</div></div>
    <div class="nav-item" data-page="card" onclick="location.href='card.php'"><div class="ni-icon"><i class="bi bi-credit-card-2-front-fill"></i></div><div class="ni-label">My Card</div></div>
    <div class="nav-item" data-page="reports" onclick="location.href='reports.php'"><div class="ni-icon"><i class="bi bi-telephone-fill"></i></div><div class="ni-label">Reports</div></div>
  </div>
</div>

<!-- DUMMY GATEWAY INTEGRATED SIMULATOR OVERLAY -->
<div class="gateway-sim-overlay hidden" id="modal-dummy-gateway">
  <div class="gateway-sim-box">
    <div class="gateway-sim-header">
      <div style="font-weight:800;font-size:0.95rem;display:flex;align-items:center;gap:6px;">
        <span id="sim-header-bank-icon">🏦</span> <span id="sim-header-bank-name">Secure Gateway</span>
      </div>
      <div class="gw-sim-lock">
        <i class="bi bi-shield-lock-fill"></i> 256-Bit SSL Secured
      </div>
    </div>
    <div class="gateway-sim-body">
      <!-- Merchant Logo and Details -->
      <div class="gw-sim-logo-row">
        <div>
          <div style="font-family:'Poppins',sans-serif;font-weight:800;font-size:0.95rem;color:#C8102E;margin:0;">Pocket<span style="color:#FFD700;">Go</span></div>
          <div style="font-size:0.6rem;color:#777;font-weight:600;">SECURE CHECKOUT</div>
        </div>
        <div class="merchant-badge">
          <div class="merchant-name">PocketGo Solutions Ltd</div>
          <div class="merchant-desc" id="sim-meta-ref">Ref: PG-938210</div>
        </div>
      </div>

      <!-- STATE 1: Connecting Gateway Loader -->
      <div class="gw-sim-state center" id="sim-state-connecting">
        <div class="spinner-sim" style="margin: 0 auto 16px auto;"></div>
        <h4 style="font-size:0.95rem;font-weight:700;color:#333;" id="sim-connecting-text">Connecting to bank portal...</h4>
        <p style="font-size:0.72rem;color:#777;margin-top:6px;">Please do not close this window or click refresh.</p>
      </div>

      <!-- STATE 2: Bank Login Portal / E-Wallet / Card Gateway Form -->
      <div class="gw-sim-state hidden" id="sim-state-form">
        <div class="bank-auth-header">
          <div class="bank-auth-title" id="sim-form-title">Bank Portal Secure Login</div>
          <div class="bank-auth-subtitle">Verify your identity to proceed with the payment of <strong style="color:#C8102E;" id="sim-form-amount">RM 50.00</strong></div>
        </div>
        
        <!-- BANK LOGIN FIELDS -->
        <div id="sim-fields-bank" class="hidden">
          <div class="sim-form-group">
            <label>Online Banking Username</label>
            <input type="text" class="sim-input" id="sim-bank-username" value="pocket_demo_parent">
          </div>
          <div class="sim-form-group">
            <label>Password</label>
            <input type="password" class="sim-input" id="sim-bank-password" value="••••••••••••">
          </div>
        </div>

        <!-- EWALLET MOBILE FIELDS -->
        <div id="sim-fields-ewallet" class="hidden">
          <div class="sim-form-group">
            <label>Registered Mobile Number</label>
            <input type="text" class="sim-input" id="sim-ewallet-phone" value="+6012-3456789">
          </div>
          <div style="font-size:0.7rem;color:#666;text-align:center;margin:8px 0;">
            Or scan the merchant QR code in your e-wallet app.
          </div>
        </div>

        <!-- NEW CARD FIELDS (IF APPLICABLE) -->
        <div id="sim-fields-card" class="hidden">
          <div class="sim-form-group">
            <label>Enter 3-Digit CVV</label>
            <input type="password" class="sim-input" id="sim-card-cvv" placeholder="•••" maxlength="3" style="letter-spacing:4px;text-align:center;">
          </div>
        </div>

        <button class="sim-btn-primary" onclick="submitSimForm()">Authorize Secure Payment</button>
        <button class="sim-btn-secondary" onclick="cancelSimPayment()">Cancel Transaction</button>
      </div>

      <!-- STATE 3: OTP / TAC Code Verification -->
      <div class="gw-sim-state hidden" id="sim-state-otp">
        <div class="bank-auth-header">
          <div class="bank-auth-title">Enter Verification Code</div>
          <div class="bank-auth-subtitle">A verification code has been sent to your registered device.</div>
        </div>
        
        <div class="otp-box-sim">
          <div style="font-size:0.76rem;color:#555;font-weight:600;">One-Time PIN (OTP / TAC)</div>
          <div class="otp-num-badge">DEMO CODE: 888888</div>
          <input type="text" class="sim-input" id="sim-otp-input" placeholder="Enter 6-digit code" maxlength="6" style="margin-top:14px; text-align:center; font-size:1.1rem; font-weight:700; letter-spacing:6px;">
        </div>

        <button class="sim-btn-primary" onclick="submitSimOtp()">Confirm & Pay</button>
        <button class="sim-btn-secondary" onclick="cancelSimPayment()">Cancel</button>
      </div>

      <!-- STATE 4: Transfer Success Message -->
      <div class="gw-sim-state center hidden" id="sim-state-success">
        <div class="success-circle-sim" style="margin: 0 auto 16px auto;">
          <i class="bi bi-check2-circle"></i>
        </div>
        <h4 style="font-size:1.1rem;font-weight:800;color:#137333;margin-bottom:4px;">Transaction Successful!</h4>
        <div style="background:#eef7f2; border:1px solid #c3e6cb; border-radius:12px; padding:14px; width:100%; margin-top:8px; margin-bottom:18px;">
          <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;"><span style="color:#555;">Amount Transferred:</span><strong style="color:#137333;" id="sim-success-amount">RM 50.00</strong></div>
          <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;"><span style="color:#555;">Payment Bank:</span><strong id="sim-success-bank">Maybank2u</strong></div>
          <div style="display:flex; justify-content:space-between; font-size:0.75rem;"><span style="color:#555;">Ref Number:</span><strong id="sim-success-ref">PG-938210</strong></div>
        </div>
        <p style="font-size:0.7rem;color:#666;margin-bottom:0;">Returning to PocketGo parent application...</p>
      </div>

    </div>
  </div>
</div>

<!-- MODAL: SECURITY PIN ENTRY (SAVED CARD) -->
<div class="modal-overlay" id="modal-pin" onclick="closeModal('modal-pin')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title" style="text-align:center;">🔒 Enter Security PIN</div>
    <p style="text-align:center;font-size:.85rem;color:#666;margin-bottom:18px;">Please enter your 6-digit transaction PIN to authorise this payment.</p>

    <div class="payment-summary">
      <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;"><span style="color:#666;">Payment to</span><strong>SK Setia Alam</strong></div>
      <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;"><span style="color:#666;">Amount</span><strong style="color:#C8102E;" id="m-sum-amount">RM 0.00</strong></div>
      <div style="display:flex;justify-content:space-between;font-size:.82rem;"><span style="color:#666;">Funding Source</span><span id="m-sum-method" style="font-size:.78rem;">—</span></div>
    </div>

    <!-- PIN display bubbles -->
    <div class="pin-display">
      <div class="pin-dot"></div>
      <div class="pin-dot"></div>
      <div class="pin-dot"></div>
      <div class="pin-dot"></div>
      <div class="pin-dot"></div>
      <div class="pin-dot"></div>
    </div>

    <!-- Custom Stylised Pin-Pad -->
    <div class="pin-pad">
      <button class="pin-btn" onclick="pinBtn('1')">1</button>
      <button class="pin-btn" onclick="pinBtn('2')">2</button>
      <button class="pin-btn" onclick="pinBtn('3')">3</button>
      <button class="pin-btn" onclick="pinBtn('4')">4</button>
      <button class="pin-btn" onclick="pinBtn('5')">5</button>
      <button class="pin-btn" onclick="pinBtn('6')">6</button>
      <button class="pin-btn" onclick="pinBtn('7')">7</button>
      <button class="pin-btn" onclick="pinBtn('8')">8</button>
      <button class="pin-btn" onclick="pinBtn('9')">9</button>
      <button class="pin-btn" style="font-size:.8rem;" onclick="pinClear()">Clear</button>
      <button class="pin-btn" onclick="pinBtn('0')">0</button>
      <button class="pin-btn" style="font-size:.8rem;color:#ccc;" disabled>#</button>
    </div>
    
    <div style="text-align:center;font-size:.78rem;color:#999;margin-top:14px;">Demo PIN: <strong>123456</strong></div>
  </div>
</div>

<!-- MODAL: LINK VISA/MASTER CARD -->
<div class="modal-overlay" id="modal-link-visa" onclick="closeModal('modal-link-visa')">
  <div class="modal-sheet" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title">💳 Link Credit / Debit Card</div>
    <p style="font-size:.82rem;color:#666;margin-bottom:18px;">Enter your Visa or MasterCard details to link it securely for e-wallet top-ups. This information is stored in our secure database.</p>
    
    <div class="form-group" style="margin-bottom:14px;">
      <label>Cardholder Name</label>
      <input type="text" id="visa-holder-name" placeholder="e.g. Ahmad Abdullah">
    </div>

    <div class="form-group" style="margin-bottom:14px;">
      <label>Card Number</label>
      <input type="text" id="visa-card-number" placeholder="e.g. 4111 2222 3333 4444" maxlength="19" oninput="formatCardNumber(this)">
    </div>

    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
      <div class="form-group">
        <label>Expiry Date</label>
        <input type="text" id="visa-expiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)">
      </div>
      <div class="form-group">
        <label>CVV</label>
        <input type="password" id="visa-cvv" placeholder="•••" maxlength="3">
      </div>
    </div>

    <button class="btn btn-primary btn-full" onclick="saveVisaCard()">Link & Save Card</button>
  </div>
</div>

<!-- MODAL: NFC TAP & CARD MATCH VERIFICATION TERMINAL -->
<div class="modal-overlay" id="modal-nfc-verify" onclick="cancelNfcVerification()">
  <div class="modal-sheet" style="max-width: 460px; border-radius: 24px;" onclick="event.stopPropagation()">
    <div class="modal-handle"></div>
    <div class="modal-title" style="text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; font-size:1.1rem; color:#1e1e2f;">
      <i class="bi bi-cpu-fill text-primary"></i> NFC Card Verification Terminal
    </div>
    <p style="text-align:center; font-size:.82rem; color:#64748b; margin-bottom:14px;">
      Physical tap and card serial verification required before completing top-up.
    </p>

    <!-- Target Card Summary Info Box -->
    <div class="mb-3" style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:14px; padding:12px 16px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
        <span style="font-size:0.72rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Required Student Card</span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1" style="font-size:0.75rem; font-weight:700;" id="tu-target-amount-badge">RM 50.00</span>
      </div>
      <div style="font-weight:800; font-size:1.05rem; color:#0f172a;" id="tu-target-student-name">Muhammad Faris</div>
      <div style="display:flex; justify-content:space-between; font-size:0.82rem; color:#475569; margin-top:4px;">
        <span>Target Card Serial: <strong style="font-family:monospace; color:#3b82f6;" id="tu-target-card-uid">1000000001</strong></span>
        <span id="tu-target-student-class" style="font-weight:600;">4 Amanah</span>
      </div>
    </div>

    <!-- Hidden focus input for hardware USB RFID scanner stream -->
    <form id="tu-rfid-form" onsubmit="handleTopupRfidSubmit(event)">
      <input type="text" id="topup-rfid-input" class="hidden-input" autocomplete="off">
    </form>

    <!-- Terminal Status Badge -->
    <div class="text-center mb-3">
      <div class="status-badge status-ready" id="tu-terminal-badge">
        <span class="status-dot"></span>
        <span id="tu-badge-text">Terminal Ready — Tap Card</span>
      </div>
    </div>

    <!-- Pulsing Target Device Graphic -->
    <div class="rfid-target mb-3" onclick="focusTopupRfid()">
      <div class="ripple"></div>
      <div class="ripple ripple-2"></div>
      <i class="bi bi-credit-card-2-front rfid-icon"></i>
    </div>

    <!-- Live Verification Status Alert Banner -->
    <div id="tu-verify-alert" class="alert alert-info py-2 px-3 mb-3 text-center" style="font-size:0.82rem; border-radius:12px; font-weight:500;">
      <i class="bi bi-broadcast me-1"></i> Place or swipe physical card on NFC scanner...
    </div>

    <!-- Previews & Test Simulation Swipes -->
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:10px 12px; margin-bottom:16px;">
      <div style="font-size:0.72rem; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
        <span><i class="bi bi-broadcast me-1"></i> Previews & Test Simulation Swipes</span>
        <span style="font-weight:500; text-transform:none; font-size:0.68rem; color:#94a3b8;">Click button to emulate tap</span>
      </div>
      <div id="tu-sim-buttons-container" style="display:flex; flex-wrap:wrap; gap:6px;">
        <!-- Dynamically populated buttons -->
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary w-100 py-2.5" style="border-radius:12px; font-weight:600; font-size:0.88rem;" onclick="cancelNfcVerification()">Cancel Transaction</button>
    </div>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/topup.js"></script>
</body>
</html>
