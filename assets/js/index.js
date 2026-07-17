// index.js - Landing Page Logic
Store.init().then(() => {
  // If already logged in, redirect to respective dashboard
  if (Store.loggedIn) {
    if (Store.isAdmin) {
      location.href = 'admin-dashboard.php';
    } else {
      location.href = 'dashboard.php';
    }
  }
});
