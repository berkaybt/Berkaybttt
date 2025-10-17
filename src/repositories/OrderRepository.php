<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;
use PDO;

class OrderRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::conn();
        $this->pdo->exec(file_get_contents(__DIR__ . '/../../scripts/schema.sql'));
    }

    public function createOrder(int $userId, int $totalCents, array $items): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('INSERT INTO orders (user_id, total_cents) VALUES (:u, :t)');
            $stmt->execute([':u' => $userId, ':t' => $totalCents]);
            $orderId = (int)$this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_cents) VALUES (:o, :p, :q, :c)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    ':o' => $orderId,
                    ':p' => $item['product_id'],
                    ':q' => $item['quantity'],
                    ':c' => $item['price_cents'],
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
