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

        /* ══════════════════════════════════════════════
         *  ✅ VALIDER LA COMMANDE
         * ══════════════════════════════════════════════ */
        elseif (isset($_POST['valider_commande'])) {
            //validerCommande();
            header('Location: /commande-recap');
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

function validerCommande() {
    global $pdo;

    // 1. Vérifie que l'utilisateur est connecté
    if (!isset($_SESSION['idClient'])) {
        header('Location: /connexion');
        exit;
    }

    $idClient = $_SESSION['idClient'];
    $idPanier = verifPanier();

    // Vérifie si l'utilisateur a des adresses
    $adressesFacturation = getAdressesByType($idClient, 'facturation')[0]['id'] ?? null;
    $adressesLivraison   = getAdressesByType($idClient, 'livraison')[0]['id'] ?? null;

    if (empty($adressesFacturation) || empty($adressesLivraison)) {
        $_SESSION['erreur'] = "Veuillez renseigner vos adresses de facturation et de livraison dans votre profil.";
        header('Location: /profil');
        exit;
    }

    // 3. Récupère les produits du panier
    $stmtPanier = $pdo->prepare("
        SELECT pp.id_produit, pp.quantite, p.prix_ht, p.id_tva, t.taux
        FROM panier_produit pp
        JOIN produit p ON p.id = pp.id_produit
        JOIN tva t ON t.id = p.id_tva
        WHERE pp.id_panier = ?
    ");
    $stmtPanier->execute([$idPanier]);
    $lignesPanier = $stmtPanier->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lignesPanier)) {
        $_SESSION['erreur'] = "Votre panier est vide.";
        header('Location: /panier');
        exit;
    }

    // 4. Calcule le total TTC
    $totalTTC = 0;
    foreach ($lignesPanier as $ligne) {
        $totalTTC += $ligne['prix_ht'] * $ligne['quantite'] * (1 + $ligne['taux'] / 100);
    }

    // 5. Génère un numéro de facture
    $numeroFacture = 'FACT-' . date('Ymd') . '-' . strtoupper(uniqid());

    // 6. Crée la commande avec les adresses de session
    $stmtCommande = $pdo->prepare("
        INSERT INTO commande (
            id_client, numero_facture, total_ttc, id_adresse_facturation, id_adresse_livraison, date_commande
        )
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmtCommande->execute([
        $idClient,
        $numeroFacture,
        $totalTTC,
        getAdressesByType($_SESSION['idClient'], 'facturation'),
        getAdressesByType($_SESSION['idClient'], 'livraison'),
    ]);

    $idCommande = $pdo->lastInsertId();

    // 7. Ajoute les produits à la commande
    foreach ($lignesPanier as $ligne) {
        $stmtProduit = $pdo->prepare("
            INSERT INTO commande_produit (
                id_commande, id_produit, prix_ht, taux_tva, quantite
            )
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtProduit->execute([
            $idCommande,
            $ligne['id_produit'],
            $ligne['prix_ht'],
            $ligne['taux'],
            $ligne['quantite']
        ]);
    }

    // 8. Vide le panier
    $pdo->prepare("DELETE FROM panier_produit WHERE id_panier = ?")->execute([$idPanier]);

    // 9. Retourne l'ID de la commande
    return $idCommande;
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
?>

<?php if (isset($_SESSION['erreur']) && strpos($_SESSION['erreur'], 'adresses') !== false): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Adresses manquantes',
                text: '<?= $_SESSION['erreur'] ?>',
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
