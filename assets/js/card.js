// card.js - Physical Card Management with Horizontal Carousel
let activeCardIndex = 0;

Store.init().then(() => {
  requireParentAuth();
  renderCardPage();
});

function renderCardPage() {
  const u = Store.user;
  const cards = u.cards || [];
  
  if (cards.length === 0) {
    document.getElementById('empty-card-container').style.display = 'block';
    document.getElementById('active-card-container').style.display = 'none';
    return;
  }
  
  document.getElementById('empty-card-container').style.display = 'none';
  document.getElementById('active-card-container').style.display = 'block';

  // Render Carousel Cards
  const track = document.getElementById('card-carousel-track');
  const dotsContainer = document.getElementById('carousel-dots-container');
  
  track.innerHTML = '';
  dotsContainer.innerHTML = '';

  cards.forEach((card, index) => {
    // Card slide with individual card balance displayed
    const cardHtml = `
      <div class="physical-card" onclick="scrollToCard(${index})">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
          <div class="pc-logo" style="margin-bottom:0;">Pocket<span>Go</span> Student</div>
          <div style="text-align: right;">
            <div class="pc-label" style="color: rgba(255,255,255,0.7); font-size: 0.58rem; letter-spacing: 0.5px;">BALANCE</div>
            <div style="font-size: 1.15rem; font-weight: 800; color: #FFD700; font-family: 'Poppins', sans-serif; line-height: 1;">RM ${parseFloat(card.balance || 0).toFixed(2)}</div>
          </div>
        </div>
        <div class="pc-chip" style="margin-bottom: 12px;"></div>
        <div class="pc-number" style="margin-bottom: 12px;">${formatSerial(card.card_serial)}</div>
        <div class="pc-row">
          <div><div class="pc-label">Student</div><div class="pc-val">${card.student_name}</div></div>
          <div><div class="pc-label">Class</div><div class="pc-val">${card.class}</div></div>
          <div><div class="pc-label">Status</div><div class="pc-val" style="color:#FFD700;">${(card.status || 'active').toUpperCase()}</div></div>
        </div>
        <div style="font-size:.6rem;color:rgba(255,255,255,.5);margin-top:10px;">ID: ${card.student_id}</div>
      </div>
    `;
    track.insertAdjacentHTML('beforeend', cardHtml);
  });

  // Add Card visual slide at the end
  const addSlideHtml = `
    <div class="add-card-slide" onclick="showModal('modal-register-card')">
      <div class="add-card-plus">${getModernIcon('➕')}</div>
      <div class="add-card-text">Add Card</div>
    </div>
  `;
  track.insertAdjacentHTML('beforeend', addSlideHtml);

  // Render Dot Indicators for cards + Add Card slide
  const totalSlides = cards.length + 1;
  for (let i = 0; i < totalSlides; i++) {
    const dotHtml = `<div class="dot ${i === activeCardIndex ? 'active' : ''}" onclick="scrollToCard(${i})"></div>`;
    dotsContainer.insertAdjacentHTML('beforeend', dotHtml);
  }

  // Bind Scroll Snapping Event
  const scroller = document.getElementById('carousel-scroller');
  if (scroller) {
    scroller.onscroll = () => {
      const firstCard = scroller.querySelector('.physical-card, .add-card-slide');
      const cardWidth = firstCard ? firstCard.offsetWidth + 16 : 306;
      const scrollLeft = scroller.scrollLeft;
      const index = Math.round(scrollLeft / cardWidth);
      if (index !== activeCardIndex && index < totalSlides) {
        activeCardIndex = index;
        updateSelectedCardDetails();
      }
    };
  }

  // Update detail displays
  updateSelectedCardDetails();
}

