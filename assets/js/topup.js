// topup.js - Topup flow with payment gateway tab selectors and full-screen mock checkout simulator
let amt = 50; // Default amount
let pm = 'fpx'; // Default to FPX
let currentGw = 'banking'; // 'banking', 'ewallet', 'card'
let selectedBank = 'Maybank2u'; // Default to first bank
let selectedEwallet = 'Touch n Go'; // Default to Touch n Go
let currentPin = '';
let simRef = '';
let topupFocusInterval = null;
let topupAudioCtx = null;

function initTopupAudio() {
  if (!topupAudioCtx) {
    topupAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
  }
}

function playTopupSuccessSound() {
  try {
    initTopupAudio();
    if (!topupAudioCtx) return;
    let osc1 = topupAudioCtx.createOscillator();
    let gainNode = topupAudioCtx.createGain();
    osc1.connect(gainNode);
    gainNode.connect(topupAudioCtx.destination);
    gainNode.gain.setValueAtTime(0, topupAudioCtx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.15, topupAudioCtx.currentTime + 0.02);
    gainNode.gain.exponentialRampToValueAtTime(0.001, topupAudioCtx.currentTime + 0.35);
    osc1.frequency.setValueAtTime(523.25, topupAudioCtx.currentTime); // C5
    osc1.frequency.setValueAtTime(659.25, topupAudioCtx.currentTime + 0.08); // E5
    osc1.frequency.setValueAtTime(783.99, topupAudioCtx.currentTime + 0.16); // G5
    osc1.frequency.setValueAtTime(1046.50, topupAudioCtx.currentTime + 0.24); // C6
    osc1.type = 'sine';
    osc1.start(topupAudioCtx.currentTime);
    osc1.stop(topupAudioCtx.currentTime + 0.4);
  } catch (e) {
    console.warn('Audio synthesis bypassed.', e);
  }
}

function playTopupErrorSound() {
  try {
    initTopupAudio();
    if (!topupAudioCtx) return;
    let osc = topupAudioCtx.createOscillator();
    let gainNode = topupAudioCtx.createGain();
    osc.connect(gainNode);
    gainNode.connect(topupAudioCtx.destination);
    gainNode.gain.setValueAtTime(0, topupAudioCtx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.2, topupAudioCtx.currentTime + 0.02);
    gainNode.gain.exponentialRampToValueAtTime(0.001, topupAudioCtx.currentTime + 0.4);
    osc.frequency.setValueAtTime(120, topupAudioCtx.currentTime);
    osc.frequency.setValueAtTime(100, topupAudioCtx.currentTime + 0.15);
    osc.type = 'sawtooth';
    osc.start(topupAudioCtx.currentTime);
    osc.stop(topupAudioCtx.currentTime + 0.42);
  } catch (e) {
    console.warn('Audio synthesis bypassed.', e);
  }
}

function focusTopupRfid() {
  const input = document.getElementById('topup-rfid-input');
  if (input) input.focus();
}

function normalizeSerial(s) {
  if (!s) return '';
  let str = String(s).trim().replace(/[^0-9a-zA-Z]/g, '');
  if (/^\d+$/.test(str) && str.length < 10) {
    str = str.padStart(10, '0');
  }
  return str;
}

Store.init().then(() => {
  requireParentAuth();
  renderTopupPage();
  const btn50 = Array.from(document.querySelectorAll('.amount-btn')).find(b => b.textContent.trim() === '50');
  selectAmount(50, btn50);
  // Pre-select ewallet
  const tngBtn = document.getElementById('ew-tng-inline');
  if (tngBtn) tngBtn.classList.add('active');
});

