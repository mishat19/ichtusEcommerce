<?php

require_once 'model/model.diagnostic.php';

function BOTests(): void
{
    // Exécution du diagnostic en direct
    $current_results = runFullDiagnostic();

    // Lecture du dernier CRON
    $last_cron = null;
    $log_file = 'logs/last_cron_diagnostic.json';
    if (file_exists($log_file)) {
        $last_cron = json_decode(file_get_contents($log_file), true);
    }

    // Export variables pour la vue
    global $bo_tests, $bo_stats_tests, $bo_alerts, $bo_last_cron_date;
    $bo_tests = $current_results['tests'];
    $bo_stats_tests = $current_results['stats'];
    $bo_alerts = $current_results['alerts'];
    $bo_last_cron_date = $last_cron ? $last_cron['timestamp'] : 'Jamais';

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-tests.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}
