<?php require __DIR__ . '/includes/header.php'; ?>
<?php $cart = getCartDetailed(); ?>
<h1>Sepet</h1>
<?php if (empty($cart['items'])): ?>
  <div class="alert">Sepetiniz boş.</div>
<?php else: ?>
  <form method="post" action="actions/update_cart.php">
    <table class="table">
      <thead>
        <tr>
          <th>Ürün</th>
          <th>Adet</th>
          <th>Birim Fiyat</th>
          <th>Ara Toplam</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart['items'] as $item): $p = $item['product']; ?>
        <tr>
          <td>
            <a href="product.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
          </td>
          <td>
            <input type="number" name="qty[<?= (int)$p['id'] ?>]" min="0" value="<?= (int)$item['quantity'] ?>" style="width:80px;">
          </td>
          <td><?= formatCurrency((float)$p['price']) ?></td>
          <td><?= formatCurrency((float)$item['line_total']) ?></td>
          <td>
            <form method="post" action="actions/remove_from_cart.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="button secondary" type="submit">Kaldır</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="display:flex; justify-content: space-between; align-items:center; margin-top:12px;">
      <strong>Toplam: <?= formatCurrency((float)$cart['total']) ?></strong>
      <div style="display:flex; gap:8px;">
        <button class="button secondary" type="submit">Sepeti Güncelle</button>
        <a class="button" href="checkout.php">Ödeme Yap</a>
      </div>
    </div>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
