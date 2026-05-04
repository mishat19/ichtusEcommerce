<?php

function BODashboard() {
    global $pdo;

    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM commande) AS total_commandes,
            (SELECT COUNT(*) FROM commande WHERE statut = 'payee') AS commandes_payees,
            (SELECT COUNT(*) FROM commande WHERE statut = 'en_attente') AS commandes_attente,
            (SELECT COALESCE(SUM(montant), 0) FROM paiement WHERE statut = 'accepte') AS ca_total,
            (SELECT COUNT(*) FROM paiement) AS total_paiements,
            (SELECT COUNT(*) FROM paiement WHERE statut = 'accepte') AS paiements_acceptes
    ")->fetch(PDO::FETCH_ASSOC);

    // 🔥 dernières commandes AVEC adresse facturation
    $dernieres_commandes = $pdo->query("
        SELECT 
            c.id,
            c.statut,
            c.total_ttc,
            a.nom AS facturation_nom,
            a.prenom AS facturation_prenom,
            a.email AS facturation_email
        FROM commande c
        LEFT JOIN adresse a ON a.id = c.id_adresse_facturation
        ORDER BY c.id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 🔥 derniers paiements + client via commande + adresse
    $derniers_paiements = $pdo->query("
        SELECT 
            p.id,
            p.statut,
            p.montant,
            p.date_paiement,
            p.numero_transaction AS ref_banque,
            a.nom AS facturation_nom,
            a.prenom AS facturation_prenom
        FROM paiement p
        JOIN commande c ON c.id = p.id_commande
        LEFT JOIN adresse a ON a.id = c.id_adresse_facturation
        ORDER BY p.id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $bo_stats = $stats;

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-backoffice-accueil.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}