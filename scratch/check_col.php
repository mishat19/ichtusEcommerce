<?php
require_once 'model/model.php';
$stmt = $pdo->query("SHOW COLUMNS FROM client LIKE 'password'");
$r = $stmt->fetch();
echo "Column Type: " . $r['Type'] . "\n";
