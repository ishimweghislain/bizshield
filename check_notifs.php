<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM notifications");
$res = $stmt->fetchAll();
print_r($res);
?>
