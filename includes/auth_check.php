<?php
/**
 * Authentication Middleware
 * Include this at the top of every protected page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
    header('Location: ' . str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) . 'auth/login.php');
    exit;
}
