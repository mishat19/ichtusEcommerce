<?php

// Étape 1 : Récapitulatif
function commandeRecap(): void
{
    global $pdo;

    if (!isset($_SESSION['idClient'])) {
        header('Location: /connexion');
        exit;
    }

    $idPanier = verifPanier();
    $lignes_panier = getLignesPanier($idPanier);
    $panier = getTotauxPanier($idPanier);

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-recap.php';
    require_once 'view/inc/inc.footer.php';
}

// Étape 2 : Sélection des adresses
function commandeAdresses(): void
{
    global $pdo;

    if (!isset($_SESSION['idClient'])) {
        header('Location: /connexion');
        exit;
    }

    $idClient = $_SESSION['idClient'];

    /* =========================
       POST (DELETE / EDIT / SAVE)
    ========================== */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();

        // ❌ DELETE
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {

            $stmt = $pdo->prepare("SELECT type FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_POST['id'], $idClient]);
            $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($adresse) {
                $type = $adresse['type'];

                $pdo->prepare("DELETE FROM adresse WHERE id = ? AND id_client = ?")
                    ->execute([$_POST['id'], $idClient]);

                // vérifier adresse par défaut
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM adresse 
                    WHERE id_client = ? AND type = ? AND est_par_defaut = 1
                ");
                $stmt->execute([$idClient, $type]);

                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("
                        SELECT id FROM adresse 
                        WHERE id_client = ? AND type = ?
                        ORDER BY id ASC LIMIT 1
                    ");
                    $stmt->execute([$idClient, $type]);
                    $ancienne = $stmt->fetch();

                    if ($ancienne) {
                        $pdo->prepare("UPDATE adresse SET est_par_defaut = 1 WHERE id = ?")
                            ->execute([$ancienne['id']]);
                    }
                }
            }

            header("Location: /adresses");
            exit;
        }

        // ✏️ EDIT (mode édition)
        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_POST['id'], $idClient]);
            $adresseEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 💾 SAVE (INSERT / UPDATE)
        if (isset($_POST['prenom'])) {

            $estParDefaut = isset($_POST['est_par_defaut']) ? 1 : 0;

            if ($estParDefaut) {
                $pdo->prepare("
                    UPDATE adresse SET est_par_defaut = 0 
                    WHERE id_client = ? AND type = ?
                ")->execute([$idClient, $_POST['type']]);
            }

            if (!empty($_POST['id'])) {
                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE adresse SET
                        prenom=?, nom=?, email=?, telephone=?,
                        type=?, adresse=?, complement=?, ville=?, code_postal=?, est_par_defaut=?
                    WHERE id=? AND id_client=?
                ");

                $stmt->execute([
                    $_POST['prenom'],
                    $_POST['nom'],
                    $_POST['email'],
                    $_POST['telephone'],
                    $_POST['type'],
                    $_POST['adresse'],
                    $_POST['complement'] ?? '',
                    $_POST['ville'],
                    $_POST['code_postal'],
                    $estParDefaut,
                    $_POST['id'],
                    $idClient
                ]);
            } else {
                // INSERT
                $stmt = $pdo->prepare("
                    INSERT INTO adresse (
                        id_client, prenom, nom, email, telephone,
                        type, adresse, complement, ville, code_postal, est_par_defaut
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $idClient,
                    $_POST['prenom'],
                    $_POST['nom'],
                    $_POST['email'],
                    $_POST['telephone'],
                    $_POST['type'],
                    $_POST['adresse'],
                    $_POST['complement'] ?? '',
                    $_POST['ville'],
                    $_POST['code_postal'],
                    $estParDefaut
                ]);
            }

            // 🔥 IMPORTANT (comme profil)
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    /* =========================
       DATA
    ========================== */

    $adressesFacturation = getAdressesByType($idClient, 'facturation');
    $adressesLivraison   = getAdressesByType($idClient, 'livraison');

    function getAdresseParDefautOuRecente($adresses) {
        foreach ($adresses as $a) {
            if ($a['est_par_defaut']) return $a;
        }
        return $adresses[0] ?? null;
    }

    $adresseFacturationSelected = getAdresseParDefautOuRecente($adressesFacturation);
    $adresseLivraisonSelected   = getAdresseParDefautOuRecente($adressesLivraison);

    // 🧠 MODE EDIT
    if (!isset($adresseEdit)) {
        $adresseEdit = null;

        if (isset($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_GET['edit'], $idClient]);
            $adresseEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    /* =========================
       VIEW
    ========================== */

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-adresses.php';
    require_once 'view/inc/inc.footer.php';
}

// Étape 3 : Paiement
function commandePaiement(): void
{
    global $pdo;

    if (!isset($_SESSION['idClient'])) {
        header('Location: /connexion');
        exit;
    }

    $idClient = $_SESSION['idClient'];

    // Vérifie que les adresses sont sélectionnées dans le POST
    if (!isset($_POST['id_adresse_facturation']) || !isset($_POST['id_adresse_livraison'])) {
        $_SESSION['erreur'] = "Veuillez sélectionner une adresse de facturation et une adresse de livraison.";
        header('Location: /adresses');
        exit;
    }

    // Stocke les adresses en session pour créer la commande
    $_SESSION['adresse_facturation'] = (int)$_POST['id_adresse_facturation'];
    $_SESSION['adresse_livraison'] = (int)$_POST['id_adresse_livraison'];

    // Appelle la fonction de paiement
    paiement();
}

// Étape 4 : Finalisation de la commande
function creerCommande() {
    global $pdo;

    if (!isset($_SESSION['idClient'])) {
        header('Location: /panier');
        exit;
    }

    $idClient = $_SESSION['idClient'];
    $idPanier = verifPanier();
    $lignesPanier = getLignesPanier($idPanier);
    $panier = getTotauxPanier($idPanier);

    $totalTTC = $panier['total_ttc'] / 100;
    $fraisLivraison = ($totalTTC >= 50) ? 0 : 4.99;
    $totalFinal = round($totalTTC + $fraisLivraison, 2);

    // Vérifier si une commande en attente existe déjà pour ce client et ce panier
    // On évite ainsi les doublons si l'utilisateur rafraîchit la page de paiement
    $stmtCheck = $pdo->prepare("
        SELECT id FROM commande 
        WHERE id_client = ? AND statut = 'en_attente' 
        ORDER BY date_commande DESC LIMIT 1
    ");
    $stmtCheck->execute([$idClient]);
    $existingCommande = $stmtCheck->fetch();

    if ($existingCommande) {
        $idCommande = $existingCommande['id'];
        // On met à jour le montant au cas où le panier aurait changé
        $pdo->prepare("UPDATE commande SET total_ttc = ?, date_commande = NOW() WHERE id = ?")
            ->execute([$totalFinal, $idCommande]);

        // On vide et on re-remplit les produits pour être sûr qu'ils correspondent au panier actuel
        $pdo->prepare("DELETE FROM commande_produit WHERE id_commande = ?")->execute([$idCommande]);
    } else {
        $numeroFacture = 'FACT-' . date('Ymd') . '-' . strtoupper(uniqid());

        $stmt = $pdo->prepare("
            INSERT INTO commande (
                id_client, numero_facture, total_ttc, frais_livraison,
                id_adresse_facturation, id_adresse_livraison, date_commande, statut
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
        ");

        $adresseFact = getAdressesByType($_SESSION['idClient'], 'facturation')[0]['id'];
        $adresseLivr = getAdressesByType($_SESSION['idClient'], 'livraison')[0]['id'];

        if (empty($adresseFact) || empty($adresseLivr)) {
            $_SESSION['erreur'] = "Adresses manquantes. Veuillez sélectionner vos adresses.";
            header('Location: /adresses');
            exit;
        }

        $stmt->execute([
            $idClient,
            $numeroFacture,
            $totalFinal,
            $fraisLivraison,
            $adresseFact,
            $adresseLivr,
            'en_attente',
        ]);

        $idCommande = $pdo->lastInsertId();
    }

    // Produits
    foreach ($lignesPanier as $ligne) {
        $stmtProduit = $pdo->prepare("
            INSERT INTO commande_produit (
                id_commande, id_produit, prix_ht, taux_tva, quantite
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $stmtProduit->execute([
            $idCommande,
            $ligne['id_produit'],
            $ligne['prix_ht'],
            $ligne['taux_tva'],
            $ligne['quantite']
        ]);
    }

    return $idCommande;
}

function getCommandeById($idCommande) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM commande WHERE id = ?");
    $stmt->execute([$idCommande]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function paiementRefuse($idCommande) {
    global $pdo;

    // 1. Récupère les produits de la commande pour libérer les réserves
    $stmt = $pdo->prepare("SELECT id_produit, quantite FROM commande_produit WHERE id_commande = ?");
    $stmt->execute([$idCommande]);
    $produitsCommande = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produitsCommande as $produit) {
        $idProduit = $produit['id_produit'];
        $quantite = $produit['quantite'];

        // Libère les quantités réservées (mais ne touche pas à quantite_disponible)
        $pdo->prepare("
            UPDATE stock
            SET quantite_reservee = quantite_reservee - ?
            WHERE id_produit = ?
        ")->execute([$quantite, $idProduit]);
    }

    // 2. Supprime les produits de la commande
    $pdo->prepare("DELETE FROM commande_produit WHERE id_commande = ?")
        ->execute([$idCommande]);

    // 3. Supprime la commande
    $pdo->prepare("DELETE FROM commande WHERE id = ?")
        ->execute([$idCommande]);
}

// Page de confirmation
function commandeConfirmation(): void
{
    global $param, $pdo;

    $etat = $param ?? 'confirme';

    // Si le paiement est confirmé, on vide le panier et on met à jour le stock
    if ($etat === 'confirme' || $etat === 'ok') {
        if (isset($_SESSION['idClient'])) {
            $idPanier = verifPanier();

            // 📦 Mise à jour du stock : décrémenter les quantités réservées et disponibles
            if (isset($_SESSION['panier_produits'])) {
                foreach ($_SESSION['panier_produits'] as $ligne) {
                    $idProduit = $ligne['id_produit'];
                    $quantite = $ligne['quantite'];

                    // 1. Décrémenter quantite_disponible et quantite_reservee
                    $pdo->prepare("
                        UPDATE stock
                        SET
                            quantite_disponible = quantite_disponible - ?,
                            quantite_reservee = quantite_reservee - ?
                        WHERE id_produit = ?
                    ")->execute([$quantite, $quantite, $idProduit]);

                    // 2. (Optionnel) Mettre à jour la date de dernière mise à jour
                    $pdo->prepare("
                        UPDATE stock
                        SET date_derniere_mise_a_jour = NOW()
                        WHERE id_produit = ?
                    ")->execute([$idProduit]);
                }
            }

            // Vider le panier
            $pdo->prepare("DELETE FROM panier_produit WHERE id_panier = ?")
                ->execute([$idPanier]);

            // Nettoyer les adresses inutilisées
            nettoyerAdressesInutilisees($_SESSION['idClient']);
        }
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-confirmation.php';
    require_once 'view/inc/inc.footer.php';

    // Nettoyage des variables de session
    unset($_SESSION['commande']);
    unset($_SESSION['panier_produits']);
}

// Nettoie les adresses inutilisées
function nettoyerAdressesInutilisees($idClient): void
{
    global $pdo;

    // 1. Récupère les IDs des adresses utilisées dans les commandes
    $stmt = $pdo->prepare("
        SELECT id_adresse_facturation, id_adresse_livraison
        FROM commande
        WHERE id_client = ?
    ");
    $stmt->execute([$idClient]);
    $adressesUtilisees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $idsUtilises = [];
    foreach ($adressesUtilisees as $cmd) {
        $idsUtilises[] = $cmd['id_adresse_facturation'];
        $idsUtilises[] = $cmd['id_adresse_livraison'];
    }
    $idsUtilises = array_unique($idsUtilises);

    // 2. Supprime les adresses non utilisées (sauf si c'est la seule adresse du client)
    if (!empty($idsUtilises)) {
        $placeholders = implode(',', array_fill(0, count($idsUtilises), '?'));
        $stmt = $pdo->prepare("
            DELETE FROM adresse
            WHERE id_client = ? AND id NOT IN ($placeholders)
        ");
        $stmt->execute(array_merge([$idClient], $idsUtilises));
    }
}
