<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function admin_config() {
  static $config;
  if (!$config) $config = require __DIR__ . '/config.php';
  return $config;
}

function admin_password() {
  $file = __DIR__ . '/password.php';
  if (is_file($file)) return require $file;
  return admin_config()['pass'];
}

function admin_logged_in() {
  return !empty($_SESSION['admin_user']);
}

function admin_require_login() {
  if (!admin_logged_in()) {
    header('Location: login.php');
    exit;
  }
}

function admin_attempt_login($user, $pass) {
  $cfg = admin_config();
  if (hash_equals($cfg['user'], $user) && hash_equals((string) admin_password(), $pass)) {
    $_SESSION['admin_user'] = $cfg['user'];
    $_SESSION['admin_name'] = $cfg['name'];
    return true;
  }
  return false;
}
