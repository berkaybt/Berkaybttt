<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;

class PageController
{
    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository();
    }

    public function home(): string
    {
        $featured = $this->products->getAll(limit: 6);
        ob_start();
        include __DIR__ . '/../views/pages/home.php';
        return (string) ob_get_clean();
    }

    public function products(): string
    {
        $items = $this->products->getAll();
        ob_start();
        include __DIR__ . '/../views/pages/products.php';
        return (string) ob_get_clean();
    }

    public function product(): string
    {
        $slug = $_GET['slug'] ?? '';
        $product = $this->products->getBySlug($slug);
        if (!$product) {
            http_response_code(404);
            return 'Ürün bulunamadı';
        }
        ob_start();
        include __DIR__ . '/../views/pages/product.php';
        return (string) ob_get_clean();
    }
}
