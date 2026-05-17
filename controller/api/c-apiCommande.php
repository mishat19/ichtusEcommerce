<?php

function APICommande() {
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

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    /* =====================================================
        DETAIL
    ===================================================== */
    if ($id > 0) {

        $stmt = $pdo->prepare("
            SELECT 
                c.*,
        
                cl.nom AS client_nom,
                cl.prenom AS client_prenom,
                cl.email AS client_email,
        
                af.nom AS fact_nom,
                af.prenom AS fact_prenom,
                af.email AS fact_email,
                af.telephone AS fact_tel,
                af.adresse AS fact_adresse,
                af.code_postal AS fact_cp,
                af.ville AS fact_ville,
        
                al.nom AS liv_nom,
                al.prenom AS liv_prenom,
                al.email AS liv_email,
                al.telephone AS liv_tel,
                al.adresse AS liv_adresse,
                al.code_postal AS liv_cp,
                al.ville AS liv_ville
        
            FROM commande c
            JOIN client cl ON cl.id = c.id_client
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
            LEFT JOIN adresse al ON al.id = c.id_adresse_livraison
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);

        $commande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$commande) {
            echo json_encode(['success' => false, 'error' => 'Not found']);
            return;
        }

        // produits
        $stmtP = $pdo->prepare("
            SELECT 
                cp.quantite,
                cp.prix_ht,
                cp.taux_tva,
        
                p.nom,
                p.identifiant,
                p.image,
        
                (cp.prix_ht * (1 + cp.taux_tva / 100)) AS prix_ttc
        
            FROM commande_produit cp
            JOIN produit p ON p.id = cp.id_produit
            WHERE cp.id_commande = ?
        ");
        $stmtP->execute([$id]);

        $commande['produits'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // paiement
        $stmtPay = $pdo->prepare("
            SELECT *
            FROM paiement
            WHERE id_commande = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtPay->execute([$id]);

        $commande['paiement'] = $stmtPay->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $commande
        ]);
        return;
    }

    /* =====================================================
        LISTE
    ===================================================== */
    $stmt = $pdo->query("
        SELECT c.id, c.total_ttc, c.statut,
               cl.nom, cl.prenom, cl.email
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.id DESC
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $commandes
    ]);
}