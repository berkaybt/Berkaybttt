<?php
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MyShop</title>
  <link rel="stylesheet" href="<?= base_url('/assets/css/styles.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= base_url('/') ?>" class="logo">
      <img src="<?= base_url('/assets/images/logo.svg') ?>" alt="MyShop" width="36" height="36">
      <span>MyShop</span>
    </a>
    <nav class="nav">
      <a href="<?= base_url('/products') ?>">Ürünler</a>
      <a href="<?= base_url('/cart') ?>">Sepet</a>
      <?php if ($user): ?>
        <span class="welcome">Merhaba, <?= htmlspecialchars($user['name']) ?></span>
        <a href="<?= base_url('/logout') ?>">Çıkış</a>
      <?php else: ?>
        <a href="<?= base_url('/login') ?>">Giriş</a>
        <a href="<?= base_url('/register') ?>" class="btn">Kayıt Ol</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
