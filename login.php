<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$error = null;

if (is_post()) {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Geçersiz istek.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if (login_user($email, $password)) {
            redirect('/index.php');
        } else {
            $error = 'E-posta veya şifre yanlış.';
        }
    }
}
?>
<h1>Giriş Yap</h1>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>E-posta
    <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
  </label>
  <label>Şifre
    <input type="password" name="password" required>
  </label>
  <button type="submit">Giriş</button>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
