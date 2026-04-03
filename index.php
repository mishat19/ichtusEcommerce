<?php

require_once 'model/model.php';

session_start();
$_SESSION['idClient'] = 1;

/* ───────── CONTROLLERS ───────── */
require_once 'controller/c-accueil.php';
require_once 'controller/c-produit.php';
require_once 'controller/c-panier.php';
require_once 'controller/c-profil.php';
require_once 'controller/c-commandes.php';
require_once 'controller/c-parcours-commande.php';
require_once 'controller/c-paiement.php';

/* ───────── ROUTEUR ───────── */

// URL propre
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = rtrim($url, '/');

// Découpage de l'URL
$segments = explode('/', trim($url, '/'));

// Exemple :
// /produit/pate-fruit → ['produit', 'pate-fruit']

$page = $segments[0] ?? 'accueil';
$param = $segments[1] ?? null;

/* ══════════════════════════════════════════════
 * ROUTES
 * ══════════════════════════════════════════════ */

switch ($page) {
    /* ───────── PRODUIT ───────── */
    case 'produit':
        if ($param) {
            $_GET['identifiant'] = $param;
        }
        produit();
        break;

    /* ───────── PANIER ───────── */
    case 'panier':
        panier();
        break;

    /* ───────── PROFIL ───────── */
    case 'profil':

        if (isset($segments[1])) {

            // /profil/ajouter-adresse
            if ($segments[1] === 'ajouter-adresse') {
                ajouterAdresse();
                break;
            }

            // /profil/modifier-adresse
            if ($segments[1] === 'modifier-adresse') {
                modifierAdresse();
                break;
            }

            // /profil/adresse/12
            if ($segments[1] === 'adresse' && isset($segments[2])) {
                $_GET['id'] = $segments[2];
                getAdresseJson();
                break;
            }
        }

        profil();
        break;

    /* ───────── COMMANDES ───────── */
    case 'commandes':
        commandes();
        break;

    case 'recapitulatif':
        commandeRecap();
        break;

    case 'adresses':
        commandeAdresses();
        break;

    case 'paiement':
        commandePaiement();
        break;

    case 'retour-paiement':
        if ($param) {
            $_GET['retourPaiement'] = $param;
        }
        retourPaiement();
        break;

    case 'ipn':
        ipnPaiement();
        break;

    case 'confirmation':
        if ($param) {
            $_GET['confirmation'] = $param;
        }
        commandeConfirmation();
        break;

    /* ───────── DEFAULT ───────── */
    default:
        accueil();
        break;
}