function renderTopupPage() {
  const u = Store.user;
  const cards = u.cards || [];

  if (cards.length === 0) {
    document.getElementById('tu-no-card-block').style.display = 'block';
    document.getElementById('tu-active-container').style.display = 'none';
    return;
  }

  document.getElementById('tu-no-card-block').style.display = 'none';
  document.getElementById('tu-active-container').style.display = 'block';

  // Load select options
  const selector = document.getElementById('tu-card-selector');
  selector.innerHTML = cards.map(c => `
    <option value="${c.card_serial}">
      ${c.student_name} (${c.class}) — Balance: RM ${parseFloat(c.balance).toFixed(2)}
    </option>
  `).join('');

  // Render card tab content dynamically
  const cardContainer = document.getElementById('dynamic-card-container');
  if (cardContainer) {
    if (u.visa_card) {
      cardContainer.innerHTML = `
        <div class="card-mini-row active" id="saved-card-row" onclick="selectSavedCardInline()">
          <span class="card-mini-icon">💳</span>
          <div class="ew-text-container" style="flex:1;">
            <span class="card-mini-num">Visa ending in ${u.visa_card.card_number.slice(-4)}</span>
            <span class="card-mini-sub">1-Click quick checkout with 6-digit PIN</span>
          </div>
          <div style="color:#C8102E; font-size:1.1rem; font-weight:bold;">✓</div>
        </div>
      `;
    } else {
      cardContainer.innerHTML = `
        <div style="text-align:center; padding: 20px 10px; color:#666; font-size:0.8rem;">
          No linked credit or debit cards found.
        </div>
        <div class="card-btn-link" onclick="openVisaLinkModal()">
          <i class="bi bi-plus-circle me-1"></i> Link Visa / MasterCard
        </div>
      `;
    }
  }
}

function selectAmount(val, btn) {
  amt = val;
  const customInput = document.getElementById('tu-custom-amount');
  if (customInput) customInput.value = '';
  document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
}

function selectCustomAmount(val) {
  const parsed = parseFloat(val) || 0;
  amt = parsed;
  document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
}

// Payment Tab Change Logic (Matching image and specifications)
function changePaymentTab(tab) {
  currentGw = tab;
  
  // Update UI tabs
  document.querySelectorAll('.pg-tab-pill').forEach(btn => btn.classList.remove('active'));
  const currentTabBtn = document.getElementById('tab-' + tab);
  if (currentTabBtn) currentTabBtn.classList.add('active');

  // Hide all sections
  document.querySelectorAll('.pg-content-sec').forEach(sec => sec.classList.add('hidden'));

  const banner = document.getElementById('pg-banner-el');
  const bannerIcon = banner.querySelector('.banner-icon');
  const bannerText = banner.querySelector('.banner-text');

  if (tab === 'banking') {
    pm = 'fpx';
    document.getElementById('sec-banking').classList.remove('hidden');
    bannerIcon.textContent = '🔓';
    bannerText.textContent = 'FPX — You\'ll be securely redirected to your bank\'s portal to authorise payment.';
  } else if (tab === 'ewallet') {
    pm = 'fpx';
    document.getElementById('sec-ewallet').classList.remove('hidden');
    bannerIcon.textContent = '📱';
    bannerText.textContent = 'E-Wallet — Choose your digital e-wallet to proceed with secure checkout.';
  } else if (tab === 'card') {
    if (Store.user.visa_card) {
      pm = 'saved';
    } else {
      pm = 'fpx'; // Fallback so we don't crash
    }
    document.getElementById('sec-card').classList.remove('hidden');
    bannerIcon.textContent = '💳';
    bannerText.textContent = 'Credit/Debit Card — Pay securely using Visa or MasterCard checkout.';
  }
}

