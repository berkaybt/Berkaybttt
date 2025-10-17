<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;
use PDO;

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::conn();
        $this->ensureInitialized();
    }

    private function ensureInitialized(): void
    {
        // Create tables on first run and seed data if empty
        $this->pdo->exec(file_get_contents(__DIR__ . '/../../scripts/schema.sql'));
        $count = (int)$this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        if ($count === 0) {
            $this->pdo->exec(file_get_contents(__DIR__ . '/../../scripts/seed.sql'));
        }
    }

    public function getAll(?int $limit = null): array
    {
        $sql = 'SELECT * FROM products ORDER BY id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
