<?php
// Basic configuration for database and app settings
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'name' => getenv('DB_NAME') ?: 'ecommerce_db',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '/',
        'currency' => 'TRY',
    ],
];
