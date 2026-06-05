<?php

require_once 'backoffice/controller/helpers/api-wrapper.php';

function BODashboard() {

    $result = executeDashboardAction();

    $data = $result['data'] ?? [];

    $stats = $data['stats'] ?? [];
    $charts = $data['charts'] ?? [];
    $evo = $data['evo_commandes'] ?? [];

    $bo_stats = [
        'ca_total' => $stats['ca_total'] ?? 0,
        'commandes_attente' => $stats['commandes_attente'] ?? 0,
        'commandes_payees' => $stats['commandes_payees'] ?? 0,
        'commandes_annulees' => $stats['commandes_annulees'] ?? 0,

        'panier_moyen' => ($stats['ca_total'] ?? 0) > 0
            ? ($stats['ca_total'] / 100) / max(1, ($stats['total_commandes'] ?? 1))
            : 0,

        'charts' => $charts,
        'evo_commandes' => $evo
    ];

    // variables vues
    $dernieres_commandes = is_array($data['dernieres_commandes'] ?? null)
        ? $data['dernieres_commandes']
        : [];

    $derniers_paiements = is_array($data['derniers_paiements'] ?? null)
        ? $data['derniers_paiements']
        : [];

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-backoffice-accueil.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}