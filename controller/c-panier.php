<?php

/* ══════════════════════════════════════════════
 *  CONTROLLER PANIER
 * ══════════════════════════════════════════════ */
function panier(): void
{
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $idPanier = verifPanier();

    /* ══════════════════════════════════════════════
     *  ⏰ VÉRIFICATION EXPIRATION PANIER (20 min)
     * ══════════════════════════════════════════════ */
    if ($idPanier && isset($_SESSION['panier_reservation_time'])) {
        $elapsed = time() - $_SESSION['panier_reservation_time'];
        if ($elapsed > 1200) { // 20 minutes = 1200 secondes
            viderPanierExpire($idPanier);
            $_SESSION['panier_expire'] = true;
            unset($_SESSION['panier_reservation_time']);
            header('Location: /panier');
            exit;
        }
    }

    /* ══════════════════════════════════════════════
     *  🔥 TRAITEMENT DES ACTIONS (POST)
     * ══════════════════════════════════════════════ */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        // 🔄 Modifier quantité
        if (isset($_POST['id_ligne'], $_POST['quantite']) && $_POST['id_ligne'] && $_POST['quantite'] >= 0) {
            $idLigne = (int)$_POST['id_ligne'];
            $newQte  = (int)$_POST['quantite'];

            // Récupérer l'ancienne quantité pour ajuster la réservation
            $stmtOld = $pdo->prepare("SELECT id_produit, quantite FROM panier_produit WHERE id = ?");
            $stmtOld->execute([$idLigne]);
            $oldLigne = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if ($oldLigne) {
                $diff = $newQte - (int)$oldLigne['quantite'];
                if ($diff > 0) {
                    // Vérifier stock disponible pour l'augmentation
                    $stmtStock = $pdo->prepare("
                        SELECT COALESCE(quantite_disponible, 0) - COALESCE(quantite_reservee, 0) AS stock_reel
                        FROM stock WHERE id_produit = ?
                    ");
                    $stmtStock->execute([$oldLigne['id_produit']]);
                    $stockRow = $stmtStock->fetch(PDO::FETCH_ASSOC);
                    $stockReel = $stockRow ? (int)$stockRow['stock_reel'] : 0;

                    if ($diff > $stockReel) {
                        $_SESSION['erreur_panier'] = "Stock insuffisant. Seulement " . ((int)$oldLigne['quantite'] + $stockReel) . " unité(s) disponible(s).";
                        header('Location: /panier');
                        exit;
                    }
                    // Réserver le surplus
                    $pdo->prepare("UPDATE stock SET quantite_reservee = quantite_reservee + ? WHERE id_produit = ?")
                        ->execute([$diff, $oldLigne['id_produit']]);
                } elseif ($diff < 0) {
                    // Libérer du stock réservé
                    $pdo->prepare("UPDATE stock SET quantite_reservee = GREATEST(0, quantite_reservee + ?) WHERE id_produit = ?")
                        ->execute([$diff, $oldLigne['id_produit']]);
                }
            }

            modifierQuantite($idLigne, $newQte);
            $_SESSION['panier_reservation_time'] = time(); // Reset timer
        }

        // ❌ Supprimer ligne
        elseif (isset($_POST['id_ligne']) && $_POST['id_ligne']) {
            $idLigne = (int)$_POST['id_ligne'];

            // Libérer le stock réservé
            $stmtOld = $pdo->prepare("SELECT id_produit, quantite FROM panier_produit WHERE id = ?");
            $stmtOld->execute([$idLigne]);
            $oldLigne = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if ($oldLigne) {
                $pdo->prepare("UPDATE stock SET quantite_reservee = GREATEST(0, quantite_reservee - ?) WHERE id_produit = ?")
                    ->execute([(int)$oldLigne['quantite'], $oldLigne['id_produit']]);
            }

            supprimerLigne($idLigne);
        }

        /* ══════════════════════════════════════════════
         *  ✅ VALIDER LA COMMANDE
         * ══════════════════════════════════════════════ */
        elseif (isset($_POST['valider_commande'])) {
            header('Location: /recapitulatif');
            exit;
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
                t.taux       AS taux_tva,
                COALESCE(s.quantite_disponible, 0) - COALESCE(s.quantite_reservee, 0) AS stock_reel
         FROM panier_produit pp
         JOIN produit p  ON p.id  = pp.id_produit
         LEFT JOIN tva t ON t.id  = p.id_tva
         LEFT JOIN stock s ON s.id_produit = p.id
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

    // Temps restant pour la réservation
    $tempsRestant = 0;
    if (isset($_SESSION['panier_reservation_time'])) {
        $tempsRestant = max(0, 1200 - (time() - $_SESSION['panier_reservation_time']));
    }

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
function ajouterProduitDansPanier(int $idProduit, int $quantite): void
{
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
        unset($_SESSION['panier_reservation_time']);
    }
}

/* ══════════════════════════════════════════════
 *  🕐 VIDER PANIER EXPIRÉ (20 min)
 * ══════════════════════════════════════════════ */
function viderPanierExpire(int $idPanier): void
{
    global $pdo;

    // Récupérer toutes les lignes du panier pour libérer le stock
    $stmt = $pdo->prepare("SELECT id_produit, quantite FROM panier_produit WHERE id_panier = ?");
    $stmt->execute([$idPanier]);
    $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lignes as $ligne) {
        $pdo->prepare("
            UPDATE stock 
            SET quantite_reservee = GREATEST(0, quantite_reservee - ?),
                date_derniere_mise_a_jour = NOW()
            WHERE id_produit = ?
        ")->execute([(int)$ligne['quantite'], $ligne['id_produit']]);
    }

    // Supprimer les lignes du panier
    $pdo->prepare("DELETE FROM panier_produit WHERE id_panier = ?")->execute([$idPanier]);

    // Supprimer le panier
    $pdo->prepare("DELETE FROM panier WHERE id = ?")->execute([$idPanier]);
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

// Récupère les lignes du panier
function getLignesPanier($idPanier) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT pp.id AS id_ligne, pp.quantite,
               p.id AS id_produit, p.nom, p.identifiant, p.prix_ht, p.image,
               t.taux AS taux_tva
        FROM panier_produit pp
        JOIN produit p ON p.id = pp.id_produit
        LEFT JOIN tva t ON t.id = p.id_tva
        WHERE pp.id_panier = ?
    ");
    $stmt->execute([$idPanier]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère les totaux du panier
function getTotauxPanier($idPanier) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT
            COUNT(pp.id) AS nb_lignes,
            COALESCE(SUM(pp.quantite), 0) AS nb_articles,
            COALESCE(SUM(p.prix_ht * pp.quantite), 0) AS total_ht,
            COALESCE(SUM(p.prix_ht * (1 + t.taux/100) * pp.quantite), 0) AS total_ttc
        FROM panier_produit pp
        JOIN produit p ON p.id = pp.id_produit
        LEFT JOIN tva t ON t.id = p.id_tva
        WHERE pp.id_panier = ?
    ");
    $stmt->execute([$idPanier]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTotalCommande($idCommande){
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            total_ttc
        FROM commande
        WHERE id = ?
    ");
    $stmt->execute([$idCommande]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php if (isset($_SESSION['erreur']) && strpos($_SESSION['erreur'], 'adresses') !== false): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Adresses manquantes',
                text: '<?php e($_SESSION['erreur']); ?>',
                confirmButtonText: 'Aller à mon profil',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/profil';
                }
            });
            <?php unset($_SESSION['erreur']); ?>
        });
    </script>
<?php endif; ?>
