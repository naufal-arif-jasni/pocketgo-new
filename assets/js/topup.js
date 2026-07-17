// topup.js - Topup flow with payment gateway tab selectors and full-screen mock checkout simulator
let amt = 0;
let pm = 'fpx'; // Default to FPX
let currentGw = 'fpx'; // 'fpx' (banking), 'ewallet', 'card'
let selectedBank = 'Maybank2u'; // Default to first bank
let selectedEwallet = 'Touch n Go'; // Default to Touch n Go
let currentPin = '';
let simRef = '';

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

// Trigger Top Up Checkout process
function doTopUp() {
  if (amt <= 0) { toast('Please select or enter an amount.'); return; }

  const u = Store.user;
  const cards = u.cards || [];
  if (cards.length === 0) {
    toast('⚠️ Please register a student card first.');
    return;
  }

  if (currentGw === 'banking') {
    if (!selectedBank) { toast('Please select a retail banking option.'); return; }
    showSimulator('banking', selectedBank);
  } else if (currentGw === 'ewallet') {
    if (!selectedEwallet) { toast('Please select an e-wallet.'); return; }
    showSimulator('ewallet', selectedEwallet);
  } else if (currentGw === 'card') {
    if (u.visa_card) {
      // Show PIN modal as usual
      document.getElementById('m-sum-amount').textContent = 'RM ' + amt.toFixed(2);
      document.getElementById('m-sum-method').textContent = `Linked Visa (${u.visa_card.card_number})`;
      currentPin = '';
      updatePinDisplay();
      showModal('modal-pin');
    } else {
      toast('Please link a Visa/MasterCard card to proceed, or choose Online Banking.');
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
    if (currentGw === 'banking') sourceStr = 'FPX Online Banking (' + selectedBank + ')';
    else if (currentGw === 'ewallet') sourceStr = 'E-Wallet (' + selectedEwallet + ')';
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