function selectBank(bank, btn) {
  selectedBank = bank;
  document.querySelectorAll('.bank-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
}

function selectEwallet(wallet, id) {
  selectedEwallet = wallet;
  document.querySelectorAll('.ew-btn').forEach(b => b.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) target.classList.add('active');
}

function selectSavedCardInline() {
  pm = 'saved';
  const savedRow = document.getElementById('saved-card-row');
  if (savedRow) savedRow.classList.add('active');
}

function openVisaLinkModal() {
  showModal('modal-link-visa');
}

function formatCardNumber(input) {
  let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
  let formatted = '';
  for (let i = 0; i < value.length; i++) {
    if (i > 0 && i % 4 === 0) {
      formatted += ' ';
    }
    formatted += value[i];
  }
  input.value = formatted;
}

function formatExpiry(input) {
  let value = input.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
  if (value.length >= 2) {
    input.value = value.substring(0, 2) + '/' + value.substring(2, 4);
  } else {
    input.value = value;
  }
}

async function saveVisaCard() {
  const name = document.getElementById('visa-holder-name').value.trim();
  const cardNum = document.getElementById('visa-card-number').value.replace(/\s/g, '');
  const expiry = document.getElementById('visa-expiry').value.trim();
  const cvv = document.getElementById('visa-cvv').value.trim();

  if (!name || !cardNum || !expiry || !cvv) {
    toast('Please fill in all credit card details.');
    return;
  }

  if (cardNum.length < 13 || cardNum.length > 19 || isNaN(Number(cardNum))) {
    toast('Invalid credit card number format.');
    return;
  }

  if (!/^\d{2}\/\d{2}$/.test(expiry)) {
    toast('Expiry date must be in MM/YY format.');
    return;
  }

  if (cvv.length !== 3 || isNaN(Number(cvv))) {
    toast('CVV must be exactly 3 digits.');
    return;
  }

  toast('Securing encrypted connection...');
  try {
    const res = await fetch('api.php?action=link-visa', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: Store.user.email,
        cardholder_name: name,
        card_number: cardNum,
        expiry_date: expiry,
        cvv: cvv
      })
    });

    if (res.ok) {
      toast('Card linked and verified successfully!');
      await Store.fetchUserData(Store.user.email);
      Store.save();
      closeModal('modal-link-visa');
      renderTopupPage();
      
      // Default to Card tab
      changePaymentTab('card');
    } else {
      const err = await res.json();
      toast(err.error || 'Failed to link card.');
    }
  } catch (e) {
    console.error(e);
    toast('Error processing request.');
  }
}

function pinBtn(num) {
  if (currentPin.length < 6) {
    currentPin += num;
    updatePinDisplay();
    if (currentPin.length === 6) {
      setTimeout(finaliseTopUp, 400);
    }
  }
}

function pinClear() {
  currentPin = '';
  updatePinDisplay();
}

function updatePinDisplay() {
  const dots = document.querySelectorAll('.pin-dot');
  dots.forEach((dot, idx) => {
    dot.classList.toggle('filled', idx < currentPin.length);
  });
}

async function finaliseTopUp() {
  closeModal('modal-pin');
  if (currentPin === '123456') {
    toast('PIN Authorised successfully!');
    await processTopUp(`Saved Card (${Store.user.visa_card.card_number})`);
  } else {
    toast('Incorrect security PIN!');
  }
}

