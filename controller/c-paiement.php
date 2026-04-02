<?php

function paiement(): void
{
    $adressesFacturation = getAdressesByType($_SESSION['idClient'], 'facturation');
    $adressesLivraison = getAdressesByType($_SESSION['idClient'], 'livraison');

    if (empty($adressesFacturation) || empty($adressesLivraison)) {
        $_SESSION['erreur'] = "Veuillez sélectionner une adresse de facturation et de livraison.";
        header('Location: /adresses');
        exit;
    }

    if (!isset($_SESSION['commande']) || !is_array($_SESSION['commande'])) {
        $_SESSION['commande'] = [];
    }

    $_SESSION['commande']['adresse_facturation'] = $adressesFacturation;
    $_SESSION['commande']['adresse_livraison'] = $adressesLivraison;

    $idPanier = verifPanier();
    $idCommande = creerCommande();
    $panier = getTotalCommande($idCommande);

    // 🔐 Identifiants fournis
    $PBX_SITE       = "3277512";
    $PBX_RANG       = "001";
    $PBX_IDENTIFIANT= "38023694";

    // getTotauxPanier retourne le total TTC en centimes (stocké dans produit.prix_ht)
    // PBX attend le montant en centimes : on prend directement la valeur
    $PBX_TOTAL = (int) ($panier['total_ttc'] ?? 0);
    $PBX_DEVISE     = "978";  // EUR

    $PBX_CMD = "25gp1-" . $idCommande;

    $PBX_PORTEUR = "test@test.com";

    $PBX_RETOUR = "Mt:M;Ref:R;Auto:A;Erreur:E";

    // Utilise le host courant pour construire des URLs compatibles avec le routeur (URL propres)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];

    // Remarques: le routeur utilise des chemins comme /retour-paiement, /ipn
    $PBX_EFFECTUE = "$baseUrl/retour-paiement?status=ok";
    $PBX_REFUSE   = "$baseUrl/retour-paiement?status=refuse";
    $PBX_ANNULE   = "$baseUrl/retour-paiement?status=annule";

    $PBX_REPONDRE_A = "$baseUrl/ipn";

    $PBX_TIME = date("c");

    /* 🔐 SIGNATURE HMAC */
    $msg = "PBX_SITE=$PBX_SITE".
        "&PBX_RANG=$PBX_RANG".
        "&PBX_IDENTIFIANT=$PBX_IDENTIFIANT".
        "&PBX_TOTAL=$PBX_TOTAL".
        "&PBX_DEVISE=$PBX_DEVISE".
        "&PBX_CMD=$PBX_CMD".
        "&PBX_PORTEUR=$PBX_PORTEUR".
        "&PBX_RETOUR=$PBX_RETOUR".
        "&PBX_EFFECTUE=$PBX_EFFECTUE".
        "&PBX_REFUSE=$PBX_REFUSE".
        "&PBX_ANNULE=$PBX_ANNULE".
        "&PBX_REPONDRE_A=$PBX_REPONDRE_A".
        "&PBX_TIME=$PBX_TIME";

    $key = hex2bin("E7DD686B8817CD0A6772BBB0C744705A6C3814444C15337FF7878EAFDC1CF4BA67ABAC9E92C8BA5C000C187DAA22CFA9C3182D94C22F69698982A285EBAB8846");

    $hmac = strtoupper(hash_hmac('sha512', $msg, $key));

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/commande/v-commande-paiement.php';
    require_once 'view/inc/inc.footer.php';
}

function retourPaiement()
{
    // La banque peut renvoyer via POST (ou GET selon le mode) : on lit les deux
    $erreur = $_POST['Erreur'] ?? ($_GET['Erreur'] ?? null);
    $status = $_POST['status'] ?? ($_GET['status'] ?? null);

    // Si la banque fournit une référence et/ou un code erreur, on en tient compte
    if ($erreur === "00000" || $status === "ok") {
        $etat = "confirme";
    } elseif ($erreur === "00001" || $status === "annule") {
        $etat = "annule";
    } else {
        $etat = "refuse";
    }

    // Redirection hors iframe vers la page de confirmation avec état en paramètre
    // Utilise URL propre attendue par le routeur : /confirmation/{etat}
    echo "<script>if(window.top) window.top.location.href = '/confirmation/" . addslashes($etat) . "'; else window.location.href = '/confirmation/" . addslashes($etat) . "';</script>";
    exit;
}
function ipnPaiement(): void
{
    global $pdo;

    // 🔥 Données envoyées par la banque
    $commande = $_POST['Ref'] ?? null;
    $montant  = $_POST['Mt'] ?? 0;
    $erreur   = $_POST['Erreur'] ?? '99999';
    $transaction = $_POST['Trans'] ?? 'N/A';
    $moyenPaiement = $_POST['Paiement'] ?? 'CB';

    if (!$commande) return;

    // 🔍 Extraire ID commande
    $idCommande = explode('-', $commande)[1] ?? null;
    if (!$idCommande) return;

    /* ════════════════════════════════════════
     * 🎯 Déterminer le statut
     * ════════════════════════════════════════ */
    if ($erreur === "00000") {
        $statut = "accepte";

        $pdo->prepare("
            UPDATE commande 
            SET statut = 'payee' 
            WHERE id = ?
        ")->execute([$idCommande]);

        paiementAccepte($idCommande);
    } elseif ($erreur === "00001") {
        $statut = "annule";

        $pdo->prepare("
            UPDATE commande 
            SET statut = 'annulee' 
            WHERE id = ?
        ")->execute([$idCommande]);

    } else {
        $statut = "refuse";

        $pdo->prepare("
            UPDATE commande 
            SET statut = 'refusee' 
            WHERE id = ?
        ")->execute([$idCommande]);

        paiementRefuse($idCommande);
    }

    /* ════════════════════════════════════════
     * 💾 INSERT PAIEMENT
     * ════════════════════════════════════════ */
    $stmt = $pdo->prepare("
    SELECT id FROM paiement 
    WHERE numero_transaction = ?
");
    $stmt->execute([$transaction]);

    if ($stmt->fetch()) {
        echo "OK";
        return;
    }

    $pdo->prepare("
        INSERT INTO paiement 
        (id_commande, numero_transaction, montant, statut, date_paiement, code_erreur, moyen_paiement)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $idCommande,
        $transaction,
        $montant,
        $statut,
        date('Y-m-d H:i:s'),
        $erreur,
        $moyenPaiement
    ]);

    echo "OK";
}