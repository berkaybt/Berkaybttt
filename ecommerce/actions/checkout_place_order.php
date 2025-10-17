<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$customer = [
  'name' => trim($_POST['name'] ?? ''),
  'email' => trim($_POST['email'] ?? ''),
  'address' => trim($_POST['address'] ?? ''),
  'city' => trim($_POST['city'] ?? ''),
  'zip' => trim($_POST['zip'] ?? ''),
];

$orderId = placeOrder($customer);
if ($orderId) {
    $base = $config['app']['base_url'] ?? '/';
    header('Location: ' . rtrim($base, '/') . '/order_success.php?id=' . $orderId);
    exit;
}

$base = $config['app']['base_url'] ?? '/';
header('Location: ' . rtrim($base, '/') . '/checkout.php');
exit;