// ──────────────── NFC TAP & CARD MATCH VERIFICATION TERMINAL ────────────────
function openNfcVerifyModal() {
  const selector = document.getElementById('tu-card-selector');
  if (!selector) return;
  const targetSerial = selector.value;
  const u = Store.user || {};
  const cards = u.cards || [];
  const targetCard = cards.find(c => String(c.card_serial) === String(targetSerial)) || cards[0];

  if (!targetCard) {
    toast('⚠️ Please select a valid student card.');
    return;
  }

  // Populate Target Details
  document.getElementById('tu-target-student-name').textContent = targetCard.student_name || 'Student';
  document.getElementById('tu-target-card-uid').textContent = targetCard.card_serial || targetSerial;
  document.getElementById('tu-target-student-class').textContent = targetCard.class || 'Student';
  document.getElementById('tu-target-amount-badge').textContent = 'RM ' + (parseFloat(amt) || 50).toFixed(2);

  // Reset Badge & Alert State
  const badge = document.getElementById('tu-terminal-badge');
  badge.className = 'status-badge status-ready';
  document.getElementById('tu-badge-text').textContent = 'Terminal Ready — Tap Card';

  const alert = document.getElementById('tu-verify-alert');
  alert.className = 'alert alert-info py-2 px-3 mb-3 text-center';
  alert.innerHTML = '<i class="bi bi-broadcast me-1"></i> Place or swipe physical card on NFC scanner...';

  // Build simulation buttons
  const container = document.getElementById('tu-sim-buttons-container');
  if (container) {
    let btnsHtml = `
      <button type="button" class="sim-tap-btn match" onclick="simulateTopupTap('${targetCard.card_serial}')">
        <span style="color:#16a34a;">●</span> Tap Target Card (${targetCard.student_name} - ${targetCard.card_serial})
      </button>
    `;

    // Add other user cards if available
    cards.filter(c => String(c.card_serial) !== String(targetCard.card_serial)).forEach(other => {
      btnsHtml += `
        <button type="button" class="sim-tap-btn mismatch" onclick="simulateTopupTap('${other.card_serial}')">
          <span style="color:#dc2626;">●</span> Tap Other Card (${other.student_name} - ${other.card_serial})
        </button>
      `;
    });

    // Add unmatched/dummy card option for mismatch testing
    btnsHtml += `
      <button type="button" class="sim-tap-btn mismatch" onclick="simulateTopupTap('9999999999')">
        <span style="color:#dc2626;">●</span> Tap Mismatched Card (9999999999)
      </button>
    `;
    container.innerHTML = btnsHtml;
  }

  // Show Modal using system standard showModal
  showModal('modal-nfc-verify');
  setTimeout(focusTopupRfid, 150);
}

function handleTopupRfidSubmit(event) {
  event.preventDefault();
  const input = document.getElementById('topup-rfid-input');
  if (!input) return;
  const rawVal = input.value.trim();
  input.value = '';
  if (!rawVal) return;
  processNfcTapVerification(rawVal);
}

function simulateTopupTap(uid) {
  const input = document.getElementById('topup-rfid-input');
  if (input) input.value = uid;
  processNfcTapVerification(uid);
}

function processNfcTapVerification(rawUid) {
  const selector = document.getElementById('tu-card-selector');
  if (!selector) return;
  const targetSerial = selector.value;
  const u = Store.user || {};
  const cards = u.cards || [];
  const targetCard = cards.find(c => String(c.card_serial) === String(targetSerial)) || cards[0];

  const cleanTapped = normalizeSerial(rawUid);
  const cleanTarget = normalizeSerial(targetCard ? targetCard.card_serial : targetSerial);

  const badge = document.getElementById('tu-terminal-badge');
  const alert = document.getElementById('tu-verify-alert');

  if (cleanTapped && cleanTapped === cleanTarget) {
    // Condition 1: NFC Tap Detected = TRUE
    // Condition 2: Card Match Verification = TRUE (MATCHED)
    playTopupSuccessSound();

    badge.className = 'status-badge status-matched';
    document.getElementById('tu-badge-text').textContent = '✅ Card Match Verified!';

    alert.className = 'alert alert-success py-2.5 px-3 mb-3 text-center';
    alert.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> <strong>Verification Successful!</strong> NFC Tap matched card UID <code>${cleanTarget}</code> (${targetCard ? targetCard.student_name : 'Student'}). Launching payment gateway...`;

    setTimeout(() => {
      cancelNfcVerification(true); // close silent
      proceedToPaymentGateway();
    }, 1200);

  } else {
    // Condition 1: NFC Tap Detected = TRUE
    // Condition 2: Card Match Verification = FALSE (MISMATCH)
    playTopupErrorSound();

    badge.className = 'status-badge status-unregistered';
    document.getElementById('tu-badge-text').textContent = '❌ Card Mismatch!';

    alert.className = 'alert alert-danger py-2.5 px-3 mb-3 text-center';
    alert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Verification Failed:</strong> Scanned card UID (<code>${rawUid}</code>) does NOT match selected target card UID (<code>${cleanTarget}</code>). Please tap the correct card!`;

    focusTopupRfid();
  }
}

