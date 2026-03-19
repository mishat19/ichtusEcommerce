<?php

/* ══════════════════════════════════════════════
 *  CONTROLLER PANIER
 * ══════════════════════════════════════════════ */
function panier() {
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $idPanier = verifPanier();

    /* ══════════════════════════════════════════════
     *  🔥 TRAITEMENT DES ACTIONS (POST)
     * ══════════════════════════════════════════════ */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // ➕ Ajouter produit
        if (isset($_POST['id_produit'], $_POST['quantite'])) {
            ajouterProduitDansPanier(
                (int)$_POST['id_produit'],
                (int)$_POST['quantite']
            );
        }

        // 🔄 Modifier quantité
        elseif (isset($_POST['id_ligne'], $_POST['quantite'])) {
            modifierQuantite(
                (int)$_POST['id_ligne'],
                (int)$_POST['quantite']
            );
        }

        // ❌ Supprimer ligne
        elseif (isset($_POST['id_ligne'])) {
            supprimerLigne((int)$_POST['id_ligne']);
        }

        // 🧹 Vider panier
        elseif (isset($_POST['vider_panier'])) {
            viderPanier();
        }

        // 🔥 REDIRECTION OBLIGATOIRE
        header('Location: /panier');
        exit;
    }

    /* ══════════════════════════════════════════════
     *  📦 RÉCUPÉRATION DES DONNÉES
     * ══════════════════════════════════════════════ */

    // Lignes du panier
    $stmt = $pdo->prepare(
        "SELECT pp.id        AS id_ligne,
                pp.quantite,
                p.id         AS id_produit,
                p.nom,
                p.identifiant,
                p.prix_ht,
                p.image,
                t.taux       AS taux_tva
         FROM panier_produit pp
         JOIN produit p  ON p.id  = pp.id_produit
         LEFT JOIN tva t ON t.id  = p.id_tva
         WHERE pp.id_panier = ?
         ORDER BY pp.id ASC"
    );
    $stmt->execute([$idPanier]);
    $lignes_panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Totaux
    $stmtTotal = $pdo->prepare(
        "SELECT COUNT(pp.id)                                          AS nb_lignes,
                COALESCE(SUM(pp.quantite), 0)                         AS nb_articles,
                COALESCE(SUM(p.prix_ht * pp.quantite), 0)             AS total_ht,
                COALESCE(SUM(p.prix_ht * (1 + t.taux/100) * pp.quantite), 0) AS total_ttc
         FROM panier_produit pp
         JOIN produit p  ON p.id  = pp.id_produit
         LEFT JOIN tva t ON t.id  = p.id_tva
         WHERE pp.id_panier = ?"
    );
    $stmtTotal->execute([$idPanier]);
    $panier = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    /* ══════════════════════════════════════════════
     *  🖥️ AFFICHAGE
     * ══════════════════════════════════════════════ */
    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/v-panier.php';
    require_once 'view/inc/inc.footer.php';
}


/* ══════════════════════════════════════════════
 *  🧠 VÉRIFIER / CRÉER PANIER
 * ══════════════════════════════════════════════ */
function verifPanier(): int {
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Panier existant
    if (isset($_SESSION['id_panier'])) {
        $stmt = $pdo->prepare("SELECT id FROM panier WHERE id = ?");
        $stmt->execute([$_SESSION['id_panier']]);

        if ($stmt->fetch()) {
            return (int) $_SESSION['id_panier'];
        }

        unset($_SESSION['id_panier']);
    }

    // Création panier
    $stmt = $pdo->prepare(
        "INSERT INTO panier (id_client, date_creation)
         VALUES (1, NOW())"
    );
    $stmt->execute();

    $idPanier = (int) $pdo->lastInsertId();
    $_SESSION['id_panier'] = $idPanier;

    return $idPanier;
}


/* ══════════════════════════════════════════════
 *  ➕ AJOUT PRODUIT
 * ══════════════════════════════════════════════ */
function ajouterProduitDansPanier(int $idProduit, int $quantite) {
    global $pdo;

    if ($quantite < 1) return;

    $idPanier = verifPanier();

    $stmt = $pdo->prepare(
        "SELECT id, quantite
         FROM panier_produit
         WHERE id_panier = ? AND id_produit = ?"
    );
    $stmt->execute([$idPanier, $idProduit]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ligne) {
        // Update
        $pdo->prepare(
            "UPDATE panier_produit
             SET quantite = ?
             WHERE id = ? AND id_panier = ?"
        )->execute([
            $ligne['quantite'] + $quantite,
            $ligne['id'],
            $idPanier
        ]);
    } else {
        // Insert
        $pdo->prepare(
            "INSERT INTO panier_produit (id_panier, id_produit, quantite)
             VALUES (?, ?, ?)"
        )->execute([$idPanier, $idProduit, $quantite]);
    }
}


/* ══════════════════════════════════════════════
 *  🔄 MODIFIER QUANTITÉ
 * ══════════════════════════════════════════════ */
function modifierQuantite(int $idLigne, int $quantite) {
    global $pdo;

    $idPanier = verifPanier();

    if ($quantite < 1) {
        supprimerLigne($idLigne);
        return;
    }

    $pdo->prepare(
        "UPDATE panier_produit
         SET quantite = ?
         WHERE id = ? AND id_panier = ?"
    )->execute([$quantite, $idLigne, $idPanier]);
}


/* ══════════════════════════════════════════════
 *  ❌ SUPPRIMER LIGNE
 * ══════════════════════════════════════════════ */
function supprimerLigne(int $idLigne) {
    global $pdo;

    $idPanier = verifPanier();

    $pdo->prepare(
        "DELETE FROM panier_produit
         WHERE id = ? AND id_panier = ?"
    )->execute([$idLigne, $idPanier]);
}


/* ══════════════════════════════════════════════
 *  🧹 VIDER PANIER
 * ══════════════════════════════════════════════ */
function viderPanier() {
    global $pdo;

    $idPanier = verifPanier();

    $pdo->prepare(
        "DELETE FROM panier_produit
         WHERE id_panier = ?"
    )->execute([$idPanier]);
}