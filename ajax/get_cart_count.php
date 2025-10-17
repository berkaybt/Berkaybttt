<?php
session_start();

header('Content-Type: application/json');

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

echo json_encode([
    'success' => true,
    'count' => $cart_count
]);
?>