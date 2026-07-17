// login.js - Login Logic
Store.init().then(() => {
  setLoginRole(Store.loginRole || 'parent');
});

function setLoginRole(role) {
  Store.loginRole = role;
  Store.save();
  const parentBtn = document.getElementById('login-role-parent');
  const adminBtn = document.getElementById('login-role-admin');
  if (role === 'parent') {
    if(parentBtn) { parentBtn.style.background = '#C8102E'; parentBtn.style.color = '#fff'; }
    if(adminBtn) { adminBtn.style.background = 'transparent'; adminBtn.style.color = '#666'; }
    const emailInput = document.getElementById('login-email');
    if (emailInput) emailInput.placeholder = 'parent@email.com';
  } else {
    if(adminBtn) { adminBtn.style.background = '#C8102E'; adminBtn.style.color = '#fff'; }
    if(parentBtn) { parentBtn.style.background = 'transparent'; parentBtn.style.color = '#666'; }
    const emailInput = document.getElementById('login-email');
    if (emailInput) emailInput.placeholder = 'Admin1';
  }
}

async function doLogin() {
  const id = document.getElementById('login-email').value.trim();
  const pass = document.getElementById('login-pass').value;
  if (!id || !pass) { toast('Please enter your credentials.'); return; }

  toast('Logging in...');
  try {
    const response = await fetch('api.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: id, password: pass, role: Store.loginRole })
    });
    
    if (response.ok) {
      const data = await response.json();
      if (Store.loginRole === 'admin') {
        Store.isAdmin = true;
        Store.loggedIn = true;
        Store.user = { email: id, name: 'Admin', child: '', childClass: '', balance: 0, cardBalance: 0, topupTotal: 0, topupCount: 0 };
        Store.save();
        toast('Welcome Admin!');
        setTimeout(() => location.href = 'admin-dashboard.php', 900);
      } else {
        Store.isAdmin = false;
        Store.loggedIn = true;
        Store.user = data.user;
        Store.save();
        toast('Welcome back, ' + data.user.name.split(' ')[0] + '!');
        setTimeout(() => location.href = 'dashboard.php', 900);
      }
    } else {
      const err = await response.json();
      toast(err.error || 'Invalid credentials!');
    }
  } catch (e) {
    console.error(e);
    toast('Login failed. Server error.');
  }
}
