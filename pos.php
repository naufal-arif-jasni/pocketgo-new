<?php
// pos.php - Dedicated Canteen POS Terminal for 125KHz RFID Cards (EM4100 / JT308 Readers)
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Canteen POS Terminal – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/common-style.css">
  <style>
    :root {
      --pos-bg: #f4f6f9;
      --pos-primary: #C8102E;
      --pos-dark: #1e1e2f;
      --pos-accent: #00d4ff;
    }
    body {
      background-color: var(--pos-bg);
      font-family: 'Poppins', sans-serif;
      color: #333;
    }
    .pos-header {
      background-color: var(--pos-dark);
      color: #fff;
      padding: 14px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 3px solid var(--pos-primary);
    }
    .pos-brand {
      font-weight: 800;
      font-size: 1.35rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .pos-brand span {
      color: var(--pos-primary);
    }
    .pos-clock {
      font-size: 0.88rem;
      color: rgba(255,255,255,0.75);
      font-weight: 500;
    }
    .pos-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      border: 1px solid #e2e8f0;
      padding: 24px;
      margin-bottom: 24px;
    }
    .amount-display-box {
      background: #111827;
      color: #10B981;
      border-radius: 14px;
      padding: 20px 24px;
      text-align: right;
      font-family: 'Courier New', Courier, monospace;
      margin-bottom: 20px;
      box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);
    }
    .amount-label {
      font-size: 0.8rem;
      color: #9CA3AF;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 4px;
      font-family: 'Poppins', sans-serif;
    }
    .amount-huge {
      font-size: 3.2rem;
      font-weight: 800;
      line-height: 1;
    }
    .preset-btn {
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 12px;
      padding: 14px;
      font-weight: 700;
      font-size: 1rem;
      color: #334155;
      transition: all 0.15s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .preset-btn:hover, .preset-btn:active {
      background: #e2e8f0;
      border-color: #94a3b8;
      transform: translateY(-2px);
    }
    .preset-sub {
      font-size: 0.72rem;
      font-weight: 500;
      color: #64748b;
      margin-top: 2px;
    }
    .keypad-btn {
      background: #ffffff;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      height: 60px;
      font-weight: 700;
      font-size: 1.35rem;
      color: #1e293b;
      transition: all 0.15s ease;
    }
    .keypad-btn:hover {
      background: #f1f5f9;
      border-color: #cbd5e1;
    }
    .keypad-btn:active {
      background: #e2e8f0;
      transform: scale(0.97);
    }
    .btn-clear {
      background: #fef2f2;
      color: #dc2626;
      border-color: #fecaca;
    }
    .btn-clear:hover {
      background: #fee2e2;
    }
    .rfid-status-box {
      background: linear-gradient(135deg, #1e1e2f 0%, #2a2a40 100%);
      color: #fff;
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .rfid-pulse-ring {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: rgba(0, 212, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0.4); }
      70% { box-shadow: 0 0 0 20px rgba(0, 212, 255, 0); }
      100% { box-shadow: 0 0 0 0 rgba(0, 212, 255, 0); }
    }
    .rfid-hidden-input {
      position: absolute;
      opacity: 0;
      top: -100px;
      left: -100px;
    }
    .receipt-modal-content {
      border-radius: 20px;
      border: none;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

  <!-- Top POS Header Navigation -->
  <header class="pos-header">
    <div class="pos-brand">
      <i class="bi bi-shop text-warning" style="font-size: 1.6rem;"></i>
      <div>Pocket<span>Go</span> <small style="font-size: 0.8rem; font-weight: 500; color: #cbd5e1;">· Canteen POS</small></div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="pos-clock" id="pos-clock-display"><i class="bi bi-clock me-1"></i> --:--:--</div>
      <a href="scan.php" class="btn btn-sm btn-outline-light" style="border-radius: 8px; font-weight: 600;"><i class="bi bi-qr-code-scan me-1"></i> Terminal Scanner</a>
      <a href="dashboard.php" class="btn btn-sm btn-outline-light" style="border-radius: 8px; font-weight: 600;"><i class="bi bi-house me-1"></i> Parent Portal</a>
      <a href="login.php" class="btn btn-sm btn-danger" style="border-radius: 8px; font-weight: 600; background-color: var(--pos-primary);"><i class="bi bi-box-arrow-right me-1"></i> Exit</a>
    </div>
  </header>

  <div class="container-fluid py-4 px-md-4">
    <div class="row g-4">
      
      <!-- Left Column: Amount Builder & Keypad -->
      <div class="col-lg-7 col-xl-6">
        <div class="pos-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0 fw-bold" style="color: #1e293b;"><i class="bi bi-calculator me-2 text-primary"></i>Purchase Total</h5>
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2.5 py-1">Mode: Canteen Sales</span>
          </div>

          <!-- Total Display Box -->
          <div class="amount-display-box">
            <div class="amount-label" id="pos-item-title">Total Charge Amount</div>
            <div class="amount-huge" id="pos-amount-val">RM 0.00</div>
          </div>

          <!-- Quick Food Presets -->
          <div class="mb-3">
            <label class="form-label text-muted fw-semibold" style="font-size: 0.8rem;">QUICK MEAL PRESETS</label>
            <div class="row g-2">
              <div class="col-4 col-sm-4">
                <button class="preset-btn w-100" onclick="addPresetAmount(1.00, 'Drink / Water')">
                  <span>RM 1.00</span>
                  <span class="preset-sub">Drink / Snack</span>
                </button>
              </div>
              <div class="col-4 col-sm-4">
                <button class="preset-btn w-100" onclick="addPresetAmount(2.00, 'Milo / Kuih')">
                  <span>RM 2.00</span>
                  <span class="preset-sub">Milo / Kuih</span>
                </button>
              </div>
              <div class="col-4 col-sm-4">
                <button class="preset-btn w-100" onclick="addPresetAmount(3.50, 'Nasi Lemak / Mee')">
                  <span>RM 3.50</span>
                  <span class="preset-sub">Nasi Lemak</span>
                </button>
              </div>
              <div class="col-6 col-sm-6">
                <button class="preset-btn w-100" onclick="addPresetAmount(5.00, 'Chicken Rice Meal')">
                  <span>RM 5.00</span>
                  <span class="preset-sub">Chicken Rice Set</span>
                </button>
              </div>
              <div class="col-6 col-sm-6">
                <button class="preset-btn w-100" onclick="addPresetAmount(10.00, 'Bookshop / Stationery')">
                  <span>RM 10.00</span>
                  <span class="preset-sub">Bookshop Items</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Keypad Builder -->
          <div>
            <label class="form-label text-muted fw-semibold" style="font-size: 0.8rem;">CUSTOM KEYPAD INPUT</label>
            <div class="row g-2">
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('7')">7</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('8')">8</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('9')">9</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('4')">4</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('5')">5</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('6')">6</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('1')">1</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('2')">2</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('3')">3</button></div>
              <div class="col-4"><button class="keypad-btn btn-clear w-100" onclick="clearAmount()"><i class="bi bi-trash3 me-1"></i> CLR</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('0')">0</button></div>
              <div class="col-4"><button class="keypad-btn w-100" onclick="pressKey('00')">00</button></div>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Column: RFID Reader Terminal & Tap Detector -->
      <div class="col-lg-5 col-xl-6">
        
        <!-- RFID Tap Listener Box -->
        <div class="rfid-status-box mb-4">
          <div class="rfid-pulse-ring">
            <i class="bi bi-broadcast text-info" style="font-size: 2.8rem;"></i>
          </div>

          <h3 class="fw-bold mb-1" style="font-size: 1.4rem;">Tap Student RFID Card</h3>
          <p class="text-white-50" style="font-size: 0.88rem; max-width: 320px; margin: 0 auto 16px;">
            Place 125KHz RFID card on reader (JT308 USB Reader auto-detects UID)
          </p>

          <!-- Hidden Input listener for USB HID Keyboard Reader -->
          <form id="pos-tap-form" onsubmit="handlePosTapSubmit(event)">
            <input type="text" id="rfid-pos-input" class="rfid-hidden-input" autocomplete="off" autofocus>
          </form>

          <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5" style="font-size: 0.8rem;">
            <i class="bi bi-check-circle-fill me-1"></i> Reader Online & Ready
          </div>
        </div>

        <!-- Manual Tester & Simulator Shortcuts -->
        <div class="pos-card">
          <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.85rem;"><i class="bi bi-cpu me-1"></i> RFID SCANNER SIMULATOR / MANUAL INPUT</h6>
          
          <div class="input-group mb-3">
            <span class="input-group-text bg-light text-muted" style="font-size: 0.85rem;">Card UID</span>
            <input type="text" class="form-control" id="manual-uid-input" placeholder="e.g. 1000000001" style="font-family: monospace;">
            <button class="btn btn-primary fw-semibold" onclick="triggerManualTap()" style="background-color: var(--pos-primary); border-color: var(--pos-primary);">
              <i class="bi bi-credit-card-2-front me-1"></i> Tap Card
            </button>
          </div>

          <p class="text-muted mb-2" style="font-size: 0.78rem;">Quick Demo Test Cards:</p>
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="simulateCardTap('1000000001')">
              <i class="bi bi-person-check me-1"></i> Faris (RM120.50)
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="simulateCardTap('1000000002')">
              <i class="bi bi-person-check me-1"></i> Aisyah (RM85.50)
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="simulateCardTap('1000000004')">
              <i class="bi bi-person-x me-1 font-danger"></i> Umar (Inactive)
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="simulateCardTap('9999999999')">
              <i class="bi bi-exclamation-triangle me-1"></i> Unregistered UID
            </button>
          </div>
        </div>

        <!-- Recent Sales Log -->
        <div class="pos-card">
          <h6 class="fw-bold mb-3 text-dark" style="font-size: 0.85rem;"><i class="bi bi-receipt-cutoff me-1"></i> TODAY'S CANTEEN TRANSACTIONS</h6>
          <div class="table-responsive" style="max-height: 220px;">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
              <thead class="table-light">
                <tr>
                  <th>Time</th>
                  <th>Student</th>
                  <th>Item</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody id="pos-sales-log-body">
                <tr>
                  <td class="text-muted">07:45 AM</td>
                  <td class="fw-semibold">Muhammad Faris</td>
                  <td>Nasi Lemak</td>
                  <td class="text-end text-danger fw-bold">-RM 3.50</td>
                </tr>
                <tr>
                  <td class="text-muted">07:42 AM</td>
                  <td class="fw-semibold">Ahmad Daniel</td>
                  <td>Mee Goreng</td>
                  <td class="text-end text-danger fw-bold">-RM 3.00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Transaction Result Modal -->
  <div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
      <div class="modal-content receipt-modal-content">
        <div class="modal-body p-4 text-center" id="receipt-modal-body">
          <!-- Populated dynamically -->
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/store.js"></script>
  <script src="assets/js/common.js"></script>
  <script>
    let currentCents = 0;
    let currentItemName = 'Canteen Purchase';
    const rfidInput = document.getElementById('rfid-pos-input');
    const receiptModalEl = new bootstrap.Modal(document.getElementById('receiptModal'));

    // Audio Feedback Effects using Web Audio API
    function playBeep(freq = 880, duration = 0.15) {
      try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(0.1, ctx.currentTime);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + duration);
      } catch (e) {}
    }

    function playSuccessSound() {
      playBeep(880, 0.1);
      setTimeout(() => playBeep(1320, 0.2), 100);
    }

    function playErrorSound() {
      playBeep(300, 0.25);
      setTimeout(() => playBeep(200, 0.35), 200);
    }

    // Keep hidden input focused at all times for USB RFID Reader JT308
    setInterval(() => {
      if (document.activeElement !== rfidInput && document.activeElement.tagName !== 'INPUT') {
        rfidInput.focus();
      }
    }, 200);

    // Live clock display
    function updateClock() {
      const now = new Date();
      document.getElementById('pos-clock-display').innerHTML = '<i class="bi bi-clock me-1"></i> ' + now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Keypad Logic
    function updateDisplay() {
      const amountVal = currentCents / 100;
      document.getElementById('pos-amount-val').innerText = 'RM ' + amountVal.toFixed(2);
      document.getElementById('pos-item-title').innerText = currentItemName;
    }

    function pressKey(key) {
      if (key === '00') {
        currentCents = currentCents * 100;
      } else {
        const num = parseInt(key, 10);
        currentCents = (currentCents * 10) + num;
      }
      if (currentCents > 99999) currentCents = 99999;
      updateDisplay();
    }

    function addPresetAmount(amount, name) {
      currentCents = Math.round(amount * 100);
      currentItemName = name;
      updateDisplay();
    }

    function clearAmount() {
      currentCents = 0;
      currentItemName = 'Canteen Purchase';
      updateDisplay();
    }

    function simulateCardTap(uid) {
      rfidInput.value = uid;
      handlePosTapSubmit(new Event('submit'));
    }

    function triggerManualTap() {
      const manualUid = document.getElementById('manual-uid-input').value.trim();
      if (!manualUid) {
        alert('Please enter a Card Serial UID to test.');
        return;
      }
      simulateCardTap(manualUid);
    }

    // Handle POS Tap Deduction Request
    function handlePosTapSubmit(e) {
      e.preventDefault();
      const cardUid = rfidInput.value.trim();
      rfidInput.value = '';
      if (!cardUid) return;

      const purchaseAmount = currentCents / 100;

      if (purchaseAmount <= 0) {
        playErrorSound();
        showReceiptModal(`
          <div class="py-3">
            <i class="bi bi-exclamation-circle-fill text-warning" style="font-size: 3.5rem;"></i>
            <h4 class="fw-bold mt-2 text-dark">No Amount Specified</h4>
            <p class="text-muted style="font-size: 0.88rem;">Please enter a purchase amount on the keypad before tapping the card.</p>
            <button class="btn btn-secondary w-100 mt-2" onclick="receiptModalEl.hide()" style="border-radius: 10px;">OK</button>
          </div>
        `);
        return;
      }

      // Show processing loader
      showReceiptModal(`
        <div class="py-4">
          <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
          <h5 class="fw-bold">Processing Transaction...</h5>
          <p class="text-muted" style="font-size: 0.85rem;">Card UID: <code class="fw-bold">${cardUid}</code></p>
        </div>
      `);

      // Post to backend api.php action=pos-deduct
      fetch('api.php?action=pos-deduct', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          card_uid: cardUid,
          amount: purchaseAmount,
          item_name: currentItemName
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          playSuccessSound();
          
          const remBal = parseFloat(data.remaining_balance).toFixed(2);
          const dedAmt = parseFloat(data.deducted).toFixed(2);

          showReceiptModal(`
            <div class="py-2">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
              <h3 class="fw-bold mt-2 mb-1" style="color: #10B981;">Payment Successful!</h3>
              <p class="text-muted mb-3" style="font-size: 0.85rem;">RM ${dedAmt} charged for ${currentItemName}</p>

              <div class="bg-light p-3 rounded-4 mb-3 text-start">
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                  <span class="text-muted">Student Name</span>
                  <strong class="text-dark">${data.student_name}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                  <span class="text-muted">Class & ID</span>
                  <span class="text-dark fw-semibold">${data.class} (${data.student_id})</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                  <span class="text-muted">Card UID</span>
                  <span class="font-monospace text-dark">${data.card_serial}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-secondary">Remaining Balance</span>
                  <span class="badge bg-success fs-6">RM ${remBal}</span>
                </div>
              </div>

              <button class="btn btn-success w-100 fw-bold py-2.5" onclick="resetPosTerminal()" style="border-radius: 12px; background-color: #10B981; border: none;">
                Next Customer (Auto-resetting in 4s)
              </button>
            </div>
          `);

          // Append to log table
          addSalesLogRow(data.student_name, currentItemName, dedAmt);

          // Clear keypad total
          clearAmount();

          // Auto hide modal after 4s
          setTimeout(() => {
            receiptModalEl.hide();
          }, 4000);

        } else {
          playErrorSound();
          showReceiptModal(`
            <div class="py-2">
              <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
              <h3 class="fw-bold mt-2 mb-1 text-danger">Transaction Declined</h3>
              <p class="text-muted mb-3" style="font-size: 0.88rem;">${data.error || 'Payment processing failed.'}</p>

              <div class="alert alert-danger text-start p-3 rounded-3" style="font-size: 0.82rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Ensure the student card has sufficient funds and is registered in the Parent Portal.
              </div>

              <button class="btn btn-secondary w-100 fw-semibold py-2" onclick="receiptModalEl.hide()" style="border-radius: 10px;">
                Try Again
              </button>
            </div>
          `);
        }
      })
      .catch(err => {
        console.error(err);
        playErrorSound();
        showReceiptModal(`
          <div class="py-3">
            <i class="bi bi-wifi-off text-danger" style="font-size: 3.5rem;"></i>
            <h4 class="fw-bold mt-2 text-danger">Connection Error</h4>
            <p class="text-muted" style="font-size: 0.85rem;">Could not connect to database backend.</p>
            <button class="btn btn-secondary w-100" onclick="receiptModalEl.hide()">Close</button>
          </div>
        `);
      });
    }

    function showReceiptModal(htmlContent) {
      document.getElementById('receipt-modal-body').innerHTML = htmlContent;
      receiptModalEl.show();
    }

    function resetPosTerminal() {
      receiptModalEl.hide();
      rfidInput.focus();
    }

    function addSalesLogRow(student, item, amount) {
      const tbody = document.getElementById('pos-sales-log-body');
      const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="text-muted">${timeStr}</td>
        <td class="fw-semibold">${student}</td>
        <td>${item}</td>
        <td class="text-end text-danger fw-bold">-RM ${amount}</td>
      `;
      tbody.insertBefore(tr, tbody.firstChild);
    }
  </script>
</body>
</html>
