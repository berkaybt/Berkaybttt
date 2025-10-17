<?php
require_once __DIR__ . '/../includes/boot.php';
require_admin();
require_once __DIR__ . '/../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$pdo = getPDO();
$product = null;
if ($id > 0) {
  $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
  $stmt->execute([$id]);
  $product = $stmt->fetch();
}
if (!$product) { echo '<p>Ürün bulunamadı.</p>'; require_once __DIR__ . '/../includes/footer.php'; exit; }

$error = null; $ok = false;
if (is_post()) {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Geçersiz istek';
    } else {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $desc = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        if ($name === '' || $price <= 0) {
            $error = 'Ad ve fiyat zorunludur.';
        } else {
            $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, image=?, stock=? WHERE id = ?');
            $stmt->execute([$name, $desc, $price, $image, $stock, $id]);
            $ok = true;
            // refresh
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $product = $stmt->fetch();
        }
    }
}
?>
<h1>Ürün Düzenle</h1>
<?php if ($ok): ?><div class="alert success">Güncellendi.</div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Ad<input name="name" required value="<?= h($product['name']) ?>"></label>
  <label>Fiyat<input type="number" step="0.01" name="price" required value="<?= h($product['price']) ?>"></label>
  <label>Stok<input type="number" name="stock" value="<?= h($product['stock']) ?>"></label>
  <label>Görsel URL<input name="image" value="<?= h($product['image']) ?>"></label>
  <label>Açıklama<textarea name="description"><?= h($product['description']) ?></textarea></label>
  <button type="submit">Kaydet</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
