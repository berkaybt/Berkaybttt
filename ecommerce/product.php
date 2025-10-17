<?php require __DIR__ . '/includes/header.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $id > 0 ? getProductById($id) : null;
if (!$product) {
    echo '<div class="alert">Ürün bulunamadı.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
?>
<div class="product">
  <div>
    <?php if (!empty($product['image'])): ?>
      <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    <?php else: ?>
      <img src="assets/images/placeholder.svg" alt="Görsel">
    <?php endif; ?>
  </div>
  <div>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>
    <div class="price" style="font-size:24px; margin: 12px 0;">
      <?= formatCurrency((float)$product['price']) ?>
    </div>
    <form method="post" action="actions/add_to_cart.php" class="form" style="padding:12px;">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
      <label for="qty">Adet</label>
      <input id="qty" type="number" name="quantity" min="1" value="1" style="max-width:140px;">
      <div class="actions">
        <button class="button" type="submit">Sepete Ekle</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
