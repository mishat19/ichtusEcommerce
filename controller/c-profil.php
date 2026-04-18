<?php
function profil() {
    global $pdo;

    if (!isset($_SESSION['idClient'])) {
        header('Location: /login');
        exit;
    }

    // 📩 POST (AJOUT / MODIF / SUPPRESSION)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ❌ DELETE
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {

            // 🔍 1. Récupérer l'adresse supprimée (pour connaître son type)
            $stmt = $pdo->prepare("SELECT type FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_POST['id'], $_SESSION['idClient']]);
            $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($adresse) {
                $type = $adresse['type'];

                // ❌ 2. Supprimer l'adresse
                $stmt = $pdo->prepare("DELETE FROM adresse WHERE id = ? AND id_client = ?");
                $stmt->execute([$_POST['id'], $_SESSION['idClient']]);

                // 🔍 3. Vérifier s'il reste une adresse par défaut pour ce type
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM adresse 
                    WHERE id_client = ? AND type = ? AND est_par_defaut = 1
                ");
                $stmt->execute([$_SESSION['idClient'], $type]);
                $hasDefault = $stmt->fetchColumn();

                // ❌ Si aucune par défaut → en définir une
                if ($hasDefault == 0) {

                    // 🔍 4. Récupérer la plus ancienne adresse
                    $stmt = $pdo->prepare("
                        SELECT id FROM adresse 
                        WHERE id_client = ? AND type = ?
                        ORDER BY id ASC
                        LIMIT 1
                    ");
                    $stmt->execute([$_SESSION['idClient'], $type]);
                    $ancienne = $stmt->fetch(PDO::FETCH_ASSOC);

                    // ✅ 5. La définir par défaut si elle existe
                    if ($ancienne) {
                        $stmt = $pdo->prepare("
                            UPDATE adresse 
                            SET est_par_defaut = 1 
                            WHERE id = ?
                        ");
                        $stmt->execute([$ancienne['id']]);
                    }
                }
            }

            header("Location: /profil");
            exit;
        }

        // ✏️ EDIT (mode édition)
        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_POST['id'], $_SESSION['idClient']]);
            $adresseEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 📩 POST (AJOUT / MODIF)
        if (isset($_POST['prenom'])) { // Si le formulaire d'adresse est soumis
            $estParDefaut = isset($_POST['est_par_defaut']) ? 1 : 0;

            if ($estParDefaut) {
                $stmt = $pdo->prepare("
                UPDATE adresse
                SET est_par_defaut = 0
                WHERE id_client = ? AND type = ?
            ");
                $stmt->execute([$_SESSION['idClient'], $_POST['type']]);
            }

            if (!empty($_POST['id'])) {
                // ✏️ UPDATE
                $stmt = $pdo->prepare("
                    UPDATE adresse SET
                        prenom=?, nom=?, email=?, telephone=?,
                        type=?, adresse=?, complement=?, ville=?, code_postal=?, est_par_defaut=?
                    WHERE id=? AND id_client=?
                ");

                $stmt->execute([
                    $_POST['prenom'],
                    $_POST['nom'],
                    $_POST['email'],
                    $_POST['telephone'],
                    $_POST['type'],
                    $_POST['adresse'],
                    $_POST['complement'] ?? '',
                    $_POST['ville'],
                    $_POST['code_postal'],
                    $estParDefaut,
                    $_POST['id'],
                    $_SESSION['idClient']
                ]);
            } else {
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
            }

            header("Location: /profil");
            exit;
        }
    }

    // 👤 Client
    $stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
    $stmt->execute([$_SESSION['idClient']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    // 📦 Adresses
    $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id_client = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['idClient']]);
    $adresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🧠 MODE EDIT
    if (!isset($adresseEdit)) {
        $adresseEdit = null;

        if (isset($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM adresse WHERE id = ? AND id_client = ?");
            $stmt->execute([$_GET['edit'], $_SESSION['idClient']]);
            $adresseEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }
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