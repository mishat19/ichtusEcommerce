<?php
function contact() {
    global $pdo;

    // Initialisation des variables pour la vue
    $errors = [];
    $success = null;
    $client = [];

    // 📩 Traitement du formulaire de contact
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        // Vérification du token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $errors[] = "Token de sécurité invalide. Veuillez réessayer.";
        } else {
            $nom = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $sujet = trim($_POST['sujet'] ?? '');
            $message = trim($_POST['message'] ?? '');

            // Validation des champs
            if (empty($nom)) {
                $errors[] = "Le nom est obligatoire.";
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email est invalide.";
            }
            if (empty($sujet)) {
                $errors[] = "Le sujet est obligatoire.";
            }
            if (empty($message)) {
                $errors[] = "Le message est obligatoire.";
            }

            // Si aucune erreur, enregistrer en BDD
            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO messages_contact (nom, email, sujet, message, date_envoi, statut)
                        VALUES (?, ?, ?, ?, NOW(), 'unread')
                    ");
                    $stmt->execute([$nom, $email, $sujet, $message]);

                    $success = "Votre message a été enregistré avec succès ! Nous vous répondrons dans les plus brefs délais.";
                } catch (PDOException $e) {
                    $errors[] = "Une erreur est survenue lors de l'enregistrement de votre message. Veuillez réessayer plus tard.";
                    error_log("Erreur BDD dans contact(): " . $e->getMessage());
                }
            }
        }
    }

    // 👤 Pré-remplir le formulaire si l'utilisateur est connecté
    if (isset($_SESSION['idClient'])) {
        try {
            $stmt = $pdo->prepare("SELECT prenom, nom, email FROM client WHERE id = ?");
            $stmt->execute([$_SESSION['idClient']]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur BDD dans contact() (client): " . $e->getMessage());
        }
    }

    // 📄 Inclusion des templates
    require_once 'view/inc/inc.head.php';
    require_once 'view/inc/inc.header.php';
    require_once 'view/contact/v-contact.php';
    require_once 'view/inc/inc.footer.php';
}