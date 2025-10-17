<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::conn();
        $this->pdo->exec(file_get_contents(__DIR__ . '/../../scripts/schema.sql'));
    }

    public function getByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :p)');
        $stmt->execute([
            ':n' => $name,
            ':e' => $email,
            ':p' => $passwordHash,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
