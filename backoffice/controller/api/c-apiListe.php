<?php

header('Content-Type: application/json');

$url = $_SERVER['REQUEST_URI'];
$url = str_replace('/api/', '', $url);
$params = explode('/', trim($url, '/'));

$resource = $params[0] ?? null;
$id = $params[1] ?? null;

switch ($resource) {
    case 'commandes':
        require_once 'c-apiCommande.php';
        handleCommandes($id);
        break;

    case 'paiements':
        require_once 'c-apiPaiement.php';
        handlePaiements($id);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}