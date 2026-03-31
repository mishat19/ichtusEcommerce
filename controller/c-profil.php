<?php
function profil() {
    global $pdo;

    // Vérifie si c'est une requête AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Ne pas afficher la vue pour les requêtes AJAX
        return;
    }

    // Récupère les infos du client
    $stmtClient = $pdo->prepare("SELECT * FROM client WHERE id = ?");
    $stmtClient->execute([$_SESSION['idClient']]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    // Récupère les adresses
    $adressesFacturation = getAdressesByType($_SESSION['idClient'], 'facturation');
    $adressesLivraison = getAdressesByType($_SESSION['idClient'], 'livraison');

    // Affiche la vue uniquement pour les requêtes normales
    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/profil/v-profil.php';
    require_once 'view/inc/inc.footer.php';
}

// Gestion des routes pour les adresses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['type']) && !isset($_POST['id'])) {
        ajouterAdresse();
    } elseif (isset($_POST['id'])) {
        modifierAdresse();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    getAdresseJson();
}

// Récupère les adresses d'un client par type
function getAdressesByType($idClient, $type) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM adresse
        WHERE id_client = ? AND type = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$idClient, $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère une adresse par ID (pour le modal de modification)
function getAdresseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Ajoute une nouvelle adresse (version AJAX)
function ajouterAdresse() {
    global $pdo;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'])) {
        $type = $_POST['type'];
        $data = [
            'id_client' => $_SESSION['idClient'],
            'type' => $type,
            'prenom' => $_POST['prenom'],
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'adresse' => $_POST['adresse'],
            'complement' => $_POST['complement'] ?? '',
            'code_postal' => $_POST['code_postal'],
            'ville' => $_POST['ville']
        ];

        // Insère la nouvelle adresse
        $stmt = $pdo->prepare("
            INSERT INTO adresse (
                id_client, type, prenom, nom, email, telephone,
                adresse, complement, code_postal, ville
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['id_client'], $data['type'], $data['prenom'], $data['nom'], $data['email'],
            $data['telephone'], $data['adresse'], $data['complement'], $data['code_postal'],
            $data['ville']
        ]);

        // Récupère l'adresse nouvellement créée pour la retourner
        $newAdresseId = $pdo->lastInsertId();
        $newAdresse = getAdresseById($newAdresseId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Adresse de $type enregistrée avec succès !",
            'adresse' => $newAdresse
        ]);
        exit;
    }
}

// Modifie une adresse existante (version AJAX)
function modifierAdresse() {
    global $pdo;

    $id = $_POST['id'];

    // 🔍 Vérifier si utilisée dans une commande
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM commande 
        WHERE id_adresse_facturation = ? 
           OR id_adresse_livraison = ?
    ");
    $stmt->execute([$id, $id]);
    $utilisee = $stmt->fetchColumn();

    if ($utilisee == 0) {
        // ✅ UPDATE (économie de place)
        $stmt = $pdo->prepare("
            UPDATE adresse SET
                prenom = ?, nom = ?, email = ?, telephone = ?,
                adresse = ?, complement = ?, code_postal = ?, ville = ?
            WHERE id = ? AND id_client = ?
        ");

        $stmt->execute([
            $_POST['prenom'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            $_POST['adresse'],
            $_POST['complement'] ?? '',
            $_POST['code_postal'],
            $_POST['ville'],
            $id,
            $_SESSION['idClient']
        ]);

        $adresse = getAdresseById($id);

    } else {
        // ❌ utilisée → INSERT nouvelle
        $stmt = $pdo->prepare("
            INSERT INTO adresse (
                id_client, type, prenom, nom, email, telephone,
                adresse, complement, code_postal, ville
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['idClient'],
            $_POST['type'],
            $_POST['prenom'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            $_POST['adresse'],
            $_POST['complement'] ?? '',
            $_POST['code_postal'],
            $_POST['ville']
        ]);

        $adresse = getAdresseById($pdo->lastInsertId());
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'adresse' => $adresse
    ]);
    exit;
}

// Endpoint pour récupérer une adresse (AJAX)
function getAdresseJson() {
    global $pdo;

    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        header('HTTP/1.0 403 Forbidden');
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    if (isset($_GET['id'])) {
        $adresse = getAdresseById($_GET['id']);

        // Vérifie que l'adresse appartient bien à l'utilisateur
        if (!$adresse || $adresse['id_client'] != $_SESSION['idClient']) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Accès refusé']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode($adresse);
        exit;
    }

    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['error' => 'ID manquant']);
    exit;
}
