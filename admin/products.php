<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Ürün işlemleri
if($_POST) {
    $action = $_POST['action'];
    
    switch($action) {
        case 'add':
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $stock_quantity = (int)$_POST['stock_quantity'];
            
            $sql = "INSERT INTO products (name, description, price, category_id, stock_quantity) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $category_id, $stock_quantity]);
            break;
            
        case 'update':
            $id = (int)$_POST['id'];
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $stock_quantity = (int)$_POST['stock_quantity'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $sql = "UPDATE products SET name=?, description=?, price=?, category_id=?, stock_quantity=?, is_active=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $category_id, $stock_quantity, $is_active, $id]);
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            $sql = "DELETE FROM products WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            break;
    }
    
    header('Location: products.php');
    exit;
}

// Ürünleri getir
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

// Kategorileri getir
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
        }
        
        .admin-sidebar h2 {
            padding: 0 1rem 2rem;
            border-bottom: 1px solid #34495e;
            margin-bottom: 1rem;
        }
        
        .admin-sidebar ul {
            list-style: none;
        }
        
        .admin-sidebar li {
            margin-bottom: 0.5rem;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 1rem;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: #34495e;
        }
        
        .admin-content {
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .btn-add {
            background: #27ae60;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn-add:hover {
            background: #229954;
        }
        
        .products-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .status-active {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .close {
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                display: none;
            }
            
            .products-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Ürünler</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> Kategoriler</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Siparişler</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Kullanıcılar</a></li>
                <li><a href="../index.php"><i class="fas fa-home"></i> Siteye Dön</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="page-header">
                <h1>Ürün Yönetimi</h1>
                <a href="#" class="btn-add" onclick="openModal('add')">
                    <i class="fas fa-plus"></i> Yeni Ürün Ekle
                </a>
            </div>
            
            <div class="products-table">
                <table>
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
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td>
                                <img src="../assets/images/products/<?php echo $product['image'] ?: 'placeholder.svg'; ?>" 
                                     alt="<?php echo $product['name']; ?>" class="product-image">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td>
                                <span class="<?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $product['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="btn-edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                    <i class="fas fa-edit"></i> Düzenle
                                </a>
                                <a href="#" class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i> Sil
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Ürün Ekleme/Düzenleme Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Yeni Ürün Ekle</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-group">
                    <label for="name">Ürün Adı</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Fiyat (₺)</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Seçiniz</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stock_quantity">Stok Miktarı</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        Aktif
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Kaydet</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">İptal</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(action) {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('formAction').value = action;
            document.getElementById('modalTitle').textContent = action === 'add' ? 'Yeni Ürün Ekle' : 'Ürün Düzenle';
            
            if(action === 'add') {
                document.getElementById('productForm').reset();
            }
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        function editProduct(product) {
            openModal('update');
            document.getElementById('productId').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('category_id').value = product.category_id;
            document.getElementById('stock_quantity').value = product.stock_quantity;
            document.getElementById('is_active').checked = product.is_active == 1;
        }
        
        function deleteProduct(id) {
            if(confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Modal dışına tıklandığında kapat
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>