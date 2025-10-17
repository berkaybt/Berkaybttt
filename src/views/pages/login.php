<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Giriş Yap</h1>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<form method="post" action="<?= base_url('/login') ?>" class="auth-form">
  <label>E-posta
    <input type="email" name="email" required>
  </label>
  <label>Şifre
    <input type="password" name="password" required>
  </label>
  <button type="submit" class="btn">Giriş</button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
