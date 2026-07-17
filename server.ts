import express from 'express';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;

app.use(express.json());

// ── SERVER-SIDE JSON DATABASE (pocketgo_db) ──
const DB_PATH = path.join(__dirname, 'pocketgo_db.json');

const INITIAL_DB = {
  users: [
    { id: 1, name: 'Ahmad Bin Abdullah', email: 'ahmad@email.com', phone: '012-3456789', child: 'Muhammad Faris', childClass: '4 Amanah', studentId: 'PG-40124', balance: 120.50, daily_limit: 50, status: 'active', password: 'password123', role: 'parent', topupTotal: 100, topupCount: 2 },
    { id: 2, name: 'Siti Binti Hassan', email: 'siti@email.com', phone: '013-9876543', child: 'Nur Aisyah', childClass: '3 Bestari', studentId: 'PG-30125', balance: 85.50, daily_limit: 30, status: 'active', password: 'password123', role: 'parent', topupTotal: 50, topupCount: 1 },
    { id: 3, name: 'Roslan Bin Bakar', email: 'roslan@email.com', phone: '019-4567890', child: 'Ahmad Daniel', childClass: '5 Cemerlang', studentId: 'PG-50126', balance: 200.00, daily_limit: 50, status: 'active', password: 'password123', role: 'parent', topupTotal: 150, topupCount: 3 },
    { id: 4, name: 'Zainab Binti Mohd', email: 'zainab@email.com', phone: '017-2345678', child: 'Umar Hakim', childClass: '2 Dedikasi', studentId: 'PG-20127', balance: 45.00, daily_limit: 20, status: 'inactive', password: 'password123', role: 'parent', topupTotal: 0, topupCount: 0 }
  ],
  transactions: [
    { id: 1, userId: 1, description: 'Top Up via FPX', amount: 50.00, date: '2026-06-22 09:14', type: 'topup', icon: '⬆️', cat: 'topup', title: 'Top Up via FPX', sub: 'Maybank · 9:14 AM' },
    { id: 2, userId: 1, description: 'Canteen - Nasi Lemak', amount: -3.50, date: '2026-06-22 07:45', type: 'spend', icon: '🍱', cat: 'canteen', title: 'Canteen – Nasi Lemak', sub: '7:45 AM' },
    { id: 3, userId: 2, description: 'Top Up via DuitNow QR', amount: 50.00, date: '2026-06-21 08:00', type: 'topup', icon: '⬆️', cat: 'topup', title: 'Top Up via DuitNow QR', sub: '8:00 AM' },
    { id: 4, userId: 2, description: 'School Bookshop', amount: -12.00, date: '2026-06-21 14:10', type: 'spend', icon: '📚', cat: 'shop', title: 'School Bookshop', sub: '2:10 PM' },
    { id: 5, userId: 3, description: 'Canteen - Mee Goreng', amount: -3.00, date: '2026-06-19 07:42', type: 'spend', icon: '🍱', cat: 'canteen', title: 'Canteen – Mee Goreng', sub: '7:42 AM' },
    { id: 6, userId: 1, description: 'Top Up via Credit Card', amount: 50.00, date: '2026-06-18 10:30', type: 'topup', icon: '⬆️', cat: 'topup', title: 'Top Up via Credit Card', sub: '10:30 AM' }
  ],
  reports: [
    { id: 1, reporterName: 'Ahmad Bin Abdullah', child: 'Muhammad Faris', type: 'damaged', subject: 'Card not scanning at canteen', description: 'The card stopped working at the canteen reader this morning, edges look cracked.', status: 'Open', createdAt: '2026-06-20 09:12' }
  ],
  adminNextId: 5,
  txnNextId: 7,
  reportNextId: 2
};

function getDB() {
  if (!fs.existsSync(DB_PATH)) {
    fs.writeFileSync(DB_PATH, JSON.stringify(INITIAL_DB, null, 2), 'utf8');
  }
  return JSON.parse(fs.readFileSync(DB_PATH, 'utf8'));
}

