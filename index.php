<?php

require_once 'model/model.php';

$_SESSION['idClient'] = 1;

require_once 'controller/c-accueil.php';
require_once 'controller/c-produit.php';
require_once 'controller/c-panier.php';

if(isset($_GET['page']) && $_GET['page']){
    switch ($_GET['page']) {
        case 'produit': produit(); break;
        case 'panier': panier(); break;
        default: accueil(); break;
    }
}else{
    accueil();
}