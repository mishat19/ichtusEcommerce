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
            [
                'nom'         => 'Stock',
                'url'         => $base . 'stock/',
                'methode'     => 'POST',
                'description' => 'Gestion du stock : consultation, réservation, libération, mise à jour, entrepôts.',
                'params'      => [
                    'token'  => 'obligatoire',
                    'action' => 'obligatoire — action à effectuer',
                ],
                'actions'     => [
                    [
                        'action'      => 'list',
                        'description' => 'Liste tous les stocks avec infos produit.',
                        'params'      => [],
                    ],
                    [
                        'action'      => 'getStock',
                        'description' => 'Récupère le stock d\'un produit spécifique.',
                        'params'      => ['id_produit' => 'obligatoire'],
                    ],
                    [
                        'action'      => 'reserver',
                        'description' => 'Réserve du stock pour un ajout au panier.',
                        'params'      => ['id_produit' => 'obligatoire', 'quantite' => 'obligatoire'],
                    ],
                    [
                        'action'      => 'liberer',
                        'description' => 'Libère du stock réservé (expiration panier).',
                        'params'      => ['id_produit' => 'obligatoire', 'quantite' => 'obligatoire'],
                    ],
                    [
                        'action'      => 'updateStock',
                        'description' => 'Met à jour le stock (entrée/sortie manuelle) avec mouvement.',
                        'params'      => [
                            'id_produit'     => 'obligatoire',
                            'quantite'       => 'obligatoire',
                            'type_mouvement' => 'optionnel — entree (défaut) ou sortie',
                            'commentaire'    => 'optionnel',
                            'id_stack'       => 'optionnel',
                        ],
                    ],
                    [
                        'action'      => 'addToStack',
                        'description' => 'Ajoute plusieurs produits dans un stack (batch).',
                        'params'      => [
                            'id_stack' => 'obligatoire',
                            'produits' => 'obligatoire — JSON array [{id_produit, quantite}, ...]',
                        ],
                    ],
                    [
                        'action'      => 'getEntrepots',
                        'description' => 'Liste les entrepôts avec meubles, stacks et taux d\'occupation.',
                        'params'      => [],
                    ],
                    [
                        'action'      => 'getStacks',
                        'description' => 'Liste tous les stacks avec infos meuble et entrepôt.',
                        'params'      => [],
                    ],
                    [
                        'action'      => 'getProduits',
                        'description' => 'Liste les produits actifs (pour les dropdowns).',
                        'params'      => [],
                    ],
                    [
                        'action'      => 'getQrCode',
                        'description' => 'Récupère le QR code d\'un produit.',
                        'params'      => ['id_produit' => 'obligatoire'],
                    ],
                ],
                'exemple'     => [
                    'liste'    => 'POST ' . $base . 'stock/  →  token=xxx&action=list',
                    'detail'   => 'POST ' . $base . 'stock/  →  token=xxx&action=getStock&id_produit=5',
                    'reserver' => 'POST ' . $base . 'stock/  →  token=xxx&action=reserver&id_produit=5&quantite=2',
                ],
            ],
        ],
    ];

    header('Content-Type: application/json');
    echo json_encode($notice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}