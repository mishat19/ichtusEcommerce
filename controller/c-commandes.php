<?php
function commandes(): void
{
    global $pdo;

    $idClient = $_SESSION['idClient'] ?? null;
    if (!$idClient) {
        header('Location: /connexion');
        exit;
    }

    // Récupère toutes les commandes du client
    $stmtCommandes = $pdo->prepare("
        SELECT c.*
        FROM commande c
        WHERE c.id_client = ?
        ORDER BY c.id DESC
    ");
    $stmtCommandes->execute([$idClient]);
    $commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupère les produits associés
    foreach ($commandes as &$commande) {
        $stmtProduits = $pdo->prepare("
            SELECT cp.*, p.nom, p.image
            FROM commande_produit cp
            INNER JOIN produit p ON cp.id_produit = p.id
            WHERE cp.id_commande = ?
        ");
        $stmtProduits->execute([$commande['id']]);
        $commande['produits'] = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/profil/v-commandes.php';
    require_once 'view/inc/inc.footer.php';
}
?>
