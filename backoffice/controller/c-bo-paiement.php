<?php

require_once 'controller/api/c-apiPaiement.php';
require_once 'backoffice/controller/helpers/api-wrapper.php';


function BOPaiement() {

    $id = isset($_GET['id'])
        ? (int)$_GET['id']
        : 0;

    // ==========================================
    // DETAIL
    // ==========================================

    if ($id > 0) {

        $result = executePaiementAction([
            'id' => $id
        ]);

        if (
            empty($result['success']) ||
            empty($result['data'])
        ) {
            header('Location: /backoffice/paiements/');
            exit;
        }

        global $bo_paiement;
        global $bo_paiement_commande;
        global $bo_paiement_produits;

        $bo_paiement =
            $result['data'];

        $bo_paiement_commande =
            $result['data'];

        $bo_paiement_produits =
            $result['data']['produits'] ?? [];

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-paiement-detail.php';
        require_once 'backoffice/view/inc/inc.footer.php';

        return;
    }

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