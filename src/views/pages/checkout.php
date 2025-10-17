<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Ödeme</h1>
<p>Demo amaçlı, bu form gerçek ödeme almaz. Sipariş oluşturulur.</p>
<form method="post" action="<?= base_url('/checkout') ?>" class="checkout-form">
  <label>Adres
    <input type="text" name="address" required>
  </label>
  <label>Şehir
    <input type="text" name="city" required>
  </label>
  <label>Posta Kodu
    <input type="text" name="zip" required>
  </label>
  <button type="submit" class="btn">Siparişi Tamamla</button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
