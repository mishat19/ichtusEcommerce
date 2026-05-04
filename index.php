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
require_once './backoffice/controller/c-backoffice.php';
require_once 'controller/api/c-apiLIste.php';
require_once 'controller/api/c-apiCommande.php';
require_once 'controller/api/c-apiPaiement.php';
require_once 'backoffice/controller/c-bo-dashboard.php';
require_once 'backoffice/controller/c-bo-commande.php';
require_once 'backoffice/controller/c-bo-paiement.php';

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

/* ══ ROUTING API ══════════════════════════════════════════════════ */
if (isset($_GET['pageAPI'])) {
    switch ($_GET['pageAPI']) {
        case 'commande': APICommande(); break;
        case 'paiement': APIPaiement(); break;
        default:         APIListe();    break;
    }
    exit;
}

switch ($page) {
    /* ───────── BO ───────── */
    case 'backoffice':

        // /bo/commande
        if (isset($segments[1])) {

            if ($segments[1] === 'commandes') {
                BOCommande();
                break;
            }

            // /bo/commande/12
            if ($segments[1] === 'commande' && isset($segments[2])) {
                BOCommande($segments[2]);
                break;
            }

            // /bo/paiements
            if ($segments[1] === 'paiements') {
                BOPaiement();
                break;
            }

            // /bo/paiements
            if ($segments[1] === 'produits') {
                boProduits();
                break;
            }

            // Si route inconnue → erreur 404
            http_response_code(404);
            echo "Page BO introuvable";
            exit;
        }

        // /bo → dashboard
        BODashboard();
        break;
    case 'api':
        require_once 'backoffice/controller/api/c-apiListe.php';
        break;
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