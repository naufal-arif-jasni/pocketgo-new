<?php
// auth.php - PHP Session Authentication Guards
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function requireParent() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
}
?>
