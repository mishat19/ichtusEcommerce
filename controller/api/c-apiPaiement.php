<?php

function APIPaiement() {
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

        $idPaiement = (int)$_POST['id'];

        /* ── Infos paiement ── */
        $stmtPaiement = $pdo->prepare("SELECT * FROM paiement WHERE id = ?");
        $stmtPaiement->execute([$idPaiement]);
        $paiement = $stmtPaiement->fetch(PDO::FETCH_ASSOC);
        if (!$paiement) {
            http_response_code(404);
            die(json_encode(['erreur' => 'Paiement introuvable']));
        }

        /* ── Commande associée ── */
        $stmtCommande = $pdo->prepare(
            "SELECT c.id,
                    c.statut,
                    c.total_ttc,
                    c.facturation_nom,
                    c.facturation_prenom,
                    c.facturation_email,
                    c.facturation_telephone,
                    c.facturation_adresse,
                    c.facturation_code_postal,
                    c.facturation_ville,
                    c.livraison_nom,
                    c.livraison_prenom,
                    c.livraison_adresse,
                    c.livraison_code_postal,
                    c.livraison_ville
             FROM commande c
             WHERE c.id = ?"
        );
        $stmtCommande->execute([$paiement['id_commande']]);
        $commande = $stmtCommande->fetch(PDO::FETCH_ASSOC);

        $paiement['commande'] = $commande;

        echo json_encode($paiement);
    } else {

        /* ── Liste tous les paiements avec infos commande essentielles ── */
        $lstPaiement = $pdo->query(
            "SELECT *
             FROM paiement p
             JOIN commande c ON c.id = p.id_commande
             ORDER BY p.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($lstPaiement);
    }
}