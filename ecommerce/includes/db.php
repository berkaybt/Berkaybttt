<?php
$config = require __DIR__ . '/config.php';

function getPdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = $GLOBALS['config']['db'] ?? $config['db'] ?? [];
    if (empty($cfg)) {
        $configLocal = require __DIR__ . '/config.php';
        $cfg = $configLocal['db'];
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
    );
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
    return $pdo;
}
