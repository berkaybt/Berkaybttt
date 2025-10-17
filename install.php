<?php
// E-Ticaret Sitesi Kurulum Scripti

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Veritabanı bağlantı testi
if ($step >= 2) {
    $host = $_POST['db_host'] ?? 'localhost';
    $dbname = $_POST['db_name'] ?? 'ecommerce_db';
    $username = $_POST['db_user'] ?? 'root';
    $password = $_POST['db_pass'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Veritabanını oluştur
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Şemayı yükle
        $schema = file_get_contents('database/schema.sql');
        $pdo->exec($schema);
        
        // Örnek verileri yükle
        $sample_data = file_get_contents('database/sample_data.sql');
        $pdo->exec($sample_data);
        
        // Config dosyasını güncelle
        $config_content = "<?php
// Veritabanı bağlantı ayarları
\$host = '$host';
\$dbname = '$dbname';
\$username = '$username';
\$password = '$password';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Veritabanı bağlantı hatası: \" . \$e->getMessage());
}
?>";
        
        file_put_contents('config/database.php', $config_content);
        
        $success = 'Kurulum başarıyla tamamlandı!';
        $step = 3;
        
    } catch (PDOException $e) {
        $error = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticaret Sitesi Kurulum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .install-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .install-content {
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e1e8ed;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: bold;
        }
        .step.active {
            background: #e74c3c;
            color: white;
        }
        .step.completed {
            background: #27ae60;
            color: white;
        }
        .step-line {
            width: 40px;
            height: 2px;
            background: #e1e8ed;
            margin: 0 0.5rem;
            margin-top: 19px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e74c3c;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #c0392b;
        }
        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .requirements {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .requirement i {
            margin-right: 0.5rem;
            width: 20px;
        }
        .requirement.met {
            color: #27ae60;
        }
        .requirement.not-met {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>E-Ticaret Sitesi Kurulum</h1>
            <p>Hoş geldiniz! Kurulumu tamamlamak için aşağıdaki adımları takip edin.</p>
        </div>
        
        <div class="install-content">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'completed' : ($step == 1 ? 'active' : ''); ?>">1</div>
                <div class="step-line"></div>
                <div class="step <?php echo $step >= 2 ? 'completed' : ($step == 2 ? 'active' : ''); ?>">2</div>
                <div class="step-line"></div>
                <div class="step <?php echo $step >= 3 ? 'completed' : ($step == 3 ? 'active' : ''); ?>">3</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>Hata:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Başarılı:</strong> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <!-- Step 1: Requirements Check -->
                <h2>1. Sistem Gereksinimleri</h2>
                <div class="requirements">
                    <?php
                    $requirements = [
                        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
                        'PDO Extension' => extension_loaded('pdo'),
                        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
                        'GD Extension' => extension_loaded('gd'),
                        'config/ directory writable' => is_writable('config/'),
                        'assets/images/products/ directory writable' => is_writable('assets/images/products/'),
                    ];
                    
                    $all_met = true;
                    foreach ($requirements as $req => $met) {
                        $all_met = $all_met && $met;
                        echo '<div class="requirement ' . ($met ? 'met' : 'not-met') . '">';
                        echo '<i class="fas fa-' . ($met ? 'check' : 'times') . '"></i>';
                        echo $req;
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <?php if ($all_met): ?>
                    <a href="?step=2" class="btn">Devam Et</a>
                <?php else: ?>
                    <p class="alert alert-error">Lütfen yukarıdaki gereksinimleri karşılayın ve tekrar deneyin.</p>
                <?php endif; ?>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Database Configuration -->
                <h2>2. Veritabanı Yapılandırması</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">Veritabanı Sunucusu</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Veritabanı Adı</label>
                        <input type="text" id="db_name" name="db_name" value="ecommerce_db" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Kullanıcı Adı</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Şifre</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                    
                    <button type="submit" class="btn">Kurulumu Başlat</button>
                </form>
                
            <?php elseif ($step == 3): ?>
                <!-- Step 3: Installation Complete -->
                <h2>3. Kurulum Tamamlandı!</h2>
                <div class="alert alert-success">
                    <strong>Tebrikler!</strong> E-Ticaret siteniz başarıyla kuruldu.
                </div>
                
                <h3>Varsayılan Hesaplar:</h3>
                <div class="requirements">
                    <div class="requirement met">
                        <i class="fas fa-user-shield"></i>
                        <strong>Admin:</strong> admin@eticaret.com / 123456
                    </div>
                    <div class="requirement met">
                        <i class="fas fa-user"></i>
                        <strong>Müşteri:</strong> ahmet@example.com / 123456
                    </div>
                </div>
                
                <h3>Sonraki Adımlar:</h3>
                <ol>
                    <li>Güvenlik için varsayılan şifreleri değiştirin</li>
                    <li>Site ayarlarını yapılandırın</li>
                    <li>Ürün resimlerini yükleyin</li>
                    <li>SSL sertifikası kurun</li>
                </ol>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="index.php" class="btn" style="margin-right: 1rem;">Siteyi Görüntüle</a>
                    <a href="admin/index.php" class="btn">Admin Paneli</a>
                </div>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <small style="color: #7f8c8d;">
                        Güvenlik için bu kurulum dosyasını silin: <code>install.php</code>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></script>
</body>
</html>