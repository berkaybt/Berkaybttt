<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class CheckoutController
{
    private OrderRepository $orders;
    private ProductRepository $products;

    public function __construct()
    {
        $this->orders = new OrderRepository();
        $this->products = new ProductRepository();
    }

    public function checkoutForm(): string
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . base_url('/login'));
            exit;
        }
        if (empty($_SESSION['cart'])) {
            header('Location: ' . base_url('/cart'));
            exit;
        }
        ob_start();
        include __DIR__ . '/../views/pages/checkout.php';
        return (string) ob_get_clean();
    }

    public function placeOrder(): string
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . base_url('/login'));
            exit;
        }
        if (empty($_SESSION['cart'])) {
            header('Location: ' . base_url('/cart'));
            exit;
        }
        $userId = (int)$_SESSION['user']['id'];

        $items = [];
        $totalCents = 0;
        foreach ($_SESSION['cart'] as $slug => $qty) {
            $product = $this->products->getBySlug($slug);
            if ($product) {
                $price = (int)$product['price_cents'];
                $items[] = [
                    'product_id' => (int)$product['id'],
                    'quantity' => (int)$qty,
                    'price_cents' => $price,
                ];
                $totalCents += $price * $qty;
            }
        }

        $orderId = $this->orders->createOrder($userId, $totalCents, $items);
        $_SESSION['cart'] = [];
        header('Location: ' . base_url('/order/success?order_id=' . $orderId));
        exit;
    }

    public function success(): string
    {
        $orderId = (int)($_GET['order_id'] ?? 0);
        ob_start();
        include __DIR__ . '/../views/pages/order_success.php';
        return (string) ob_get_clean();
    }
}
