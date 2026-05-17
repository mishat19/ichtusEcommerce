<?php

function APIPaiement() {
    global $pdo;

    $isDirectApiCall = isset($_GET['pageAPI']);

    // Header JSON uniquement pour les vrais appels API
    if ($isDirectApiCall) {
        header('Content-Type: application/json; charset=utf-8');
    }

    // Vérification méthode uniquement pour API HTTP
    if ($isDirectApiCall && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $idPaiement =
        isset($_POST['id'])
            ? (int)$_POST['id']
            : 0;

    // =========================================================
    // DETAIL D'UN PAIEMENT
    // =========================================================

    if ($idPaiement > 0) {

        $stmt = $pdo->prepare("
            SELECT
                p.*,

                (p.montant / 100) AS montant,
                p.numero_transaction AS ref_banque,

                c.id AS id_commande,
                c.numero_facture,
                c.statut AS statut_commande,
                c.total_ttc,

                cl.nom AS client_nom,
                cl.prenom AS client_prenom,
                cl.email AS client_email,

                af.nom AS facturation_nom,
                af.prenom AS facturation_prenom,
                af.email AS facturation_email,
                af.adresse AS facturation_adresse,
                af.code_postal AS facturation_code_postal,
                af.ville AS facturation_ville,

                al.nom AS livraison_nom,
                al.prenom AS livraison_prenom,
                al.adresse AS livraison_adresse,
                al.code_postal AS livraison_code_postal,
                al.ville AS livraison_ville

            FROM paiement p

            JOIN commande c
                ON c.id = p.id_commande

            JOIN client cl
                ON cl.id = c.id_client

            LEFT JOIN adresse af
                ON af.id = c.id_adresse_facturation

            LEFT JOIN adresse al
                ON al.id = c.id_adresse_livraison

            WHERE p.id = ?
        ");

        $stmt->execute([$idPaiement]);

        $paiement =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paiement) {

            http_response_code(404);

            echo json_encode([
                'success' => false,
                'error' => 'Paiement introuvable'
            ]);

            return;
        }

        // =====================================================
        // PRODUITS
        // =====================================================

        $stmtProduits = $pdo->prepare("
            SELECT
                cp.quantite,
                cp.prix_ht,
                cp.taux_tva,

                p.id,
                p.nom,
                p.identifiant,
                p.image

            FROM commande_produit cp

            JOIN produit p
                ON p.id = cp.id_produit

            WHERE cp.id_commande = ?
        ");

        $stmtProduits->execute([
            $paiement['id_commande']
        ]);

        $produits =
            $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

        foreach ($produits as &$p) {

            $p['image'] =
                'images/' . $p['image'] . '.png';

            $p['prix_ht'] =
                $p['prix_ht'] / 100;

            $p['prix_ttc'] =
                round(
                    $p['prix_ht']
                    * (1 + $p['taux_tva'] / 100),
                    2
                );
        }

        $paiement['produits'] = $produits;

        echo json_encode([
            'success' => true,
            'data' => $paiement
        ]);

        return;
    }

    // =========================================================
    // LISTE DES PAIEMENTS
    // =========================================================

    $stmt = $pdo->query("
        SELECT
            p.id,
            p.statut,
            (p.montant / 100) AS montant,
            p.date_paiement,
            p.numero_transaction,
            p.moyen_paiement,

            c.id AS id_commande,
            c.numero_facture,
            c.statut AS statut_commande,

            COALESCE(af.nom, cl.nom)
                AS facturation_nom,

            COALESCE(af.prenom, cl.prenom)
                AS facturation_prenom,

            COALESCE(af.email, cl.email)
                AS facturation_email

        FROM paiement p

        JOIN commande c
            ON c.id = p.id_commande

        JOIN client cl
            ON cl.id = c.id_client

        LEFT JOIN adresse af
            ON af.id = c.id_adresse_facturation

        ORDER BY p.id DESC
    ");

    $paiements =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $paiements
    ]);

    // =====================================================
    // DASHBOARD PAIEMENTS
    // =====================================================
    if (isset($_POST['action']) && $_POST['action'] === 'dashboard') {

        $stats = $pdo->query("
            SELECT
                COALESCE(SUM(CASE WHEN statut = 'accepte' THEN montant ELSE 0 END),0) AS ca_total,
                COUNT(*) AS total_paiements,
                SUM(CASE WHEN statut = 'accepte' THEN 1 ELSE 0 END) AS paiements_acceptes
            FROM paiement
        ")->fetch(PDO::FETCH_ASSOC);

        $derniers = $pdo->query("
            SELECT 
                p.id,
                p.statut,
                p.montant,
                p.date_paiement,
                p.numero_transaction,
                a.nom AS facturation_nom,
                a.prenom AS facturation_prenom
            FROM paiement p
            JOIN commande c ON c.id = p.id_commande
            LEFT JOIN adresse a ON a.id = c.id_adresse_facturation
            ORDER BY p.id DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'derniers_paiements' => $derniers
            ]
        ]);

        return;
    }
}