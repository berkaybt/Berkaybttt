<?php
require_once __DIR__ . '/../includes/boot.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
  $stmt = getPDO()->prepare('DELETE FROM products WHERE id = ?');
  $stmt->execute([$id]);
}
redirect('/admin/index.php');
