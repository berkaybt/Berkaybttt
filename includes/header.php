<?php require_once __DIR__ . '/config.php'; require_once __DIR__ . '/db.php'; require_once __DIR__ . '/functions.php'; ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(APP_NAME) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/index.php"><?= h(APP_NAME) ?></a>
    <form class="search" action="/products.php" method="get">
      <input type="text" name="q" placeholder="Ürün ara..." value="<?= h($_GET['q'] ?? '') ?>">
      <button type="submit">Ara</button>
    </form>
    <nav class="nav">
      <a href="/products.php">Ürünler</a>
      <a href="/cart.php">Sepet (<?= get_cart_count(); ?>)</a>
      <?php if (is_logged_in()): ?>
        <span>Merhaba, <?= h(current_user()['name']) ?></span>
        <?php if (is_admin()): ?><a href="/admin/index.php">Admin</a><?php endif; ?>
        <a href="/logout.php">Çıkış</a>
      <?php else: ?>
        <a href="/login.php">Giriş</a>
        <a href="/register.php">Kayıt Ol</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
