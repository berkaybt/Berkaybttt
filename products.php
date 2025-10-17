<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Sayfalama için değişkenler
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filtreleme
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Kategorileri getir
$categories = getCategories();

// Ürünleri getir
$db = getDB();
$where_conditions = [];
$params = [];

if ($category_id) {
    $where_conditions[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sıralama
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_clause .= "p.price ASC";
        break;
    case 'price_high':
        $order_clause .= "p.price DESC";
        break;
    case 'name':
        $order_clause .= "p.name ASC";
        break;
    default:
        $order_clause .= "p.created_at DESC";
}

// Toplam ürün sayısı
$count_sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_clause";
$count_stmt = $db->prepare($count_sql);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $per_page);

// Ürünleri getir
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_clause $order_clause 
        LIMIT :offset, :per_page";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Seçili kategori bilgisi
$selected_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $selected_category = $cat;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - E-Ticaret</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-shopping-bag"></i> E-Ticaret
                </a>
                
                <div class="user-menu">
                    <?php if (isLoggedIn()): ?>
                        <span>Hoş geldin, <?php echo $_SESSION['username']; ?></span>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Kayıt</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="products.php" class="active"><i class="fas fa-box"></i> Ürünler</a></li>
                    <li><a href="categories.php"><i class="fas fa-list"></i> Kategoriler</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> İletişim</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <!-- Başlık ve Arama -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h1>
                <?php if ($selected_category): ?>
                    <?php echo htmlspecialchars($selected_category['name']); ?> Ürünleri
                <?php elseif ($search): ?>
                    "<?php echo htmlspecialchars($search); ?>" Arama Sonuçları
                <?php else: ?>
                    Tüm Ürünler
                <?php endif; ?>
                <span style="font-size: 1rem; color: #666;">(<?php echo $total_products; ?> ürün)</span>
            </h1>
            
            <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <?php if ($category_id): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                
                <div style="position: relative;">
                    <input type="text" name="search" placeholder="Ürün ara..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           style="padding: 8px 40px 8px 12px; border: 2px solid #ddd; border-radius: 25px; width: 250px;">
                    <button type="submit" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #667eea;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <select name="sort" onchange="this.form.submit()" style="padding: 8px 12px; border: 2px solid #ddd; border-radius: 8px;">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>En Yeni</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Fiyat (Düşük-Yüksek)</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Fiyat (Yüksek-Düşük)</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>İsim (A-Z)</option>
                </select>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
            <!-- Sidebar - Kategoriler -->
            <div>
                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1rem; color: #333;">
                        <i class="fas fa-list"></i> Kategoriler
                    </h3>
                    
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.5rem;">
                            <a href="products.php" style="text-decoration: none; color: <?php echo !$category_id ? '#667eea' : '#666'; ?>; display: block; padding: 8px; border-radius: 5px; transition: all 0.3s;">
                                <i class="fas fa-th"></i> Tüm Ürünler (<?php echo $total_products; ?>)
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="products.php?category=<?php echo $category['id']; ?>" 
                                   style="text-decoration: none; color: <?php echo $category_id == $category['id'] ? '#667eea' : '#666'; ?>; display: block; padding: 8px; border-radius: 5px; transition: all 0.3s;"
                                   onmouseover="this.style.backgroundColor='#f8f9fa'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div>
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <i class="fas fa-search" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                        <h3>Ürün bulunamadı</h3>
                        <p>Arama kriterlerinize uygun ürün bulunamadı.</p>
                        <a href="products.php" class="btn">Tüm Ürünleri Gör</a>
                    </div>
                <?php else: ?>
                    <!-- Ürün Listesi -->
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card fade-in">
                                <?php if ($product['featured']): ?>
                                    <div class="featured-badge">Öne Çıkan</div>
                                <?php endif; ?>
                                
                                <img src="<?php echo $product['image'] ?: 'images/no-image.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image">
                                
                                <div class="product-info">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-description">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div style="color: #888; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                    </div>
                                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                                    
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">
                                            <i class="fas fa-eye"></i> Detay
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-success">
                                                <i class="fas fa-cart-plus"></i> Sepete Ekle
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sayfalama -->
                    <?php if ($total_pages > 1): ?>
                        <div style="text-align: center; margin-top: 3rem;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <?php if ($page > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                       class="btn" style="padding: 8px 12px;">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                       class="btn <?php echo $i === $page ? 'btn-secondary' : ''; ?>" 
                                       style="padding: 8px 12px;">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                       class="btn" style="padding: 8px 12px;">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <p style="margin-top: 1rem; color: #666;">
                                Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?> 
                                (Toplam <?php echo $total_products; ?> ürün)
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 E-Ticaret Sitesi. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script>
        // Sepete ekleme fonksiyonu
        function addToCart(productId) {
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün sepete eklendi!');
                    updateCartCount();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // Sepet sayısını güncelle
        function updateCartCount() {
            fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.cart-count').textContent = data.count;
            });
        }

        // Sayfa yüklendiğinde sepet sayısını güncelle
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isLoggedIn()): ?>
                updateCartCount();
            <?php endif; ?>
            
            // Fade-in animasyonu
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>

    <style>
        .active {
            background-color: rgba(255,255,255,0.2) !important;
        }
        
        @media (max-width: 768px) {
            .container > div:first-child {
                grid-template-columns: 1fr !important;
            }
            
            .container > div:first-child > div:first-child {
                order: 2;
            }
        }
    </style>
</body>
</html>