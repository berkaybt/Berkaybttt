<?php
// Bootstrap: sessions, config, db, helpers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development (toggle off in production)
ini_set('display_errors', '1');
error_reporting(E_ALL);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

date_default_timezone_set('Europe/Istanbul');
