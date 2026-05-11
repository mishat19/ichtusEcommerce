<?php

function boProduits() {
    global $pdo;

    // Récupération de tous les produits
    $stmt = $pdo->query("
        SELECT p.id, p.nom, p.identifiant, p.prix_ht, p.statut, p.image
        FROM produit p
        ORDER BY p.id DESC
    ");
    
    global $produits;
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalisation des données pour la vue
    foreach ($produits as &$p) {
        if ($p['image']) {
            $p['image'] = 'images/' . $p['image'];
        }
    }

    // Chargement de la vue
    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-produits.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}
