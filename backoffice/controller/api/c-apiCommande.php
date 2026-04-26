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
        SELECT c.*, cl.nom, cl.prenom
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.date_commande DESC
        LIMIT 50
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($commandes);
}

function getCommande($id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT c.*, cl.nom, cl.prenom
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);

    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        http_response_code(404);
        echo json_encode(['error' => 'Commande not found']);
        return;
    }

    // récupérer produits liés
    $stmtProduits = $pdo->prepare("
        SELECT cp.*, p.nom
        FROM commande_produit cp
        JOIN produit p ON p.id = cp.id_produit
        WHERE cp.id_commande = ?
    ");
    $stmtProduits->execute([$id]);

    $commande['produits'] = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($commande);
}