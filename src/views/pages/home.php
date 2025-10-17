<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="hero">
  <div class="hero-content">
    <h1>En İyi Ürünler, Uygun Fiyatlarla</h1>
    <p>Güncel kampanyaları kaçırmayın!</p>
    <a class="btn" href="<?= base_url('/products') ?>">Alışverişe Başla</a>
  </div>
</section>
<section>
  <h2>Öne Çıkanlar</h2>
  <div class="grid products">
    <?php foreach ($featured as $p): ?>
      <a class="card" href="<?= base_url('/product?slug=' . urlencode($p['slug'])) ?>">
        <img src="<?= base_url('/assets/images/' . $p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        <div class="card-body">
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <p class="price">₺<?= number_format($p['price_cents'] / 100, 2, ',', '.') ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
