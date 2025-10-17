<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id > 0) {
    removeFromCart($id);
}
$base = $config['app']['base_url'] ?? '/';
header('Location: ' . rtrim($base, '/') . '/cart.php');
exit;
