<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = getDB();
$message = '';
$error = '';

// Ürün ekleme/düzenleme
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category_id = (int)$_POST['category_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($name) || $price <= 0 || $stock_quantity < 0) {
        $error = 'Lütfen tüm gerekli alanları doldurun!';
    } else {
        $image_path = null;
        
        // Resim yükleme
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_path = uploadImage($_FILES['image']);
            if (!$image_path) {
                $error = 'Resim yüklenirken hata oluştu!';
            }
        }
        
        if (!$error) {
            if ($action === 'add') {
                // Yeni ürün ekleme
                $sql = "INSERT INTO products (name, description, price, stock_quantity, category_id, image, featured) 
                        VALUES (:name, :description, :price, :stock_quantity, :category_id, :image, :featured)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':image', $image_path);
            } else {
                // Ürün güncelleme
                if ($image_path) {
                    $sql = "UPDATE products SET name = :name, description = :description, price = :price, 
                            stock_quantity = :stock_quantity, category_id = :category_id, image = :image, featured = :featured 
                            WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':image', $image_path);
                    $stmt->bindParam(':id', $product_id);
                } else {
                    $sql = "UPDATE products SET name = :name, description = :description, price = :price, 
                            stock_quantity = :stock_quantity, category_id = :category_id, featured = :featured 
                            WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id', $product_id);
                }
            }
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock_quantity', $stock_quantity);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':featured', $featured);
            
            if ($stmt->execute()) {
                $message = $action === 'add' ? 'Ürün başarıyla eklendi!' : 'Ürün başarıyla güncellendi!';
            } else {
                $error = 'İşlem sırasında hata oluştu!';
            }
        }
    }
}

// Ürün silme
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);
    if ($stmt->execute()) {
        $message = 'Ürün başarıyla silindi!';
    } else {
        $error = 'Ürün silinirken hata oluştu!';
    }
}

// Düzenlenecek ürün
$edit_product = null;
if (isset($_GET['edit'])) {
    $product_id = (int)$_GET['edit'];
    $edit_product = getProduct($product_id);
}

// Ürünleri getir
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "p.name LIKE :search";
    $params[':search'] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Toplam ürün sayısı
$count_sql = "SELECT COUNT(*) as total FROM products p $where_clause";
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
        $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT :offset, :per_page";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri getir
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: white; margin-bottom: 0.5rem;">
                <i class="fas fa-cog"></i> Admin Panel
            </h2>
            <p style="color: #ccc; font-size: 0.9rem;">Hoş geldin, <?php echo $_SESSION['username']; ?></p>
        </div>
        
        <ul>
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Ürünler</a></li>
            <li><a href="categories.php"><i class="fas fa-list"></i> Kategoriler</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Siparişler</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Kullanıcılar</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Ayarlar</a></li>
            <li style="margin-top: 2rem; border-top: 1px solid #555; padding-top: 1rem;">
                <a href="../index.php"><i class="fas fa-home"></i> Siteye Dön</a>
            </li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1><i class="fas fa-box"></i> Ürün Yönetimi</h1>
            <button onclick="showAddForm()" class="btn">
                <i class="fas fa-plus"></i> Yeni Ürün Ekle
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Ürün Ekleme/Düzenleme Formu -->
        <div id="product-form" style="<?php echo $edit_product || isset($_GET['action']) ? 'display: block;' : 'display: none;'; ?> background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">
                <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus'; ?>"></i> 
                <?php echo $edit_product ? 'Ürün Düzenle' : 'Yeni Ürün Ekle'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <div>
                        <div class="form-group">
                            <label for="name">Ürün Adı *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Açıklama</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="price">Fiyat (₺) *</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                                       value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">Stok Miktarı *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                                       value="<?php echo $edit_product ? $edit_product['stock_quantity'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Kategori</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="0">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <div class="form-group">
                            <label for="image">Ürün Resmi</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <?php if ($edit_product && $edit_product['image']): ?>
                                <div style="margin-top: 1rem;">
                                    <img src="../<?php echo $edit_product['image']; ?>" 
                                         alt="Mevcut resim" 
                                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Mevcut resim</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo ($edit_product && $edit_product['featured']) ? 'checked' : ''; ?>>
                                <span>Öne Çıkan Ürün</span>
                            </label>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Kaydet
                            </button>
                            <button type="button" onclick="hideForm()" class="btn btn-secondary">
                                <i class="fas fa-times"></i> İptal
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Arama ve Filtreleme -->
        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <form method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search">Ürün Ara</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search); ?>" placeholder="Ürün adı...">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" class="form-control">
                        <option value="0">Tüm Kategoriler</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Ara
                </button>
                
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Temizle
                </a>
            </form>
        </div>

        <!-- Ürün Listesi -->
        <div class="table">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Resim</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #666; padding: 2rem;">
                                Ürün bulunamadı
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo $product['image'] ?: 'images/no-image.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td>
                                    <div style="font-weight: bold;"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <?php if ($product['featured']): ?>
                                        <span style="background: #ff4757; color: white; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.7rem;">
                                            Öne Çıkan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?: 'Kategori Yok'); ?></td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td>
                                    <span style="color: <?php echo $product['stock_quantity'] < 5 ? '#ff7675' : '#00b894'; ?>;">
                                        <?php echo $product['stock_quantity']; ?> adet
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span style="background: #00b894; color: white; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem;">
                                            Stokta
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #ff7675; color: white; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem;">
                                            Tükendi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="?edit=<?php echo $product['id']; ?>" class="btn" style="padding: 0.5rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../product.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-secondary" style="padding: 0.5rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?php echo $product['id']; ?>" 
                                           onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')" 
                                           class="btn btn-danger" style="padding: 0.5rem;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <div style="text-align: center; margin-top: 2rem;">
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
    </div>

    <script>
        function showAddForm() {
            document.getElementById('product-form').style.display = 'block';
            document.querySelector('input[name="action"]').value = 'add';
            document.querySelector('input[name="product_id"]')?.remove();
            document.querySelector('form').reset();
            document.querySelector('#product-form h3').innerHTML = '<i class="fas fa-plus"></i> Yeni Ürün Ekle';
        }

        function hideForm() {
            document.getElementById('product-form').style.display = 'none';
            // URL'den edit parametresini kaldır
            const url = new URL(window.location);
            url.searchParams.delete('edit');
            url.searchParams.delete('action');
            window.history.replaceState({}, '', url);
        }

        // Resim önizleme
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'image-preview';
                        preview.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 1rem;';
                        document.getElementById('image').parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        .active {
            background-color: #555 !important;
        }
        
        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0 !important;
            }
            
            #product-form > form > div {
                grid-template-columns: 1fr !important;
            }
            
            .table {
                overflow-x: auto;
            }
        }
    </style>
</body>
</html>