<?php

require_once 'controller/api/c-apiCommande.php';
require_once 'backoffice/controller/helpers/api-wrapper.php';

function BOCommande() {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // =====================================================
    // DETAIL
    // =====================================================
    if ($id > 0) {

        $result = executeCommandeAction([
            'id' => $id
        ]);

        if (
            empty($result['success']) ||
            empty($result['data'])
        ) {
            header('Location: /backoffice/commandes/');
            exit;
        }

        global $bo_commande;
        global $bo_commande_produits;
        global $bo_commande_paiement;

        $bo_commande = $result['data'];
        $bo_commande_produits = $result['data']['produits'] ?? [];
        $bo_commande_paiement = $result['data']['paiement'] ?? null;

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-commande-detail.php';
        require_once 'backoffice/view/inc/inc.footer.php';

        return;
    }

    // =====================================================
    // LISTE
    // =====================================================
    $result = executeCommandeAction();

    global $bo_commandes;
    $bo_commandes = $result['data'] ?? [];

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-commandes.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}