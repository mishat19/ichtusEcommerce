<?php

function connexion() {
    global $pdo;

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $client = checkClient($email, $password);

        if ($client) {
            $_SESSION['idClient'] = $client['id'];
            $_SESSION['nomClient'] = $client['nom'];
            $_SESSION['prenomClient'] = $client['prenom'];
            $_SESSION['role'] = $client['role'];

            session_write_close();
            header('Location: /accueil');
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/auth/v-connexion.php';
    require_once 'view/inc/inc.footer.php';
}

function inscription() {
    global $pdo;

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (getClientByEmail($email)) {
            $error = "Cet email est déjà utilisé.";
        } else {
            if (addClient($nom, $prenom, $email, $password)) {
                // Connexion automatique après inscription
                $client = getClientByEmail($email);
                $_SESSION['idClient'] = $client['id'];
                $_SESSION['nomClient'] = $client['nom'];
                $_SESSION['prenomClient'] = $client['prenom'];
                $_SESSION['role'] = $client['role'];

                session_write_close();
                header('Location: /accueil');
                exit;
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }

    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/auth/v-inscription.php';
    require_once 'view/inc/inc.footer.php';
}

function deconnexion() {
    session_destroy();
    header('Location: /accueil');
    exit;
}
