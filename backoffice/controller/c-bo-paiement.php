<?php

require_once 'controller/api/c-apiPaiement.php';
require_once 'backoffice/controller/helpers/api-wrapper.php';


function BOPaiement(): void
{

    $id = isset($_GET['id'])
        ? (int)$_GET['id']
        : 0;

    // ==========================================
    // LISTE
    // ==========================================

    $result = executePaiementAction();

    global $bo_paiements;

    $bo_paiements =
        $result['data'] ?? [];

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-paiement.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}