<?php

function BOPaiement() {
    global $pdo;

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {

        /* ── Détail paiement ── */
        $stmt = $pdo->prepare("
            SELECT p.*, (p.montant / 100) AS montant, p.numero_transaction AS ref_banque, c.numero_facture
            FROM paiement p
            JOIN commande c ON c.id = p.id_commande
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);

        global $bo_paiement;
        $bo_paiement = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bo_paiement) {
            header('Location: /backoffice/paiements/');
            exit;
        }

        /* ── Commande ── */
        $stmtC = $pdo->prepare("
            SELECT 
                c.*, 
                af.prenom AS facturation_prenom,
                af.nom AS facturation_nom,
                af.email AS facturation_email,
                af.adresse AS facturation_adresse,
                af.code_postal AS facturation_code_postal,
                af.ville AS facturation_ville,

                al.prenom AS livraison_prenom,
                al.nom AS livraison_nom,
                al.adresse AS livraison_adresse,
                al.code_postal AS livraison_code_postal,
                al.ville AS livraison_ville

            FROM commande c
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
            LEFT JOIN adresse al ON al.id = c.id_adresse_livraison
            WHERE c.id = ?
        ");
        $stmtC->execute([$bo_paiement['id_commande']]);

        global $bo_paiement_commande;
        $bo_paiement_commande = $stmtC->fetch(PDO::FETCH_ASSOC);

        /* ── Produits ── */
        $stmtP = $pdo->prepare("
            SELECT cp.quantite, cp.prix_ht, cp.taux_tva,
                   p.nom, p.identifiant, p.image
            FROM commande_produit cp
            JOIN produit p ON p.id = cp.id_produit
            WHERE cp.id_commande = ?
        ");
        $stmtP->execute([$bo_paiement['id_commande']]);

        global $bo_paiement_produits;
        $bo_paiement_produits = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bo_paiement_produits as &$p) {
            $p['image'] = 'images/' . $p['image'] . '.png';
            $p['prix_ht'] = $p['prix_ht'] / 100; // Normalisation en euros
            $p['prix_ttc'] = round($p['prix_ht'] * (1 + $p['taux_tva'] / 100), 2);
        }

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-paiement-detail.php';
        require_once 'backoffice/view/inc/inc.footer.php';

    } else {

        /* ── Liste paiements ── */
        global $bo_paiements;

        $bo_paiements = $pdo->query("
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

                COALESCE(af.nom, cl.nom) AS facturation_nom,
                COALESCE(af.prenom, cl.prenom) AS facturation_prenom,
                COALESCE(af.email, cl.email) AS facturation_email

            FROM paiement p
            JOIN commande c ON c.id = p.id_commande
            JOIN client cl ON cl.id = c.id_client
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation

            ORDER BY p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-paiement.php';
        require_once 'backoffice/view/inc/inc.footer.php';
    }
}