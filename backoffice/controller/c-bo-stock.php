<?php
/**
 * BACKOFFICE — GESTION DU STOCK / ENTREPÔTS
 * Utilise les fonctions de c-apiStock.php directement (sans HTTP)
 */

// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fonctions de l'API
require_once 'controller/api/c-apiStock.php';

// Fonction pour vérifier le token CSRF
if (!function_exists('verify_csrf')) {
    function verify_csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                die("Erreur : Token CSRF invalide.");
            }
            unset($_SESSION['csrf_token']);
        }
    }
}

// Fonction pour générer un champ CSRF
if (!function_exists('csrf_field')) {
    function csrf_field() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}

// =====================================================
// FONCTIONS POUR EXÉCUTER LES ACTIONS DE L'API SANS HTTP
// =====================================================
/**
 * Exécute une action de l'API et retourne le résultat sous forme de tableau PHP
 * @param string $action Nom de l'action (ex: 'getEntrepots')
 * @param array $data Données à passer à l'action
 * @return array Résultat de l'action (ex: ['success' => true, 'message' => '...'])
 */
function executeStockAction($action, $data = []) {
    global $pdo;

    // Sauvegarde
    $oldPost = $_POST;
    $oldGet = $_GET;

    // Faux POST API
    $_POST = array_merge($data, [
        'action' => $action,
        'token' => 'WDIhUThWMz9aN0Y0VDFwOUE2'
    ]);

    // Capture sortie
    ob_start();
    APIStock();
    $output = ob_get_clean();

    // Restauration
    $_POST = $oldPost;
    $_GET = $oldGet;

    return json_decode($output, true);
}

