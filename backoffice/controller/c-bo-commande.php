<?php

function BOCommande() {
    global $pdo;

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {

        /* ── Détail commande ── */
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                
                af.prenom AS fact_prenom,
                af.nom AS fact_nom,
                af.email AS fact_email,
                af.telephone AS fact_tel,
                af.adresse AS fact_adresse,
                af.complement AS fact_complement,
                af.code_postal AS fact_cp,
                af.ville AS fact_ville,

                al.prenom AS liv_prenom,
                al.nom AS liv_nom,
                al.email AS liv_email,
                al.telephone AS liv_tel,
                al.adresse AS liv_adresse,
                al.complement AS liv_complement,
                al.code_postal AS liv_cp,
                al.ville AS liv_ville

            FROM commande c
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation
            LEFT JOIN adresse al ON al.id = c.id_adresse_livraison

            WHERE c.id = ?
        ");
        $stmt->execute([$id]);

        global $bo_commande;
        $bo_commande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bo_commande) {
            header('Location: /backoffice/commandes/');
            exit;
        }

        /* ── Produits ── */
        $stmtP = $pdo->prepare("
            SELECT cp.quantite, cp.prix_ht, cp.taux_tva,
                   p.nom, p.identifiant, p.image
            FROM commande_produit cp
            JOIN produit p ON p.id = cp.id_produit
            WHERE cp.id_commande = ?
        ");
        $stmtP->execute([$id]);

        global $bo_commande_produits;
        $bo_commande_produits = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bo_commande_produits as &$p) {
            $imgs = array_values(array_filter(array_map('trim', explode(',', $p['image'] ?? ''))));
            $p['image'] = $imgs[0] ?? null;
            $p['prix_ht'] = $p['prix_ht'] / 100; // Normalisation en euros
            $p['prix_ttc'] = round($p['prix_ht'] * (1 + $p['taux_tva'] / 100), 2);
        }

        /* ── Paiement ── */
        $stmtPay = $pdo->prepare("
            SELECT id, id_commande, numero_transaction, (montant / 100) AS montant, statut, date_paiement
            FROM paiement
            WHERE id_commande = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtPay->execute([$id]);

        global $bo_commande_paiement;
        $bo_commande_paiement = $stmtPay->fetch(PDO::FETCH_ASSOC);

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-commande-detail.php';
        require_once 'backoffice/view/inc/inc.footer.php';

    } else {

        /* ── Liste commandes ── */
        global $bo_commandes;

        $bo_commandes = $pdo->query("
            SELECT 
                c.id,
                c.numero_facture,
                c.statut,
                c.total_ttc,
                c.date_commande,

                cl.nom,
                cl.prenom,
                cl.email,

                af.ville AS ville_facturation,

                (SELECT COUNT(*) 
                 FROM commande_produit cp 
                 WHERE cp.id_commande = c.id) AS nb_produits

            FROM commande c
            JOIN client cl ON cl.id = c.id_client
            LEFT JOIN adresse af ON af.id = c.id_adresse_facturation

            ORDER BY c.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        require_once 'backoffice/view/inc/inc.head.php';
        require_once 'backoffice/view/inc/inc.header.php';
        require_once 'backoffice/view/v-commandes.php';
        require_once 'backoffice/view/inc/inc.footer.php';
    }
}