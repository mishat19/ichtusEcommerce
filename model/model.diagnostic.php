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
        'API Paiement' => '/api/paiement',
        'API Dashboard' => '/api/dashboard',
        'API Stock' => '/api/stock'
    ];

    foreach ($routes_to_test as $name => $path) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $api_url = "$protocol://$host$path";
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Authentification pour les APIs internes
        if (strpos($path, '/api/') === 0) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => 'WDIhUThWMz9aN0Y0VDFwOUE2']));
        }

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
    $required_tables = ['produit', 'commande', 'commande_produit', 'paiement', 'client', 'adresse', 'tva', 'stock', 'mouvement_stock', 'entrepot', 'meuble', 'stack', 'stack_produit', 'qr_code', 'panier', 'panier_produit'];
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

    // 5. Dossiers et Permissions
    $directories = ['images/', 'logs/'];
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $results['tests'][] = [
            'name' => 'Droits d\'écriture (' . $dir . ')',
            'status' => is_writable($dir) ? 'success' : 'danger',
            'message' => is_writable($dir) ? 'Dossier accessible en écriture.' : 'Dossier non accessible en écriture !',
            'category' => 'Système'
        ];
    }

    // 6. Configuration PHP (Performances et Uploads)
    $upload_max = ini_get('upload_max_filesize');
    $post_max = ini_get('post_max_size');
    $results['tests'][] = [
        'name' => 'Limites d\'Upload PHP',
        'status' => (intval($upload_max) >= 2 && intval($post_max) >= 2) ? 'success' : 'warning',
        'message' => "upload_max_filesize: $upload_max, post_max_size: $post_max",
        'category' => 'Environnement'
    ];

    // 7. Sécurité (HTTPS)
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $results['tests'][] = [
        'name' => 'Connexion Sécurisée (HTTPS)',
        'status' => $is_https ? 'success' : 'warning',
        'message' => $is_https ? 'Le site est servi via HTTPS.' : 'Le site n\'est pas en HTTPS. Fortement recommandé en production.',
        'category' => 'Sécurité'
    ];

    // ───────── 2. STATISTIQUES ─────────
    try {
        $results['stats']['produits_total'] = $pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn();
        $results['stats']['produits_actifs'] = $pdo->query("SELECT COUNT(*) FROM produit WHERE statut = 'actif'")->fetchColumn();
        $results['stats']['commandes_total'] = $pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
        $results['stats']['ca_total'] = $pdo->query("SELECT SUM(montant) FROM paiement WHERE statut = 'accepte'")->fetchColumn() / 100;
        $results['stats']['clients_total'] = $pdo->query("SELECT COUNT(*) FROM client")->fetchColumn();
        $results['stats']['clients_actifs'] = $pdo->query("SELECT COUNT(DISTINCT id_client) FROM commande")->fetchColumn();
        
        $results['stats']['panier_moyen'] = $results['stats']['commandes_total'] > 0 ? $results['stats']['ca_total'] / $results['stats']['commandes_total'] : 0;

        $files = glob('images/' . "*");
        $results['stats']['nb_images'] = count($files);
        $total_size = 0;
        foreach ($files as $file) { $total_size += filesize($file); }
        $results['stats']['size_images'] = round($total_size / (1024 * 1024), 2);

        // Stats supplémentaires (QR Codes, Paniers)
        $results['stats']['qr_codes_total'] = $pdo->query("SELECT COUNT(*) FROM qr_code")->fetchColumn();
        $results['stats']['paniers_actifs'] = $pdo->query("SELECT COUNT(*) FROM panier")->fetchColumn();
    } catch (Exception $e) {}

    // ───────── 3. ALERTES ─────────
    try {
        $orphans = $pdo->query("SELECT id FROM commande WHERE id NOT IN (SELECT DISTINCT id_commande FROM commande_produit)")->fetchAll();
        if (count($orphans) > 0) $results['alerts'][] = ['level' => 'warning', 'message' => count($orphans) . ' commande(s) sans produit.'];
        
        $unpaid = $pdo->query("SELECT id FROM commande WHERE statut = 'payee' AND id NOT IN (SELECT DISTINCT id_commande FROM paiement WHERE statut = 'accepte')")->fetchAll();
        if (count($unpaid) > 0) $results['alerts'][] = ['level' => 'danger', 'message' => count($unpaid) . ' commande(s) payée(s) sans transaction.'];

        $negative_stock = $pdo->query("SELECT id_produit FROM stock WHERE quantite_disponible < 0")->fetchAll();
        if (count($negative_stock) > 0) $results['alerts'][] = ['level' => 'danger', 'message' => count($negative_stock) . ' produit(s) en stock négatif.'];

        $alert_stock = $pdo->query("SELECT id_produit FROM stock WHERE quantite_disponible <= seuil_alerte")->fetchAll();
        if (count($alert_stock) > 0) $results['alerts'][] = ['level' => 'warning', 'message' => count($alert_stock) . ' produit(s) sous le seuil d\'alerte.'];

        // Alerte Produits orphelins (sans TVA)
        $no_tva = $pdo->query("SELECT id FROM produit WHERE id_tva IS NULL")->fetchAll();
        if (count($no_tva) > 0) $results['alerts'][] = ['level' => 'danger', 'message' => count($no_tva) . ' produit(s) sans taux de TVA associé.'];

        // Alerte Paniers abandonnés (plus de 24h)
        $abandoned_carts = $pdo->query("SELECT id FROM panier WHERE date_creation < DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetchAll();
        if (count($abandoned_carts) > 0) $results['alerts'][] = ['level' => 'warning', 'message' => count($abandoned_carts) . ' panier(s) potentiellement abandonné(s) (plus de 24h).'];
    } catch (Exception $e) {}

    return $results;
}
