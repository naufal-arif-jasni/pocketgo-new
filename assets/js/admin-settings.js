// admin-settings.js
Store.init().then(() => {
  requireAdminAuth();
});
