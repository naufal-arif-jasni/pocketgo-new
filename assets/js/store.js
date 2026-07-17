/* ============================================================
   store.js
   Central API-based data layer for PocketGo.
   Communicates with the Node.js database server.
   ============================================================ */

const SESSION_KEY = 'pocketgo_session';

const Store = {
  user: {
    name: '',
    child: '',
    childClass: '',
    balance: 0,
    cardBalance: 0,
    topupTotal: 0,
    topupCount: 0
  },

  loggedIn: false,
  isAdmin: false,
  loginRole: 'parent',

  adminUsers: [],
  adminTransactions: [],
  historyItems: [],
  reports: [],
  lastReceipt: null,
  dailyLimit: 50,

  async init() {
    // Load loginRole from local preference
    const rolePref = localStorage.getItem('pocketgo_login_role');
    if (rolePref) {
      this.loginRole = rolePref;
    }

    const sess = localStorage.getItem(SESSION_KEY);
    if (sess) {
      try {
        const data = JSON.parse(sess);
        this.loginRole = data.role || 'parent';
        if (data.role === 'admin') {
          this.isAdmin = true;
          this.loggedIn = true;
          await this.fetchAdminData();
        } else if (data.email) {
          this.loggedIn = true;
          await this.fetchUserData(data.email);
        }
      } catch (e) {
        console.error('Session restore failed', e);
        this.reset();
      }
    } else {
      this.loggedIn = false;
      this.isAdmin = false;
    }

    // Load last receipt
    const r = localStorage.getItem('pocketgo_last_receipt');
    if (r) {
      try {
        this.lastReceipt = JSON.parse(r);
      } catch(e) {}
    }
  },

  async fetchUserData(email) {
    try {
      const res = await fetch(`api.php?action=user&email=${encodeURIComponent(email)}`);
      if (res.ok) {
        const data = await res.json();
        this.user = data.user;
        this.historyItems = data.transactions;
        this.reports = data.reports;
        this.dailyLimit = data.user.daily_limit || 50;
      } else {
        this.reset();
      }
    } catch (e) {
      console.error('Fetch user data failed', e);
    }
  },

  async fetchAdminData() {
    try {
      const res = await fetch('api.php?action=admin-data');
      if (res.ok) {
        const data = await res.json();
        this.adminUsers = data.users;
        this.adminTransactions = data.transactions;
        this.reports = data.reports;
      }
    } catch (e) {
      console.error('Fetch admin data failed', e);
    }
  },

  save() {
    localStorage.setItem('pocketgo_login_role', this.loginRole);
    if (this.loggedIn) {
      localStorage.setItem(SESSION_KEY, JSON.stringify({
        email: this.user.email || 'Admin1',
        role: this.isAdmin ? 'admin' : 'parent'
      }));
    } else {
      localStorage.removeItem(SESSION_KEY);
    }
    if (this.lastReceipt) {
      localStorage.setItem('pocketgo_last_receipt', JSON.stringify(this.lastReceipt));
    }
  },

  reset() {
    localStorage.removeItem(SESSION_KEY);
    localStorage.removeItem('pocketgo_last_receipt');
    this.loggedIn = false;
    this.isAdmin = false;
    this.user = { name: '', child: '', childClass: '', balance: 0, cardBalance: 0, topupTotal: 0, topupCount: 0 };
    window.location.href = 'index.php';
  }
};
