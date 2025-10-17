<?php require __DIR__ . '/includes/header.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<div class="success" style="margin:12px 0; padding:12px;">
  <strong>Teşekkürler!</strong> Siparişiniz alındı. Sipariş No: #<?= $id ?>
</div>
<p><a class="button" href="index.php">Alışverişe devam et</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
