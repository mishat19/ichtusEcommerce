<?php

/**
 * API Stock — Gestion du stock, entrepôts, meubles, stacks
 * Authentification par token POST (identique aux autres API)
 */
function APIStock() {
    global $pdo;

    $isDirectApiCall = isset($_GET['pageAPI']);

    // Header JSON uniquement pour les vrais appels API
    if ($isDirectApiCall) {
        header('Content-Type: application/json');
    }

    // Vérification méthode uniquement pour API HTTP
    if ($isDirectApiCall && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
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

            $stacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($stacks as &$s) {

                $stmtP = $pdo->prepare("
                    SELECT sp.*, p.nom, p.identifiant, p.image, p.prix_ht
                    FROM stack_produit sp
                    JOIN produit p ON p.id = sp.id_produit
                    WHERE sp.id_stack = ?
                ");

                $stmtP->execute([$s['id']]);

                $s['produits'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
            }

            unset($s);

            echo json_encode($stacks);
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
        /* ══════════════════════════════════════
         *  SCANNER QR CODE
         * ══════════════════════════════════════ */
        case "parseQr":

            $url = $_GET["url"] ?? "";

            // récupère dernier segment
            $slug = basename(parse_url($url, PHP_URL_PATH));

            $stmt = $pdo->prepare("SELECT id, nom FROM produit WHERE slug = ?");
            $stmt->execute([$slug]);
            $prod = $stmt->fetch();

            echo json_encode($prod);
            break;
        /* ══════════════════════════════════════
        *  CRÉER UN ENTREPÔT
        * ══════════════════════════════════════ */
        case 'createEntrepot':
            $nom = trim($_POST['nom'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $codePostal = trim($_POST['code_postal'] ?? '');
            $pays = trim($_POST['pays'] ?? 'France');
            $capaciteTotale = (int)($_POST['capacite_totale'] ?? 0);

            if (empty($nom) || empty($adresse) || empty($ville) || empty($codePostal)) {
                http_response_code(400);
                echo json_encode(['error' => 'Champs obligatoires manquants']);
                return;
            }

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO entrepot (nom, adresse, ville, code_postal, pays, capacite_totale)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nom, $adresse, $ville, $codePostal, $pays, $capaciteTotale > 0 ? $capaciteTotale : null]);
                echo json_encode(['success' => true, 'message' => 'Entrepôt créé avec succès', 'id' => $pdo->lastInsertId()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;
        /* ══════════════════════════════════════
        *  ENTREE PRODUIT
        * ══════════════════════════════════════ */
        case "entree":

            $data = json_decode(file_get_contents("php://input"), true);

            $id = $data["id_produit"];
            $qte = (int)$data["quantite"];

            $pdo->beginTransaction();

            // stock global
            $pdo->prepare("UPDATE produit SET stock = stock + ? WHERE id = ?")
                ->execute([$qte, $id]);

            // mouvement
                        $pdo->prepare("INSERT INTO mouvement_stock
            (id_produit, type_mouvement, quantite, quantite_avant, quantite_apres)
            VALUES (?, 'entree', ?, 0, ?)")
                ->execute([$id, $qte, $qte]);

            $pdo->commit();

            echo json_encode(["ok"=>true]);
            break;
        /* ══════════════════════════════════════
        *  SORTIE PRODUIT
        * ══════════════════════════════════════ */
        case 'removeFromStack':

            $idStack = (int)($_POST['id_stack'] ?? 0);

            $produits = json_decode($_POST['produits'] ?? '[]', true);

            if ($idStack <= 0 || empty($produits)) {

                http_response_code(400);

                echo json_encode([
                    'error' => 'Données invalides'
                ]);

                return;
            }

            try {

                $pdo->beginTransaction();

                foreach ($produits as $p) {

                    $idProduit = (int)$p['id_produit'];

                    $qte = (int)$p['quantite'];

                    /* STOCK ACTUEL DU STACK */
                    $stmt = $pdo->prepare("
                        SELECT quantite
                        FROM stack_produit
                        WHERE id_stack = ?
                        AND id_produit = ?
                    ");

                    $stmt->execute([
                        $idStack,
                        $idProduit
                    ]);

                    $stockActuel = (int)$stmt->fetchColumn();

                    /*
                     * VALIDATION
                     */
                    if ($qte > $stockActuel) {

                        http_response_code(409);

                        echo json_encode([
                            'error' => "Capacité dépassée ! Vous essayez de supprimer {$qte} produit(s) mais il n'y en a que {$stockActuel}."
                        ]);

                        return;
                    }

                    /*
                     * RETIRER DU STACK
                     */
                    $stmt = $pdo->prepare("
                UPDATE stack_produit
                SET quantite = quantite - ?
                WHERE id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $qte,
                        $idStack,
                        $idProduit
                    ]);

                    $stmt = $pdo->prepare("
                        UPDATE stack
                        SET capacite_utilisee = GREATEST(0, capacite_utilisee - ?)
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $qte,
                        $idStack
                    ]);

                    /*
                     * RETIRER DU STOCK GLOBAL
                     */
                    $stmt = $pdo->prepare("
                UPDATE stock
                SET quantite_disponible = quantite_disponible - ?
                WHERE id_produit = ?
            ");

                    $stmt->execute([
                        $qte,
                        $idProduit
                    ]);

                    /*
                     * DELETE stack_produit SI VIDE
                     */
                    $stmt = $pdo->prepare("
                DELETE FROM stack_produit
                WHERE quantite <= 0
                AND id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $idStack,
                        $idProduit
                    ]);

                    /*
                     * DELETE stock SI VIDE
                     */
                    $stmt = $pdo->prepare("
                DELETE FROM stock
                WHERE quantite_disponible <= 0
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $idProduit
                    ]);

                    /*
                     * MOUVEMENT
                     */
                    $stmt = $pdo->prepare("
                INSERT INTO mouvement_stock
                (
                    id_produit,
                    type_mouvement,
                    quantite,
                    quantite_avant,
                    quantite_apres,
                    id_stack,
                    commentaire
                )

                VALUES
                (
                    ?, 'sortie', ?, 0, 0, ?, ?
                )
            ");

                    $stmt->execute([
                        $idProduit,
                        $qte,
                        $idStack,
                        'Suppression depuis stack'
                    ]);
                }

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Produit retiré du stack'
                ]);

            } catch (Exception $e) {

                $pdo->rollBack();

                http_response_code(500);

                echo json_encode([
                    'error' => $e->getMessage()
                ]);
            }

            return;

        case 'moveStock':

            $source = (int)($_POST['id_stack_source'] ?? 0);

            $destination = (int)($_POST['id_stack_destination'] ?? 0);

            $produits = json_decode($_POST['produits'] ?? '[]', true);

            if (
                $source <= 0 ||
                $destination <= 0 ||
                $source === $destination ||
                empty($produits)
            ) {

                http_response_code(400);

                echo json_encode([
                    'error' => 'Données invalides'
                ]);

                return;
            }

            try {

                $pdo->beginTransaction();

                foreach ($produits as $p) {

                    $idProduit = (int)($p['id_produit'] ?? 0);

                    $qte = (int)($p['quantite'] ?? 0);

                    if ($idProduit <= 0 || $qte <= 0) {
                        continue;
                    }

                    /*
                     * STOCK SOURCE AVANT
                     */
                    $stmt = $pdo->prepare("
                SELECT quantite
                FROM stack_produit
                WHERE id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $source,
                        $idProduit
                    ]);

                    $stockSourceAvant = (int)$stmt->fetchColumn();

                    /*
                     * Vérifie quantité disponible
                     */
                    if ($qte > $stockSourceAvant) {

                        throw new Exception(
                            "Quantité insuffisante dans le stack source"
                        );
                    }

                    /*
                     * Vérifie capacité destination
                     */
                    $stmt = $pdo->prepare("
                SELECT capacite_max, capacite_utilisee
                FROM stack
                WHERE id = ?
            ");

                    $stmt->execute([$destination]);

                    $stackDestination = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$stackDestination) {

                        throw new Exception(
                            "Stack destination introuvable"
                        );
                    }

                    $capaciteRestante =
                        $stackDestination['capacite_max']
                        - $stackDestination['capacite_utilisee'];

                    if ($qte > $capaciteRestante) {

                        throw new Exception(
                            "Capacité insuffisante dans le stack destination"
                        );
                    }

                    /*
                     * STOCK DESTINATION AVANT
                     */
                    $stmt = $pdo->prepare("
                SELECT quantite
                FROM stack_produit
                WHERE id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $destination,
                        $idProduit
                    ]);

                    $stockDestinationAvant = (int)$stmt->fetchColumn();

                    /*
                     * RETIRE DU STACK SOURCE
                     */
                    $stmt = $pdo->prepare("
                UPDATE stack_produit
                SET quantite = quantite - ?
                WHERE id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $qte,
                        $source,
                        $idProduit
                    ]);

                    /*
                     * MAJ capacité source
                     */
                    $stmt = $pdo->prepare("
                UPDATE stack
                SET capacite_utilisee = GREATEST(0, capacite_utilisee - ?)
                WHERE id = ?
            ");

                    $stmt->execute([
                        $qte,
                        $source
                    ]);

                    /*
                     * AJOUTE AU STACK DESTINATION
                     */
                    $stmt = $pdo->prepare("
                INSERT INTO stack_produit
                (
                    id_stack,
                    id_produit,
                    quantite
                )

                VALUES
                (
                    ?, ?, ?
                )

                ON DUPLICATE KEY UPDATE
                quantite = quantite + VALUES(quantite)
            ");

                    $stmt->execute([
                        $destination,
                        $idProduit,
                        $qte
                    ]);

                    /*
                     * MAJ capacité destination
                     */
                    $stmt = $pdo->prepare("
                UPDATE stack
                SET capacite_utilisee = capacite_utilisee + ?
                WHERE id = ?
            ");

                    $stmt->execute([
                        $qte,
                        $destination
                    ]);

                    /*
                     * Nettoyage stack source si vide
                     */
                    $stmt = $pdo->prepare("
                DELETE FROM stack_produit
                WHERE quantite <= 0
                AND id_stack = ?
                AND id_produit = ?
            ");

                    $stmt->execute([
                        $source,
                        $idProduit
                    ]);

                    /*
                     * Stocks après mouvement
                     */
                    $stockSourceApres = $stockSourceAvant - $qte;

                    $stockDestinationApres =
                        $stockDestinationAvant + $qte;

                    /*
                     * HISTORIQUE MOUVEMENT
                     */
                    $stmt = $pdo->prepare("
                INSERT INTO mouvement_stock
                (
                    id_produit,
                    type_mouvement,
                    quantite,
                    quantite_avant,
                    quantite_apres,
                    commentaire,
                    id_stack_source,
                    id_stack_destination
                )

                VALUES
                (
                    ?, 'deplacement', ?, ?, ?, ?, ?, ?
                )
            ");

                    $stmt->execute([
                        $idProduit,
                        $qte,
                        $stockSourceAvant,
                        $stockSourceApres,
                        'Déplacement de stock',
                        $source,
                        $destination
                    ]);
                }

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Stock déplacé avec succès'
                ]);

            } catch (Exception $e) {

                $pdo->rollBack();

                http_response_code(500);

                echo json_encode([
                    'error' => $e->getMessage()
                ]);
            }

            return;
        /* ══════════════════════════════════════
         *  MODIFIER UN ENTREPÔT
         * ══════════════════════════════════════ */
        case 'editEntrepot':
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $codePostal = trim($_POST['code_postal'] ?? '');
            $pays = trim($_POST['pays'] ?? 'France');
            $capaciteTotale = (int)($_POST['capacite_totale'] ?? 0);

            if (empty($nom) || empty($adresse) || empty($ville) || empty($codePostal) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Champs obligatoires manquants']);
                return;
            }

            try {
                $stmt = $pdo->prepare("
            UPDATE entrepot
            SET nom = ?, adresse = ?, ville = ?, code_postal = ?, pays = ?, capacite_totale = ?
            WHERE id = ?
        ");
                $stmt->execute([$nom, $adresse, $ville, $codePostal, $pays, $capaciteTotale > 0 ? $capaciteTotale : null, $id]);
                echo json_encode(['success' => true, 'message' => 'Entrepôt modifié avec succès']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  SUPPRIMER UN ENTREPÔT
         * ══════════════════════════════════════ */
        case 'deleteEntrepot':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            try {
                $pdo->beginTransaction();

                // Supprime les stacks associés
                $stmtStacks = $pdo->prepare("DELETE s FROM stack s JOIN meuble m ON m.id = s.id_meuble WHERE m.id_entrepot = ?");
                $stmtStacks->execute([$id]);

                // Supprime les meubles
                $stmtMeubles = $pdo->prepare("DELETE FROM meuble WHERE id_entrepot = ?");
                $stmtMeubles->execute([$id]);

                // Supprime l'entrepôt
                $stmt = $pdo->prepare("DELETE FROM entrepot WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Entrepôt supprimé avec succès']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  AJOUTER UN MEUBLE
         * ══════════════════════════════════════ */
        case 'addMeuble':
            $idEntrepot = (int)($_POST['id_entrepot'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capaciteMax = (int)($_POST['capacite_max'] ?? 10); // Valeur par défaut

            if (empty($nom) || $idEntrepot <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Nom du meuble manquant ou entrepôt invalide']);
                return;
            }

            // Vérifie la capacité de l'entrepôt
            $capaciteMaxEntrepot = (int)($pdo->query("SELECT capacite_totale FROM entrepot WHERE id = $idEntrepot")->fetchColumn());
            $nbMeublesActuels = (int)($pdo->query("SELECT COUNT(*) FROM meuble WHERE id_entrepot = $idEntrepot")->fetchColumn());

            if ($capaciteMaxEntrepot > 0 && $nbMeublesActuels >= $capaciteMaxEntrepot) {
                http_response_code(409);
                echo json_encode(['error' => "Capacité maximale de l'entrepôt atteinte ($nbMeublesActuels/$capaciteMaxEntrepot)"]);
                return;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO meuble (id_entrepot, nom, capacite_max) VALUES (?, ?, ?)");
                $stmt->execute([$idEntrepot, $nom, $capaciteMax]);
                echo json_encode(['success' => true, 'message' => 'Meuble ajouté avec succès', 'id' => $pdo->lastInsertId()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  MODIFIER UN MEUBLE
         * ══════════════════════════════════════ */
        case 'editMeuble':
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capaciteMax = (int)($_POST['capacite_max'] ?? 0);

            if (empty($nom) || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Nom du meuble manquant ou ID invalide']);
                return;
            }

            try {
                $stmt = $pdo->prepare("UPDATE meuble SET nom = ?, capacite_max = ? WHERE id = ?");
                $stmt->execute([$nom, $capaciteMax, $id]);
                echo json_encode(['success' => true, 'message' => 'Meuble modifié avec succès']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  SUPPRIMER UN MEUBLE
         * ══════════════════════════════════════ */
        case 'deleteMeuble':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            try {
                $pdo->beginTransaction();

                // Supprime les stacks associés
                $stmtStacks = $pdo->prepare("DELETE FROM stack WHERE id_meuble = ?");
                $stmtStacks->execute([$id]);

                // Supprime le meuble
                $stmt = $pdo->prepare("DELETE FROM meuble WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Meuble supprimé avec succès']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  AJOUTER UN STACK
         * ══════════════════════════════════════ */
        case 'addStack':
            $idMeuble = (int)($_POST['id_meuble'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capaciteMax = (int)($_POST['capacite_max'] ?? 0);

            if (empty($nom) || $idMeuble <= 0 || $capaciteMax <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes ou invalides']);
                return;
            }

            // Vérifie la capacité du meuble
            $capaciteMaxMeuble = (int)($pdo->query("SELECT capacite_max FROM meuble WHERE id = $idMeuble")->fetchColumn());
            $nbStacksActuels = (int)($pdo->query("SELECT COUNT(*) FROM stack WHERE id_meuble = $idMeuble")->fetchColumn());

            if ($capaciteMaxMeuble > 0 && $nbStacksActuels >= $capaciteMaxMeuble) {
                http_response_code(409);
                echo json_encode(['error' => "Capacité maximale du meuble atteinte ($nbStacksActuels/$capaciteMaxMeuble)"]);
                return;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO stack (id_meuble, nom, capacite_max, capacite_utilisee) VALUES (?, ?, ?, 0)");
                $stmt->execute([$idMeuble, $nom, $capaciteMax]);
                echo json_encode(['success' => true, 'message' => 'Stack ajouté avec succès', 'id' => $pdo->lastInsertId()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  MODIFIER UN STACK
         * ══════════════════════════════════════ */
        case 'editStack':
            $id = (int)($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $capaciteMax = (int)($_POST['capacite_max'] ?? 0);

            if (empty($nom) || $id <= 0 || $capaciteMax <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes ou invalides']);
                return;
            }

            try {
                $stmt = $pdo->prepare("UPDATE stack SET nom = ?, capacite_max = ? WHERE id = ?");
                $stmt->execute([$nom, $capaciteMax, $id]);
                echo json_encode(['success' => true, 'message' => 'Stack modifié avec succès']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  SUPPRIMER UN STACK
         * ══════════════════════════════════════ */
        case 'deleteStack':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            try {
                $pdo->beginTransaction();

                // Supprime les produits associés
                $stmtProduits = $pdo->prepare("DELETE FROM stack_produit WHERE id_stack = ?");
                $stmtProduits->execute([$id]);

                // Supprime le stack
                $stmt = $pdo->prepare("DELETE FROM stack WHERE id = ?");
                $stmt->execute([$id]);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Stack supprimé avec succès']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        /* ══════════════════════════════════════
         *  RÉCUPÉRER LES PRODUITS D'UN STACK (pour la visualisation)
         * ══════════════════════════════════════ */
        case 'getStackProduits':
            $idStack = (int)($_POST['id_stack'] ?? 0);
            if ($idStack <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de stack invalide']);
                return;
            }

            try {
                // Récupère les produits du stack
                $stmt = $pdo->prepare("
            SELECT sp.quantite, p.id, p.nom, p.identifiant, p.image, p.prix_ht
            FROM stack_produit sp
            JOIN produit p ON p.id = sp.id_produit
            WHERE sp.id_stack = ?
            ORDER BY p.nom ASC
        ");
                $stmt->execute([$idStack]);
                $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calcule la quantité totale
                $totalQuantite = array_reduce($produits, function($sum, $p) {
                    return $sum + $p['quantite'];
                }, 0);

                echo json_encode([
                    'success' => true,
                    'produits' => $produits,
                    'total_quantite' => $totalQuantite
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
            }
            return;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue: ' . $action]);
            return;
    }


}
