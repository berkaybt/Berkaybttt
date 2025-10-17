<?php
// Update these values to match your MySQL setup or set environment variables
// DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'ecommerce_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// App settings
define('APP_NAME', 'MyShop');