function updateSelectedCardDetails() {
  const cards = Store.user.cards || [];
  if (cards.length === 0) return;
  
  const totalSlides = cards.length + 1;
  if (activeCardIndex >= totalSlides) {
    activeCardIndex = totalSlides - 1;
  }

  // Update Dot Classes
  const dots = document.querySelectorAll('.carousel-dots .dot');
  dots.forEach((dot, idx) => {
    if (idx === activeCardIndex) dot.classList.add('active');
    else dot.classList.remove('active');
  });

  // Update Side Arrow States (Disabled when at boundaries)
  const prevBtn = document.getElementById('carousel-prev-btn');
  const nextBtn = document.getElementById('carousel-next-btn');
  if (prevBtn) prevBtn.classList.toggle('disabled', activeCardIndex === 0);
  if (nextBtn) nextBtn.classList.toggle('disabled', activeCardIndex === totalSlides - 1);

  const settingsSec = document.getElementById('card-settings-details');

  if (activeCardIndex < cards.length) {
    // Show settings since it is an active card
    if (settingsSec) settingsSec.style.display = 'block';

    const card = cards[activeCardIndex];
    // Daily limit card info
    const limitVal = card.daily_limit || 50;
    document.getElementById('card-limit-val').textContent = limitVal;
    
    // Spend statistics (calculate today's spends from transactions for this specific card)
    const todayStr = new Date().toISOString().slice(0, 10);
    const todaySpends = Store.historyItems
      .filter(t => {
        const dateMatch = t.date.slice(0, 10) === todayStr;
        const isSpend = t.amount < 0;
        // Match specific child name inside transaction descriptions if there are multiple cards
        const nameMatch = t.description && t.description.includes(card.student_name);
        return dateMatch && isSpend && (cards.length === 1 || nameMatch);
      })
      .reduce((sum, t) => sum + Math.abs(t.amount), 0);

    document.getElementById('card-spent-val').textContent = todaySpends.toFixed(2);
    const remaining = Math.max(0, limitVal - todaySpends);
    document.getElementById('card-rem-val').textContent = remaining.toFixed(2);

    const fillPercent = Math.min(100, (todaySpends / limitVal) * 100);
    document.getElementById('card-limit-fill').style.width = fillPercent + '%';
  } else {
    // Hide settings when looking at "Add Card" slide
    if (settingsSec) settingsSec.style.display = 'none';
  }
}

function navigateCarousel(direction) {
  const cards = Store.user.cards || [];
  const totalSlides = cards.length + 1;
  let targetIndex = activeCardIndex + direction;
  if (targetIndex >= 0 && targetIndex < totalSlides) {
    scrollToCard(targetIndex);
  }
}

// Make globally accessible
window.navigateCarousel = navigateCarousel;

function scrollToCard(index) {
  const scroller = document.getElementById('carousel-scroller');
  if (!scroller) return;
  const firstCard = scroller.querySelector('.physical-card, .add-card-slide');
  const cardWidth = firstCard ? firstCard.offsetWidth + 16 : 306;
  scroller.scrollTo({
    left: index * cardWidth,
    behavior: 'smooth'
  });
  activeCardIndex = index;
  updateSelectedCardDetails();
}

function formatSerial(serial) {
  if (!serial) return '— • — • —';
  if (serial.length === 10) {
    return serial.substring(0, 4) + ' • ' + serial.substring(4, 8) + ' • ' + serial.substring(8);
  }
  return serial;
}

async function registerStudentCard() {
  const serial = document.getElementById('reg-card-serial').value.trim();
  const name = document.getElementById('reg-student-name').value.trim();
  const nric = document.getElementById('reg-student-nric').value.trim();
  const cls = document.getElementById('reg-student-class').value.trim();

  if (!serial || !name || !nric || !cls) {
    toast('Please fill in all card details.');
    return;
  }

  if (serial.length !== 10 || isNaN(Number(serial))) {
    toast('Card Serial No. must be exactly 10 digits.');
    return;
  }

  toast('Registering student card...');
  try {
    const res = await fetch('api.php?action=register-card', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: Store.user.email,
        card_serial: serial,
        student_name: name,
        student_nric: nric,
        class: cls
      })
    });

    if (res.ok) {
      toast('Student card registered successfully!');
      // Fetch full updated user details from store
      await Store.fetchUserData(Store.user.email);
      Store.save();
      closeModal('modal-register-card');
      
      // Auto switch to newly registered card
      activeCardIndex = (Store.user.cards || []).length - 1;
      renderCardPage();
    } else {
      const err = await res.json();
      toast(err.error || 'Registration failed.');
    }
  } catch (e) {
    console.error(e);
    toast('Error registering card.');
  }
}

function selectLimit(val, btn) {
  const custom = document.getElementById('card-custom-limit');
  if (custom) custom.value = '';
  document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  updateLimitState(val);
}

function selectCustomLimit(val) {
  const parsed = parseFloat(val) || 0;
  document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
  updateLimitState(parsed);
}

let pendingLimit = 50;
function updateLimitState(val) {
  pendingLimit = val;
}

async function saveLimit() {
  if (pendingLimit <= 0) { toast('Please specify a valid limit amount.'); return; }
  
  const cards = Store.user.cards || [];
  if (cards.length === 0) return;
  const card = cards[activeCardIndex];

  toast('Updating daily limit...');
  try {
    const res = await fetch('api.php?action=update-limit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        email: Store.user.email, 
        limit: pendingLimit,
        card_serial: card.card_serial
      })
    });
    if (res.ok) {
      // Refresh user details
      await Store.fetchUserData(Store.user.email);
      Store.save();
      closeModal('modal-limit');
      toast('Daily spend limit updated successfully!');
      renderCardPage();
    } else {
      toast('Update failed.');
    }
  } catch (e) {
    console.error(e);
    toast('Error updating limit.');
  }
}
