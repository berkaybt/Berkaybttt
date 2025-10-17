<?php include __DIR__ . '/../partials/header.php'; ?>
<h1>Teşekkürler!</h1>
<p>Siparişiniz alındı. Sipariş numaranız: <strong>#<?= (int)$orderId ?></strong></p>
<a class="btn" href="<?= base_url('/') ?>">Alışverişe Devam Et</a>
<?php include __DIR__ . '/../partials/footer.php'; ?>
