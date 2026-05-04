<?php

function handleCommandes($id = null) {
    if ($id) {
        getCommande($id);
    } else {
        getCommandes();
    }
}

function getCommandes() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.numero_facture,
            c.total_ttc,
            c.date_commande,
            c.statut,

            cl.nom,
            cl.prenom,

            af.ville AS ville_facturation,
            al.ville AS ville_livraison

        FROM commande c
        JOIN client cl ON cl.id = c.id_client

        LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
        LEFT JOIN adresse al ON al.id = c.id_adresse_livraison

        ORDER BY c.date_commande DESC
        LIMIT 50
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($commandes);
}
function getCommande($id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            cl.nom,
            cl.prenom,

            af.adresse AS adresse_facturation,
            af.ville AS ville_facturation,
            af.code_postal AS cp_facturation,

            al.adresse AS adresse_livraison,
            al.ville AS ville_livraison,
            al.code_postal AS cp_livraison

        FROM commande c
        JOIN client cl ON cl.id = c.id_client

        LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
        LEFT JOIN adresse al ON al.id = c.id_adresse_livraison

        WHERE c.id = ?
    ");
    $stmt->execute([$id]);

    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        http_response_code(404);
        echo json_encode(['error' => 'Commande not found']);
        return;
    }

    // Produits
    $stmtProduits = $pdo->prepare("
        SELECT 
            cp.*,
            p.nom
        FROM commande_produit cp
        JOIN produit p ON p.id = cp.id_produit
        WHERE cp.id_commande = ?
    ");
    $stmtProduits->execute([$id]);

    $commande['produits'] = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($commande);
}