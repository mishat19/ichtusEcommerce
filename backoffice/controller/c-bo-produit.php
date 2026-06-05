<?php

function boProduits(): void
{
    global $pdo;

    global $messageSucces, $messageErreur;

    /* ══════════════════════════════════════════════
     *  POST : AJOUT D'UN NOUVEAU PRODUIT
     * ══════════════════════════════════════════════ */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_produit'])) {
        verify_csrf();

        $nom         = trim($_POST['nom'] ?? '');
        $identifiant = trim($_POST['identifiant'] ?? '');
        $prixHt      = (int)($_POST['prix_ht'] ?? 0);
        $idTva       = (int)($_POST['id_tva'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $statut      = $_POST['statut'] ?? 'actif';

        // Validation
        $erreurs = [];
        if (empty($nom)) $erreurs[] = "Le nom du produit est obligatoire.";
        if (empty($identifiant)) $erreurs[] = "L'identifiant (slug) est obligatoire.";
        if ($prixHt <= 0) $erreurs[] = "Le prix HT doit être supérieur à 0 (en centimes).";
        if ($idTva <= 0) $erreurs[] = "Veuillez sélectionner un taux de TVA.";

        // Vérifier unicité identifiant
        if (!empty($identifiant)) {
            $stmtCheck = $pdo->prepare("SELECT id FROM produit WHERE identifiant = ?");
            $stmtCheck->execute([$identifiant]);
            if ($stmtCheck->fetch()) {
                $erreurs[] = "Cet identifiant est déjà utilisé par un autre produit.";
            }
        }

        // Upload image
        $imageFilename = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $erreurs[] = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WebP.";
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageFilename = $identifiant . '.' . strtolower($ext);
                $destination = __DIR__ . '/../../images/' . $imageFilename;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $erreurs[] = "Erreur lors de l'upload de l'image.";
                    $imageFilename = null;
                }
            }
        }

        if (!empty($erreurs)) {
            $messageErreur = implode('<br>', $erreurs);
        } else {
            try {
                $pdo->beginTransaction();

                // INSERT produit
                $stmtInsert = $pdo->prepare("
                    INSERT INTO produit (nom, identifiant, prix_ht, id_tva, image, description, statut)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtInsert->execute([$nom, $identifiant, $prixHt, $idTva, $imageFilename, $description, $statut]);
                $idProduit = $pdo->lastInsertId();

                // INSERT stock (quantité initiale = 0)
                $pdo->prepare("
                    INSERT INTO stock (id_produit, quantite_disponible, quantite_reservee, seuil_alerte)
                    VALUES (?, 0, 0, 15)
                ")->execute([$idProduit]);

                // INSERT qr_code
                $codeUnique = bin2hex(random_bytes(16)); // UUID-like
                $urlProduit = 'https://b2-gp97.kevinpecro.info/produit/' . $identifiant;

                $pdo->prepare("
                    INSERT INTO qr_code (type, id_entite, code, url, est_actif)
                    VALUES ('produit', ?, ?, ?, 1)
                ")->execute([$idProduit, $codeUnique, $urlProduit]);

                $pdo->commit();
                $messageSucces = "Produit \"$nom\" créé avec succès ! Stock initialisé et QR code généré.";

            } catch (Exception $e) {
                $pdo->rollBack();
                $messageErreur = "Erreur lors de la création du produit : " . $e->getMessage();
            }
        }
    }

    /* ══════════════════════════════════════════════
     *  RÉCUPÉRATION DES DONNÉES
     * ══════════════════════════════════════════════ */

    // Tous les produits avec stock et QR
    $stmt = $pdo->query("
        SELECT p.id, p.nom, p.identifiant, p.prix_ht, p.statut, p.image,
               COALESCE(s.quantite_disponible, 0) AS stock_disponible,
               COALESCE(s.quantite_reservee, 0) AS stock_reserve,
               COALESCE(s.seuil_alerte, 15) AS seuil_alerte,
               qr.code AS qr_code,
               qr.url AS qr_url
        FROM produit p
        LEFT JOIN stock s ON s.id_produit = p.id
        LEFT JOIN qr_code qr ON qr.type = 'produit' AND qr.id_entite = p.id AND qr.est_actif = 1
        ORDER BY p.id DESC
    ");

    global $produits;
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalisation des données pour la vue
    foreach ($produits as &$p) {
        if ($p['image']) {
            $p['image'] = 'images/' . $p['image'];
        }
    }

    // Liste des TVA pour le formulaire
    global $listeTva;
    $stmtTva = $pdo->query("SELECT * FROM tva ORDER BY taux ASC");
    $listeTva = $stmtTva->fetchAll(PDO::FETCH_ASSOC);

    // Chargement de la vue
    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-produits.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}
