<?php

function APICommande() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die('Method not allowed');
    }

    if (!isset($_POST['token']) || $_POST['token'] !== 'WDIhUThWMz9aN0Y0VDFwOUE2') {
        http_response_code(401);
        die('Non autorisé');
    }

    if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

        $idCommande = (int)$_POST['id'];

        /* ── Infos commande ── */
        $stmtCommande = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
        $stmtCommande->execute([$idCommande]);
        $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);

        if (!$commande) {
            http_response_code(404);
            die(json_encode(['erreur' => 'Commande introuvable']));
        }

        /* ── Produits de la commande ── */
        $stmtProduits = $pdo->prepare(
            "SELECT cp.quantite,
                    cp.prix_ht,
                    cp.taux_tva,
                    p.id          AS produit_id,
                    p.nom,
                    p.identifiant,
                    p.image,
                    p.description
             FROM commande_produit cp
             JOIN produit p ON p.id = cp.id_produit
             WHERE cp.id_commande = ?"
        );
        $stmtProduits->execute([$idCommande]);
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produits as &$p) {
            $imgs       = array_values(array_filter(array_map('trim', explode(',', $p['image'] ?? ''))));
            $p['image'] = $imgs[0] ?? null;
            $p['prix_ttc'] = round($p['prix_ht'] * (1 + $p['taux_tva'] / 100), 2);
        }
        unset($p);

        $commande['produits'] = $produits;

        echo json_encode($commande);

    } else {

        $lstCommande = $pdo->query(
            "SELECT *
             FROM commande
             ORDER BY id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($lstCommande);
    }
}