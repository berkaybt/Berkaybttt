<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = DB_HOST;
    $port = DB_PORT;
    $db   = DB_NAME;
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>Please create the database and update <code>includes/config.php</code>.</p>';
        exit;
    }

    return $pdo;
}
