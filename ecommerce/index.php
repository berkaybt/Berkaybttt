<?php require __DIR__ . '/includes/header.php'; ?>
<h1>Ürünler</h1>
<div class="grid">
<?php foreach (getAllProducts() as $p): ?>
  <div class="card">
    <a href="product.php?id=<?= (int)$p['id'] ?>">
      <?php if (!empty($p['image'])): ?>
        <img src="assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <?php else: ?>
        <img src="assets/images/placeholder.svg" alt="Görsel">
      <?php endif; ?>
    </a>
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p><?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 80, '…', 'UTF-8')) ?></p>
    <div class="price"><?= formatCurrency((float)$p['price']) ?></div>
    <div style="margin-top:8px;display:flex;gap:8px;">
      <a class="button" href="product.php?id=<?= (int)$p['id'] ?>">İncele</a>
      <form method="post" action="actions/add_to_cart.php" style="margin:0;">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="quantity" value="1">
        <button class="button secondary" type="submit">Sepete Ekle</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
