<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$quantities = $_POST['qty'] ?? [];
updateCartQuantities($quantities);
$base = $config['app']['base_url'] ?? '/';
header('Location: ' . rtrim($base, '/') . '/cart.php');
exit;
