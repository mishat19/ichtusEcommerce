<?php

/**
 * Contrôleur Backoffice — Gestion des entrepôts, meubles, stacks
 * et ajout batch de produits dans les stacks
 */

function BOEntrepots() {
    global $pdo;

    // Récupérer les entrepôts avec meubles et stacks
    $stmt = $pdo->query("SELECT * FROM entrepot ORDER BY nom ASC");
    $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($entrepots as &$e) {
        $stmtM = $pdo->prepare("SELECT * FROM meuble WHERE id_entrepot = ? ORDER BY nom ASC");
        $stmtM->execute([$e['id']]);
        $e['meubles'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        $e['total_capacite'] = 0;
        $e['total_utilise'] = 0;

        foreach ($e['meubles'] as &$m) {
            $stmtS = $pdo->prepare("SELECT * FROM stack WHERE id_meuble = ? ORDER BY nom ASC");
            $stmtS->execute([$m['id']]);
            $m['stacks'] = $stmtS->fetchAll(PDO::FETCH_ASSOC);

            $m['total_capacite'] = 0;
            $m['total_utilise'] = 0;

            foreach ($m['stacks'] as &$s) {
                // Produits dans le stack
                $stmtP = $pdo->prepare("
                    SELECT sp.*, p.nom AS produit_nom, p.identifiant
                    FROM stack_produit sp
                    JOIN produit p ON p.id = sp.id_produit
                    WHERE sp.id_stack = ?
                ");
                $stmtP->execute([$s['id']]);
                $s['produits'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                $s['taux_occupation'] = $s['capacite_max'] > 0
                    ? round(($s['capacite_utilisee'] / $s['capacite_max']) * 100, 1)
                    : 0;

                $m['total_capacite'] += $s['capacite_max'];
                $m['total_utilise'] += $s['capacite_utilisee'];
            }
            unset($s);

            $m['taux_occupation'] = $m['total_capacite'] > 0
                ? round(($m['total_utilise'] / $m['total_capacite']) * 100, 1)
                : 0;

            $e['total_capacite'] += $m['total_capacite'];
            $e['total_utilise'] += $m['total_utilise'];
        }
        unset($m);

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

function BOStockAjout() {
    global $pdo;

    /* ── Traitement POST : Ajout batch ── */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajout_batch'])) {
        verify_csrf();

        $idStack  = (int)($_POST['id_stack'] ?? 0);
        $produitsIds = $_POST['produit_id'] ?? [];
        $quantites   = $_POST['produit_qte'] ?? [];

        global $messageSucces, $messageErreur;

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