function saveDB(data: any) {
  fs.writeFileSync(DB_PATH, JSON.stringify(data, null, 2), 'utf8');
}

// Initialize DB on start
getDB();

// Helper to convert PHP template into pure HTML by stripping PHP tags
function renderPhpAsHtml(filePath: string): string {
  let content = fs.readFileSync(filePath, 'utf8');
  // Strip out PHP blocks <?php ... ?>
  content = content.replace(/<\?php[\s\S]*?\?>/g, '');
  return content;
}

// ── API ROUTES ──

function hydrateUserNode(user: any) {
  if (!user) return null;
  
  // Parse cards
  let cards = [];
  if (user.cards_json) {
    try {
      cards = typeof user.cards_json === 'string' ? JSON.parse(user.cards_json) : user.cards_json;
    } catch (e) {
      cards = [];
    }
  }

  // Backwards-compatible fallback
  if ((!cards || cards.length === 0) && (user.card_serial || user.studentId)) {
    cards = [
      {
        card_serial: user.card_serial,
        student_name: user.child,
        student_id: user.studentId,
        class: user.childClass,
        balance: parseFloat(user.balance) || 0,
        daily_limit: parseFloat(user.daily_limit) || 50,
        status: user.status || 'active'
      }
    ];
  }
  user.cards = cards;

  // Parse Visa Card
  let visa_card = null;
  if (user.visa_card_json) {
    try {
      visa_card = typeof user.visa_card_json === 'string' ? JSON.parse(user.visa_card_json) : user.visa_card_json;
    } catch (e) {
      visa_card = null;
    }
  }
  user.visa_card = visa_card;

  return user;
}

