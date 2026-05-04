<?php

function APICommande() {
    global $pdo;

    header('Content-Type: application/json');

    /* ───────── METHOD CHECK ───────── */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    /* ───────── TOKEN CHECK ───────── */
    $token = $_POST['token'] ?? null;

    if ($token !== 'WDIhUThWMz9aN0Y0VDFwOUE2') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    /* ───────── SI ID → DETAIL ───────── */
    if (!empty($_POST['id']) && (int)$_POST['id'] > 0) {

        $idCommande = (int)$_POST['id'];

        /* ── COMMANDE + CLIENT + ADRESSES ── */
        $stmt = $pdo->prepare("
            SELECT c.*,
                   cl.nom, cl.prenom, cl.email,

                   af.prenom AS fact_prenom,
                   af.nom AS fact_nom,
                   af.email AS fact_email,
                   af.telephone AS fact_tel,
                   af.adresse AS fact_adresse,
                   af.complement AS fact_complement,
                   af.ville AS fact_ville,
                   af.code_postal AS fact_cp,

                   al.prenom AS liv_prenom,
                   al.nom AS liv_nom,
                   al.email AS liv_email,
                   al.telephone AS liv_tel,
                   al.adresse AS liv_adresse,
                   al.complement AS liv_complement,
                   al.ville AS liv_ville,
                   al.code_postal AS liv_cp

            FROM commande c
            JOIN client cl ON cl.id = c.id_client
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
            LEFT JOIN adresse al ON al.id = c.id_adresse_livraison

            WHERE c.id = ?
        ");
        $stmt->execute([$idCommande]);

        $commande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$commande) {
            http_response_code(404);
            echo json_encode(['error' => 'Commande not found']);
            return;
        }

        /* ── PRODUITS ── */
        $stmtProduits = $pdo->prepare("
            SELECT cp.quantite,
                   cp.prix_ht,
                   cp.taux_tva,
                   p.id AS produit_id,
                   p.nom,
                   p.identifiant,
                   p.image
            FROM commande_produit cp
            JOIN produit p ON p.id = cp.id_produit
            WHERE cp.id_commande = ?
        ");
        $stmtProduits->execute([$idCommande]);

        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produits as &$p) {
            $imgs = array_values(array_filter(array_map('trim', explode(',', $p['image'] ?? ''))));
            $p['image'] = $imgs[0] ?? null;
            $p['prix_ttc'] = round($p['prix_ht'] * (1 + $p['taux_tva'] / 100), 2);
        }
        unset($p);

        $commande['produits'] = $produits;

        /* ── PAIEMENT ── */
        $stmtPaiement = $pdo->prepare("
            SELECT *
            FROM paiement
            WHERE id_commande = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtPaiement->execute([$idCommande]);

        $commande['paiement'] = $stmtPaiement->fetch(PDO::FETCH_ASSOC);

        echo json_encode($commande);
        return;
    }

    /* ───────── SINON → LISTE ───────── */
    $stmt = $pdo->query("
        SELECT c.id, c.total_ttc, c.statut,
               cl.nom, cl.prenom, cl.email
        FROM commande c
        JOIN client cl ON cl.id = c.id_client
        ORDER BY c.id DESC
    ");

    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($commandes);
}