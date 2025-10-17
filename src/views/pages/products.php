<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Tüm Ürünler</h1>
<div class="grid products">
  <?php foreach ($items as $p): ?>
    <a class="card" href="<?= base_url('/product?slug=' . urlencode($p['slug'])) ?>">
      <img src="<?= base_url('/assets/images/' . $p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <div class="card-body">
        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <p class="price">₺<?= number_format($p['price_cents'] / 100, 2, ',', '.') ?></p>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
