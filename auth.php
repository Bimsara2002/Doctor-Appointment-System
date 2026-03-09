<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}


function require_login() {
  if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
  }
}


function require_role($role) {
  require_login();
  if (!isset($_SESSION["user"]["role"]) || $_SESSION["user"]["role"] !== $role) {
    header("Location: dashboard.php");
    exit;
  }
}


function is_role($role) {
  return isset($_SESSION["user"]["role"]) && $_SESSION["user"]["role"] === $role;
}