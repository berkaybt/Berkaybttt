<?php require __DIR__ . '/includes/header.php'; ?>
<?php $cart = getCartDetailed(); ?>
<h1>Ödeme</h1>
<?php if (empty($cart['items'])): ?>
  <div class="alert">Sepetiniz boş.</div>
<?php else: ?>
  <div class="form" style="margin-bottom:16px;">
    <h3>Sipariş Özeti</h3>
    <table class="table" style="margin-top:8px;">
      <thead><tr><th>Ürün</th><th>Adet</th><th>Tutar</th></tr></thead>
      <tbody>
      <?php foreach ($cart['items'] as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['product']['name']) ?></td>
          <td><?= (int)$item['quantity'] ?></td>
          <td><?= formatCurrency((float)$item['line_total']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <p style="text-align:right;"><strong>Toplam: <?= formatCurrency((float)$cart['total']) ?></strong></p>
  </div>
  <form class="form" method="post" action="actions/checkout_place_order.php">
    <h3>Müşteri Bilgileri</h3>
    <div class="row">
      <div>
        <label>Ad Soyad</label>
        <input name="name" required>
      </div>
      <div>
        <label>E-posta</label>
        <input type="email" name="email" required>
      </div>
    </div>
    <label>Adres</label>
    <textarea name="address" rows="3" required></textarea>
    <div class="row">
      <div>
        <label>Şehir</label>
        <input name="city" required>
      </div>
      <div>
        <label>Posta Kodu</label>
        <input name="zip" required>
      </div>
    </div>
    <div class="actions">
      <button class="button" type="submit">Siparişi Tamamla</button>
    </div>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
