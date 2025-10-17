<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Sayfalama ve filtreleme parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Ürünleri getir
$where_conditions = ["status = 'active'"];
$params = [];

if ($category_id) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($min_price !== null) {
    $where_conditions[] = "price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null) {
    $where_conditions[] = "price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

// Sıralama
$order_by = "created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "price ASC";
        break;
    case 'price_high':
        $order_by = "price DESC";
        break;
    case 'name':
        $order_by = "name ASC";
        break;
    case 'newest':
    default:
        $order_by = "created_at DESC";
        break;
}

// Toplam ürün sayısını al
$count_sql = "SELECT COUNT(*) as total FROM products WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];
$total_pages = ceil($total_products / $limit);

// Ürünleri getir
$sql = "SELECT * FROM products WHERE $where_clause ORDER BY $order_by LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Kategorileri getir
$categories = getAllCategories($pdo);

// Fiyat aralığını belirle
$price_sql = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE status = 'active'";
$price_stmt = $pdo->prepare($price_sql);
$price_stmt->execute();
$price_range = $price_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - E-Ticaret Sitesi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="products-page">
        <div class="container">
            <div class="page-header">
                <h1>Ürünler</h1>
                <p><?php echo $total_products; ?> ürün bulundu</p>
            </div>
            
            <div class="products-layout">
                <!-- Filtreler -->
                <aside class="filters">
                    <div class="filter-section">
                        <h3>Kategoriler</h3>
                        <ul class="category-list">
                            <li><a href="products.php" class="<?php echo !$category_id ? 'active' : ''; ?>">Tümü</a></li>
                            <?php foreach($categories as $category): ?>
                            <li>
                                <a href="products.php?category=<?php echo $category['id']; ?>" 
                                   class="<?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="filter-section">
                        <h3>Fiyat Aralığı</h3>
                        <form method="GET" class="price-filter">
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                            
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="<?php echo $min_price; ?>" 
                                       min="<?php echo $price_range['min_price']; ?>" 
                                       max="<?php echo $price_range['max_price']; ?>">
                                <span>-</span>
                                <input type="number" name="max_price" placeholder="Max" 
                                       value="<?php echo $max_price; ?>" 
                                       min="<?php echo $price_range['min_price']; ?>" 
                                       max="<?php echo $price_range['max_price']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Filtrele</button>
                        </form>
                    </div>
                    
                    <div class="filter-section">
                        <h3>Markalar</h3>
                        <div class="brand-list">
                            <label><input type="checkbox" name="brand" value="apple"> Apple</label>
                            <label><input type="checkbox" name="brand" value="samsung"> Samsung</label>
                            <label><input type="checkbox" name="brand" value="nike"> Nike</label>
                            <label><input type="checkbox" name="brand" value="adidas"> Adidas</label>
                            <label><input type="checkbox" name="brand" value="ikea"> IKEA</label>
                        </div>
                    </div>
                </aside>
                
                <!-- Ürünler -->
                <div class="products-content">
                    <!-- Sıralama ve Görünüm -->
                    <div class="products-toolbar">
                        <div class="sort-options">
                            <label for="sort">Sırala:</label>
                            <select id="sort" onchange="changeSort()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>En Yeni</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Fiyat (Düşük-Yüksek)</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Fiyat (Yüksek-Düşük)</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>İsim (A-Z)</option>
                            </select>
                        </div>
                        
                        <div class="view-options">
                            <button class="view-btn active" data-view="grid"><i class="fas fa-th"></i></button>
                            <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
                        </div>
                    </div>
                    
                    <!-- Ürün Listesi -->
                    <div class="products-grid" id="products-grid">
                        <?php if(empty($products)): ?>
                            <div class="no-products">
                                <i class="fas fa-search"></i>
                                <h3>Ürün bulunamadı</h3>
                                <p>Arama kriterlerinize uygun ürün bulunamadı. Filtreleri değiştirmeyi deneyin.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="assets/images/products/<?php echo $product['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-overlay">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                            Detayları Gör
                                        </a>
                                    </div>
                                    <?php if($product['sale_price']): ?>
                                    <div class="sale-badge">
                                        %<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?> İndirim
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description"><?php echo htmlspecialchars($product['short_description']); ?></p>
                                    <div class="product-price">
                                        <?php if($product['sale_price']): ?>
                                            <span class="sale-price">₺<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <span class="original-price">₺<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="price">₺<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart"></i> Sepete Ekle
                                        </button>
                                        <button class="btn btn-outline wishlist-btn" data-id="<?php echo $product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sayfalama -->
                    <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i> Önceki
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                                Sonraki <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/products.js"></script>
</body>
</html>