// Intercept api.php calls to simulate real PHP backend logic in AI Studio
app.all('/api.php', (req, res) => {
  const action = req.query.action as string;
  const db = getDB();

  if (action === 'login') {
    const { email, password, role } = req.body;
    if (!email || !password) return res.status(400).json({ error: 'Missing credentials' });
    if (role === 'admin') {
      if (email === 'Admin1' && password === '12345') {
        return res.json({ success: true, role: 'admin' });
      }
      return res.status(401).json({ error: 'Invalid admin credentials' });
    } else {
      const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase() && u.password === password && u.role === 'parent');
      if (user) {
        return res.json({ success: true, user: hydrateUserNode(user) });
      }
      return res.status(401).json({ error: 'Invalid parent credentials' });
    }
  }

  if (action === 'register') {
    const { name, ic, email, phone, password } = req.body;
    if (!name || !email || !password) {
      return res.status(400).json({ error: 'Missing required registration fields' });
    }
    const existing = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (existing) {
      return res.status(400).json({ error: 'Email address already registered' });
    }
    const newUser = {
      id: db.adminNextId++,
      name,
      ic: ic || '',
      email,
      phone: phone || '',
      child: '',
      childClass: '',
      studentId: '',
      card_serial: '',
      cards_json: null,
      visa_card_json: null,
      balance: 0,
      daily_limit: 50,
      status: 'active',
      password,
      role: 'parent' as const,
      topupTotal: 0,
      topupCount: 0
    };
    db.users.push(newUser);
    saveDB(db);
    return res.json({ success: true, user: hydrateUserNode(newUser) });
  }

  if (action === 'register-card') {
    const { email, card_serial, student_name, student_nric, class: studentClass } = req.body;
    if (!email || !card_serial || !student_name || !student_nric || !studentClass) {
      return res.status(400).json({ error: 'Please fill in all card details.' });
    }
    if (card_serial.length !== 10 || isNaN(Number(card_serial))) {
      return res.status(400).json({ error: 'Card Serial No. must be exactly 10 digits.' });
    }
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    // Hydrate existing cards first
    const hydrated = hydrateUserNode(user);
    const existingCards = hydrated.cards || [];

    // Check if duplicate
    const isDup = existingCards.some((c: any) => c.card_serial === card_serial);
    if (isDup) {
      return res.status(400).json({ error: 'This card serial is already registered.' });
    }

    const newCard = {
      card_serial,
      student_name,
      student_id: student_nric,
      class: studentClass,
      balance: 0,
      daily_limit: 50,
      status: 'active'
    };

    existingCards.push(newCard);
    user.cards_json = JSON.stringify(existingCards);

    // Sync back first card if counts match
    if (existingCards.length === 1) {
      user.child = student_name;
      user.childClass = studentClass;
      user.studentId = student_nric;
      user.card_serial = card_serial;
      user.balance = 0;
      user.daily_limit = 50;
    }

    saveDB(db);
    return res.json({ success: true, user: hydrateUserNode(user) });
  }

  if (action === 'link-visa') {
    const { email, cardholder_name, card_number, expiry_date, cvv } = req.body;
    if (!email || !cardholder_name || !card_number || !expiry_date || !cvv) {
      return res.status(400).json({ error: 'Please fill in all credit card details.' });
    }

    const cleanCard = card_number.replace(/[\s-]/g, '');
    if (cleanCard.length < 13 || cleanCard.length > 19 || isNaN(Number(cleanCard))) {
      return res.status(400).json({ error: 'Invalid card number format.' });
    }

    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    const visaObj = {
      cardholder_name,
      card_number: '•••• ' + cleanCard.slice(-4),
      expiry_date
    };

    user.visa_card_json = JSON.stringify(visaObj);
    saveDB(db);

    return res.json({ success: true, user: hydrateUserNode(user) });
  }

  if (action === 'user') {
    const email = req.query.email as string;
    if (!email) {
      return res.status(400).json({ error: 'Email parameter required' });
    }
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }
    const userTransactions = db.transactions.filter((t: any) => t.userId === user.id);
    const userReports = db.reports.filter((r: any) => r.reporterName === user.name);
    return res.json({
      success: true,
      user: hydrateUserNode(user),
      transactions: userTransactions,
      reports: userReports
    });
  }

  if (action === 'update-limit') {
    const { email, limit, card_serial } = req.body;
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    const hydrated = hydrateUserNode(user);
    const cards = hydrated.cards || [];

    let syncLimit = parseFloat(limit) || 50;
    for (const c of cards) {
      if (c.card_serial === card_serial || (!card_serial && cards.length === 1)) {
        c.daily_limit = syncLimit;
        break;
      }
    }

    user.cards_json = JSON.stringify(cards);
    user.daily_limit = syncLimit;

    saveDB(db);
    return res.json({ success: true, user: hydrateUserNode(user) });
  }

  if (action === 'topup') {
    const { email, amount, method, card_serial } = req.body;
    const numAmount = parseFloat(amount);
    if (!email || isNaN(numAmount) || numAmount <= 0) {
      return res.status(400).json({ error: 'Invalid topup parameters' });
    }
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    const hydrated = hydrateUserNode(user);
    const cards = hydrated.cards || [];

    if (cards.length === 0) {
      return res.status(400).json({ error: 'Please register a student card before topping up.' });
    }

    let targetCardName = 'Child Wallet';
    let newCardBalance = 0;
    for (const c of cards) {
      if (c.card_serial === card_serial || (!card_serial && cards.length > 0)) {
        c.balance = (c.balance || 0) + numAmount;
        newCardBalance = c.balance;
        targetCardName = c.student_name;
        break;
      }
    }

    user.cards_json = JSON.stringify(cards);
    user.balance = cards.length === 1 ? newCardBalance : (user.balance || 0) + numAmount;
    user.topupTotal = (user.topupTotal || 0) + numAmount;
    user.topupCount = (user.topupCount || 0) + 1;

    const now = new Date();
    const dateStr = now.toISOString().slice(0, 16).replace('T', ' ');
    const timeStr = now.toLocaleTimeString('en-MY', { hour: 'numeric', minute: '2-digit' });

    const newTxn = {
      id: db.txnNextId++,
      userId: user.id,
      description: `Top Up RM ${numAmount.toFixed(2)} for ${targetCardName} via ${method}`,
      amount: numAmount,
      date: dateStr,
      type: 'topup' as const,
      icon: '⬆️',
      cat: 'topup',
      title: `Top Up (${targetCardName})`,
      sub: `${method.includes('Bank') || method.includes('FPX') ? 'Online Banking' : 'Card'} · ${timeStr}`
    };

    db.transactions.push(newTxn);
    saveDB(db);

    const userTransactions = db.transactions.filter((t: any) => t.userId === user.id);
    return res.json({ success: true, user: hydrateUserNode(user), transactions: userTransactions });
  }

  if (action === 'create-report') {
    const { email, type, subject, description } = req.body;
    if (!email || !subject || !description) {
      return res.status(400).json({ error: 'Missing report parameters' });
    }
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }
    const now = new Date();
    const dateStr = now.toISOString().slice(0, 16).replace('T', ' ');

    const newReport = {
      id: db.reportNextId++,
      reporterName: user.name,
      child: user.child,
      type,
      subject,
      description,
      status: 'Open',
      createdAt: dateStr
    };
    db.reports.push(newReport);
    saveDB(db);

    const userReports = db.reports.filter((r: any) => r.reporterName === user.name);
    return res.json({ success: true, reports: userReports });
  }

  if (action === 'delete-report') {
    const { id } = req.body;
    db.reports = db.reports.filter((r: any) => r.id !== parseInt(id));
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-data') {
    return res.json({
      success: true,
      users: db.users.filter((u: any) => u.role !== 'admin'),
      transactions: db.transactions,
      reports: db.reports
    });
  }

  if (action === 'admin-save-user') {
    const { action: crudAction, id, name, email, phone, child, balance, status } = req.body;
    if (crudAction === 'create') {
      const newUser = {
        id: db.adminNextId++,
        name,
        email,
        phone,
        child,
        childClass: '4 Amanah',
        studentId: 'PG-' + Math.floor(10000 + Math.random() * 90000),
        balance: parseFloat(balance) || 0,
        daily_limit: 50,
        status,
        password: 'password123',
        role: 'parent' as const,
        topupTotal: parseFloat(balance) > 0 ? parseFloat(balance) : 0,
        topupCount: parseFloat(balance) > 0 ? 1 : 0
      };
      db.users.push(newUser);
    } else {
      const user = db.users.find((u: any) => u.id === parseInt(id));
      if (user) {
        user.name = name;
        user.email = email;
        user.phone = phone;
        user.child = child;
        user.balance = parseFloat(balance) || 0;
        user.status = status;
      }
    }
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-delete-user') {
    const { id } = req.body;
    db.users = db.users.filter((u: any) => u.id !== parseInt(id));
    db.transactions = db.transactions.filter((t: any) => t.userId !== parseInt(id));
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-save-transaction') {
    const { action: crudAction, id, userId, description, amount, type, date } = req.body;
    const numAmount = parseFloat(amount);
    const txnAmount = type === 'topup' ? Math.abs(numAmount) : -Math.abs(numAmount);

    if (crudAction === 'create') {
      const newTxn = {
        id: db.txnNextId++,
        userId: parseInt(userId),
        description,
        amount: txnAmount,
        date,
        type,
        icon: type === 'topup' ? '⬆️' : '🍱',
        cat: type === 'topup' ? 'topup' : 'canteen',
        title: description,
        sub: date
      };
      db.transactions.push(newTxn);
      const user = db.users.find((u: any) => u.id === parseInt(userId));
      if (user) {
        user.balance += txnAmount;
        if (type === 'topup') {
          user.topupTotal = (user.topupTotal || 0) + Math.abs(txnAmount);
          user.topupCount = (user.topupCount || 0) + 1;
        }
      }
    } else {
      const txn = db.transactions.find((t: any) => t.id === parseInt(id));
      if (txn) {
        const oldUser = db.users.find((u: any) => u.id === txn.userId);
        if (oldUser) {
          oldUser.balance -= txn.amount;
        }
        txn.userId = parseInt(userId);
        txn.description = description;
        txn.amount = txnAmount;
        txn.date = date;
        txn.type = type;
        const newUser = db.users.find((u: any) => u.id === parseInt(userId));
        if (newUser) {
          newUser.balance += txnAmount;
        }
      }
    }
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-delete-transaction') {
    const { id } = req.body;
    const txn = db.transactions.find((t: any) => t.id === parseInt(id));
    if (txn) {
      const user = db.users.find((u: any) => u.id === txn.userId);
      if (user) {
        user.balance -= txn.amount;
      }
    }
    db.transactions = db.transactions.filter((t: any) => t.id !== parseInt(id));
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-update-report-status') {
    const { id, status } = req.body;
    const report = db.reports.find((r: any) => r.id === parseInt(id));
    if (report) {
      report.status = status;
    }
    saveDB(db);
    return res.json({ success: true });
  }

  if (action === 'admin-delete-report') {
    const { id } = req.body;
    db.reports = db.reports.filter((r: any) => r.id !== parseInt(id));
    saveDB(db);
    return res.json({ success: true });
  }

  return res.status(400).json({ error: 'Invalid API Action: ' + action });
});

