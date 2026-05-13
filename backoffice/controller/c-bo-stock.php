<?php
/**
 * =========================================================
 * BACKOFFICE — GESTION DU STOCK / ENTREPÔTS
 * =========================================================
 */

// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// FONCTIONS CSRF (si elles ne sont pas déjà définies ailleurs)
// =====================================================
if (!function_exists('csrf_field')) {
    function csrf_field() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}

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

// Fonction pour calculer la capacité utilisée d'un entrepôt
function getEntrepotCapaciteUtilisee($pdo, $id_entrepot) {
    $stmt = $pdo->prepare("
        SELECT SUM(s.capacite_max) as total_utilise
        FROM stack s
        JOIN meuble m ON m.id = s.id_meuble
        WHERE m.id_entrepot = ?
    ");
    $stmt->execute([$id_entrepot]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($result['total_utilise'] ?? 0);
}

// Fonction principale pour gérer les entrepôts
function BOEntrepots() {
    global $pdo;
    global $messageSucces, $messageErreur;

    // Initialise les messages
    $messageSucces = '';
    $messageErreur = '';

    // Vérifie et traite les actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf(); // Vérifie le token CSRF

        $action = $_POST['action'] ?? '';

        // =====================================================
        // CRÉATION D'ENTREPÔT
        // =====================================================
        if (isset($_POST['create_entrepot'])) {
            $nom = trim($_POST['nom'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $codePostal = trim($_POST['code_postal'] ?? '');
            $pays = trim($_POST['pays'] ?? 'France');
            $capaciteTotale = (int)($_POST['capacite_totale'] ?? 0);

            if (empty($nom) || empty($adresse) || empty($ville) || empty($codePostal)) {
                $messageErreur = "Veuillez remplir tous les champs obligatoires.";
            } else {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO entrepot (nom, adresse, ville, code_postal, pays, capacite_totale)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $nom, $adresse, $ville, $codePostal, $pays,
                        $capaciteTotale > 0 ? $capaciteTotale : null
                    ]);
                    $messageSucces = "Entrepôt créé avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }

        // =====================================================
        // AJOUTER UN MEUBLE
        // =====================================================
        elseif ($action === 'add_meuble') {
            $id_entrepot = (int)($_POST['id_entrepot'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capacite_max_meuble = (int)($_POST['capacite_max'] ?? 0); // Nouvelle capacité pour le meuble

            if (empty($nom) || $id_entrepot <= 0) {
                $messageErreur = "Nom du meuble manquant ou entrepôt invalide.";
            } else {
                // Vérifie la capacité maximale de l'entrepôt (nombre de meubles)
                $capacite_max_entrepot = (int)($pdo->query("SELECT capacite_totale FROM entrepot WHERE id = $id_entrepot")->fetchColumn());
                $nb_meubles_actuels = (int)($pdo->query("SELECT COUNT(*) FROM meuble WHERE id_entrepot = $id_entrepot")->fetchColumn());

                if ($capacite_max_entrepot > 0 && $nb_meubles_actuels >= $capacite_max_entrepot) {
                    $messageErreur = "Impossible d'ajouter un meuble : la capacité maximale de l'entrepôt est atteinte ($nb_meubles_actuels/$capacite_max_entrepot).";
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO meuble (id_entrepot, nom, capacite_max) VALUES (?, ?, ?)");
                        $stmt->execute([$id_entrepot, $nom, $capacite_max_meuble > 0 ? $capacite_max_meuble : 10]); // Valeur par défaut: 10 stacks
                        $messageSucces = "Meuble ajouté avec succès.";
                    } catch (Exception $e) {
                        $messageErreur = "Erreur : " . $e->getMessage();
                    }
                }
            }
        }

        // =====================================================
        // MODIFIER UN MEUBLE
        // =====================================================
        elseif ($action === 'edit_meuble') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');

            if (empty($nom) || $id <= 0) {
                $messageErreur = "Nom du meuble manquant ou ID invalide.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE meuble SET nom = ? WHERE id = ?");
                    $stmt->execute([$nom, $id]);
                    $messageSucces = "Meuble modifié avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur : " . $e->getMessage();
                }
            }
        }

        // =====================================================
        // SUPPRIMER UN MEUBLE
        // =====================================================
        elseif ($action === 'delete_meuble') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $messageErreur = "ID du meuble invalide.";
            } else {
                try {
                    // Supprime d'abord les stacks associés
                    $stmtStacks = $pdo->prepare("DELETE FROM stack WHERE id_meuble = ?");
                    $stmtStacks->execute([$id]);

                    // Puis supprime le meuble
                    $stmt = $pdo->prepare("DELETE FROM meuble WHERE id = ?");
                    $stmt->execute([$id]);
                    $messageSucces = "Meuble supprimé avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur : " . $e->getMessage();
                }
            }
        }

        // =====================================================
        // AJOUTER UN STACK
        // =====================================================
        elseif ($action === 'add_stack') {
            $id_meuble = (int)($_POST['id_meuble'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capacite_max = (int)($_POST['capacite_max'] ?? 0);

            if (empty($nom) || $id_meuble <= 0 || $capacite_max <= 0) {
                $messageErreur = "Données manquantes ou invalides pour le stack.";
            } else {
                // Récupère la capacité maximale du meuble (nombre de stacks)
                $capacite_max_meuble = (int)($pdo->query("SELECT capacite_max FROM meuble WHERE id = $id_meuble")->fetchColumn());
                $nb_stacks_actuels = (int)($pdo->query("SELECT COUNT(*) FROM stack WHERE id_meuble = $id_meuble")->fetchColumn());

                if ($capacite_max_meuble > 0 && $nb_stacks_actuels >= $capacite_max_meuble) {
                    $messageErreur = "Impossible d'ajouter ce stack : la capacité maximale du meuble est atteinte ($nb_stacks_actuels/$capacite_max_meuble).";
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO stack (id_meuble, nom, capacite_max, capacite_utilisee) VALUES (?, ?, ?, 0)");
                        $stmt->execute([$id_meuble, $nom, $capacite_max]);
                        $messageSucces = "Stack ajouté avec succès.";
                    } catch (Exception $e) {
                        $messageErreur = "Erreur : " . $e->getMessage();
                    }
                }
            }
        }

        // =====================================================
        // MODIFIER UN STACK
        // =====================================================
        elseif ($action === 'edit_stack') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $nouvelle_capacite_max = (int)($_POST['capacite_max'] ?? 0);

            if (empty($nom) || $id <= 0 || $nouvelle_capacite_max <= 0) {
                $messageErreur = "Données manquantes ou invalides pour le stack.";
            } else {
                // Récupère l'ancienne capacité du stack
                $stmtOld = $pdo->prepare("SELECT capacite_max, id_meuble FROM stack WHERE id = ?");
                $stmtOld->execute([$id]);
                $oldStack = $stmtOld->fetch(PDO::FETCH_ASSOC);

                if (!$oldStack) {
                    $messageErreur = "Stack introuvable.";
                } else {
                    $id_meuble = (int)$oldStack['id_meuble'];

                    // Vérifie que la modification ne dépasse pas la capacité du meuble (nombre de stacks)
                    // Ici, on ne vérifie PAS la capacité du meuble car on modifie un stack existant (pas d'ajout)
                    // On vérifie seulement que la nouvelle capacité_max est valide
                    try {
                        $stmt = $pdo->prepare("UPDATE stack SET nom = ?, capacite_max = ? WHERE id = ?");
                        $stmt->execute([$nom, $nouvelle_capacite_max, $id]);
                        $messageSucces = "Stack modifié avec succès.";
                    } catch (Exception $e) {
                        $messageErreur = "Erreur : " . $e->getMessage();
                    }
                }
            }
        }

        // =====================================================
        // SUPPRIMER UN STACK
        // =====================================================
        elseif ($action === 'delete_stack') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $messageErreur = "ID du stack invalide.";
            } else {
                try {
                    // Supprime d'abord les produits associés (si nécessaire)
                    $stmtProduits = $pdo->prepare("DELETE FROM stack_produit WHERE id_stack = ?");
                    $stmtProduits->execute([$id]);

                    // Puis supprime le stack
                    $stmt = $pdo->prepare("DELETE FROM stack WHERE id = ?");
                    $stmt->execute([$id]);
                    $messageSucces = "Stack supprimé avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur : " . $e->getMessage();
                }
            }
        }

        // =====================================================
        // MODIFIER UN ENTREPÔT
        // =====================================================
        elseif ($action === 'edit_entrepot') {
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');
            $pays = trim($_POST['pays'] ?? 'France');
            $capacite_totale = (int)($_POST['capacite_totale'] ?? 0);

            if (empty($nom) || empty($adresse) || empty($ville) || empty($code_postal) || $id <= 0) {
                $messageErreur = "Champs obligatoires manquants.";
            } else {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE entrepot
                        SET nom = ?, adresse = ?, ville = ?, code_postal = ?, pays = ?, capacite_totale = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nom, $adresse, $ville, $code_postal, $pays, $capacite_totale > 0 ? $capacite_totale : null, $id]);
                    $messageSucces = "Entrepôt modifié avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur : " . $e->getMessage();
                }
            }
        }

        // =====================================================
        // SUPPRIMER UN ENTREPÔT
        // =====================================================
        elseif ($action === 'delete_entrepot') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $messageErreur = "ID de l'entrepôt invalide.";
            } else {
                try {
                    // Supprime d'abord les stacks associés
                    $stmtStacks = $pdo->prepare("
                        DELETE s FROM stack s
                        JOIN meuble m ON m.id = s.id_meuble
                        WHERE m.id_entrepot = ?
                    ");
                    $stmtStacks->execute([$id]);

                    // Puis supprime les meubles
                    $stmtMeubles = $pdo->prepare("DELETE FROM meuble WHERE id_entrepot = ?");
                    $stmtMeubles->execute([$id]);

                    // Enfin, supprime l'entrepôt
                    $stmt = $pdo->prepare("DELETE FROM entrepot WHERE id = ?");
                    $stmt->execute([$id]);
                    $messageSucces = "Entrepôt supprimé avec succès.";
                } catch (Exception $e) {
                    $messageErreur = "Erreur : " . $e->getMessage();
                }
            }
        }
    }

    // =====================================================
    // RÉCUPÉRATION DES ENTREPÔTS ET LEURS DONNÉES
    // =====================================================
    $stmt = $pdo->query("SELECT * FROM entrepot ORDER BY nom ASC");
    $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($entrepots as &$e) {
        // Récupère les meubles de l'entrepôt
        $stmtM = $pdo->prepare("SELECT * FROM meuble WHERE id_entrepot = ? ORDER BY nom ASC");
        $stmtM->execute([$e['id']]);
        $e['meubles'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Initialise les totaux pour l'entrepôt
        $e['total_capacite'] = 0;
        $e['total_utilise'] = 0;

        foreach ($e['meubles'] as &$m) {
            // Récupère les stacks du meuble
            $stmtS = $pdo->prepare("SELECT * FROM stack WHERE id_meuble = ? ORDER BY nom ASC");
            $stmtS->execute([$m['id']]);
            $m['stacks'] = $stmtS->fetchAll(PDO::FETCH_ASSOC);

            // Initialise les totaux pour le meuble
            $m['total_capacite'] = 0;
            $m['total_utilise'] = 0;

            foreach ($m['stacks'] as &$s) {
                // Récupère les produits du stack
                $stmtP = $pdo->prepare("
                    SELECT sp.*, p.nom AS produit_nom, p.identifiant
                    FROM stack_produit sp
                    JOIN produit p ON p.id = sp.id_produit
                    WHERE sp.id_stack = ?
                ");
                $stmtP->execute([$s['id']]);
                $s['produits'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);

                // Calcule le taux d'occupation du stack
                $s['taux_occupation'] = $s['capacite_max'] > 0
                    ? round(($s['capacite_utilisee'] / $s['capacite_max']) * 100, 1)
                    : 0;

                // Met à jour les totaux du meuble
                $m['total_capacite'] += $s['capacite_max'];
                $m['total_utilise'] += $s['capacite_utilisee'];
            }
            unset($s);

            // Calcule le taux d'occupation du meuble
            $m['taux_occupation'] = $m['total_capacite'] > 0
                ? round(($m['total_utilise'] / $m['total_capacite']) * 100, 1)
                : 0;

            // Met à jour les totaux de l'entrepôt
            $e['total_capacite'] += $m['total_capacite'];
            $e['total_utilise'] += $m['total_utilise'];
        }
        unset($m);

        // Calcule le taux d'occupation de l'entrepôt
        $e['taux_occupation'] = $e['total_capacite'] > 0
            ? round(($e['total_utilise'] / $e['total_capacite']) * 100, 1)
            : 0;
    }
    unset($e);

    global $entrepotsList;
    $entrepotsList = $entrepots;

    // Inclut les vues
    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-entrepots.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

// Fonction pour gérer l'ajout de stock
function BOStockAjout() {
    global $pdo;
    global $messageSucces, $messageErreur;

    // Traitement POST : Ajout batch
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_batch'])) {
        verify_csrf();

        $idStack = (int)($_POST['id_stack'] ?? 0);
        $produitsIds = $_POST['produit_id'] ?? [];
        $quantites = $_POST['produit_qte'] ?? [];

        if ($idStack <= 0) {
            $messageErreur = "Veuillez sélectionner un stack.";
        } elseif (empty($produitsIds)) {
            $messageErreur = "Veuillez ajouter au moins un produit.";
        } else {
            // Vérifier le stack
            $stmtStack = $pdo->prepare("SELECT * FROM stack WHERE id = ?");
            $stmtStack->execute([$idStack]);
            $stack = $stmtStack->fetch(PDO::FETCH_ASSOC);

            if (!$stack) {
                $messageErreur = "Stack introuvable.";
            } else {
                // Calculer quantité totale
                $totalAjout = 0;
                $lignes = [];
                for ($i = 0; $i < count($produitsIds); $i++) {
                    $pid = (int)($produitsIds[$i] ?? 0);
                    $qty = (int)($quantites[$i] ?? 0);
                    if ($pid > 0 && $qty > 0) {
                        $lignes[] = ['id_produit' => $pid, 'quantite' => $qty];
                        $totalAjout += $qty;
                    }
                }

                $capaciteRestante = $stack['capacite_max'] - $stack['capacite_utilisee'];

                if ($totalAjout > $capaciteRestante) {
                    $messageErreur = "Capacité insuffisante dans le stack. Restant : $capaciteRestante, demandé : $totalAjout.";
                } elseif (empty($lignes)) {
                    $messageErreur = "Aucune ligne valide à ajouter.";
                } else {
                    $pdo->beginTransaction();
                    try {
                        foreach ($lignes as $l) {
                            $idProduit = $l['id_produit'];
                            $qte = $l['quantite'];

                            // Upsert stack_produit
                            $stmtExist = $pdo->prepare("SELECT id, quantite FROM stack_produit WHERE id_stack = ? AND id_produit = ?");
                            $stmtExist->execute([$idStack, $idProduit]);
                            $existing = $stmtExist->fetch(PDO::FETCH_ASSOC);

                            if ($existing) {
                                $pdo->prepare("UPDATE stack_produit SET quantite = quantite + ? WHERE id = ?")
                                    ->execute([$qte, $existing['id']]);
                            } else {
                                $pdo->prepare("INSERT INTO stack_produit (id_stack, id_produit, quantite) VALUES (?, ?, ?)")
                                    ->execute([$idStack, $idProduit, $qte]);
                            }

                            // Mettre à jour stock global
                            $stmtStock = $pdo->prepare("SELECT * FROM stock WHERE id_produit = ?");
                            $stmtStock->execute([$idProduit]);
                            $stockExist = $stmtStock->fetch(PDO::FETCH_ASSOC);

                            $qteAvant = $stockExist ? (int)$stockExist['quantite_disponible'] : 0;
                            $qteApres = $qteAvant + $qte;

                            if ($stockExist) {
                                $pdo->prepare("UPDATE stock SET quantite_disponible = ?, id_stack = ?, date_derniere_mise_a_jour = NOW() WHERE id_produit = ?")
                                    ->execute([$qteApres, $idStack, $idProduit]);
                            } else {
                                $pdo->prepare("INSERT INTO stock (id_produit, quantite_disponible, quantite_reservee, seuil_alerte, id_stack) VALUES (?, ?, 0, 15, ?)")
                                    ->execute([$idProduit, $qteApres, $idStack]);
                            }

                            // Mouvement stock
                            $pdo->prepare("
                                INSERT INTO mouvement_stock (id_produit, type_mouvement, quantite, quantite_avant, quantite_apres, id_stack, commentaire)
                                VALUES (?, 'entree', ?, ?, ?, ?, 'Ajout batch via backoffice')
                            ")->execute([$idProduit, $qte, $qteAvant, $qteApres, $idStack]);
                        }

                        // Mettre à jour capacité utilisée du stack
                        $pdo->prepare("UPDATE stack SET capacite_utilisee = capacite_utilisee + ? WHERE id = ?")
                            ->execute([$totalAjout, $idStack]);

                        $pdo->commit();
                        $messageSucces = "$totalAjout produit(s) ajouté(s) avec succès dans le stack.";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $messageErreur = "Erreur lors de l'ajout : " . $e->getMessage();
                    }
                }
            }
        }
    }

    // Récupérer les stacks pour le dropdown (groupés par entrepôt > meuble)
    $stmtStacks = $pdo->query("
        SELECT s.*, m.nom AS meuble_nom, e.nom AS entrepot_nom
        FROM stack s
        JOIN meuble m ON m.id = s.id_meuble
        JOIN entrepot e ON e.id = m.id_entrepot
        ORDER BY e.nom, m.nom, s.nom
    ");
    global $stacksList;
    $stacksList = $stmtStacks->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les produits actifs pour le dropdown
    $stmtProduits = $pdo->query("SELECT id, nom, identifiant FROM produit WHERE statut = 'actif' ORDER BY nom ASC");
    global $produitsActifs;
    $produitsActifs = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-stock-ajout.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

// =====================================================
// FONCTION POUR VISUALISER UN STACK
// =====================================================
function BOStockVisualisation() {
    global $pdo;
    global $messageSucces, $messageErreur;

    // Récupère l'ID du stack depuis l'URL
    $id_stack = (int)($_GET['id_stack'] ?? 0);

    // Récupère les stacks pour le dropdown (groupés par entrepôt > meuble)
    $stmtStacks = $pdo->query("
        SELECT s.*, m.nom AS meuble_nom, e.nom AS entrepot_nom
        FROM stack s
        JOIN meuble m ON m.id = s.id_meuble
        JOIN entrepot e ON e.id = m.id_entrepot
        ORDER BY e.nom, m.nom, s.nom
    ");
    global $stacksList;
    $stacksList = $stmtStacks->fetchAll(PDO::FETCH_ASSOC);

    // Si un stack est sélectionné, récupère ses infos
    if ($id_stack > 0) {
        $stmtStack = $pdo->prepare("SELECT * FROM stack WHERE id = ?");
        $stmtStack->execute([$id_stack]);
        $stack = $stmtStack->fetch(PDO::FETCH_ASSOC);

        if (!$stack) {
            $messageErreur = "Stack introuvable.";
        }
    }

    // Inclut les vues
    require_once 'backoffice/view/inc/inc.head.php';
    require_once 'backoffice/view/inc/inc.header.php';
    require_once 'backoffice/view/v-stock-visualisation.php';
    require_once 'backoffice/view/inc/inc.footer.php';
}

// =====================================================
// FONCTION POUR RÉCUPÉRER LES PRODUITS D'UN STACK (AJAX)
// =====================================================
function getStackProduits() {
    global $pdo;

    // Vérifie que la requête est en AJAX et que l'ID est valide
    if (!isset($_GET['id_stack']) || !is_numeric($_GET['id_stack'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de stack invalide.']);
        exit;
    }

    $id_stack = (int)$_GET['id_stack'];

    // Récupère les produits du stack
    $stmt = $pdo->prepare("
        SELECT sp.quantite, p.id, p.nom, p.identifiant
        FROM stack_produit sp
        JOIN produit p ON p.id = sp.id_produit
        WHERE sp.id_stack = ?
        ORDER BY p.nom ASC
    ");
    $stmt->execute([$id_stack]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcule la quantité totale
    $total_quantite = 0;
    foreach ($produits as $p) {
        $total_quantite += $p['quantite'];
    }

    // Retourne les données en JSON
    header('Content-Type: application/json');
    echo json_encode([
        'produits' => $produits,
        'total_quantite' => $total_quantite
    ]);
    exit;
}

?>