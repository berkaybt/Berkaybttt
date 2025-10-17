<?php

function formatCurrency(float $amount, string $currency = 'TRY'): string {
    $symbol = $currency === 'TRY' ? '₺' : $currency;
    return $symbol . number_format($amount, 2, ',', '.');
}

function getAllProducts(): array {
    try {
        $pdo = getPdo();
        $stmt = $pdo->query('SELECT id, name, description, price, image FROM products ORDER BY id DESC');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function getProductById(int $id): ?array {
    try {
        $pdo = getPdo();
        $stmt = $pdo->prepare('SELECT id, name, description, price, image FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function ensureCart(): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function getCart(): array {
    ensureCart();
    return $_SESSION['cart'];
}

function addToCart(int $productId, int $quantity): void {
    ensureCart();
    if ($quantity < 1) { $quantity = 1; }
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += $quantity;
}

function updateCartQuantities(array $quantities): void {
    ensureCart();
    foreach ($quantities as $productId => $qty) {
        $pid = (int)$productId;
        $quantity = max(0, (int)$qty);
        if ($quantity === 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $quantity;
        }
    }
}

function removeFromCart(int $productId): void {
    ensureCart();
    unset($_SESSION['cart'][$productId]);
}

function getCartDetailed(): array {
    ensureCart();
    $items = [];
    $total = 0.0;
    foreach ($_SESSION['cart'] as $productId => $qty) {
        $product = getProductById((int)$productId);
        if (!$product) { continue; }
        $lineTotal = (float)$product['price'] * (int)$qty;
        $items[] = [
            'product' => $product,
            'quantity' => (int)$qty,
            'line_total' => $lineTotal,
        ];
        $total += $lineTotal;
    }
    return ['items' => $items, 'total' => $total];
}

function placeOrder(array $customer): ?int {
    // $customer: ['name','email','address','city','zip']
    $cart = getCartDetailed();
    if (empty($cart['items'])) {
        return null;
    }
    $pdo = getPdo();
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO orders (customer_name, customer_email, customer_address, customer_city, customer_zip, total_amount, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $customer['name'] ?? '',
            $customer['email'] ?? '',
            $customer['address'] ?? '',
            $customer['city'] ?? '',
            $customer['zip'] ?? '',
            $cart['total'],
        ]);
        $orderId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
        foreach ($cart['items'] as $item) {
            $itemStmt->execute([
                $orderId,
                $item['product']['id'],
                $item['quantity'],
                $item['product']['price'],
            ]);
        }
        $pdo->commit();
        // Clear cart after successful order
        $_SESSION['cart'] = [];
        return $orderId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return null;
    }
}
