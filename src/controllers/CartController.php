<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;

class CartController
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function viewCart(): string
    {
        $cartItems = [];
        $totalCents = 0;
        foreach ($_SESSION['cart'] as $slug => $qty) {
            $product = $this->products->getBySlug($slug);
            if ($product) {
                $lineTotal = $product['price_cents'] * $qty;
                $totalCents += $lineTotal;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'line_total_cents' => $lineTotal,
                ];
            }
        }
        ob_start();
        include __DIR__ . '/../views/pages/cart.php';
        return (string) ob_get_clean();
    }

    public function addToCart(): string
    {
        $slug = $_POST['slug'] ?? '';
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if (!$slug) {
            header('Location: ' . base_url('/products'));
            exit;
        }
        $_SESSION['cart'][$slug] = ($_SESSION['cart'][$slug] ?? 0) + $qty;
        header('Location: ' . base_url('/cart'));
        exit;
    }

    public function updateCart(): string
    {
        foreach (($_POST['qty'] ?? []) as $slug => $qty) {
            $qty = max(0, (int)$qty);
            if ($qty === 0) {
                unset($_SESSION['cart'][$slug]);
            } else {
                $_SESSION['cart'][$slug] = $qty;
            }
        }
        header('Location: ' . base_url('/cart'));
        exit;
    }

    public function clearCart(): string
    {
        $_SESSION['cart'] = [];
        header('Location: ' . base_url('/cart'));
        exit;
    }
}