// =====================================================
// FONCTION PRINCIPALE POUR GÉRER LES ENTREPÔTS
// =====================================================
function BOEntrepots() {
    global $pdo, $messageSucces, $messageErreur;
    $messageSucces = '';
    $messageErreur = '';

    // Vérifie et traite les actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();

        $action = $_POST['action'] ?? '';

        // CRÉATION D'ENTREPÔT
        if (isset($_POST['create_entrepot'])) {
            $result = executeStockAction('createEntrepot', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // AJOUTER UN MEUBLE
        elseif ($action === 'add_meuble') {
            $result = executeStockAction('addMeuble', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // MODIFIER UN MEUBLE
        elseif ($action === 'edit_meuble') {
            $result = executeStockAction('editMeuble', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // SUPPRIMER UN MEUBLE
        elseif ($action === 'delete_meuble') {
            $result = executeStockAction('deleteMeuble', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // AJOUTER UN STACK
        elseif ($action === 'add_stack') {
            $result = executeStockAction('addStack', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // MODIFIER UN STACK
        elseif ($action === 'edit_stack') {
            $result = executeStockAction('editStack', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // SUPPRIMER UN STACK
        elseif ($action === 'delete_stack') {
            $result = executeStockAction('deleteStack', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // MODIFIER UN ENTREPÔT
        elseif ($action === 'edit_entrepot') {
            $result = executeStockAction('editEntrepot', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
        // SUPPRIMER UN ENTREPÔT
        elseif ($action === 'delete_entrepot') {
            $result = executeStockAction('deleteEntrepot', $_POST);
            if (isset($result['success'])) {
                $messageSucces = $result['message'];
            } else {
                $messageErreur = $result['error'] ?? 'Erreur inconnue';
            }
        }
    }

    // Récupération des entrepôts et leurs données
    $result = executeStockAction('getEntrepots');

    if (!empty($result) && is_array($result)){
        $entrepots = $result;
    } else {
        $entrepots = [];
        $messageErreur = $result['error'] ?? 'Erreur lors de la récupération des entrepôts';
    }

    // Ajoute les produits pour chaque stack
    foreach ($entrepots as &$e) {
        foreach ($e['meubles'] as &$m) {
            foreach ($m['stacks'] as &$s) {
                $stackProduits = executeStockAction('getStackProduits', ['id_stack' => $s['id']]);
                $s['produits'] = $stackProduits['produits'] ?? [];
                $s['taux_occupation'] = $s['capacite_max'] > 0
                    ? round(($s['capacite_utilisee'] / $s['capacite_max']) * 100, 1)
                    : 0;
            }
            unset($s);

            $m['total_capacite'] = array_reduce($m['stacks'], function($sum, $s) {
                return $sum + $s['capacite_max'];
            }, 0);
            $m['total_utilise'] = array_reduce($m['stacks'], function($sum, $s) {
                return $sum + $s['capacite_utilisee'];
            }, 0);
            $m['taux_occupation'] = $m['total_capacite'] > 0
                ? round(($m['total_utilise'] / $m['total_capacite']) * 100, 1)
                : 0;
        }
        unset($m);

        $e['total_capacite'] = array_reduce($e['meubles'], function($sum, $m) {
            return $sum + $m['total_capacite'];
        }, 0);
        $e['total_utilise'] = array_reduce($e['meubles'], function($sum, $m) {
            return $sum + $m['total_utilise'];
        }, 0);
        $e['taux_occupation'] = $e['total_capacite'] > 0
            ? round(($e['total_utilise'] / $e['total_capacite']) * 100, 1)
            : 0;
    }
    unset($e);

    global $entrepotsList;
    $entrepotsList = $entrepots;

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-entrepots.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

// =====================================================
// FONCTION POUR GÉRER L'AJOUT DE STOCK
// =====================================================
function BOStockAjout() {
    global $pdo, $messageSucces, $messageErreur;
    $messageSucces = '';
    $messageErreur = '';

    // Traitement POST : Ajout batch
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_batch'])) {

        verify_csrf();

        $action =
            $_POST['stock_action'] ?? 'add';

        $idStack =
            (int)($_POST['id_stack'] ?? 0);

        $idStackDestination =
            (int)($_POST['id_stack_destination'] ?? 0);

        $produitsIds =
            $_POST['produit_id'] ?? [];

        $quantites =
            $_POST['produit_qte'] ?? [];

        if ($idStack <= 0) {

            $messageErreur =
                "Veuillez sélectionner un stack.";
        }

        elseif (empty($produitsIds)) {

            $messageErreur =
                "Veuillez ajouter au moins un produit.";
        }

        else {

            $produits = [];

            for ($i = 0; $i < count($produitsIds); $i++) {

                $pid =
                    (int)($produitsIds[$i] ?? 0);

                $qty =
                    (int)($quantites[$i] ?? 0);

                if ($pid > 0 && $qty > 0) {

                    $produits[] = [
                        'id_produit' => $pid,
                        'quantite' => $qty
                    ];
                }
            }

            if (empty($produits)) {

                $messageErreur =
                    "Aucune ligne valide.";

            } else {

                // ===================================
                // AJOUT
                // ===================================

                if ($action === 'add') {

                    $result = executeStockAction(
                        'addToStack',
                        [
                            'id_stack' => $idStack,
                            'produits' => json_encode($produits)
                        ]
                    );
                }

                // ===================================
                // SUPPRESSION
                // ===================================

                elseif ($action === 'remove') {

                    $result = executeStockAction(
                        'removeFromStack',
                        [
                            'id_stack' => $idStack,
                            'produits' => json_encode($produits)
                        ]
                    );
                }

                // ===================================
                // DEPLACEMENT
                // ===================================

                elseif ($action === 'move') {

                    if ($idStackDestination <= 0) {

                        $messageErreur =
                            "Veuillez sélectionner un stack destination.";

                    } else {

                        $result = executeStockAction(
                            'moveStock',
                            [
                                'id_stack_source' => $idStack,
                                'id_stack_destination' => $idStackDestination,
                                'produits' => json_encode($produits)
                            ]
                        );
                    }
                }

                if (
                    empty($messageErreur) &&
                    isset($result['success'])
                ) {

                    $messageSucces =
                        $result['message'];

                } elseif (
                    empty($messageErreur)
                ) {

                    $messageErreur =
                        $result['error'] ?? 'Erreur inconnue';
                }
            }
        }
    }

    global $stacksList, $produitsActifs;

    // Récupération des stacks
    $resultStacks = executeStockAction('getStacks');

    if (isset($resultStacks['error'])) {
        $messageErreur = $resultStacks['error'];
        $stacksList = [];
    } else {
        $stacksList = $resultStacks ?? [];
    }

    // Récupération des produits
    $resultProduits = executeStockAction('getProduits');

    // Récupération de l'historique
    $resultHistorique = executeStockAction('getHistoriqueStock');

    if (isset($resultHistorique['error'])) {
        $mouvementsStock = [];
        $messageErreur .= ' ' . $resultHistorique['error'];
    } else {
        $mouvementsStock = $resultHistorique["data"] ?? [];
    }

    /**
     * Récupère les produits d’un stack sélectionné
     */
    function getProduitsDuStackSelectionne($idStack) {

        if ($idStack <= 0) {
            return [
                'success' => false,
                'produits' => []
            ];
        }

        return executeStockAction('getStackProduits', [
            'id_stack' => $idStack
        ]);
    }

    if (isset($resultProduits['error'])) {
        $messageErreur .= ' ' . $resultProduits['error'];
        $produitsActifs = [];
    } else {
        $produitsActifs = $resultProduits ?? [];
    }

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-stock-ajout.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}
?>