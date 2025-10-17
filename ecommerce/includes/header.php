<?php
require_once __DIR__ . '/bootstrap.php';
$app = $config['app'];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>E-Ticaret</title>
  <link rel="stylesheet" href="<?= $app['base_url']; ?>assets/css/styles.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?= $app['base_url']; ?>index.php">E-Ticaret</a>
    <nav>
      <a href="<?= $app['base_url']; ?>index.php">Ürünler</a>
      <a href="<?= $app['base_url']; ?>cart.php">Sepet</a>
    </nav>
  </div>
</header>
<main class="container">
