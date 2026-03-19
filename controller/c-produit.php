<?php

function produit() {
    global $pdo;

    /* ── Traitement POST ajout panier AVANT le rendu ── */
//    if (isset($_POST['quantite'], $_POST['id_produit'])) {
//        ajouterProduitDansPanier((int)$_POST['id_produit'], (int)$_POST['quantite']);
//
//        //header('Location: /panier'); // Redirection vers la page panier
//        exit;
//    }

    if (isset($_GET['identifiant']) && $_GET['identifiant']) {
        $identifiant = $_GET['identifiant'];
        $stmt = $pdo->prepare("
            SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc
            FROM produit p
            INNER JOIN tva t ON p.id_tva = t.id
            WHERE p.identifiant = :identifiant
            AND p.statut = 'actif'
        ");
        $stmt->execute(['identifiant' => $identifiant]);

        global $unProduit;
        $unProduit = $stmt->fetch(PDO::FETCH_ASSOC);
        unProduit();
    } else {
        global $lProduit;
        $lProduit = $pdo->query("SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc 
            FROM produit p 
            INNER JOIN tva t ON p.id_tva = t.id 
            WHERE p.statut = 'actif'");
        lstProduit();
    }
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