<?php
declare(strict_types=1);

// Base configuration
const APP_NAME = 'MyShop';
const APP_DEBUG = true; // set false in production

// If deploying in subfolder, set as '/subfolder'. For root, use ''
const BASE_PATH = '';

// Database (SQLite by default for easy setup)
const DB_DSN = 'sqlite:' . __DIR__ . '/../../data/app.sqlite';
const DB_USER = null; // not used for sqlite
const DB_PASS = null; // not used for sqlite

function base_url(string $path = ''): string {
    $base = rtrim(BASE_PATH, '/');
    return $base . '/' . ltrim($path, '/');
}
