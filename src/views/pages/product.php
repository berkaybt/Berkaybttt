<?php include __DIR__ . '/../partials/header.php'; ?>
<article class="product-detail">
  <div class="media">
    <img src="<?= base_url('/assets/images/' . $product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
  </div>
  <div class="info">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p class="price">₺<?= number_format($product['price_cents'] / 100, 2, ',', '.') ?></p>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <form method="post" action="<?= base_url('/cart/add') ?>">
      <input type="hidden" name="slug" value="<?= htmlspecialchars($product['slug']) ?>">
      <label>Adet
        <input type="number" name="quantity" value="1" min="1">
      </label>
      <button type="submit" class="btn">Sepete Ekle</button>
    </form>
  </div>
</article>
<?php include __DIR__ . '/../partials/footer.php'; ?>
