<?php
// Kurulum scripti
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if($_POST) {
    switch($step) {
        case 2:
            // Veritabanı bağlantı testi
            $host = $_POST['host'];
            $dbname = $_POST['dbname'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Veritabanı ayarlarını kaydet
                $config = "<?php
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
                
                file_put_contents('config/database.php', $config);
                header('Location: install.php?step=3');
                exit;
            } catch(PDOException $e) {
                $error = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Veritabanı tablolarını oluştur
            $host = $_POST['host'];
            $dbname = $_POST['dbname'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = file_get_contents('database.sql');
                $pdo->exec($sql);
                
                $success = 'Veritabanı başarıyla oluşturuldu!';
                header('Location: install.php?step=4');
                exit;
            } catch(PDOException $e) {
                $error = 'Veritabanı oluşturma hatası: ' . $e->getMessage();
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticaret Kurulum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .install-container {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .install-header h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
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
            background: #ddd;
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
        
        .btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        
        .requirements {
            text-align: left;
            margin-bottom: 2rem;
        }
        
        .requirements ul {
            list-style: none;
            padding: 0;
        }
        
        .requirements li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .requirements li i {
            margin-right: 0.5rem;
        }
        
        .success {
            color: #27ae60;
        }
        
        .error {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1><i class="fas fa-shopping-bag"></i> E-Ticaret Kurulum</h1>
            <p>Web sitenizi kurmak için aşağıdaki adımları takip edin</p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
        </div>
        
        <?php if($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php switch($step): case 1: ?>
            <h2>Gereksinimler</h2>
            <div class="requirements">
                <ul>
                    <li><i class="fas fa-check"></i> PHP 7.4 veya üzeri</li>
                    <li><i class="fas fa-check"></i> MySQL 5.7 veya üzeri</li>
                    <li><i class="fas fa-check"></i> Web sunucusu (Apache/Nginx)</li>
                    <li><i class="fas fa-check"></i> PDO MySQL extension</li>
                </ul>
            </div>
            <a href="install.php?step=2" class="btn btn-primary">Devam Et</a>
            
        <?php break; case 2: ?>
            <h2>Veritabanı Ayarları</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="host">Sunucu Adresi</label>
                    <input type="text" id="host" name="host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="dbname">Veritabanı Adı</label>
                    <input type="text" id="dbname" name="dbname" value="ecommerce_db" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password">
                </div>
                
                <button type="submit" class="btn btn-primary">Bağlantıyı Test Et</button>
            </form>
            
        <?php break; case 3: ?>
            <h2>Veritabanı Oluşturma</h2>
            <p>Veritabanı tabloları oluşturuluyor...</p>
            <form method="POST">
                <input type="hidden" name="host" value="<?php echo $_POST['host'] ?? ''; ?>">
                <input type="hidden" name="dbname" value="<?php echo $_POST['dbname'] ?? ''; ?>">
                <input type="hidden" name="username" value="<?php echo $_POST['username'] ?? ''; ?>">
                <input type="hidden" name="password" value="<?php echo $_POST['password'] ?? ''; ?>">
                <button type="submit" class="btn btn-primary">Tabloları Oluştur</button>
            </form>
            
        <?php break; case 4: ?>
            <h2>Kurulum Tamamlandı!</h2>
            <div class="requirements">
                <ul>
                    <li><i class="fas fa-check"></i> Veritabanı bağlantısı başarılı</li>
                    <li><i class="fas fa-check"></i> Tablolar oluşturuldu</li>
                    <li><i class="fas fa-check"></i> Örnek veriler eklendi</li>
                </ul>
            </div>
            
            <h3>Admin Bilgileri</h3>
            <p><strong>Kullanıcı Adı:</strong> admin</p>
            <p><strong>Şifre:</strong> admin123</p>
            
            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn btn-primary">Siteye Git</a>
                <a href="admin/login.php" class="btn btn-secondary">Admin Paneli</a>
            </div>
            
        <?php break; endswitch; ?>
    </div>
</body>
</html>