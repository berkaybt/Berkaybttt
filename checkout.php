<?php
require_once __DIR__ . '/includes/boot.php';
require_login('/login.php');

$error = null;
$orderId = null;

if (is_post()) {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Geçersiz istek.';
    } else {
        try {
            $orderId = create_order((int)current_user()['id']);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<h1>Ödeme</h1>
<?php if ($orderId): ?>
  <div class="alert success">Siparişiniz alındı. Sipariş No: #<?= (int)$orderId ?></div>
<?php else: ?>
  <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
  <p>Toplam Tutar: <strong><?= format_price(get_cart_total()) ?></strong></p>
  <form method="post">
    <?= csrf_field() ?>
    <label>Adres
      <textarea name="address" required placeholder="Adresiniz"></textarea>
    </label>
    <label>Kart Numarası
      <input name="card" required placeholder="**** **** **** ****">
    </label>
    <button type="submit">Siparişi Tamamla</button>
  </form>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
