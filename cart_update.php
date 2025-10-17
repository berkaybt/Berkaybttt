<?php
require_once __DIR__ . '/includes/boot.php';
if (!is_post() || !verify_csrf($_POST['csrf'] ?? null)) { redirect('/cart.php'); }
$productId = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['quantity'] ?? 0);
if ($productId > 0) { update_cart($productId, $qty); }
redirect('/cart.php');
