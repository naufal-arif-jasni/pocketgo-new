// register.js - Register Logic
Store.init().then(() => {
  if (Store.loggedIn) {
    location.href = 'dashboard.php';
  }
});

// Setup dynamic validation on inputs
document.addEventListener('DOMContentLoaded', () => {
  const inputs = {
    name: document.getElementById('reg-name'),
    ic: document.getElementById('reg-ic'),
    email: document.getElementById('reg-email'),
    phone: document.getElementById('reg-phone'),
    pass: document.getElementById('reg-pass'),
    pass2: document.getElementById('reg-pass2')
  };

  Object.entries(inputs).forEach(([key, el]) => {
    if (!el) return;
    
    // Validate on input and blur
    el.addEventListener('input', () => validateField(key, el));
    el.addEventListener('blur', () => validateField(key, el));
  });
});

// Helper to display error message under an input
function showError(el, message) {
  el.classList.remove('valid');
  el.classList.add('invalid');
  
  // Check if error-msg already exists
  let errorEl = el.parentNode.querySelector('.error-msg');
  if (!errorEl) {
    errorEl = document.createElement('span');
    errorEl.className = 'error-msg';
    el.parentNode.appendChild(errorEl);
  }
  errorEl.textContent = message;
}

// Helper to clear error message
function clearError(el) {
  el.classList.remove('invalid');
  el.classList.add('valid');
  const errorEl = el.parentNode.querySelector('.error-msg');
  if (errorEl) {
    errorEl.remove();
  }
}

// Check validation logic for a specific field
function validateField(key, el) {
  const value = el.value.trim();
  const rawValue = el.value; // For passwords (preserve spaces/leading spaces if any)

  if (key === 'name') {
    if (!value) {
      showError(el, 'Full Name is required.');
      return false;
    }
    // "full name dont have numbers or symbol" -> letters and spaces only
    const nameRegex = /^[a-zA-Z\s]+$/;
    if (!nameRegex.test(value)) {
      showError(el, 'Full Name must contain only letters and spaces (no numbers or symbols).');
      return false;
    }
    clearError(el);
    return true;
  }

  if (key === 'ic') {
    if (!value) {
      showError(el, 'NRIC Number is required.');
      return false;
    }
    if (value.includes('-')) {
      showError(el, 'NRIC Number must not contain any dashes (-).');
      return false;
    }
    if (/[^\d]/.test(value)) {
      showError(el, 'NRIC Number must only contain digits.');
      return false;
    }
    if (value.length !== 12) {
      showError(el, 'NRIC Number must have exactly 12 digits (currently: ' + value.length + ').');
      return false;
    }
    clearError(el);
    return true;
  }

  if (key === 'email') {
    if (!value) {
      showError(el, 'Email Address is required.');
      return false;
    }
    if (!value.includes('@')) {
      showError(el, 'Email Address must contain the "@" symbol.');
      return false;
    }
    // standard email syntax check
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      showError(el, 'Please enter a valid email address.');
      return false;
    }
    clearError(el);
    return true;
  }

  if (key === 'phone') {
    if (!value) {
      showError(el, 'Phone Number is required.');
      return false;
    }
    if (value.includes('-')) {
      showError(el, 'Phone Number must not contain any dashes (-).');
      return false;
    }
    if (/[^\d]/.test(value)) {
      showError(el, 'Phone Number must only contain digits.');
      return false;
    }
    if (value.length < 9) {
      showError(el, 'Phone Number must be at least 9 digits long.');
      return false;
    }
    if (value.length > 12) {
      showError(el, 'Phone Number must not exceed 12 digits.');
      return false;
    }
    clearError(el);
    return true;
  }

  if (key === 'pass') {
    if (!rawValue) {
      showError(el, 'Password is required.');
      return false;
    }
    if (rawValue.length < 8) {
      showError(el, 'Password must be at least 8 characters long.');
      return false;
    }
    if (!/[A-Z]/.test(rawValue)) {
      showError(el, 'Password must contain at least one capital letter.');
      return false;
    }
    if (!/[0-9]/.test(rawValue)) {
      showError(el, 'Password must contain at least one number.');
      return false;
    }
    if (!/[^a-zA-Z0-9]/.test(rawValue)) {
      showError(el, 'Password must contain at least one symbol.');
      return false;
    }
    clearError(el);
    return true;
  }

  if (key === 'pass2') {
    const passVal = document.getElementById('reg-pass').value;
    if (!rawValue) {
      showError(el, 'Please confirm your password.');
      return false;
    }
    if (rawValue !== passVal) {
      showError(el, 'Passwords do not match.');
      return false;
    }
    clearError(el);
    return true;
  }

  return true;
}

async function doRegister() {
  const inputs = {
    name: document.getElementById('reg-name'),
    ic: document.getElementById('reg-ic'),
    email: document.getElementById('reg-email'),
    phone: document.getElementById('reg-phone'),
    pass: document.getElementById('reg-pass'),
    pass2: document.getElementById('reg-pass2')
  };

  let allValid = true;
  Object.entries(inputs).forEach(([key, el]) => {
    if (el) {
      const isValid = validateField(key, el);
      if (!isValid) {
        allValid = false;
      }
    }
  });

  if (!allValid) {
    toast('Please correct the validation errors before registering.');
    return;
  }

  const name = inputs.name.value.trim();
  const ic = inputs.ic.value.trim();
  const email = inputs.email.value.trim();
  const phone = inputs.phone.value.trim();
  const pass = inputs.pass.value;

  toast('Creating account...');
  try {
    const response = await fetch('api.php?action=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name, ic, email, phone, password: pass
      })
    });

    if (response.ok) {
      const data = await response.json();
      Store.loggedIn = true;
      Store.isAdmin = false;
      Store.user = data.user;
      Store.save();
      toast('Account created! Welcome, ' + name.split(' ')[0] + '!');
      setTimeout(() => location.href = 'dashboard.php', 1200);
    } else {
      const err = await response.json();
      toast(err.error || 'Registration failed!');
    }
  } catch (e) {
    console.error(e);
    toast('Registration failed. Server error.');
  }
}