// Health Check
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok' });
});

// Authentication: Login
app.post('/api/auth/login', (req, res) => {
  const { email, password, role } = req.body;
  if (!email || !password) {
    return res.status(400).json({ error: 'Missing credentials' });
  }

  const db = getDB();

  if (role === 'admin') {
    // Admin login: Simple check matching 'Admin1' and '12345'
    if (email === 'Admin1' && password === '12345') {
      return res.json({ success: true, role: 'admin' });
    }
    return res.status(401).json({ error: 'Invalid admin credentials' });
  } else {
    // Parent login
    const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase() && u.password === password && u.role === 'parent');
    if (user) {
      return res.json({ success: true, user });
    }
    return res.status(401).json({ error: 'Invalid parent credentials' });
  }
});

// Authentication: Register
app.post('/api/auth/register', (req, res) => {
  const { name, ic, email, phone, password, child, childClass, studentId } = req.body;
  if (!name || !email || !password || !child) {
    return res.status(400).json({ error: 'Missing required registration fields' });
  }

  const db = getDB();
  const existing = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
  if (existing) {
    return res.status(400).json({ error: 'Email address already registered' });
  }

  const newUser = {
    id: db.adminNextId++,
    name,
    ic: ic || '',
    email,
    phone: phone || '',
    child,
    childClass: childClass || '4 Amanah',
    studentId: studentId || 'PG-' + Math.floor(10000 + Math.random() * 90000),
    balance: 0,
    daily_limit: 50,
    status: 'active',
    password,
    role: 'parent' as const,
    topupTotal: 0,
    topupCount: 0
  };

  db.users.push(newUser);
  saveDB(db);

  res.json({ success: true, user: newUser });
});

