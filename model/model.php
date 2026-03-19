<?php

session_start();

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
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactive l'émulation des requêtes préparées (meilleure sécurité)
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

function getProduitById($identifiant)
{
    global $pdo;
    $sql = "SELECT p., t.taux, (p.prix_ht (1 + (t.taux / 100))) AS prix_ttc 
            FROM produit p 
            INNER JOIN tva t ON p.id_tva = t.id 
            WHERE p.identifiant = :identifiant 
            AND p.statut = 'actif'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['identifiant' => $identifiant]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // fetch() car on ne veut qu'un seul résultat
}