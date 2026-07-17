// admin-transactions.js
Store.init().then(() => {
  requireAdminAuth();
  renderAdminTables();
});
