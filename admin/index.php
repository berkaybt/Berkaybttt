<?php
require_once __DIR__ . '/../includes/boot.php';
require_admin();
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
$products = $pdo->query('SELECT id, name, price, stock FROM products ORDER BY created_at DESC')->fetchAll();
$orders = $pdo->query('SELECT id, user_id, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 10')->fetchAll();
?>
<h1>Admin Paneli</h1>
<section>
  <h2>Ürünler</h2>
  <p><a class="button" href="/admin/product_new.php">Yeni Ürün</a></p>
  <table>
    <thead><tr><th>ID</th><th>Ad</th><th>Fiyat</th><th>Stok</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td>#<?= (int)$p['id'] ?></td>
        <td><?= h($p['name']) ?></td>
        <td><?= format_price((float)$p['price']) ?></td>
        <td><?= (int)$p['stock'] ?></td>
        <td>
          <a href="/admin/product_edit.php?id=<?= (int)$p['id'] ?>">Düzenle</a>
          <a href="/admin/product_delete.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Silinsin mi?')">Sil</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
<section>
  <h2>Son Siparişler</h2>
  <table>
    <thead><tr><th>ID</th><th>Kullanıcı</th><th>Toplam</th><th>Durum</th><th>Tarih</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td>#<?= (int)$o['id'] ?></td>
        <td><?= (int)$o['user_id'] ?></td>
        <td><?= format_price((float)$o['total']) ?></td>
        <td><?= h($o['status']) ?></td>
        <td><?= h($o['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
