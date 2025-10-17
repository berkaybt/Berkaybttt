<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Kayıt Ol</h1>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<form method="post" action="<?= base_url('/register') ?>" class="auth-form">
  <label>Ad Soyad
    <input type="text" name="name" required>
  </label>
  <label>E-posta
    <input type="email" name="email" required>
  </label>
  <label>Şifre
    <input type="password" name="password" required>
  </label>
  <label>Şifre (Tekrar)
    <input type="password" name="confirm" required>
  </label>
  <button type="submit" class="btn">Kayıt Ol</button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
