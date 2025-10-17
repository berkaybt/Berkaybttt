<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Sepetiniz</h1>
<?php if (empty($cartItems)): ?>
  <p>Sepetiniz boş.</p>
<?php else: ?>
  <form method="post" action="<?= base_url('/cart/update') ?>" class="cart-form">
    <table class="cart">
      <thead>
        <tr>
          <th>Ürün</th>
          <th>Adet</th>
          <th>Fiyat</th>
          <th>Ara Toplam</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cartItems as $item): $p = $item['product']; ?>
        <tr>
          <td class="cart-product">
            <img src="<?= base_url('/assets/images/' . $p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <span><?= htmlspecialchars($p['name']) ?></span>
          </td>
          <td>
            <input type="number" name="qty[<?= htmlspecialchars($p['slug']) ?>]" value="<?= (int)$item['quantity'] ?>" min="0">
          </td>
          <td>₺<?= number_format($p['price_cents'] / 100, 2, ',', '.') ?></td>
          <td>₺<?= number_format($item['line_total_cents'] / 100, 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="cart-actions">
      <button type="submit" class="btn">Güncelle</button>
      <button type="submit" formaction="<?= base_url('/cart/clear') ?>" class="btn btn-secondary">Temizle</button>
    </div>
  </form>
  <div class="cart-total">
    <strong>Toplam:</strong> ₺<?= number_format($totalCents / 100, 2, ',', '.') ?>
  </div>
  <a class="btn" href="<?= base_url('/checkout') ?>">Ödeme Yap</a>
<?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?>
