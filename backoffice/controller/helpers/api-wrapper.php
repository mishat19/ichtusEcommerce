<?php

require_once 'controller/api/c-apiCommande.php';
require_once 'controller/api/c-apiPaiement.php';
require_once 'controller/api/c-apiDashboard.php';

function executeDashboardAction($data = []) {

    $oldPost = $_POST;
    $oldGet = $_GET;

    $_POST = array_merge($data, [
        'token' => 'WDIhUThWMz9aN0Y0VDFwOUE2'
    ]);

    ob_start();
    APIDashboard();
    $output = ob_get_clean();

    $_POST = $oldPost;
    $_GET = $oldGet;

    $decoded = json_decode($output, true);

    // 🔥 DEBUG SAFE (très important)
    if (!is_array($decoded)) {
        error_log("Dashboard API JSON ERROR: " . $output);
        return [
            'success' => false,
            'data' => []
        ];
    }

    return $decoded;
}

function executeCommandeAction($data = []) {
    global $pdo;

    $oldPost = $_POST;
    $oldGet = $_GET;

    $_POST = array_merge($data, [
        'token' => 'WDIhUThWMz9aN0Y0VDFwOUE2'
    ]);

    ob_start();
    APICommande();
    $output = ob_get_clean();

    $_POST = $oldPost;
    $_GET = $oldGet;

    return json_decode($output, true);
}

function executePaiementAction($data = []) {
    global $pdo;

    $oldPost = $_POST;
    $oldGet = $_GET;

    $_POST = array_merge($data, [
        'token' => 'WDIhUThWMz9aN0Y0VDFwOUE2'
    ]);

    ob_start();
    APIPaiement();
    $output = ob_get_clean();

    $_POST = $oldPost;
    $_GET = $oldGet;

    return json_decode($output, true);
}
