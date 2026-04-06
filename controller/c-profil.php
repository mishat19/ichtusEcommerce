<?php
function profil(): void
{
    global $pdo;

    // 🔐 Sécurité
    if (!isset($_SESSION['idClient'])) {
        header('Location: /login');
        exit;
    }

    // 👤 Infos client
    $stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
    $stmt->execute([$_SESSION['idClient']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    // 📦 Récupération des adresses
    $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id_client = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['idClient']]);
    $adresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 📩 TRAITEMENT FORMULAIRE
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $estParDefaut = isset($_POST['est_par_defaut']) ? 1 : 0;

        // 👉 Si on coche "par défaut"
        if ($estParDefaut == 1) {
            // On enlève l'ancien défaut pour CE TYPE
            $stmt = $pdo->prepare("
                UPDATE adresse 
                SET est_par_defaut = 0 
                WHERE id_client = ? AND type = ?
            ");
            $stmt->execute([$_SESSION['idClient'], $_POST['type']]);
        }

        // ➕ INSERT
        $stmt = $pdo->prepare("
            INSERT INTO adresse (
                id_client, prenom, nom, email, telephone,
                type, adresse, complement, ville, code_postal, est_par_defaut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['idClient'],
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

        // 🔄 Refresh (PRG pattern)
        header("Location: /profil");
        exit;
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/profil/v-profil.php';
    require_once 'view/inc/inc.footer.php';
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

// Récupère une adresse par ID
function getAdresseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
