<?php

function bo() {
    global $pdo;

    // 🧮 CA
    $stmt = $pdo->query("
        SELECT SUM(montant) as ca
        FROM paiement
        WHERE statut = 'accepte'
    ");
    $ca = $stmt->fetchColumn() ?? 0;

    // 📦 Commandes
    $stmt = $pdo->query("SELECT COUNT(*) FROM commande");
    $nbCommandes = $stmt->fetchColumn();

    // ✅ Paiements OK
    $stmt = $pdo->query("SELECT COUNT(*) FROM paiement WHERE statut = 'accepte'");
    $paiementsOk = $stmt->fetchColumn();

    // ❌ Paiements KO
    $stmt = $pdo->query("SELECT COUNT(*) FROM paiement WHERE statut != 'accepte'");
    $paiementsKo = $stmt->fetchColumn();

    // 📊 Tableau final
    $stats = [
        'ca' => $ca,
        'nb_commandes' => $nbCommandes,
        'paiements_ok' => $paiementsOk,
        'paiements_ko' => $paiementsKo
    ];

    // 📦 Dernières commandes
    $stmt = $pdo->query("
        SELECT c.*, cl.nom, cl.prenom
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.date_commande DESC
        LIMIT 10
    ");
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-backoffice-accueil.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

function boCommandes() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT c.*, cl.nom, cl.prenom
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.date_commande DESC
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-commandes.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

function boCommandeDetail() {
    global $pdo;

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo "ID manquant";
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT c.*, cl.nom, cl.prenom
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        WHERE c.id = ?
    ");
    $stmt->execute([$_GET['id']]);

    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        http_response_code(404);
        echo "Commande introuvable";
        exit;
    }

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-commande-detail.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

function boPaiements() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT *
        FROM paiement
        ORDER BY date_paiement DESC
    ");

    $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-paiement.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

function boProduits() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT *
        FROM produit
    ");

    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-produits.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}