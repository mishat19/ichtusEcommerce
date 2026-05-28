<?php

function messages() {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
        header('Location: /connexion');
        exit;
    }

    $filter = $_GET['filter'] ?? 'all';

    $validFilters = ['all', 'unread', 'read', 'processed', 'archived'];

    if (!in_array($filter, $validFilters)) {
        $filter = 'all';
    }

    /*
     * Mise à jour d'un statut
     */
    if (
        (isset($_GET['action']) && $_GET['action'] === 'updateStatut') ||
        (isset($_POST['action']) && $_POST['action'] === 'updateStatut')
    ) {

        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $statut = $_POST['statut'] ?? $_GET['statut'] ?? '';

        $response = APIMessagesProxy('updateStatut', [
            'id' => $id,
            'statut' => $statut
        ]);

        // Si c'est une requête AJAX, on répond en JSON et on s'arrête là
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }

        header('Location: /backoffice/messages/?filter=' . $filter);
        exit;
    }

    /*
     * Récupération des messages
     */
    $response = APIMessagesProxy('getMessages', [
        'filter' => 'all'
    ]);

    $messages = $response['messages'] ?? [];

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-messages.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}


/**
 * Appel API interne
 */
function APIMessagesProxy(string $action, array $data = []) {

    $oldPost = $_POST;
    $oldGet = $_GET;

    $_POST = [
        'token' => 'WDIhUThWMz9aN0Y0VDFwOUE2',
        'action' => $action
    ];

    foreach ($data as $key => $value) {
        $_POST[$key] = $value;
    }

    $_GET = [];

    ob_start();
    APIMessages();
    $json = ob_get_clean();

    $_POST = $oldPost;
    $_GET = $oldGet;

    return json_decode($json, true);
}