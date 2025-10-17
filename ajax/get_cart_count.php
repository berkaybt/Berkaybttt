<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$count = getCartItemCount($_SESSION['user_id']);
echo json_encode(['count' => $count]);
?>