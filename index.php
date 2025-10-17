<?php
require_once __DIR__ . '/includes/header.php';
$latest = get_products(8);
?>
<h1>Hoş Geldiniz</h1>
<p>En yeni ürünler</p>
<div class="grid products">
<?php foreach ($latest as $p): ?>
  <a class="card" href="/product.php?id=<?= (int)$p['id'] ?>">
    <img src="<?= h($p['image'] ?: '/public/images/placeholder.svg') ?>" alt="<?= h($p['name']) ?>">
    <div class="card-body">
      <h3><?= h($p['name']) ?></h3>
      <div class="price"><?= format_price((float)$p['price']) ?></div>
    </div>
  </a>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
