// admin-users.js
Store.init().then(() => {
  requireAdminAuth();
  renderAdminTables();
});
