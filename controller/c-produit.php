<?php

function produit() {
    global $pdo;

    /* ── Traitement POST ajout panier AVANT le rendu ── */
    if (isset($_POST['quantite'], $_POST['id_produit'])) {
        $idProduit = (int)$_POST['id_produit'];
        $quantite  = (int)$_POST['quantite'];

        // Vérifier le stock disponible avant d'ajouter au panier
        $stmtStock = $pdo->prepare("
            SELECT COALESCE(quantite_disponible, 0) - COALESCE(quantite_reservee, 0) AS stock_reel
            FROM stock WHERE id_produit = ?
        ");
        $stmtStock->execute([$idProduit]);
        $stockRow = $stmtStock->fetch(PDO::FETCH_ASSOC);
        $stockReel = $stockRow ? (int)$stockRow['stock_reel'] : 0;

        if ($quantite > $stockReel) {
            $_SESSION['erreur_stock'] = "Stock insuffisant. Seulement $stockReel unité(s) disponible(s).";
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        // Réserver le stock
        $pdo->prepare("
            UPDATE stock 
            SET quantite_reservee = quantite_reservee + ?,
                date_derniere_mise_a_jour = NOW()
            WHERE id_produit = ?
        ")->execute([$quantite, $idProduit]);

        ajouterProduitDansPanier($idProduit, $quantite);

        // Stocker le timestamp de la dernière réservation
        $_SESSION['panier_reservation_time'] = time();

        header('Location: /panier');
        exit;
    }

    if (isset($_GET['identifiant']) && $_GET['identifiant']) {
        $identifiant = $_GET['identifiant'];
        $stmt = $pdo->prepare("
            SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc,
                   COALESCE(s.quantite_disponible, 0) AS stock_total,
                   COALESCE(s.quantite_reservee, 0) AS stock_reserve,
                   COALESCE(s.quantite_disponible, 0) - COALESCE(s.quantite_reservee, 0) AS stock_disponible,
                   COALESCE(s.seuil_alerte, 15) AS seuil_alerte
            FROM produit p
            INNER JOIN tva t ON p.id_tva = t.id
            LEFT JOIN stock s ON s.id_produit = p.id
            WHERE p.identifiant = :identifiant
            AND p.statut = 'actif'
        ");
        $stmt->execute(['identifiant' => $identifiant]);

        global $unProduit;
        $unProduit = $stmt->fetch(PDO::FETCH_ASSOC);
        unProduit();
    } else {
        global $lProduit;
        $stmt = $pdo->query("
            SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc,
                   COALESCE(s.quantite_disponible, 0) AS stock_total,
                   COALESCE(s.quantite_reservee, 0) AS stock_reserve,
                   COALESCE(s.quantite_disponible, 0) - COALESCE(s.quantite_reservee, 0) AS stock_disponible
            FROM produit p
            INNER JOIN tva t ON p.id_tva = t.id
            LEFT JOIN stock s ON s.id_produit = p.id
            WHERE p.statut = 'actif'
        ");
        $lProduit = $stmt->fetchAll(PDO::FETCH_ASSOC);
        lstProduit();
    }
}

function getBestSellers() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc,
               COALESCE(s.quantite_disponible, 0) - COALESCE(s.quantite_reservee, 0) AS stock_disponible
        FROM produit p
        INNER JOIN tva t ON p.id_tva = t.id
        LEFT JOIN stock s ON s.id_produit = p.id
        WHERE p.statut = 'actif'
        LIMIT 3
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function unProduit() {
    global $unProduit;
    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/produit/v-unProduit.php';
    require_once 'view/inc/inc.footer.php';
}

function lstProduit() {
    global $lProduit;
    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/produit/v-lProduit.php';
    require_once 'view/inc/inc.footer.php';
}