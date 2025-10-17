<?php
require_once __DIR__ . '/includes/header.php';
$items = get_cart_items_detailed();
$total = get_cart_total();
?>
<h1>Sepet</h1>
<?php if (empty($items)): ?>
  <p>Sepetiniz boş.</p>
<?php else: ?>
  <table class="cart-table">
    <thead>
      <tr>
        <th>Ürün</th><th>Adet</th><th>Birim Fiyat</th><th>Tutar</th><th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): $p = $item['product']; ?>
      <tr>
        <td class="product">
          <img src="<?= h($p['image'] ?: '/public/images/placeholder.svg') ?>" alt="<?= h($p['name']) ?>">
          <a href="/product.php?id=<?= (int)$p['id'] ?>"><?= h($p['name']) ?></a>
        </td>
        <td>
          <form action="/cart_update.php" method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="number" min="0" name="quantity" value="<?= (int)$item['quantity'] ?>">
            <button type="submit">Güncelle</button>
          </form>
        </td>
        <td><?= format_price((float)$p['price']) ?></td>
        <td><?= format_price((float)$item['line_total']) ?></td>
        <td>
          <form action="/cart_remove.php" method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button type="submit">Kaldır</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" class="right">Toplam</td>
        <td><?= format_price($total) ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>
  <p class="right"><a class="button" href="/checkout.php">Ödeme Yap</a></p>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