function cancelNfcVerification(silent = false) {
  closeModal('modal-nfc-verify');
  const input = document.getElementById('topup-rfid-input');
  if (input) input.value = '';
  if (!silent) {
    toast('Top up verification cancelled.');
  }
}

// Trigger Top Up Checkout process
function doTopUp() {
  if (!amt || amt <= 0) {
    // Default to 50 if somehow 0
    amt = 50;
  }

  const u = Store.user || {};
  const cards = u.cards || [];
  if (cards.length === 0) {
    toast('⚠️ Please register a student card first.');
    return;
  }

  if ((currentGw === 'banking' || currentGw === 'fpx') && !selectedBank) {
    selectedBank = 'Maybank2u';
  }
  if (currentGw === 'ewallet' && !selectedEwallet) {
    selectedEwallet = 'Touch n Go';
  }
  if (currentGw === 'card' && !u.visa_card) {
    toast('Please link a Visa/MasterCard card to proceed, or choose Online Banking.');
    return;
  }

  // Open NFC Verification Terminal as mandatory requirement!
  openNfcVerifyModal();
}

function proceedToPaymentGateway() {
  const u = Store.user || {};
  if (currentGw === 'banking' || currentGw === 'fpx') {
    showSimulator('banking', selectedBank || 'Maybank2u');
  } else if (currentGw === 'ewallet') {
    showSimulator('ewallet', selectedEwallet || 'Touch n Go');
  } else if (currentGw === 'card') {
    if (u.visa_card) {
      document.getElementById('m-sum-amount').textContent = 'RM ' + amt.toFixed(2);
      document.getElementById('m-sum-method').textContent = `Linked Visa (${u.visa_card.card_number})`;
      currentPin = '';
      updatePinDisplay();
      showModal('modal-pin');
    } else {
      toast('Please link a Visa/MasterCard card to proceed.');
    }
  }
}


// ──────────────── MOCK PAYMENT GATEWAY STATE MACHINE ────────────────
function showSimulator(type, providerName) {
  // Generate random reference
  simRef = 'PG-' + Math.floor(100000 + Math.random() * 900000);
  
  // Configure metadata in DOM
  document.getElementById('sim-meta-ref').textContent = 'Ref: ' + simRef;
  document.getElementById('sim-success-ref').textContent = simRef;
  document.getElementById('sim-form-amount').textContent = 'RM ' + amt.toFixed(2);
  document.getElementById('sim-success-amount').textContent = 'RM ' + amt.toFixed(2);
  document.getElementById('sim-success-bank').textContent = providerName;
  document.getElementById('sim-header-bank-name').textContent = providerName;
  
  // Set Icon
  const headerIcon = document.getElementById('sim-header-bank-icon');
  if (type === 'banking') {
    headerIcon.textContent = '🏦';
    document.getElementById('sim-connecting-text').textContent = 'Connecting to ' + providerName + ' portal...';
  } else {
    headerIcon.textContent = '📱';
    document.getElementById('sim-connecting-text').textContent = 'Connecting to ' + providerName + ' secure application...';
  }

  // Display the Overlay
  const overlay = document.getElementById('modal-dummy-gateway');
  overlay.classList.remove('hidden');

  // Reset to Connecting State (State 1)
  setSimState('connecting');

  // After 1.5 seconds, transition to Form (State 2)
  setTimeout(() => {
    // Enable corresponding fields
    document.getElementById('sim-fields-bank').classList.add('hidden');
    document.getElementById('sim-fields-ewallet').classList.add('hidden');
    document.getElementById('sim-fields-card').classList.add('hidden');

    if (type === 'banking') {
      document.getElementById('sim-fields-bank').classList.remove('hidden');
      document.getElementById('sim-form-title').textContent = providerName + ' Secure Login';
    } else {
      document.getElementById('sim-fields-ewallet').classList.remove('hidden');
      document.getElementById('sim-form-title').textContent = providerName + ' Authorization';
    }

    setSimState('form');
  }, 1500);
}

