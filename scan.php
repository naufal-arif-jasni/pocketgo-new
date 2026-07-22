<?php
require_once 'db_conn.php';

// Fetch all parents to allow linking from terminal
$parents = [];
try {
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'parent' ORDER BY name ASC");
    $parents = $stmt->fetchAll();
} catch (\Exception $e) {
    // Ignore error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>RFID Terminal – PocketGo</title>
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Stylesheets -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <style>
    :root {
      --primary-color: #4361ee;
      --primary-hover: #3a56d4;
      --success-color: #2ec4b6;
      --warning-color: #ff9f1c;
      --danger-color: #e63946;
      --dark-color: #1e1e2f;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --border-color: #eef1f6;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-color);
      color: var(--dark-color);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      margin: 0;
      padding: 0;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
    }

    /* Navbar styling matching index.php */
    .scan-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 32px;
      background-color: var(--card-bg);
      border-bottom: 1px solid var(--border-color);
      box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    .logo {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--primary-color);
      text-decoration: none;
    }

    .logo span {
      color: var(--warning-color);
    }

    /* Layout wrapper */
    .terminal-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    /* Terminal card styling */
    .terminal-card {
      background-color: var(--card-bg);
      border-radius: 16px;
      border: 1px solid var(--border-color);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
      width: 100%;
      max-width: 580px;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Header inside terminal */
    .terminal-header {
      padding: 24px 32px 16px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .terminal-title {
      font-size: 1.1rem;
      margin: 0;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Terminal state styling */
    .terminal-body {
      padding: 36px 32px;
      text-align: center;
    }

    /* Status indicator */
    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 14px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 24px;
      transition: all 0.3s ease;
    }

    .status-ready {
      background-color: rgba(46, 196, 182, 0.1);
      color: var(--success-color);
    }

    .status-loading {
      background-color: rgba(67, 97, 238, 0.1);
      color: var(--primary-color);
    }

    .status-matched {
      background-color: rgba(46, 196, 182, 0.15);
      color: #1b9e91;
    }

    .status-unregistered {
      background-color: rgba(255, 159, 28, 0.15);
      color: #d47a00;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      margin-right: 8px;
      display: inline-block;
    }

    .status-ready .status-dot {
      background-color: var(--success-color);
      animation: pulse-green 1.5s infinite;
    }

    .status-loading .status-dot {
      background-color: var(--primary-color);
    }

    @keyframes pulse-green {
      0% { transform: scale(0.9); opacity: 0.6; }
      50% { transform: scale(1.2); opacity: 1; box-shadow: 0 0 10px rgba(46, 196, 182, 0.5); }
      100% { transform: scale(0.9); opacity: 0.6; }
    }

    /* RFID Tap Visual Indicator */
    .rfid-target {
      position: relative;
      width: 140px;
      height: 140px;
      background: radial-gradient(circle, #f8f9ff 0%, #edf1ff 100%);
      border: 2px dashed #cfd7f5;
      border-radius: 50%;
      margin: 0 auto 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .rfid-target:hover {
      border-color: var(--primary-color);
      transform: scale(1.02);
    }

    .rfid-icon {
      font-size: 3rem;
      color: var(--primary-color);
      z-index: 2;
    }

    /* Ripples effect */
    .ripple {
      position: absolute;
      width: 100%;
      height: 100%;
      border: 1px solid rgba(67, 97, 238, 0.3);
      border-radius: 50%;
      animation: ripple-pulse 2s infinite linear;
      z-index: 1;
    }

    .ripple-2 {
      animation-delay: 1s;
    }

    @keyframes ripple-pulse {
      0% { transform: scale(1); opacity: 0.8; }
      100% { transform: scale(1.5); opacity: 0; }
    }

    /* Hidden inputs to capture USB RFID scanning stream */
    .hidden-input {
      position: absolute;
      left: -9999px;
      opacity: 0;
      width: 1px;
      height: 1px;
    }

    /* Focus lock overlay banner */
    .focus-lock-banner {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f1f3f9;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 16px;
    }

    .focus-lock-banner i {
      color: var(--success-color);
      margin-right: 6px;
    }

    /* Card Details display style */
    .card-details-panel {
      display: none;
      text-align: left;
      animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slide-up {
      0% { transform: translateY(15px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }

    .student-badge-initials {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      margin: 0 auto 16px;
    }

    .balance-huge {
      font-size: 2.4rem;
      font-weight: 800;
      color: var(--dark-color);
      text-align: center;
      margin: 8px 0 24px;
      letter-spacing: -1px;
    }

    .balance-huge span {
      font-size: 1.3rem;
      font-weight: 600;
      color: #a0aec0;
      margin-right: 4px;
    }

    .details-grid {
      background-color: #f8fafd;
      border-radius: 12px;
      padding: 16px 20px;
      border: 1px solid var(--border-color);
      margin-bottom: 24px;
    }

    .details-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(0,0,0,0.03);
    }

    .details-row:last-child {
      border-bottom: none;
    }

    .details-label {
      color: #718096;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .details-value {
      font-weight: 600;
      color: var(--dark-color);
      font-size: 0.9rem;
      text-align: right;
    }

    /* Progress bar for auto-reset */
    .countdown-bar {
      height: 4px;
      width: 0%;
      background-color: var(--primary-color);
      border-radius: 2px;
      transition: width 0.1s linear;
    }

    .reset-alert {
      text-align: center;
      font-size: 0.8rem;
      color: #a0aec0;
      margin-top: 12px;
    }

    /* Help Text footer */
    .terminal-footer {
      background-color: #fafbfd;
      padding: 20px 32px;
      border-top: 1px solid var(--border-color);
      font-size: 0.85rem;
      color: #718096;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .terminal-footer a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }

    .terminal-footer a:hover {
      text-decoration: underline;
    }

    /* Manual simulation input triggers for users testing in preview iframe */
    .sim-panel {
      margin-top: 24px;
      background-color: var(--card-bg);
      border-radius: 12px;
      padding: 16px 24px;
      border: 1px solid var(--border-color);
      max-width: 580px;
      width: 100%;
    }

    .sim-title {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 700;
      color: #a0aec0;
      margin-bottom: 12px;
    }

    .sim-badge-btn {
      border: 1px solid var(--border-color);
      background-color: #f8fafd;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      cursor: pointer;
      margin: 4px;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
    }

    .sim-badge-btn:hover {
      background-color: #edf2f7;
      border-color: #cbd5e0;
    }
  </style>
</head>
<body>

  <!-- Cohesive navigation header -->
  <nav class="scan-nav">
    <a href="index.php" class="logo">Pocket<span>Go</span></a>
    <div>
      <a href="admin-dashboard.php" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-speedometer2"></i> Admin</a>
      <a href="dashboard.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-wallet2"></i> Parent</a>
    </div>
  </nav>

  <!-- Main Terminal UI -->
  <div class="terminal-container flex-column">
    <div class="terminal-card" id="main-terminal">
      
      <div class="terminal-header">
        <h3 class="terminal-title">RFID Terminal</h3>
        <span class="text-muted"><i class="bi bi-cpu"></i> Online</span>
      </div>

      <div class="terminal-body">
        
        <!-- RFID HID Scanner Form (Hidden but auto-focused) -->
        <form id="rfid-form" onsubmit="handleFormSubmit(event)">
          <input type="text" 
                 name="card_uid" 
                 class="hidden-input" 
                 id="rfid-input" 
                 autocomplete="off" 
                 autofocus 
                 required>
        </form>

        <!-- Scanning Mode UI Panel -->
        <div id="scanning-panel">
          <div class="status-badge status-ready" id="terminal-badge">
            <span class="status-dot"></span>
            <span id="badge-text">Terminal Ready</span>
          </div>

          <!-- Pulsing Target Device Graphic -->
          <div class="rfid-target" onclick="triggerFocus()">
            <div class="ripple"></div>
            <div class="ripple ripple-2"></div>
            <i class="bi bi-credit-card-2-front rfid-icon"></i>
          </div>

          <h2 style="font-size: 1.4rem; margin-bottom: 8px;">Tap Student Card</h2>
          <p class="text-muted mb-4" style="font-size: 0.95rem; max-width: 320px; margin: 0 auto 20px;">
            Please swipe or tap your school RFID card. The reader will capture your details instantly.
          </p>

          <div class="focus-lock-banner" id="focus-lock">
            <i class="bi bi-shield-lock-fill"></i> Secure Auto-Focus Active
          </div>
        </div>

        <!-- Dynamic Reading / Processing State -->
        <div id="loading-panel" style="display: none; padding: 40px 0;">
          <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 4px; margin-bottom: 24px;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <h2 style="font-size: 1.4rem; margin-bottom: 8px;">Processing Card</h2>
          <p class="text-muted">Reading contactless chip. Please hold still...</p>
        </div>

        <!-- Success/Failure Result Panel -->
        <div class="card-details-panel" id="result-panel">
          <div id="result-badge-container">
            <!-- Student initials or Status Icon -->
            <div class="student-badge-initials" id="student-initials">MF</div>
          </div>
          
          <h2 id="student-name" class="text-center mb-1">Muhammad Faris</h2>
          <p id="student-class" class="text-muted text-center mb-4">4 Amanah · Student ID: PG-40124</p>

          <!-- Standard Details Display Block (Hidden for unregistered cards) -->
          <div id="registered-card-block">
            <div class="details-grid">
              <div class="details-row">
                <span class="details-label">Card Serial UID</span>
                <span class="details-value text-monospace" id="result-uid" style="font-weight: 700; color: #4a5568;">1000000001</span>
              </div>
              <div class="details-row">
                <span class="details-label">Card Status</span>
                <span class="details-value" id="result-status"><span class="badge bg-success">Active</span></span>
              </div>
              <div class="details-row">
                <span class="details-label">Daily Limit</span>
                <span class="details-value" id="result-limit">RM 50.00</span>
              </div>
              <div class="details-row">
                <span class="details-label">Parent Account</span>
                <span class="details-value" id="result-parent">Ahmad Bin Abdullah</span>
              </div>
            </div>

            <div class="text-center">
              <p class="mb-2" style="font-weight: 500; font-size: 0.9rem; color: #718096;">Remaining Wallet Balance</p>
              <div class="balance-huge" id="result-balance"><span>RM</span>120.50</div>
            </div>
          </div>

          <!-- Unregistered Link Form Block (Shown ONLY for unregistered cards) -->
          <div id="unregistered-card-block" style="display: none; text-align: left;">
            <div style="background-color: #fff9f0; border: 1px solid #ffe8cc; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;">
              <h5 style="color: #d97706; font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-exclamation-triangle-fill"></i> Unregistered NFC Card
              </h5>
              <p style="font-size: 0.8rem; color: #666; margin: 0; line-height: 1.4;">
                This physical RFID card (UID: <strong id="link-form-uid-display" style="font-family:monospace; color:#333;"></strong>) is currently a dummy card. Link it to a student below.
              </p>
            </div>

            <form id="link-student-form" onsubmit="handleLinkStudent(event)">
              <input type="hidden" id="link-card-serial">
              
              <div class="mb-3">
                <label class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">Parent Account / Owner</label>
                <select class="form-select" id="link-parent-email" required style="font-size: 0.88rem; padding: 10px 12px; border-radius: 8px;">
                  <option value="" disabled selected>-- Select Parent --</option>
                  <?php foreach ($parents as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['email']); ?>">
                      <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['email']); ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">Student Full Name</label>
                <input type="text" class="form-control" id="link-student-name" placeholder="e.g. Muhammad Faris" required style="font-size: 0.88rem; padding: 10px 12px; border-radius: 8px;">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">Student NRIC (for ID)</label>
                <input type="text" class="form-control" id="link-student-nric" placeholder="e.g. 120512-10-1234" required style="font-size: 0.88rem; padding: 10px 12px; border-radius: 8px;">
              </div>

              <div class="mb-3">
                <label class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #4a5568;">Class</label>
                <input type="text" class="form-control" id="link-student-class" placeholder="e.g. 4 Amanah" required style="font-size: 0.88rem; padding: 10px 12px; border-radius: 8px;">
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <button type="button" class="btn btn-outline-secondary w-100" onclick="resetTerminal()" style="padding: 11px; border-radius: 8px; font-weight: 600; font-size: 0.88rem;">Cancel</button>
                </div>
                <div class="col-6">
                  <button type="submit" class="btn btn-primary w-100" style="padding: 11px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; background-color: var(--primary-color); border-color: var(--primary-color);">Link & Save</button>
                </div>
              </div>
            </form>
          </div>

          <!-- Auto Reset Progress Bar Block -->
          <div id="countdown-bar-block">
            <div class="progress" style="height: 4px; border-radius: 2px; background-color: #edf2f7; overflow: hidden;">
              <div class="countdown-bar" id="reset-progress"></div>
            </div>
            <p class="reset-alert"><i class="bi bi-arrow-repeat"></i> Auto-resetting terminal in <span id="reset-countdown">5</span>s...</p>
          </div>
        </div>

      </div>

      <div class="terminal-footer">
        <span>Need assistance?</span>
        <a href="admin-settings.php"><i class="bi bi-gear-fill"></i> System Config</a>
      </div>
    </div>

    <!-- Live Testing Simulator Panel (Great for preview testing without physical reader) -->
    <div class="sim-panel">
      <div class="sim-title"><i class="bi bi-broadcast"></i> Previews & Test Simulation Swipes</div>
      <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 12px;">
        Since you are running inside a virtual browser container, tap on any simulated student profile below to emulate physical RFID card taps from the JT308 reader:
      </p>
      <div class="d-flex flex-wrap">
        <button class="sim-badge-btn" onclick="simulateTap('1000000001')">
          <span style="color:#2ec4b6; margin-right:6px;">●</span> Muhammad Faris (1000000001)
        </button>
        <button class="sim-badge-btn" onclick="simulateTap('1000000002')">
          <span style="color:#2ec4b6; margin-right:6px;">●</span> Nur Aisyah (1000000002)
        </button>
        <button class="sim-badge-btn" onclick="simulateTap('1000000003')">
          <span style="color:#2ec4b6; margin-right:6px;">●</span> Ahmad Daniel (1000000003)
        </button>
        <button class="sim-badge-btn" onclick="simulateTap('9999999999')">
          <span style="color:#e63946; margin-right:6px;">●</span> Unregistered Card (9999999999)
        </button>
      </div>
    </div>
  </div>

  <script>
    // Elements Setup
    const rfidInput = document.getElementById('rfid-input');
    const scanningPanel = document.getElementById('scanning-panel');
    const loadingPanel = document.getElementById('loading-panel');
    const resultPanel = document.getElementById('result-panel');
    const resetProgressBar = document.getElementById('reset-progress');
    const resetCountdownText = document.getElementById('reset-countdown');

    // Synthesis Chime audio parameters
    let audioCtx = null;

    function initAudio() {
      if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      }
    }

    function playSuccessSound() {
      try {
        initAudio();
        if (!audioCtx) return;
        
        // Positive Beep Beep
        let osc1 = audioCtx.createOscillator();
        let osc2 = audioCtx.createOscillator();
        let gainNode = audioCtx.createGain();

        osc1.connect(gainNode);
        osc2.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);

        // Arpeggio
        osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
        osc1.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.08); // E5
        osc1.frequency.setValueAtTime(783.99, audioCtx.currentTime + 0.16); // G5
        osc1.frequency.setValueAtTime(1046.50, audioCtx.currentTime + 0.24); // C6

        osc1.type = 'sine';
        osc1.start(audioCtx.currentTime);
        osc1.stop(audioCtx.currentTime + 0.4);
      } catch (e) {
        console.warn('Audio synthesis bypassed or muted by browser policy.', e);
      }
    }

    function playErrorSound() {
      try {
        initAudio();
        if (!audioCtx) return;
        
        // low buzz sound
        let osc = audioCtx.createOscillator();
        let gainNode = audioCtx.createGain();

        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.2, audioCtx.currentTime + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);

        osc.frequency.setValueAtTime(120, audioCtx.currentTime); // Low bass buzz
        osc.frequency.setValueAtTime(100, audioCtx.currentTime + 0.15); // descend

        osc.type = 'sawtooth';
        osc.start(audioCtx.currentTime);
        osc.stop(audioCtx.currentTime + 0.42);
      } catch (e) {
        console.warn('Audio synthesis bypassed or muted by browser policy.', e);
      }
    }

    // Force focus on RFID capture input field at all times
    function triggerFocus() {
      rfidInput.focus();
      const banner = document.getElementById('focus-lock');
      if (banner) {
        banner.style.backgroundColor = '#e6fffa';
        banner.style.color = '#1b9e91';
        setTimeout(() => {
          banner.style.backgroundColor = '#f1f3f9';
          banner.style.color = '#6c757d';
        }, 1000);
      }
    }

    // Capture click outside to keep refocusing
    document.addEventListener('click', (e) => {
      // Do not interrupt focus if user is working on simulated swipe buttons or header
      if (e.target.closest('.sim-badge-btn') || e.target.closest('a') || e.target.closest('button')) {
        return;
      }
      triggerFocus();
    });

    // Persistent timer focus reinforcement
    setInterval(() => {
      if (document.activeElement !== rfidInput && scanningPanel.style.display !== 'none') {
        rfidInput.focus();
      }
    }, 150);

    // Initial load focus hook
    window.addEventListener('DOMContentLoaded', () => {
      triggerFocus();
    });

    // Simulate clicking or swiping RFID on real reader
    function simulateTap(uid) {
      rfidInput.value = uid;
      handleFormSubmit(new Event('submit'));
    }

    // Form submit pipeline (triggered automatically when barcode scanner emits newline/Enter key)
    function handleFormSubmit(event) {
      event.preventDefault();
      
      const rawUid = rfidInput.value.trim();
      if (!rawUid) return;

      // Reset form field for future reads
      rfidInput.value = '';

      // Transition terminal view to processing/loading state
      scanningPanel.style.display = 'none';
      resultPanel.style.display = 'none';
      loadingPanel.style.display = 'block';

      // Perform non-blocking asynchronous AJAC/fetch request
      fetch('process.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ card_uid: rawUid })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response failure');
        }
        return response.json();
      })
      .then(data => {
        displayResult(data);
      })
      .catch(error => {
        console.error('AJAX error:', error);
        displayResult({
          success: false,
          uid: rawUid,
          message: 'Offline mode: Card scanned but database processing failed.',
          card_details: {
            student_name: 'Database Offline',
            student_id: 'PG-OFFLINE',
            class: 'N/A',
            balance: '0.00',
            daily_limit: '0.00',
            status: 'Offline',
            parent_name: 'System local container'
          }
        });
      });
    }

    // Handle results render back to view
    let resetTimer = null;
    let progressInterval = null;
    let initialLinkFormHtml = '';

    window.addEventListener('DOMContentLoaded', () => {
      // Keep track of the clean initial form structure
      const formBlock = document.getElementById('unregistered-card-block');
      if (formBlock) {
        initialLinkFormHtml = formBlock.innerHTML;
      }
      triggerFocus();
    });

    function restoreLinkFormStructure() {
      const formBlock = document.getElementById('unregistered-card-block');
      if (formBlock && initialLinkFormHtml) {
        formBlock.innerHTML = initialLinkFormHtml;
      }
    }

    function displayResult(data) {
      loadingPanel.style.display = 'none';
      scanningPanel.style.display = 'none';
      
      const initialsEl = document.getElementById('student-initials');
      const nameEl = document.getElementById('student-name');
      const classEl = document.getElementById('student-class');
      const uidEl = document.getElementById('result-uid');
      const statusEl = document.getElementById('result-status');
      const limitEl = document.getElementById('result-limit');
      const parentEl = document.getElementById('result-parent');
      const balanceEl = document.getElementById('result-balance');

      // Clear any running timers
      if (resetTimer) clearTimeout(resetTimer);
      if (progressInterval) clearInterval(progressInterval);
      
      const details = data.card_details || {};
      
      // Handle card state formatting
      if (data.matched) {
        playSuccessSound();
        
        // Show normal details panel
        document.getElementById('registered-card-block').style.display = 'block';
        document.getElementById('unregistered-card-block').style.display = 'none';
        document.getElementById('countdown-bar-block').style.display = 'block';

        nameEl.innerText = details.student_name || 'Unknown student';
        classEl.innerText = `${details.class || 'N/A'} · Student ID: ${details.student_id || 'N/A'}`;
        uidEl.innerText = data.uid || 'Unknown';
        limitEl.innerText = `RM ${parseFloat(details.daily_limit || 0).toFixed(2)}`;
        parentEl.innerText = details.parent_name || 'N/A';
        balanceEl.innerHTML = `<span>RM</span>${parseFloat(details.balance || 0).toFixed(2)}`;

        initialsEl.style.backgroundColor = 'var(--success-color)';
        initialsEl.innerHTML = '';
        const names = (details.student_name || 'US').split(' ');
        const initials = names.length > 1 ? (names[0][0] + names[1][0]).toUpperCase() : names[0].substring(0,2).toUpperCase();
        initialsEl.innerText = initials;
        
        const status = (details.status || 'Active').toLowerCase();
        if (status === 'active') {
          statusEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Active</span>`;
        } else {
          statusEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">${details.status || 'Inactive'}</span>`;
        }

        // Display results card container
        resultPanel.style.display = 'block';

        // Start countdown auto-reset
        startResetCountdown(5);
      } else {
        playErrorSound();
        
        // Show registration / linking form inside terminal card
        document.getElementById('registered-card-block').style.display = 'none';
        document.getElementById('unregistered-card-block').style.display = 'block';
        document.getElementById('countdown-bar-block').style.display = 'none';

        nameEl.innerText = 'Unregistered Card';
        classEl.innerText = 'Tap a parent profile below to link this chip';

        initialsEl.style.backgroundColor = 'var(--warning-color)';
        initialsEl.innerHTML = '<i class="bi bi-exclamation-triangle" style="font-size:1.8rem;color:white;display:flex;align-items:center;justify-content:center;height:100%;"></i>';

        // Populate card serial fields in form
        const serialInput = document.getElementById('link-card-serial');
        if (serialInput) serialInput.value = data.uid || '';
        
        const uidDisplay = document.getElementById('link-form-uid-display');
        if (uidDisplay) uidDisplay.innerText = data.uid || '';

        // Display results card container
        resultPanel.style.display = 'block';
        
        // Do NOT start auto-reset countdown! Let them register in peace.
      }
    }

    // Handle AJAX submission from Terminal Link Student form
    function handleLinkStudent(event) {
      event.preventDefault();
      
      const serial = document.getElementById('link-card-serial').value;
      const email = document.getElementById('link-parent-email').value;
      const name = document.getElementById('link-student-name').value.trim();
      const nric = document.getElementById('link-student-nric').value.trim();
      const cls = document.getElementById('link-student-class').value.trim();

      if (!email || !name || !nric || !cls) {
        alert('Please fill in all student details.');
        return;
      }

      const submitBtn = event.target.querySelector('button[type="submit"]');
      const origBtnText = submitBtn.innerText;
      submitBtn.disabled = true;
      submitBtn.innerText = 'Linking Card...';

      fetch('api.php?action=register-card', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email: email,
          card_serial: serial,
          student_name: name,
          student_nric: nric,
          class: cls
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          playSuccessSound();
          
          // Show delightful success state in the card form
          const block = document.getElementById('unregistered-card-block');
          block.innerHTML = `
            <div class="text-center py-4">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem; display: block; margin-bottom: 12px;"></i>
              <h4 style="font-weight: 700; color: #1e1e2f; margin-bottom: 6px;">Card Linked Successfully!</h4>
              <p class="text-muted" style="font-size: 0.85rem; max-width: 280px; margin: 0 auto 20px;">
                Physical Card <strong>${serial}</strong> has been registered and linked to <strong>${name}</strong>.
              </p>
              <div class="spinner-border spinner-border-sm text-primary" role="status" style="margin-right:6px;"></div>
              <span style="font-size:0.82rem;color:#718096;">Returning to scanner...</span>
            </div>
          `;
          
          // Reset terminal after 5 seconds
          setTimeout(() => {
            restoreLinkFormStructure();
            resetTerminal();
          }, 5000);
        } else {
          playErrorSound();
          alert(data.error || 'Failed to link card.');
          submitBtn.disabled = false;
          submitBtn.innerText = origBtnText;
        }
      })
      .catch(err => {
        console.error(err);
        playErrorSound();
        alert('Network error registering card.');
        submitBtn.disabled = false;
        submitBtn.innerText = origBtnText;
      });
    }

    // Visual progress countdown reset
    function startResetCountdown(seconds) {
      if (resetTimer) clearTimeout(resetTimer);
      if (progressInterval) clearInterval(progressInterval);

      const durationMs = seconds * 1000;
      let elapsedMs = 0;
      const stepMs = 50;

      resetCountdownText.innerText = seconds;

      // Progress bar fill rate
      progressInterval = setInterval(() => {
        elapsedMs += stepMs;
        const progressPercent = Math.min((elapsedMs / durationMs) * 100, 100);
        resetProgressBar.style.width = `${progressPercent}%`;

        const remainingSec = Math.ceil((durationMs - elapsedMs) / 1000);
        resetCountdownText.innerText = Math.max(remainingSec, 0);
      }, stepMs);

      // Trigger actual screen reset
      resetTimer = setTimeout(() => {
        clearInterval(progressInterval);
        resetTerminal();
      }, durationMs);
    }

    // Reset screen terminal back to scanning status
    function resetTerminal() {
      if (resetTimer) clearTimeout(resetTimer);
      if (progressInterval) clearInterval(progressInterval);

      resultPanel.style.display = 'none';
      loadingPanel.style.display = 'none';
      scanningPanel.style.display = 'block';
      
      resetProgressBar.style.width = '0%';
      rfidInput.value = '';
      restoreLinkFormStructure();
      triggerFocus();
    }
  </script>
</body>
</html>
