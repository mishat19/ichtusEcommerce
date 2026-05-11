<?php
session_start();
$_SESSION['test'] = 'working';
echo "Session ID: " . session_id() . "\n";
echo "Session Test: " . ($_SESSION['test'] ?? 'not set') . "\n";
