<?php
require __DIR__ . '/auth.php';
admin_require_login();
require __DIR__ . '/ga.php';

$period = $_GET['period'] ?? '30';
$map = ['1' => 1, '7' => 7, '30' => 30, '90' => 90, 'all' => 'all'];
$days = $map[$period] ?? 30;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(ga_fetch($days));
