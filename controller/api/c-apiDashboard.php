<?php

function APIDashboard() {
    global $pdo;

    $isDirectApiCall = isset($_GET['pageAPI']);

    if ($isDirectApiCall) {
        header('Content-Type: application/json; charset=utf-8');
    }

    if ($isDirectApiCall && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    $token = $_POST['token'] ?? null;

    if ($token !== 'WDIhUThWMz9aN0Y0VDFwOUE2') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        return;
    }

    // =========================
    // STATS GLOBAL
    // =========================
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM commande) AS total_commandes,
            (SELECT COUNT(*) FROM commande WHERE statut = 'payee') AS commandes_payees,
            (SELECT COUNT(*) FROM commande WHERE statut = 'en_attente') AS commandes_attente,
            (SELECT COUNT(*) FROM commande WHERE statut = 'annulee') AS commandes_annulees,
            (SELECT COALESCE(SUM(montant),0) FROM paiement WHERE statut='accepte') AS ca_total
    ")->fetch(PDO::FETCH_ASSOC);

    // =========================
    // CA CHARTS
    // =========================
    $ca_jour = $pdo->query("
        SELECT DATE(date_paiement) label, SUM(montant) total
        FROM paiement
        WHERE statut='accepte'
        GROUP BY label
        ORDER BY label
    ")->fetchAll(PDO::FETCH_ASSOC);

    $ca_mois = $pdo->query("
        SELECT DATE_FORMAT(date_paiement,'%Y-%m') label, SUM(montant) total
        FROM paiement
        WHERE statut='accepte'
        GROUP BY label
        ORDER BY label
    ")->fetchAll(PDO::FETCH_ASSOC);

    $ca_an = $pdo->query("
        SELECT DATE_FORMAT(date_paiement,'%Y') label, SUM(montant) total
        FROM paiement
        WHERE statut='accepte'
        GROUP BY label
        ORDER BY label
    ")->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // EVOLUTION COMMANDES
    // =========================
    $evo = $pdo->query("
        SELECT 
            (SELECT COUNT(*) 
             FROM commande 
             WHERE date_commande >= DATE_FORMAT(NOW(),'%Y-%m-01')) current_month,

            (SELECT COUNT(*) 
             FROM commande 
             WHERE date_commande >= DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 MONTH),'%Y-%m-01')
             AND date_commande < DATE_FORMAT(NOW(),'%Y-%m-01')) prev_month
    ")->fetch(PDO::FETCH_ASSOC);

    // =========================
    // DERNIERES COMMANDES
    // =========================
    $dernieres_commandes = $pdo->query("
        SELECT 
            c.id,
            c.statut,
            c.total_ttc,
            cl.nom,
            cl.prenom,
            cl.email
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // DERNIERS PAIEMENTS
    // =========================
    $derniers_paiements = $pdo->query("
        SELECT 
            p.id,
            p.statut,
            p.montant,
            p.date_paiement,
            p.numero_transaction,
            cl.nom AS facturation_nom,
            cl.prenom AS facturation_prenom
        FROM paiement p
        JOIN commande c ON c.id = p.id_commande
        JOIN client cl ON cl.id = c.id_client
        ORDER BY p.id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // RESPONSE
    // =========================
    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'charts' => [
                'jour' => $ca_jour,
                'mois' => $ca_mois,
                'an' => $ca_an
            ],
            'evo_commandes' => [
                'count_current' => $evo['current_month'],
                'count_prev' => $evo['prev_month']
            ],
            'dernieres_commandes' => $dernieres_commandes,
            'derniers_paiements' => $derniers_paiements
        ]
    ]);
}