// Get User Specific Data (Balance, History, Reports)
app.get('/api/user', (req, res) => {
  const email = req.query.email as string;
  if (!email) {
    return res.status(400).json({ error: 'Email parameter required' });
  }

  const db = getDB();
  const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  const userTransactions = db.transactions.filter((t: any) => t.userId === user.id);
  const userReports = db.reports.filter((r: any) => r.reporterName === user.name);

  res.json({
    user,
    transactions: userTransactions,
    reports: userReports
  });
});

// Update Spend Limit
app.post('/api/user/update-limit', (req, res) => {
  const { email, limit } = req.body;
  const db = getDB();
  const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  user.daily_limit = parseFloat(limit) || 50;
  saveDB(db);

  res.json({ success: true, user });
});

// Perform Top Up
app.post('/api/topup', (req, res) => {
  const { email, amount, method } = req.body;
  const numAmount = parseFloat(amount);
  if (!email || isNaN(numAmount) || numAmount <= 0) {
    return res.status(400).json({ error: 'Invalid topup parameters' });
  }

  const db = getDB();
  const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  user.balance += numAmount;
  user.topupTotal = (user.topupTotal || 0) + numAmount;
  user.topupCount = (user.topupCount || 0) + 1;

  const now = new Date();
  const dateStr = now.toISOString().slice(0, 16).replace('T', ' ');
  const timeStr = now.toLocaleTimeString('en-MY', { hour: 'numeric', minute: '2-digit' });

  const newTxn = {
    id: db.txnNextId++,
    userId: user.id,
    description: `Top Up via ${method}`,
    amount: numAmount,
    date: dateStr,
    type: 'topup' as const,
    icon: '⬆️',
    cat: 'topup',
    title: `Top Up via ${method.split(' ')[0]}`,
    sub: `${method.includes('Bank') ? method.substring(method.indexOf('(')+1, method.indexOf(')')) : 'Card'} · ${timeStr}`
  };

  db.transactions.push(newTxn);
  saveDB(db);

  const userTransactions = db.transactions.filter((t: any) => t.userId === user.id);
  res.json({ success: true, user, transactions: userTransactions });
});

