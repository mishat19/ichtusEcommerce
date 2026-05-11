<?php

function runFullDiagnostic() {
    global $pdo;
    $results = [
        'timestamp' => date('Y-m-d H:i:s'),
        'tests' => [],
        'stats' => [],
        'alerts' => []
    ];

    // ───────── 1. TESTS UNITAIRES ─────────
    
    // 1. Connexion BDD
    try {
        $pdo->query("SELECT 1");
        $results['tests'][] = ['name' => 'Connexion Base de Données', 'status' => 'success', 'message' => 'Connecté avec succès.', 'category' => 'Système'];
    } catch (Exception $e) {
        $results['tests'][] = ['name' => 'Connexion Base de Données', 'status' => 'danger', 'message' => 'Erreur : ' . $e->getMessage(), 'category' => 'Système'];
    }

    // 7. Tests des API Internes (Santé des routes)
    $routes_to_test = [
        'Accueil' => '/',
        'API Produits' => '/api/produits',
        'API Commande' => '/api/commande',
        'API Paiement' => '/api/paiement'
    ];

    foreach ($routes_to_test as $name => $path) {
        $api_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$path";
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code >= 200 && $http_code < 400) ? 'success' : 'warning';
        $results['tests'][] = [
            'name' => 'Route: ' . $name,
            'status' => $status,
            'message' => 'Code HTTP: ' . ($http_code ?: 'Timeout'),
            'category' => 'Réseau'
        ];
    }

    // 2. Intégrité des Tables
    $required_tables = ['produit', 'commande', 'commande_produit', 'paiement', 'client', 'adresse', 'tva'];
    try {
        $existing_tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $missing = array_diff($required_tables, $existing_tables);
        if (empty($missing)) {
            $results['tests'][] = ['name' => 'Schéma de Données', 'status' => 'success', 'message' => 'Toutes les tables requises sont présentes.', 'category' => 'Base de Données'];
        } else {
            $results['tests'][] = ['name' => 'Schéma de Données', 'status' => 'danger', 'message' => 'Tables manquantes : ' . implode(', ', $missing), 'category' => 'Base de Données'];
        }
    } catch (Exception $e) {}

    // 3. Extensions PHP
    $extensions = ['pdo_mysql', 'mbstring', 'openssl', 'gd', 'json', 'curl'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            $results['tests'][] = ['name' => 'Extension PHP: ' . $ext, 'status' => 'success', 'message' => 'Chargée.', 'category' => 'Environnement'];
        } else {
            $results['tests'][] = ['name' => 'Extension PHP: ' . $ext, 'status' => 'danger', 'message' => 'MANQUANTE !', 'category' => 'Environnement'];
        }
    }

    // 4. Sécurité
    $results['tests'][] = [
        'name' => 'Mode Débogage',
        'status' => (ini_get('display_errors') == '1') ? 'warning' : 'success',
        'message' => (ini_get('display_errors') == '1') ? 'display_errors est ACTIVÉ.' : 'display_errors est désactivé.',
        'category' => 'Sécurité'
    ];

    // 5. Dossier Images
    $img_dir = 'images/';
    if (is_dir($img_dir)) {
        $results['tests'][] = [
            'name' => 'Droits d\'écriture (images/)',
            'status' => is_writable($img_dir) ? 'success' : 'danger',
            'message' => is_writable($img_dir) ? 'Dossier accessible.' : 'Dossier non accessible en écriture !',
            'category' => 'Système'
        ];
    }

    // ───────── 2. STATISTIQUES ─────────
    try {
        $results['stats']['produits_total'] = $pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn();
        $results['stats']['produits_actifs'] = $pdo->query("SELECT COUNT(*) FROM produit WHERE statut = 'actif'")->fetchColumn();
        $results['stats']['commandes_total'] = $pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
        $results['stats']['ca_total'] = $pdo->query("SELECT SUM(montant) FROM paiement WHERE statut = 'accepte'")->fetchColumn() / 100;
        $results['stats']['clients_total'] = $pdo->query("SELECT COUNT(*) FROM client")->fetchColumn();
        $results['stats']['clients_actifs'] = $pdo->query("SELECT COUNT(DISTINCT id_client) FROM commande")->fetchColumn();
        
        $results['stats']['panier_moyen'] = $results['stats']['commandes_total'] > 0 ? $results['stats']['ca_total'] / $results['stats']['commandes_total'] : 0;

        $files = glob($img_dir . "*");
        $results['stats']['nb_images'] = count($files);
        $total_size = 0;
        foreach ($files as $file) { $total_size += filesize($file); }
        $results['stats']['size_images'] = round($total_size / (1024 * 1024), 2);
    } catch (Exception $e) {}

    // ───────── 3. ALERTES ─────────
    try {
        $orphans = $pdo->query("SELECT id FROM commande WHERE id NOT IN (SELECT DISTINCT id_commande FROM commande_produit)")->fetchAll();
        if (count($orphans) > 0) $results['alerts'][] = ['level' => 'warning', 'message' => count($orphans) . ' commande(s) sans produit.'];
        
        $unpaid = $pdo->query("SELECT id FROM commande WHERE statut = 'payee' AND id NOT IN (SELECT DISTINCT id_commande FROM paiement WHERE statut = 'accepte')")->fetchAll();
        if (count($unpaid) > 0) $results['alerts'][] = ['level' => 'danger', 'message' => count($unpaid) . ' commande(s) payée(s) sans transaction.'];
    } catch (Exception $e) {}

    return $results;
}
