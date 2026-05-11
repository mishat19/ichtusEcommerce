<?php

/**
 * Script de CRON - Diagnostic Quotidien
 * À exécuter via CLI : php scripts/cron-tests.php
 */

// On utilise des chemins absolus pour plus de robustesse
$baseDir = dirname(__DIR__);
require_once $baseDir . '/model/model.php';
require_once $baseDir . '/model/model.diagnostic.php';

// On se place à la racine pour les chemins relatifs (logs, images, etc.)
chdir($baseDir);

echo "[ " . date('Y-m-d H:i:s') . " ] Démarrage du diagnostic...\n";

try {
    $results = runFullDiagnostic();
    
    $log_file = $baseDir . '/logs/last_cron_diagnostic.json';
    
    // On s'assure que le dossier logs existe
    if (!is_dir($baseDir . '/logs')) {
        mkdir($baseDir . '/logs', 0755, true);
    }

    file_put_contents($log_file, json_encode($results, JSON_PRETTY_PRINT));
    
    echo "[ " . date('Y-m-d H:i:s') . " ] Diagnostic terminé avec succès.\n";
    echo "Log sauvegardé dans : $log_file\n";

} catch (Exception $e) {
    echo "[ " . date('Y-m-d H:i:s') . " ] ERREUR FATALE : " . $e->getMessage() . "\n";
    exit(1);
}
