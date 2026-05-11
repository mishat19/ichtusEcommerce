<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Paris');

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'b2-gp97';
$dbUser = getenv('DB_USER') ?: 'b2-gp97';
$dbPass = getenv('DB_PASS') ?: '6!T4F3?X1Z9Q7p2A8V';
$charset = getenv('CHARSET') ?: 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$charset";

// Options de configuration de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les exceptions en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => true,                   // Émulation des requêtes préparées pour compatibilité
];

try {
    // Création de l'instance PDO (connexion)
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    // En cas d'erreur, on affiche le message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function getAllProduits()
{
    global $pdo;
    $sql = "SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc 
            FROM produit p 
            INNER JOIN tva t ON p.id_tva = t.id 
            WHERE p.statut = 'actif'";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProduitById($idOrIdentifiant)
{
    global $pdo;
    
    // Si c'est un nombre, on cherche par ID, sinon par identifiant (slug)
    $column = is_numeric($idOrIdentifiant) ? 'id' : 'identifiant';
    
    $sql = "SELECT p.*, t.taux, (p.prix_ht * (1 + (t.taux / 100))) AS prix_ttc 
            FROM produit p 
            INNER JOIN tva t ON p.id_tva = t.id 
            WHERE p.$column = :val 
            AND p.statut = 'actif'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['val' => $idOrIdentifiant]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ───────── AUTHENTIFICATION ───────── */

function getClientByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function addClient($nom, $prenom, $email, $password) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, email, password, role, date_creation) VALUES (?, ?, ?, ?, 'CLIENT', NOW())");
    return $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
}

function checkClient($email, $password) {
    $client = getClientByEmail($email);
    if ($client && password_verify($password, $client['password'])) {
        return $client;
    }
    return false;
}

/* ───────── SÉCURITÉ (CSRF & XSS) ───────── */

/**
 * Génère ou récupère le token CSRF actuel
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le token CSRF fourni est valide
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals(get_csrf_token(), $token)) {
            http_response_code(403);
            error_log("CSRF Error: Submitted [$token] vs Session [" . get_csrf_token() . "]");
            die("Erreur de sécurité (CSRF) : Votre session a peut-être expiré. Veuillez rafraîchir la page et réessayer.");
        }
    }
}

/**
 * Échappe une chaîne pour l'affichage HTML (Protection XSS)
 */
function e($value) {
    echo htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}