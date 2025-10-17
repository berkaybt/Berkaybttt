<?php
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$pdo = getPDO();
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT id, name, description, price, image, stock FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
    $products = $stmt->fetchAll();
} else {
    $products = get_products();
}
?>
<h1>Ürünler</h1>
<form class="filters" method="get">
  <input type="text" name="q" placeholder="Ara" value="<?= h($q) ?>">
  <button type="submit">Ara</button>
</form>
<div class="grid products">
<?php foreach ($products as $p): ?>
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