// Create Report/Complaint
app.post('/api/reports/create', (req, res) => {
  const { email, type, subject, description } = req.body;
  if (!email || !subject || !description) {
    return res.status(400).json({ error: 'Missing report parameters' });
  }

  const db = getDB();
  const user = db.users.find((u: any) => u.email.toLowerCase() === email.toLowerCase());
  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  const now = new Date();
  const dateStr = now.toISOString().slice(0, 16).replace('T', ' ');

  const newReport = {
    id: db.reportNextId++,
    reporterName: user.name,
    child: user.child,
    type,
    subject,
    description,
    status: 'Open',
    createdAt: dateStr
  };

  db.reports.push(newReport);
  saveDB(db);

  const userReports = db.reports.filter((r: any) => r.reporterName === user.name);
  res.json({ success: true, reports: userReports });
});

// Delete Report
app.post('/api/reports/delete', (req, res) => {
  const { id } = req.body;
  const db = getDB();
  db.reports = db.reports.filter((r: any) => r.id !== parseInt(id));
  saveDB(db);
  res.json({ success: true });
});

// Admin: Get All Data
app.get('/api/admin/data', (req, res) => {
  const db = getDB();
  res.json({
    users: db.users.filter((u: any) => u.role !== 'admin'),
    transactions: db.transactions,
    reports: db.reports
  });
});

// Admin: Save User
app.post('/api/admin/user/save', (req, res) => {
  const { action, id, name, email, phone, child, balance, status } = req.body;
  const db = getDB();

  if (action === 'create') {
    const newUser = {
      id: db.adminNextId++,
      name,
      email,
      phone,
      child,
      childClass: '4 Amanah',
      studentId: 'PG-' + Math.floor(10000 + Math.random() * 90000),
      balance: parseFloat(balance) || 0,
      daily_limit: 50,
      status,
      password: 'password123',
      role: 'parent' as const,
      topupTotal: parseFloat(balance) > 0 ? parseFloat(balance) : 0,
      topupCount: parseFloat(balance) > 0 ? 1 : 0
    };
    db.users.push(newUser);
  } else {
    const user = db.users.find((u: any) => u.id === parseInt(id));
    if (user) {
      user.name = name;
      user.email = email;
      user.phone = phone;
      user.child = child;
      user.balance = parseFloat(balance) || 0;
      user.status = status;
    }
  }

  saveDB(db);
  res.json({ success: true });
});

// Admin: Delete User
app.post('/api/admin/user/delete', (req, res) => {
  const { id } = req.body;
  const db = getDB();
  db.users = db.users.filter((u: any) => u.id !== parseInt(id));
  db.transactions = db.transactions.filter((t: any) => t.userId !== parseInt(id));
  saveDB(db);
  res.json({ success: true });
});

