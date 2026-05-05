<?php

function BODashboard() {
    global $pdo;

    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM commande) AS total_commandes,
            (SELECT COUNT(*) FROM commande WHERE statut = 'payee') AS commandes_payees,
            (SELECT COUNT(*) FROM commande WHERE statut = 'en_attente') AS commandes_attente,
            (SELECT COUNT(*) FROM commande WHERE statut = 'annulee') AS commandes_annulees,
            (SELECT COALESCE(SUM(montant), 0) FROM paiement WHERE statut = 'accepte') AS ca_total,
            (SELECT COUNT(*) FROM paiement) AS total_paiements,
            (SELECT COUNT(*) FROM paiement WHERE statut = 'accepte') AS paiements_acceptes
    ")->fetch(PDO::FETCH_ASSOC);

    // CA par jour (30 derniers jours)
    $ca_jour = $pdo->query("
        SELECT DATE(date_paiement) as label, SUM(montant) as total 
        FROM paiement 
        WHERE statut = 'accepte' 
        AND date_paiement >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        GROUP BY label 
        ORDER BY label ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // CA par mois (12 derniers mois)
    $ca_mois = $pdo->query("
        SELECT DATE_FORMAT(date_paiement, '%Y-%m') as label, SUM(montant) as total 
        FROM paiement 
        WHERE statut = 'accepte' 
        AND date_paiement >= DATE_SUB(NOW(), INTERVAL 12 MONTH) 
        GROUP BY label 
        ORDER BY label ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // CA par an
    $ca_an = $pdo->query("
        SELECT DATE_FORMAT(date_paiement, '%Y') as label, SUM(montant) as total 
        FROM paiement 
        WHERE statut = 'accepte' 
        GROUP BY label 
        ORDER BY label ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Évolution commandes (M vs M-1)
    $evo_commandes = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM commande WHERE date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01')) as count_current,
            (SELECT COUNT(*) FROM commande WHERE date_commande >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') AND date_commande < DATE_FORMAT(NOW(), '%Y-%m-01')) as count_prev
    ")->fetch(PDO::FETCH_ASSOC);

    // Panier moyen
    $panier_moyen = $stats['total_commandes'] > 0 ? ($stats['ca_total'] / 100) / $stats['total_commandes'] : 0;

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
    $bo_stats['panier_moyen'] = $panier_moyen;
    $bo_stats['evo_commandes'] = $evo_commandes;
    $bo_stats['charts'] = [
        'jour' => $ca_jour,
        'mois' => $ca_mois,
        'an' => $ca_an
    ];

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-backoffice-accueil.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}