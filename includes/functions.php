<?php

function redirect(string $path): void {
    if (!headers_sent()) {
        header('Location: ' . $path);
    }
    exit;
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool {
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_price(float $value): string {
    return number_format($value, 2, ',', '.') . ' TL';
}

// Auth helpers (session-based)
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return current_user() !== null; }
function is_admin(): bool { return (int)($_SESSION['user']['is_admin'] ?? 0) === 1; }
function require_login(string $redirectTo = '/login.php'): void { if (!is_logged_in()) redirect($redirectTo); }
function require_admin(): void { if (!is_admin()) redirect('/login.php'); }

// Product queries
function get_products(int $limit = 0): array {
    $pdo = getPDO();
    $sql = 'SELECT id, name, description, price, image, stock FROM products ORDER BY created_at DESC';
    if ($limit > 0) { $sql .= ' LIMIT ' . (int)$limit; }
    return $pdo->query($sql)->fetchAll();
}

function get_product_by_id(int $id): ?array {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, name, description, price, image, stock FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Cart helpers (session)
function get_cart(): array {
    return $_SESSION['cart'] ?? [];
}

function set_cart(array $cart): void {
    $_SESSION['cart'] = $cart;
}

function add_to_cart(int $productId, int $quantity = 1): void {
    $cart = get_cart();
    if (!isset($cart[$productId])) { $cart[$productId] = 0; }
    $cart[$productId] += max(1, $quantity);
    set_cart($cart);
}

function update_cart(int $productId, int $quantity): void {
    $cart = get_cart();
    if ($quantity <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }
    set_cart($cart);
}

function remove_from_cart(int $productId): void {
    $cart = get_cart();
    unset($cart[$productId]);
    set_cart($cart);
}

function get_cart_items_detailed(): array {
    $cart = get_cart();
    if (empty($cart)) { return []; }
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = getPDO()->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $map = [];
    foreach ($rows as $row) { $map[$row['id']] = $row; }
    $items = [];
    foreach ($cart as $pid => $qty) {
        if (!isset($map[$pid])) { continue; }
        $p = $map[$pid];
        $items[] = [
            'product' => $p,
            'quantity' => $qty,
            'line_total' => $qty * (float)$p['price'],
        ];
    }
    return $items;
}

function get_cart_count(): int {
    return array_sum(get_cart());
}

function get_cart_total(): float {
    $total = 0.0;
    foreach (get_cart_items_detailed() as $item) {
        $total += $item['line_total'];
    }
    return $total;
}

// Order creation
function create_order(int $userId): int {
    $items = get_cart_items_detailed();
    if (empty($items)) {
        throw new RuntimeException('Sepet boş.');
    }
    $pdo = getPDO();
    $pdo->beginTransaction();
    try {
        $total = get_cart_total();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$userId, $total]);
        $orderId = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
        $dec = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        foreach ($items as $item) {
            $p = $item['product'];
            $qty = (int)$item['quantity'];
            $ins->execute([$orderId, (int)$p['id'], $qty, (float)$p['price']]);
            $dec->execute([$qty, (int)$p['id']]);
        }
        $pdo->commit();
        set_cart([]);
        return $orderId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
