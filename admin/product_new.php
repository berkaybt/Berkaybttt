<?php
require_once __DIR__ . '/../includes/boot.php';
require_admin();
require_once __DIR__ . '/../includes/header.php';

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
            $stmt = getPDO()->prepare('INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $desc, $price, $image, $stock]);
            $ok = true;
        }
    }
}
?>
<h1>Yeni Ürün</h1>
<?php if ($ok): ?><div class="alert success">Ürün eklendi.</div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Ad<input name="name" required value="<?= h($_POST['name'] ?? '') ?>"></label>
  <label>Fiyat<input type="number" step="0.01" name="price" required value="<?= h($_POST['price'] ?? '') ?>"></label>
  <label>Stok<input type="number" name="stock" value="<?= h($_POST['stock'] ?? '0') ?>"></label>
  <label>Görsel URL<input name="image" placeholder="/public/images/placeholder.svg" value="<?= h($_POST['image'] ?? '') ?>"></label>
  <label>Açıklama<textarea name="description"><?= h($_POST['description'] ?? '') ?></textarea></label>
  <button type="submit">Kaydet</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
