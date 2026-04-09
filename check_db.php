<?php
require 'config.php';
$stmt = $pdo->query("DESCRIBE users");
echo "Column | Type | Null | Key | Default | Extra\n";
echo "--------------------------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . "\n";
}
echo "--- Data Sample (Complete Rows) ---\n";
$stmt = $pdo->query("SELECT * FROM users LIMIT 10");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    foreach($row as $k => $v) { echo "[$k]: " . ($v ?? 'NULL') . " | "; }
    echo "\n";
}
?>
