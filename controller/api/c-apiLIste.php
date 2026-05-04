<?php

function APIListe() {

    $base = 'https://b2-gp97.kevinpecro.info/api/';

    $notice = [
        'api'       => 'VOLTEX API',
        'version'   => '1.0',
        'auth'      => 'Toutes les requêtes nécessitent un POST avec le champ token',
        'endpoints' => [
            [
                'nom'         => 'Liste des endpoints',
                'url'         => $base . 'liste/',
                'methode'     => 'POST',
                'description' => 'Retourne la liste de tous les endpoints disponibles.',
                'params'      => ['token' => 'obligatoire'],
            ],
            [
                'nom'         => 'Commandes',
                'url'         => $base . 'commande/',
                'methode'     => 'POST',
                'description' => 'Sans id : liste toutes les commandes. Avec id : détail commande + produits.',
                'params'      => [
                    'token' => 'obligatoire',
                    'id'    => 'optionnel — id de la commande',
                ],
                'exemple'     => [
                    'liste'  => 'POST ' . $base . 'commande/  →  token=xxx',
                    'detail' => 'POST ' . $base . 'commande/  →  token=xxx&id=12',
                ],
            ],
            [
                'nom'         => 'Paiements',
                'url'         => $base . 'paiement/',
                'methode'     => 'POST',
                'description' => 'Sans id : liste tous les paiements + infos commande. Avec id : détail paiement + commande + produits.',
                'params'      => [
                    'token' => 'obligatoire',
                    'id'    => 'optionnel — id du paiement',
                ],
                'exemple'     => [
                    'liste'  => 'POST ' . $base . 'paiement/  →  token=xxx',
                    'detail' => 'POST ' . $base . 'paiement/  →  token=xxx&id=1',
                ],
                'statuts_paiement' => ['accepte', 'refuse', 'annule'],
                'statuts_commande' => ['en_attente', 'payee', 'refusee', 'annulee'],
            ],
        ],
    ];

    header('Content-Type: application/json');
    echo json_encode($notice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}