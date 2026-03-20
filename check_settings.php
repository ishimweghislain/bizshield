<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM settings");
print_r($stmt->fetchAll());
?>