function setSimState(stateName) {
  document.getElementById('sim-state-connecting').classList.add('hidden');
  document.getElementById('sim-state-form').classList.add('hidden');
  document.getElementById('sim-state-otp').classList.add('hidden');
  document.getElementById('sim-state-success').classList.add('hidden');

  document.getElementById('sim-state-' + stateName).classList.remove('hidden');
}

function submitSimForm() {
  // Simulating validation loader (State 1)
  document.getElementById('sim-connecting-text').textContent = 'Verifying login details...';
  setSimState('connecting');

  setTimeout(() => {
    // Go to OTP (State 3)
    document.getElementById('sim-otp-input').value = '';
    setSimState('otp');
  }, 1200);
}

function submitSimOtp() {
  const otpVal = document.getElementById('sim-otp-input').value.trim();
  if (!otpVal) {
    toast('Please enter the 6-digit TAC/OTP code.');
    return;
  }

  // Loader for processing payment
  document.getElementById('sim-connecting-text').textContent = 'Processing and transferring funds...';
  setSimState('connecting');

  // Execute actual API transaction to update database user's balance
  setTimeout(async () => {
    let sourceStr = '';
    if (currentGw === 'banking' || currentGw === 'fpx') sourceStr = 'FPX Online Banking (' + (selectedBank || 'Maybank2u') + ')';
    else if (currentGw === 'ewallet') sourceStr = 'E-Wallet (' + (selectedEwallet || 'Touch n Go') + ')';
    else sourceStr = 'Credit/Debit Card';

    const cardSerial = document.getElementById('tu-card-selector').value;

    try {
      const res = await fetch('api.php?action=topup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          email: Store.user.email, 
          amount: amt, 
          method: sourceStr,
          card_serial: cardSerial
        })
      });
      if (res.ok) {
        const data = await res.json();
        Store.user = data.user;
        Store.historyItems = data.transactions;
        
        const receipt = {
          amount: amt,
          method: sourceStr,
          ref: simRef,
          balance: data.user.balance
        };
        
        Store.lastReceipt = receipt;
        Store.save();

        // Transition to Success (State 4)
        setSimState('success');

        // Redirect to success receipt page after 2.5 seconds
        setTimeout(() => {
          location.href = 'success.php';
        }, 2500);

      } else {
        const err = await res.json();
        toast(err.error || 'Top Up failed on server.');
        cancelSimPayment();
      }
    } catch (e) {
      console.error(e);
      toast('Network error during top up.');
      cancelSimPayment();
    }
  }, 1800);
}

function cancelSimPayment() {
  document.getElementById('modal-dummy-gateway').classList.add('hidden');
  toast('Transaction cancelled by user.');
}

async function processTopUp(methodStr) {
  toast('Processing top up...');
  const cardSerial = document.getElementById('tu-card-selector').value;
  try {
    const res = await fetch('api.php?action=topup', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        email: Store.user.email, 
        amount: amt, 
        method: methodStr,
        card_serial: cardSerial
      })
    });
    if (res.ok) {
      const data = await res.json();
      Store.user = data.user;
      Store.historyItems = data.transactions;
      
      const receipt = {
        amount: amt,
        method: methodStr,
        ref: 'PG-' + Math.floor(100000 + Math.random() * 900000),
        balance: data.user.balance
      };
      
      Store.lastReceipt = receipt;
      Store.save();
      location.href = 'success.php';
    } else {
      const err = await res.json();
      toast(err.error || 'Top Up failed on server.');
    }
  } catch (e) {
    console.error(e);
    toast('Network error during top up.');
  }
}
