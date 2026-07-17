<?php
require_once 'db_conn.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image" href="images/logo.png">
  <title>Top Up Successful – PocketGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/success-style.css">
</head>
<body>

<div class="page page-active" id="page-success">
  <div class="success-screen">
    <div class="success-icon"><i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i></div>
    <h2>Top Up Successful!</h2>
    <p>Your child's school card has been topped up instantly.</p>
    <div class="receipt-box">
      <div class="receipt-row"><span style="color:#666;">Amount</span><span style="font-weight:700;color:#C8102E;" id="s-amount">RM 0.00</span></div>
      <div class="receipt-row"><span style="color:#666;">Method</span><span style="font-weight:600;" id="s-method">—</span></div>
      <div class="receipt-row"><span style="color:#666;">Reference</span><span style="font-weight:600;font-size:.8rem;" id="s-ref">PG-—</span></div>
      <div class="receipt-row"><span style="color:#666;">New Card Balance</span><span style="font-weight:700;" id="s-balance">RM 0.00</span></div>
    </div>
    <button class="btn btn-primary btn-full" style="max-width:360px;" onclick="location.href='dashboard.php'">Back to Dashboard</button>
  </div>
</div>

<div class="toast" id="toast-el"></div>

<script src="assets/js/store.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/success.js"></script>
</body>
</html>
