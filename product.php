<?php
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$product = $id > 0 ? get_product_by_id($id) : null;
if (!$product) {
    echo '<h1>Ürün bulunamadı</h1>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>
<div class="product-detail">
  <div class="image">
    <img src="<?= h($product['image'] ?: '/public/images/placeholder.svg') ?>" alt="<?= h($product['name']) ?>">
  </div>
  <div class="info">
    <h1><?= h($product['name']) ?></h1>
    <div class="price"><?= format_price((float)$product['price']) ?></div>
    <p><?= nl2br(h($product['description'])) ?></p>
    <form action="/cart_add.php" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
      <label>Adet
        <input type="number" min="1" value="1" name="quantity">
      </label>
      <button type="submit">Sepete Ekle</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
