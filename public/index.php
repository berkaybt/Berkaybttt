<?php
// Simple front controller and router
declare(strict_types=1);

session_start();

// Support PHP built-in server for static files
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false; // Let the server serve the file directly
    }
}

// Autoload minimal (flat include map)
require_once __DIR__ . '/../src/core/config.php';
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/controllers/PageController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/CartController.php';
require_once __DIR__ . '/../src/controllers/CheckoutController.php';
require_once __DIR__ . '/../src/repositories/ProductRepository.php';
require_once __DIR__ . '/../src/repositories/UserRepository.php';
require_once __DIR__ . '/../src/repositories/OrderRepository.php';

use App\Controllers\PageController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Strip base if deployed in subfolder (optional)
// Polyfill for PHP < 8 str_starts_with
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        if ($needle === '') { return true; }
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

$base = rtrim(BASE_PATH, '/');
if ($base && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'GET' => [
        '/' => [PageController::class, 'home'],
        '/products' => [PageController::class, 'products'],
        '/product' => [PageController::class, 'product'],
        '/cart' => [CartController::class, 'viewCart'],
        '/login' => [AuthController::class, 'loginForm'],
        '/register' => [AuthController::class, 'registerForm'],
        '/logout' => [AuthController::class, 'logout'],
        '/checkout' => [CheckoutController::class, 'checkoutForm'],
        '/order/success' => [CheckoutController::class, 'success'],
    ],
    'POST' => [
        '/cart/add' => [CartController::class, 'addToCart'],
        '/cart/update' => [CartController::class, 'updateCart'],
        '/cart/clear' => [CartController::class, 'clearCart'],
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/checkout' => [CheckoutController::class, 'placeOrder'],
    ],
];

$handler = $routes[$method][$path] ?? null;
if (!$handler) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[$controllerClass, $action] = $handler;
$controller = new $controllerClass();

// Basic error handling
try {
    echo $controller->$action();
} catch (Throwable $e) {
    if (APP_DEBUG) {
        http_response_code(500);
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
    }
}
