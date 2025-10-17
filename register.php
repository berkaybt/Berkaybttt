<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$success = false;

if (is_post()) {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $errors[] = 'Geçersiz istek. Lütfen tekrar deneyin.';
    } else {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = register_user($name, $email, $password);
        if ($result['success']) {
            $success = true;
        } else {
            $errors[] = $result['error'];
        }
    }
}
?>
<h1>Kayıt Ol</h1>
<?php if ($success): ?>
  <div class="alert success">Kayıt başarılı. <a href="/login.php">Giriş yap</a>.</div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
  <div class="alert error"><?= h($e) ?></div>
<?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Ad Soyad
    <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
  </label>
  <label>E-posta
    <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
  </label>
  <label>Şifre
    <input type="password" name="password" required>
  </label>
  <button type="submit">Kayıt Ol</button>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
