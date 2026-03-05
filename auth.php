<?php
// auth.php - session + access guards

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Must be logged in
function require_login() {
  if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
  }
}

// Must be a specific role: 'admin' or 'staff'
function require_role($role) {
  require_login();
  if (!isset($_SESSION["user"]["role"]) || $_SESSION["user"]["role"] !== $role) {
    header("Location: dashboard.php");
    exit;
  }
}

// Optional helper: check role quickly
function is_role($role) {
  return isset($_SESSION["user"]["role"]) && $_SESSION["user"]["role"] === $role;
}