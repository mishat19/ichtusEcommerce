<?php
require_once 'model/model.php';
$stmt = $pdo->query("SELECT email, password FROM client LIMIT 10");
foreach ($stmt->fetchAll() as $r) {
    echo $r['email'] . " | " . (password_get_info($r['password'])['algo'] ? 'HASHED' : 'PLAIN') . " | " . $r['password'] . "\n";
}
