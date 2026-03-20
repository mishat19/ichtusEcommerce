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
        // 🔄 Modifier quantité
        if (isset($_POST['id_ligne'], $_POST['quantite']) && $_POST['id_ligne'] && $_POST['quantite'] >= 0) {
            modifierQuantite(
                (int)$_POST['id_ligne'],
                (int)$_POST['quantite']
            );
        }

        // ❌ Supprimer ligne
        elseif (isset($_POST['id_ligne']) && $_POST['id_ligne']) {
            supprimerLigne((int)$_POST['id_ligne']);
        }

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
function verifPanier() {
    global $pdo;
    $id_client = $_SESSION['idClient'] ?? null;
    if (!$id_client) return false;

    $sql = "SELECT id FROM panier WHERE id_client = :id_client";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_client' => $id_client]);
    $panier = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($panier) {
        return $panier['id'];
    } else {
        $query = "INSERT INTO panier (id_client, date_creation) VALUES(:id_client, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_client' => $id_client]);
        return $pdo->lastInsertId();
    }
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

    // Suppression de la ligne
    $pdo->prepare(
        "DELETE FROM panier_produit
         WHERE id = ? AND id_panier = ?"
    )->execute([$idLigne, $idPanier]);

    // Vérification si le panier est vide
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as count FROM panier_produit
         WHERE id_panier = ?"
    );
    $stmt->execute([$idPanier]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si le panier est vide, on le supprime
    if ($result['count'] == 0) {
        $pdo->prepare(
            "DELETE FROM panier
             WHERE id = ?"
        )->execute([$idPanier]);
    }
}

function getNombreArticlesDansPanier(): int
{
    // Vérifie si l'utilisateur est connecté (idClient en session)
    if (!isset($_SESSION['idClient'])) {
        return 0;
    }

    global $pdo;
    $idClient = $_SESSION['idClient'];

    // 1. Récupère l'id du panier actif pour ce client
    $stmtPanier = $pdo->prepare("
        SELECT id
        FROM panier
        WHERE id_client = ?
        ORDER BY date_creation DESC
        LIMIT 1
    ");
    $stmtPanier->execute([$idClient]);
    $panier = $stmtPanier->fetch(PDO::FETCH_ASSOC);

    // Si aucun panier actif, retourne 0
    if (!$panier) {
        return 0;
    }

    $idPanier = $panier['id'];

    // 2. Compte le nombre d'articles dans ce panier
    $stmtArticles = $pdo->prepare("
        SELECT SUM(quantite) as nb_articles
        FROM panier_produit
        WHERE id_panier = ?
    ");
    $stmtArticles->execute([$idPanier]);
    $result = $stmtArticles->fetch(PDO::FETCH_ASSOC);

    return (int)($result['nb_articles'] ?? 0);
}
