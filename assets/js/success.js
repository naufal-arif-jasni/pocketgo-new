// success.js
Store.init().then(() => {
  requireParentAuth();
  const r = Store.lastReceipt;
  if (r) {
    document.getElementById('s-amount').textContent = 'RM ' + r.amount.toFixed(2);
    document.getElementById('s-method').textContent = r.method;
    document.getElementById('s-ref').textContent = r.ref;
    document.getElementById('s-balance').textContent = 'RM ' + r.balance.toFixed(2);
  } else {
    location.href = 'dashboard.php';
  }
});
