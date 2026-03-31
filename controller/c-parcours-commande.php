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

    // Vérifie si l'utilisateur a des adresses
    $adressesFacturation = getAdressesByType($_SESSION['idClient'], 'facturation');
    $adressesLivraison = getAdressesByType($_SESSION['idClient'], 'livraison');

    if (empty($adressesFacturation) || empty($adressesLivraison)) {
        $_SESSION['erreur'] = "Veuillez renseigner vos adresses de facturation et de livraison dans votre profil.";
        header('Location: /profil');
        exit;
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-adresses.php';
    require_once 'view/inc/inc.footer.php';
}

// Étape 3 : Paiement
function commandePaiement(): void
{
    global $pdo;

    if (!isset($_SESSION['idClient']) || !isset($_POST['id_adresse_facturation'], $_POST['id_adresse_livraison'])) {
        header('Location: /commande-recap');
        exit;
    }

    $idPanier = verifPanier();
    $lignes_panier = getLignesPanier($idPanier);
    $panier = getTotauxPanier($idPanier);

    if (empty($lignes_panier)) {
        $_SESSION['erreur'] = "Votre panier est vide.";
        header('Location: /panier');
        exit;
    }

    // Stocke les IDs d'adresse en session pour la finalisation
    $_SESSION['commande']['adresse_facturation'] = $_POST['id_adresse_facturation'];
    $_SESSION['commande']['adresse_livraison'] = $_POST['id_adresse_livraison'];

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-paiement.php';
    require_once 'view/inc/inc.footer.php';
}

// Étape 4 : Finalisation de la commande
function commandeFinaliser() {
    global $pdo;

    if (!isset($_SESSION['idClient']) || !isset($_SESSION['commande'])) {
        header('Location: /panier');
        exit;
    }

    $idClient = $_SESSION['idClient'];
    $idPanier = verifPanier();
    $lignesPanier = getLignesPanier($idPanier);
    $panier = getTotauxPanier($idPanier);

    // 1. Calcule le total TTC
    $totalTTC = $panier['total_ttc'] / 100;
    $fraisLivraison = ($totalTTC >= 50) ? 0 : 4.99;
    $totalFinal = $totalTTC + $fraisLivraison;

    // 2. Génère un numéro de facture
    $numeroFacture = 'FACT-' . date('Ymd') . '-' . strtoupper(uniqid());

    // 3. Crée la commande
    $stmtCommande = $pdo->prepare("
        INSERT INTO commande (
            id_client, numero_facture, total_ttc,
            id_adresse_facturation, id_adresse_livraison, date_commande
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmtCommande->execute([
        $idClient,
        $numeroFacture,
        $totalFinal,
        $_SESSION['commande']['adresse_facturation'],
        $_SESSION['commande']['adresse_livraison']
    ]);

    $idCommande = $pdo->lastInsertId();

    // 4. Ajoute les produits à la commande
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

    // 5. Vide le panier
    $pdo->prepare("DELETE FROM panier_produit WHERE id_panier = ?")->execute([$idPanier]);

    // 6. Nettoie les adresses inutilisées (voir fonction ci-dessous)
    nettoyerAdressesInutilisees($idClient);

    // 7. Stocke l'ID de la commande pour la page de confirmation
    $_SESSION['commande']['id'] = $idCommande;
    $_SESSION['commande']['numero'] = $numeroFacture;

    header('Location: /commande-confirmation');
    exit;
}

// Page de confirmation
function commandeConfirmation(): void
{
    if (!isset($_SESSION['commande']['id'])) {
        header('Location: /panier');
        exit;
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-confirmation.php';
    require_once 'view/inc/inc.footer.php';

    // Nettoie la session après affichage
    unset($_SESSION['commande']);
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