// Admin: Save Transaction
app.post('/api/admin/transaction/save', (req, res) => {
  const { action, id, userId, description, amount, type, date } = req.body;
  const db = getDB();
  const numAmount = parseFloat(amount);
  const txnAmount = type === 'topup' ? Math.abs(numAmount) : -Math.abs(numAmount);

  if (action === 'create') {
    const newTxn = {
      id: db.txnNextId++,
      userId: parseInt(userId),
      description,
      amount: txnAmount,
      date,
      type,
      icon: type === 'topup' ? '⬆️' : '🍱',
      cat: type === 'topup' ? 'topup' : 'canteen',
      title: description,
      sub: date
    };
    db.transactions.push(newTxn);

    // Update user balance
    const user = db.users.find((u: any) => u.id === parseInt(userId));
    if (user) {
      user.balance += txnAmount;
      if (type === 'topup') {
        user.topupTotal = (user.topupTotal || 0) + Math.abs(txnAmount);
        user.topupCount = (user.topupCount || 0) + 1;
      }
    }
  } else {
    const txn = db.transactions.find((t: any) => t.id === parseInt(id));
    if (txn) {
      // Revert old transaction user balance
      const oldUser = db.users.find((u: any) => u.id === txn.userId);
      if (oldUser) {
        oldUser.balance -= txn.amount;
      }

      txn.userId = parseInt(userId);
      txn.description = description;
      txn.amount = txnAmount;
      txn.date = date;
      txn.type = type;

      // Apply new transaction user balance
      const newUser = db.users.find((u: any) => u.id === parseInt(userId));
      if (newUser) {
        newUser.balance += txnAmount;
      }
    }
  }

  saveDB(db);
  res.json({ success: true });
});

// Admin: Delete Transaction
app.post('/api/admin/transaction/delete', (req, res) => {
  const { id } = req.body;
  const db = getDB();
  const txn = db.transactions.find((t: any) => t.id === parseInt(id));
  if (txn) {
    const user = db.users.find((u: any) => u.id === txn.userId);
    if (user) {
      user.balance -= txn.amount;
    }
  }
  db.transactions = db.transactions.filter((t: any) => t.id !== parseInt(id));
  saveDB(db);
  res.json({ success: true });
});

// Admin: Update Report Status
app.post('/api/admin/report/status', (req, res) => {
  const { id, status } = req.body;
  const db = getDB();
  const report = db.reports.find((r: any) => r.id === parseInt(id));
  if (report) {
    report.status = status;
  }
  saveDB(db);
  res.json({ success: true });
});

// Admin: Delete Report
app.post('/api/admin/report/delete', (req, res) => {
  const { id } = req.body;
  const db = getDB();
  db.reports = db.reports.filter((r: any) => r.id !== parseInt(id));
  saveDB(db);
  res.json({ success: true });
});

// ── SERVING STATIC ASSETS ──

// Serve CSS from assets/css
app.use('/assets', express.static(path.join(__dirname, 'assets')));

// Map /js requests to assets/js directory (as referenced inside HTML files)
app.use('/js', express.static(path.join(__dirname, 'assets/js')));

// ── ROUTING FOR PHP FILES ──

// Catch any .php or .html requests and route them properly to our root-level PHP files
app.get('/:page.:ext(php|html)', (req, res) => {
  const page = req.params.page;
  const filePath = path.join(__dirname, `${page}.php`);
  if (fs.existsSync(filePath)) {
    const html = renderPhpAsHtml(filePath);
    res.setHeader('Content-Type', 'text/html');
    res.send(html);
  } else {
    res.status(404).send('Page not found');
  }
});

// Serve index.php as fallback for the root path
app.get('/', (req, res) => {
  const filePath = path.join(__dirname, 'index.php');
  if (fs.existsSync(filePath)) {
    const html = renderPhpAsHtml(filePath);
    res.setHeader('Content-Type', 'text/html');
    res.send(html);
  } else {
    res.status(404).send('Main Landing Page not found');
  }
});

// Fallback to index.php for undefined routes
app.get('*', (req, res) => {
  const filePath = path.join(__dirname, 'index.php');
  if (fs.existsSync(filePath)) {
    const html = renderPhpAsHtml(filePath);
    res.setHeader('Content-Type', 'text/html');
    res.send(html);
  } else {
    res.status(404).send('Home Page not found');
  }
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on http://0.0.0.0:${PORT}`);
});
