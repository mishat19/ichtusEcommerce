<?php

/**
 * API Stock — Gestion du stock, entrepôts, meubles, stacks
 * Authentification par token POST (identique aux autres API)
 */
function APIStock() {
    global $pdo;

    header('Content-Type: application/json');

    /* ───────── METHOD CHECK ───────── */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    /* ───────── TOKEN CHECK ───────── */
    $token = $_POST['token'] ?? null;

    if ($token !== 'WDIhUThWMz9aN0Y0VDFwOUE2') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    /* ───────── ACTION ROUTING ───────── */
    $action = $_POST['action'] ?? 'list';

    switch ($action) {

        /* ══════════════════════════════════════
         *  STOCK D'UN PRODUIT
         * ══════════════════════════════════════ */
        case 'getStock':
            $idProduit = (int)($_POST['id_produit'] ?? 0);
            if ($idProduit <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'id_produit requis']);
                return;
            }

            $stmt = $pdo->prepare("
                SELECT s.*, p.nom AS produit_nom, p.identifiant
                FROM stock s
                JOIN produit p ON p.id = s.id_produit
                WHERE s.id_produit = ?
            ");
            $stmt->execute([$idProduit]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock) {
                echo json_encode(['quantite_disponible' => 0, 'quantite_reservee' => 0, 'seuil_alerte' => 15]);
                return;
            }

            echo json_encode($stock);
            return;

        /* ══════════════════════════════════════
         *  LISTE DE TOUS LES STOCKS
         * ══════════════════════════════════════ */
        case 'list':
            $stmt = $pdo->query("
                SELECT s.*, p.nom AS produit_nom, p.identifiant, p.image, p.statut AS produit_statut
                FROM stock s
                JOIN produit p ON p.id = s.id_produit
                ORDER BY p.nom ASC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            return;

        /* ══════════════════════════════════════
         *  RÉSERVER DU STOCK (ajout panier)
         * ══════════════════════════════════════ */
        case 'reserver':
            $idProduit = (int)($_POST['id_produit'] ?? 0);
            $quantite  = (int)($_POST['quantite'] ?? 0);

            if ($idProduit <= 0 || $quantite <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'id_produit et quantite requis']);
                return;
            }

            // Vérifier le stock disponible
            $stmt = $pdo->prepare("SELECT * FROM stock WHERE id_produit = ?");
            $stmt->execute([$idProduit]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock) {
                http_response_code(404);
                echo json_encode(['error' => 'Produit non trouvé en stock']);
                return;
            }

            $disponible = $stock['quantite_disponible'] - $stock['quantite_reservee'];
            if ($quantite > $disponible) {
                http_response_code(409);
                echo json_encode(['error' => 'Stock insuffisant', 'disponible' => $disponible]);
                return;
            }

            // Réserver
            $pdo->prepare("
                UPDATE stock 
                SET quantite_reservee = quantite_reservee + ?,
                    date_derniere_mise_a_jour = NOW()
                WHERE id_produit = ?
            ")->execute([$quantite, $idProduit]);

            echo json_encode(['success' => true, 'message' => 'Stock réservé']);
            return;

        /* ══════════════════════════════════════
         *  LIBÉRER DU STOCK (expiration panier)
         * ══════════════════════════════════════ */
        case 'liberer':
            $idProduit = (int)($_POST['id_produit'] ?? 0);
            $quantite  = (int)($_POST['quantite'] ?? 0);

            if ($idProduit <= 0 || $quantite <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'id_produit et quantite requis']);
                return;
            }

            $pdo->prepare("
                UPDATE stock 
                SET quantite_reservee = GREATEST(0, quantite_reservee - ?),
                    date_derniere_mise_a_jour = NOW()
                WHERE id_produit = ?
            ")->execute([$quantite, $idProduit]);

            echo json_encode(['success' => true, 'message' => 'Stock libéré']);
            return;

        /* ══════════════════════════════════════
         *  MISE À JOUR STOCK (entrée manuelle)
         * ══════════════════════════════════════ */
        case 'updateStock':
            $idProduit     = (int)($_POST['id_produit'] ?? 0);
            $quantite      = (int)($_POST['quantite'] ?? 0);
            $typeMouvement = $_POST['type_mouvement'] ?? 'entree';
            $commentaire   = $_POST['commentaire'] ?? null;
            $idStack       = !empty($_POST['id_stack']) ? (int)$_POST['id_stack'] : null;

            if ($idProduit <= 0 || $quantite <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'id_produit et quantite requis']);
                return;
            }

            // Récupérer stock actuel
            $stmt = $pdo->prepare("SELECT * FROM stock WHERE id_produit = ?");
            $stmt->execute([$idProduit]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            $quantiteAvant = $stock ? (int)$stock['quantite_disponible'] : 0;

            if ($typeMouvement === 'entree') {
                $quantiteApres = $quantiteAvant + $quantite;
            } else {
                $quantiteApres = max(0, $quantiteAvant - $quantite);
            }

            // Upsert stock
            if ($stock) {
                $pdo->prepare("
                    UPDATE stock SET quantite_disponible = ?, date_derniere_mise_a_jour = NOW() WHERE id_produit = ?
                ")->execute([$quantiteApres, $idProduit]);
            } else {
                $pdo->prepare("
                    INSERT INTO stock (id_produit, quantite_disponible, quantite_reservee, seuil_alerte)
                    VALUES (?, ?, 0, 15)
                ")->execute([$idProduit, $quantiteApres]);
            }

            // Enregistrer le mouvement
            $pdo->prepare("
                INSERT INTO mouvement_stock (id_produit, type_mouvement, quantite, quantite_avant, quantite_apres, id_stack, commentaire)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([$idProduit, $typeMouvement, $quantite, $quantiteAvant, $quantiteApres, $idStack, $commentaire]);

            echo json_encode(['success' => true, 'quantite_avant' => $quantiteAvant, 'quantite_apres' => $quantiteApres]);
            return;

        /* ══════════════════════════════════════
         *  AJOUT BATCH PRODUITS DANS UN STACK
         * ══════════════════════════════════════ */
        case 'addToStack':
            $idStack  = (int)($_POST['id_stack'] ?? 0);
            // produits = JSON array [{id_produit: X, quantite: Y}, ...]
            $produits = json_decode($_POST['produits'] ?? '[]', true);

            if ($idStack <= 0 || empty($produits)) {
                http_response_code(400);
                echo json_encode(['error' => 'id_stack et produits requis']);
                return;
            }

            // Vérifier le stack
            $stmtStack = $pdo->prepare("SELECT * FROM stack WHERE id = ?");
            $stmtStack->execute([$idStack]);
            $stack = $stmtStack->fetch(PDO::FETCH_ASSOC);

            if (!$stack) {
                http_response_code(404);
                echo json_encode(['error' => 'Stack introuvable']);
                return;
            }

            // Calculer la quantité totale à ajouter
            $totalAjout = 0;
            foreach ($produits as $p) {
                $totalAjout += (int)($p['quantite'] ?? 0);
            }

            $capaciteRestante = $stack['capacite_max'] - $stack['capacite_utilisee'];
            if ($totalAjout > $capaciteRestante) {
                http_response_code(409);
                echo json_encode([
                    'error' => 'Capacité du stack insuffisante',
                    'capacite_restante' => $capaciteRestante,
                    'quantite_demandee' => $totalAjout
                ]);
                return;
            }

            $pdo->beginTransaction();

            try {
                foreach ($produits as $p) {
                    $idProduit = (int)($p['id_produit'] ?? 0);
                    $qte       = (int)($p['quantite'] ?? 0);

                    if ($idProduit <= 0 || $qte <= 0) continue;

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
                echo json_encode(['success' => true, 'message' => "$totalAjout produit(s) ajouté(s) au stack"]);

            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'ajout', 'detail' => $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  LISTE DES ENTREPÔTS
         * ══════════════════════════════════════ */
        case 'getEntrepots':
            $stmt = $pdo->query("SELECT * FROM entrepot ORDER BY nom ASC");
            $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($entrepots as &$e) {
                // Meubles
                $stmtM = $pdo->prepare("SELECT * FROM meuble WHERE id_entrepot = ? ORDER BY nom ASC");
                $stmtM->execute([$e['id']]);
                $e['meubles'] = $stmtM->fetchAll(PDO::FETCH_ASSOC);

                foreach ($e['meubles'] as &$m) {
                    // Stacks
                    $stmtS = $pdo->prepare("SELECT * FROM stack WHERE id_meuble = ? ORDER BY nom ASC");
                    $stmtS->execute([$m['id']]);
                    $m['stacks'] = $stmtS->fetchAll(PDO::FETCH_ASSOC);

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
                    }
                    unset($s);
                }
                unset($m);
            }
            unset($e);

            echo json_encode($entrepots);
            return;

        /* ══════════════════════════════════════
         *  LISTE DES STACKS (pour dropdown)
         * ══════════════════════════════════════ */
        case 'getStacks':
            $stmt = $pdo->query("
                SELECT s.*, m.nom AS meuble_nom, e.nom AS entrepot_nom
                FROM stack s
                JOIN meuble m ON m.id = s.id_meuble
                JOIN entrepot e ON e.id = m.id_entrepot
                ORDER BY e.nom, m.nom, s.nom
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            return;

        /* ══════════════════════════════════════
         *  LISTE DES PRODUITS (pour dropdown)
         * ══════════════════════════════════════ */
        case 'getProduits':
            $stmt = $pdo->query("
                SELECT p.id, p.nom, p.identifiant
                FROM produit p
                WHERE p.statut = 'actif'
                ORDER BY p.nom ASC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            return;

        /* ══════════════════════════════════════
         *  QR CODE D'UN PRODUIT
         * ══════════════════════════════════════ */
        case 'getQrCode':
            $idProduit = (int)($_POST['id_produit'] ?? 0);
            if ($idProduit <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'id_produit requis']);
                return;
            }

            $stmt = $pdo->prepare("SELECT * FROM qr_code WHERE type = 'produit' AND id_entite = ? AND est_actif = 1");
            $stmt->execute([$idProduit]);
            $qr = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$qr) {
                echo json_encode(['exists' => false]);
                return;
            }

            $qr['image_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qr['url']);
            $qr['exists'] = true;
            echo json_encode($qr);
            return;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue: ' . $action]);
            return;
    }
}
