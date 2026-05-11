<?php
/**
 * Health Agent Child v3 - diagnostic quotidien complet
 * -----------------------------------------------------------------------------
 * À déposer à la racine de chaque site étudiant :
 *   https://b2-gpX.kevinpecro.info/health-agent-child.php
 *
 * Objectif :
 * - répondre uniquement au serveur mère autorisé ;
 * - exécuter des diagnostics prédéfinis ;
 * - charger le model.php local pour récupérer directement le PDO du site ;
 * - ne rien écrire en base ;
 * - ne jamais exécuter de commande libre envoyée depuis l'extérieur.
 */

declare(strict_types=1);

// -----------------------------------------------------------------------------
// CONFIGURATION
// -----------------------------------------------------------------------------
const HEALTH_AGENT_TOKEN = 'TOKEN';
const HEALTH_ALLOWED_IPS = ['212.227.244.122'];
const HEALTH_AGENT_ROOT = __DIR__;
const HEALTH_MAX_FILES = 1200;
const HEALTH_MAX_FILE_SIZE = 2097152; // 2 Mo par fichier lu
const HEALTH_DB_MAX_TABLES = 120;
const HEALTH_DB_MAX_COLUMNS = 50;
const HEALTH_ENABLE_PHP_LINT = true;

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow', true);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// -----------------------------------------------------------------------------
// SORTIE / SÉCURITÉ
// -----------------------------------------------------------------------------
function ha_out(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function ha_client_ip(): string
{
    // Si Cloudflare/proxy est utilisé, REMOTE_ADDR reste le plus sûr côté serveur.
    // On évite de faire confiance à X-Forwarded-For pour l'autorisation.
    return (string)($_SERVER['REMOTE_ADDR'] ?? '');
}

function ha_check_access(): void
{
    if (HEALTH_ALLOWED_IPS !== [] && !in_array(ha_client_ip(), HEALTH_ALLOWED_IPS, true)) {
        ha_out([
            'success' => false,
            'message' => 'IP non autorisée.',
            'client_ip' => ha_client_ip(),
        ], 403);
    }

    $given = (string)($_GET['token'] ?? $_POST['token'] ?? $_SERVER['HTTP_X_HEALTH_TOKEN'] ?? '');

    if (HEALTH_AGENT_TOKEN === '' || HEALTH_AGENT_TOKEN === 'CHANGE-ME-TOKEN-LONG') {
        ha_out([
            'success' => false,
            'message' => 'Token agent non configuré. Modifie HEALTH_AGENT_TOKEN.',
        ], 500);
    }

    if (!hash_equals(HEALTH_AGENT_TOKEN, $given)) {
        ha_out([
            'success' => false,
            'message' => 'Token invalide.',
        ], 403);
    }
}

function ha_root(): string
{
    return realpath(HEALTH_AGENT_ROOT) ?: HEALTH_AGENT_ROOT;
}

function ha_rel_path(string $file): string
{
    $root = rtrim(ha_root(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $real = realpath($file) ?: $file;
    return str_starts_with($real, $root) ? substr($real, strlen($root)) : $file;
}

function ha_safe_identifier(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function ha_mask_secret(?string $value): ?string
{
    if ($value === null || $value === '') {
        return $value;
    }

    $len = mb_strlen($value);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }

    return mb_substr($value, 0, 2) . str_repeat('*', max(4, $len - 4)) . mb_substr($value, -2);
}

// -----------------------------------------------------------------------------
// CHARGEMENT DU MODEL / DÉTECTION PDO
// -----------------------------------------------------------------------------
function ha_include_file_and_capture_pdo(string $file): array
{
    $loaded = false;
    $error = null;

    /*
     * Important : model/model.php crée souvent $pdo au niveau du fichier.
     * Comme l'include est fait dans cette fonction, le $pdo créé existe ici,
     * dans ce scope local. On le capture donc immédiatement après l'include.
     */
    try {
        require_once $file;
        $loaded = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    $candidates = [
        'pdo' => $pdo ?? null,
        'bdd' => $bdd ?? null,
        'db' => $db ?? null,
        'database' => $database ?? null,
        'dbh' => $dbh ?? null,
        'conn' => $conn ?? null,
        'connection' => $connection ?? null,
    ];

    foreach ($candidates as $name => $candidate) {
        if ($candidate instanceof PDO) {
            try {
                $candidate->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $candidate->query('SELECT 1')->fetchColumn();

                return [
                    'pdo' => $candidate,
                    'status' => 'found',
                    'source' => '$' . $name,
                    'message' => 'Connexion PDO détectée via $' . $name . ' dans ' . ha_rel_path($file) . '.',
                    'file' => ha_rel_path($file),
                    'loaded' => $loaded,
                    'error' => $error,
                ];
            } catch (Throwable $e) {
                return [
                    'pdo' => null,
                    'status' => 'invalid',
                    'source' => '$' . $name,
                    'message' => 'PDO détecté via $' . $name . ' dans ' . ha_rel_path($file) . ', mais SELECT 1 échoue : ' . $e->getMessage(),
                    'file' => ha_rel_path($file),
                    'loaded' => $loaded,
                    'error' => $error,
                ];
            }
        }
    }

    return [
        'pdo' => null,
        'status' => $error ? 'error' : 'not_found',
        'source' => null,
        'message' => $error
            ? 'Erreur au chargement de ' . ha_rel_path($file) . ' : ' . $error
            : 'Aucune variable PDO détectée dans ' . ha_rel_path($file) . '.',
        'file' => ha_rel_path($file),
        'loaded' => $loaded,
        'error' => $error,
    ];
}

function ha_get_pdo_from_project(): array
{
    static $done = false;
    static $result = null;

    if ($done && is_array($result)) {
        return $result;
    }

    $done = true;
    $loaded = [];
    $errors = [];
    $attempts = [];

    $bootstrapFiles = [
        HEALTH_AGENT_ROOT . '/model/model.php',
        HEALTH_AGENT_ROOT . '/config.php',
        HEALTH_AGENT_ROOT . '/app/config.php',
        HEALTH_AGENT_ROOT . '/config/config.php',
        HEALTH_AGENT_ROOT . '/include/config.php',
        HEALTH_AGENT_ROOT . '/includes/config.php',
        HEALTH_AGENT_ROOT . '/db.php',
        HEALTH_AGENT_ROOT . '/database.php',
        HEALTH_AGENT_ROOT . '/connexion.php',
        HEALTH_AGENT_ROOT . '/connection.php',
    ];

    foreach ($bootstrapFiles as $file) {
        if (!is_file($file) || !is_readable($file)) {
            continue;
        }

        $capture = ha_include_file_and_capture_pdo($file);
        $attempts[] = [
            'file' => $capture['file'],
            'status' => $capture['status'],
            'source' => $capture['source'],
            'message' => $capture['message'],
        ];

        if (!empty($capture['loaded'])) {
            $loaded[] = $capture['file'];
        }

        if (!empty($capture['error'])) {
            $errors[] = [
                'file' => $capture['file'],
                'message' => $capture['error'],
            ];
        }

        if (($capture['pdo'] ?? null) instanceof PDO) {
            $result = [
                'pdo' => $capture['pdo'],
                'status' => 'found',
                'source' => $capture['source'],
                'message' => $capture['message'],
                'bootstrap' => [
                    'loaded' => $loaded,
                    'errors' => $errors,
                    'attempts' => $attempts,
                    'strategy' => 'include_and_capture_local_pdo',
                    'stopped_after_pdo_found' => true,
                ],
            ];
            return $result;
        }
    }

    $result = [
        'pdo' => null,
        'status' => 'not_found',
        'source' => null,
        'message' => 'Aucune connexion PDO détectée après chargement des fichiers de bootstrap.',
        'bootstrap' => [
            'loaded' => $loaded,
            'errors' => $errors,
            'attempts' => $attempts,
            'strategy' => 'include_and_capture_local_pdo',
            'stopped_after_pdo_found' => false,
        ],
    ];
    return $result;
}

function ha_load_project_bootstrap(): array
{
    $pdoResult = ha_get_pdo_from_project();
    return $pdoResult['bootstrap'] ?? [
        'loaded' => [],
        'errors' => [],
        'attempts' => [],
        'strategy' => 'none',
    ];
}

// -----------------------------------------------------------------------------
// FICHIERS / ANALYSE STATIQUE
// -----------------------------------------------------------------------------
function ha_php_files(): array
{
    $root = ha_root();
    $exclude = ['vendor', 'node_modules', '.git', 'storage', 'cache', 'tmp', 'logs', 'uploads', 'backup', 'backups', 'var/cache'];
    $files = [];

    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getPathname();
            $normalized = str_replace('\\', '/', $path);

            foreach ($exclude as $part) {
                if (preg_match('#(^|/)' . preg_quote($part, '#') . '(/|$)#i', $normalized)) {
                    continue 2;
                }
            }

            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                $files[] = $path;
                if (count($files) >= HEALTH_MAX_FILES) {
                    break;
                }
            }
        }
    } catch (Throwable $e) {
        return [];
    }

    sort($files);
    return $files;
}

function ha_issue(string $severity, string $rule, string $title, string $message, ?string $file = null, ?int $line = null, ?string $recommendation = null): array
{
    return [
        'severity' => $severity,
        'rule' => $rule,
        'title' => $title,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'recommendation' => $recommendation,
    ];
}

function ha_action_files_scan(): array
{
    $files = ha_php_files();
    $issues = [];
    $linesTotal = 0;
    $readFiles = 0;
    $skippedLarge = 0;

    foreach ($files as $file) {
        if (!is_readable($file)) {
            continue;
        }

        $size = @filesize($file);
        if ($size !== false && $size > HEALTH_MAX_FILE_SIZE) {
            $skippedLarge++;
            continue;
        }

        $content = (string)@file_get_contents($file);
        if ($content === '') {
            continue;
        }

        $readFiles++;
        $rel = ha_rel_path($file);
        $lines = preg_split('/\R/', $content) ?: [];
        $lineCount = count($lines);
        $linesTotal += $lineCount;

        if ($lineCount > 900) {
            $issues[] = ha_issue('info', 'large_file', 'Fichier volumineux', $lineCount . ' lignes.', $rel, null, 'Découper le fichier pour améliorer la maintenance.');
        }

        if (preg_match_all('/^(<<<<<<<|=======|>>>>>>>)/m', $content, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $hit) {
                $issues[] = ha_issue('critical', 'conflict_marker', 'Conflit Git non résolu', 'Marqueur de conflit détecté.', $rel, substr_count(substr($content, 0, $hit[1]), "\n") + 1, 'Résoudre le conflit avant correction ou mise en ligne.');
            }
        }

        if (preg_match_all('/<form\b[^>]*method=[\'\"]?post[\'\"]?[^>]*>/i', $content, $forms, PREG_OFFSET_CAPTURE)) {
            foreach ($forms[0] as $form) {
                $chunk = substr($content, $form[1], 3500);
                if (!preg_match('/csrf|token/i', $chunk)) {
                    $issues[] = ha_issue('warning', 'missing_csrf_token', 'Formulaire POST sans CSRF apparent', 'Un formulaire POST ne semble pas contenir de token CSRF.', $rel, substr_count(substr($content, 0, $form[1]), "\n") + 1, 'Ajouter un token en session et le vérifier côté contrôleur.');
                }
            }
        }

        if (preg_match_all('/<\?=\s*(?!htmlspecialchars|htmlentities|number_format|date\(|intval\(|floatval\(|\(int\)|\(float\))[^;]+\?>/i', $content, $outs, PREG_OFFSET_CAPTURE)) {
            foreach ($outs[0] as $out) {
                $issues[] = ha_issue('warning', 'unescaped_output', 'Sortie HTML potentiellement non échappée', 'Une sortie <?= semble afficher une donnée sans échappement.', $rel, substr_count(substr($content, 0, $out[1]), "\n") + 1, 'Utiliser htmlspecialchars((string)$valeur, ENT_QUOTES, UTF-8).');
            }
        }

        if (preg_match_all('/(API_TOKEN|SECRET|PASSWORD|PRIVATE_KEY|TOKEN)\s*[=:>]\s*[\'\"][^\'\"]{8,}[\'\"]/i', $content, $tokens, PREG_OFFSET_CAPTURE)) {
            foreach ($tokens[0] as $tok) {
                $issues[] = ha_issue('warning', 'hardcoded_secret', 'Secret possiblement en dur', 'Une valeur sensible semble être écrite dans le code.', $rel, substr_count(substr($content, 0, $tok[1]), "\n") + 1, 'Déplacer les secrets dans une configuration non versionnée.');
            }
        }

        if (preg_match_all('/echo\s+\$e->getMessage\s*\(|print_r\s*\(\s*\$e\s*\)/i', $content, $errs, PREG_OFFSET_CAPTURE)) {
            foreach ($errs[0] as $err) {
                $issues[] = ha_issue('warning', 'sensitive_error_output', 'Erreur technique affichée', 'Un message d’exception semble affiché côté utilisateur.', $rel, substr_count(substr($content, 0, $err[1]), "\n") + 1, 'Logger l’erreur et afficher un message générique.');
            }
        }
    }

    $severityCounts = ['critical' => 0, 'warning' => 0, 'info' => 0];
    foreach ($issues as $issue) {
        $sev = (string)($issue['severity'] ?? 'info');
        $severityCounts[$sev] = ($severityCounts[$sev] ?? 0) + 1;
    }

    return [
        'success' => true,
        'action' => 'files_scan',
        'status' => $severityCounts['critical'] > 0 ? 'fail' : ($severityCounts['warning'] > 0 ? 'warning' : 'pass'),
        'files_count' => count($files),
        'files_read' => $readFiles,
        'files_skipped_large' => $skippedLarge,
        'lines_total' => $linesTotal,
        'issues_count' => count($issues),
        'severity_counts' => $severityCounts,
        'issues' => array_slice($issues, 0, 500),
    ];
}

function ha_find_php_bin(): ?string
{
    if (!function_exists('shell_exec')) {
        return null;
    }

    $candidates = [PHP_BINARY, '/opt/plesk/php/8.3/bin/php', '/opt/plesk/php/8.2/bin/php', '/opt/plesk/php/8.1/bin/php', '/usr/bin/php', '/usr/local/bin/php', 'php'];
    foreach (array_unique(array_filter($candidates)) as $candidate) {
        if (stripos($candidate, 'php-fpm') !== false) {
            continue;
        }
        $out = trim((string)@shell_exec(escapeshellarg($candidate) . ' -v 2>&1'));
        if ($out !== '' && stripos($out, 'PHP') !== false && stripos($out, 'not found') === false && stripos($out, 'No such file') === false) {
            return $candidate;
        }
    }

    return null;
}

function ha_action_php_syntax(): array
{
    $files = ha_php_files();

    if (!HEALTH_ENABLE_PHP_LINT) {
        return [
            'success' => true,
            'action' => 'php_syntax',
            'status' => 'warning',
            'message' => 'php -l désactivé par configuration.',
            'files_count' => count($files),
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    $php = ha_find_php_bin();
    if (!$php) {
        return [
            'success' => true,
            'action' => 'php_syntax',
            'status' => 'warning',
            'message' => 'PHP CLI introuvable ou shell_exec désactivé.',
            'files_count' => count($files),
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    $errors = [];
    foreach ($files as $file) {
        $result = trim((string)@shell_exec(escapeshellarg($php) . ' -l ' . escapeshellarg($file) . ' 2>&1'));
        if ($result !== '' && stripos($result, 'No syntax errors detected') === false) {
            $errors[] = [
                'file' => ha_rel_path($file),
                'message' => $result,
            ];
        }
    }

    return [
        'success' => true,
        'action' => 'php_syntax',
        'status' => count($errors) > 0 ? 'fail' : 'pass',
        'php_bin' => $php,
        'files_count' => count($files),
        'errors_count' => count($errors),
        'errors' => array_slice($errors, 0, 100),
    ];
}

// -----------------------------------------------------------------------------
// BDD GÉNÉRIQUE VIA model/model.php
// -----------------------------------------------------------------------------
function ha_detect_site_theme(array $tableNames, array $columnNames): array
{
    $text = strtolower(implode(' ', $tableNames) . ' ' . implode(' ', $columnNames));
    $themes = [];

    $rules = [
        'e-commerce' => ['product', 'produit', 'commande', 'order', 'cart', 'panier', 'payment', 'paiement', 'stock'],
        'utilisateurs / comptes' => ['user', 'utilisateur', 'client', 'customer', 'role', 'permission', 'login'],
        'contenu / CMS' => ['page', 'article', 'post', 'media', 'menu', 'slug', 'content', 'contenu'],
        'facturation / comptabilité' => ['facture', 'invoice', 'devis', 'quote', 'payment', 'paiement', 'depense', 'recette', 'mouvement'],
        'planning / événements' => ['event', 'evenement', 'calendar', 'planning', 'reservation', 'booking', 'date_debut', 'date_fin'],
        'support / messages' => ['message', 'ticket', 'conversation', 'chat', 'mail', 'notification'],
    ];

    foreach ($rules as $theme => $keywords) {
        $score = 0;
        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                $score++;
            }
        }
        if ($score > 0) {
            $themes[] = ['theme' => $theme, 'score' => $score];
        }
    }

    usort($themes, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_slice($themes, 0, 5);
}

function ha_action_db_discovery(): array
{
    $pdoResult = ha_get_pdo_from_project();
    $pdo = $pdoResult['pdo'] ?? null;

    if (!$pdo instanceof PDO) {
        return [
            'success' => true,
            'action' => 'db_discovery',
            'status' => 'warning',
            'message' => $pdoResult['message'],
            'pdo_detection' => [
                'status' => $pdoResult['status'],
                'source' => $pdoResult['source'],
                'bootstrap' => $pdoResult['bootstrap'],
            ],
        ];
    }

    try {
        $driver = (string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $serverVersion = '';
        try {
            $serverVersion = (string)$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (Throwable $e) {
            $serverVersion = 'inconnu';
        }
    } catch (Throwable $e) {
        return [
            'success' => false,
            'action' => 'db_discovery',
            'status' => 'fail',
            'message' => 'Impossible de lire les attributs PDO : ' . $e->getMessage(),
            'pdo_detection' => $pdoResult,
        ];
    }

    if ($driver !== 'mysql') {
        return [
            'success' => true,
            'action' => 'db_discovery',
            'status' => 'warning',
            'message' => 'Connexion PDO trouvée, mais analyse détaillée prévue surtout pour MySQL/MariaDB.',
            'driver' => $driver,
            'server_version' => $serverVersion,
            'pdo_detection' => [
                'status' => $pdoResult['status'],
                'source' => $pdoResult['source'],
                'bootstrap' => $pdoResult['bootstrap'],
            ],
        ];
    }

    try {
        $database = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

        $tables = [];
        $tableNames = [];
        $columnNamesAll = [];
        $schema = [];
        $tablesWithoutPrimaryKey = [];
        $emptyTables = [];
        $largeTables = [];
        $engines = [];
        $approxRowsTotal = 0;
        $approxSizeMbTotal = 0.0;

        $stmt = $pdo->query('SHOW FULL TABLES');
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_NUM) : [];
        foreach ($rows as $row) {
            $name = (string)($row[0] ?? '');
            $type = strtoupper((string)($row[1] ?? 'BASE TABLE'));
            if ($name === '') {
                continue;
            }
            $tables[] = ['name' => $name, 'type' => $type];
            $tableNames[] = $name;
        }

        $statusRows = [];
        try {
            $statusStmt = $pdo->query('SHOW TABLE STATUS');
            $statusRows = $statusStmt ? $statusStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $statusRows = [];
        }

        $tableStatusByName = [];
        foreach ($statusRows as $row) {
            $name = (string)($row['Name'] ?? '');
            if ($name === '') {
                continue;
            }

            $rowsApprox = (int)($row['Rows'] ?? 0);
            $sizeMb = round(((int)($row['Data_length'] ?? 0) + (int)($row['Index_length'] ?? 0)) / 1024 / 1024, 2);
            $engine = (string)($row['Engine'] ?? 'inconnu');

            $tableStatusByName[$name] = [
                'approx_rows' => $rowsApprox,
                'size_mb' => $sizeMb,
                'engine' => $engine,
            ];

            $engines[$engine] = ($engines[$engine] ?? 0) + 1;
            $approxRowsTotal += $rowsApprox;
            $approxSizeMbTotal += $sizeMb;

            if ($rowsApprox === 0) {
                $emptyTables[] = $name;
            }

            if ($rowsApprox >= 50000 || $sizeMb >= 20) {
                $largeTables[] = [
                    'table' => $name,
                    'approx_rows' => $rowsApprox,
                    'size_mb' => $sizeMb,
                    'engine' => $engine,
                ];
            }
        }

        foreach (array_slice($tables, 0, HEALTH_DB_MAX_TABLES) as $tableInfo) {
            $table = (string)$tableInfo['name'];
            $type = (string)$tableInfo['type'];

            try {
                $colStmt = $pdo->query('SHOW COLUMNS FROM ' . ha_safe_identifier($table));
                $columns = $colStmt ? $colStmt->fetchAll(PDO::FETCH_ASSOC) : [];

                $columnNames = [];
                $hasPrimary = false;
                $hasUpdated = false;
                $hasCreated = false;

                foreach ($columns as $column) {
                    $field = (string)($column['Field'] ?? '');
                    if ($field === '') {
                        continue;
                    }

                    $columnNames[] = $field;
                    $columnNamesAll[] = $field;

                    if (($column['Key'] ?? '') === 'PRI') {
                        $hasPrimary = true;
                    }

                    if (preg_match('/created_at|date_creation|created|date_ajout|date_add/i', $field)) {
                        $hasCreated = true;
                    }

                    if (preg_match('/updated_at|date_modification|modified_at|updated|date_maj|maj/i', $field)) {
                        $hasUpdated = true;
                    }
                }

                if (!$hasPrimary && $type === 'BASE TABLE') {
                    $tablesWithoutPrimaryKey[] = $table;
                }

                $status = $tableStatusByName[$table] ?? [];
                $schema[] = [
                    'table' => $table,
                    'type' => $type,
                    'columns_count' => count($columnNames),
                    'columns_sample' => array_slice($columnNames, 0, HEALTH_DB_MAX_COLUMNS),
                    'has_primary_key' => $hasPrimary,
                    'has_created_at_like_column' => $hasCreated,
                    'has_updated_at_like_column' => $hasUpdated,
                    'approx_rows' => $status['approx_rows'] ?? null,
                    'size_mb' => $status['size_mb'] ?? null,
                    'engine' => $status['engine'] ?? null,
                ];
            } catch (Throwable $e) {
                $schema[] = [
                    'table' => $table,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $themes = ha_detect_site_theme($tableNames, $columnNamesAll);
        $status = count($tables) === 0 ? 'warning' : (count($tablesWithoutPrimaryKey) > 0 ? 'warning' : 'pass');

        return [
            'success' => true,
            'action' => 'db_discovery',
            'status' => $status,
            'message' => count($tables) . ' table(s) détectée(s) dans la base.',
            'driver' => $driver,
            'server_version' => $serverVersion,
            'database' => $database,
            'pdo_detection' => [
                'status' => $pdoResult['status'],
                'source' => $pdoResult['source'],
                'message' => $pdoResult['message'],
                'bootstrap' => $pdoResult['bootstrap'],
            ],
            'summary' => [
                'tables_count' => count($tables),
                'approx_rows_total' => $approxRowsTotal,
                'approx_size_mb' => round($approxSizeMbTotal, 2),
                'engines' => $engines,
                'tables_without_primary_key_count' => count($tablesWithoutPrimaryKey),
                'empty_tables_count' => count($emptyTables),
                'large_tables_count' => count($largeTables),
                'probable_themes' => $themes,
            ],
            'tables_sample' => array_slice($tables, 0, 60),
            'large_tables' => array_slice($largeTables, 0, 30),
            'empty_tables' => array_slice($emptyTables, 0, 50),
            'tables_without_primary_key' => array_slice($tablesWithoutPrimaryKey, 0, 50),
            'schema_sample' => array_slice($schema, 0, 80),
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'action' => 'db_discovery',
            'status' => 'fail',
            'message' => 'Erreur analyse BDD : ' . $e->getMessage(),
            'pdo_detection' => [
                'status' => $pdoResult['status'],
                'source' => $pdoResult['source'],
                'bootstrap' => $pdoResult['bootstrap'],
            ],
        ];
    }
}


// -----------------------------------------------------------------------------
// ANALYSE STRUCTURE / FONCTIONS / NETTOYAGE
// -----------------------------------------------------------------------------
function ha_all_files(int $max = 2500): array
{
    $root = ha_root();
    $exclude = ['vendor', 'node_modules', '.git', 'storage/cache', 'var/cache'];
    $files = [];

    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getPathname();
            $normalized = str_replace('\\', '/', $path);

            foreach ($exclude as $part) {
                if (preg_match('#(^|/)' . preg_quote($part, '#') . '(/|$)#i', $normalized)) {
                    continue 2;
                }
            }

            if ($file->isFile()) {
                $files[] = $path;
                if (count($files) >= $max) {
                    break;
                }
            }
        }
    } catch (Throwable $e) {
        return [];
    }

    sort($files);
    return $files;
}

function ha_action_structure_scan(): array
{
    $root = ha_root();
    $expectedAny = [
        'model' => ['model', 'models'],
        'controller' => ['controller', 'controllers'],
        'view' => ['view', 'views', 'templates'],
        'assets' => ['assets', 'public', 'css', 'js', 'images'],
    ];

    $missingFamilies = [];
    $detectedFamilies = [];
    foreach ($expectedAny as $family => $candidates) {
        $found = [];
        foreach ($candidates as $dir) {
            if (is_dir($root . '/' . $dir)) {
                $found[] = $dir;
            }
        }
        if ($found) {
            $detectedFamilies[$family] = $found;
        } else {
            $missingFamilies[] = $family;
        }
    }

    $emptyDirs = [];
    $heavyDirs = [];
    $dirStats = [];

    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            /** @var SplFileInfo $item */
            if (!$item->isDir()) {
                continue;
            }

            $path = $item->getPathname();
            $rel = ha_rel_path($path);
            $normalized = str_replace('\\', '/', $rel);

            if (preg_match('#(^|/)(vendor|node_modules|\.git|cache|tmp|logs)(/|$)#i', $normalized)) {
                continue;
            }

            $count = 0;
            $size = 0;
            try {
                $sub = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
                foreach ($sub as $child) {
                    $count++;
                    if ($child->isFile()) {
                        $size += (int)$child->getSize();
                    }
                }
            } catch (Throwable $e) {
                continue;
            }

            if ($count === 0) {
                $emptyDirs[] = $rel;
            }

            if ($count >= 80) {
                $heavyDirs[] = ['dir' => $rel, 'items' => $count, 'direct_size_mb' => round($size / 1024 / 1024, 2)];
            }

            if (count($dirStats) < 80) {
                $dirStats[] = ['dir' => $rel, 'items' => $count, 'direct_size_mb' => round($size / 1024 / 1024, 2)];
            }
        }
    } catch (Throwable $e) {
        return [
            'success' => true,
            'action' => 'structure_scan',
            'status' => 'warning',
            'message' => 'Analyse structure partielle : ' . $e->getMessage(),
            'detected_families' => $detectedFamilies,
            'missing_families' => $missingFamilies,
        ];
    }

    $status = count($missingFamilies) >= 3 ? 'warning' : 'pass';
    return [
        'success' => true,
        'action' => 'structure_scan',
        'status' => $status,
        'message' => count($emptyDirs) . ' dossier(s) vide(s), ' . count($heavyDirs) . ' dossier(s) très chargés détectés.',
        'detected_families' => $detectedFamilies,
        'missing_families' => $missingFamilies,
        'empty_dirs_count' => count($emptyDirs),
        'empty_dirs' => array_slice($emptyDirs, 0, 120),
        'heavy_dirs' => array_slice($heavyDirs, 0, 40),
        'dirs_sample' => $dirStats,
    ];
}

function ha_php_declared_functions_from_content(string $content, string $rel): array
{
    $functions = [];
    if (!function_exists('token_get_all')) {
        return $functions;
    }

    $tokens = token_get_all($content);
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        // Ignore anonymous functions: next meaningful token should be T_STRING.
        $line = $token[2] ?? null;
        $j = $i + 1;
        while ($j < $count) {
            $next = $tokens[$j];
            if (is_array($next) && in_array($next[0], [T_WHITESPACE, T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG, T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG], true)) {
                $j++;
                continue;
            }
            break;
        }

        if ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
            $name = (string)$tokens[$j][1];
            $functions[] = ['name' => $name, 'file' => $rel, 'line' => $line];
        }
    }

    return $functions;
}

function ha_action_functions_scan(): array
{
    $files = ha_php_files();
    $declared = [];
    $declaredByName = [];
    $duplicates = [];
    $allContent = '';
    $functionIssues = [];

    foreach ($files as $file) {
        if (!is_readable($file)) {
            continue;
        }
        $size = @filesize($file);
        if ($size !== false && $size > HEALTH_MAX_FILE_SIZE) {
            continue;
        }
        $content = (string)@file_get_contents($file);
        if ($content === '') {
            continue;
        }
        $rel = ha_rel_path($file);
        $allContent .= "\n/* FILE: " . $rel . " */\n" . $content;

        foreach (ha_php_declared_functions_from_content($content, $rel) as $fn) {
            $lower = strtolower($fn['name']);
            if (isset($declaredByName[$lower])) {
                $duplicates[] = ['name' => $fn['name'], 'first' => $declaredByName[$lower], 'duplicate' => $fn];
            }
            $declaredByName[$lower] = $fn;
            $declared[] = $fn;

            if (preg_match('/^(test|debug|dump|dd|var_dump|todo|temp|tmp)/i', $fn['name'])) {
                $functionIssues[] = ha_issue('info', 'debug_like_function', 'Fonction de debug ou temporaire probable', 'Nom de fonction à vérifier : ' . $fn['name'], $fn['file'], $fn['line'], 'Supprimer ou renommer si cette fonction n’est pas utilisée en production.');
            }
        }
    }

    foreach ($duplicates as $dup) {
        $functionIssues[] = ha_issue('critical', 'duplicate_function', 'Fonction déclarée plusieurs fois', 'La fonction ' . $dup['name'] . ' semble déclarée plusieurs fois.', $dup['duplicate']['file'], $dup['duplicate']['line'], 'Renommer ou supprimer le doublon pour éviter un fatal error.');
    }

    $possiblyUnused = [];
    foreach ($declared as $fn) {
        $name = preg_quote($fn['name'], '/');
        $matches = preg_match_all('/\b' . $name . '\s*\(/i', $allContent, $m);
        // 1 occurrence = déclaration seule dans beaucoup de cas.
        if ($matches !== false && $matches <= 1 && !preg_match('/^ha_|^hm_|^get_csrf_token$|^verify_csrf$|^e$/i', $fn['name'])) {
            $possiblyUnused[] = $fn;
        }
    }

    foreach (array_slice($possiblyUnused, 0, 80) as $fn) {
        $functionIssues[] = ha_issue('info', 'possibly_unused_function', 'Fonction possiblement inutilisée', 'Aucun appel évident trouvé pour ' . $fn['name'] . '().', $fn['file'], $fn['line'], 'À vérifier manuellement : cette détection peut avoir des faux positifs si la fonction est appelée dynamiquement.');
    }

    $status = count($duplicates) > 0 ? 'fail' : (count($possiblyUnused) > 0 ? 'warning' : 'pass');

    return [
        'success' => true,
        'action' => 'functions_scan',
        'status' => $status,
        'message' => count($declared) . ' fonction(s) déclarée(s), ' . count($duplicates) . ' doublon(s), ' . count($possiblyUnused) . ' fonction(s) possiblement inutilisée(s).',
        'functions_count' => count($declared),
        'duplicates_count' => count($duplicates),
        'possibly_unused_count' => count($possiblyUnused),
        'functions_sample' => array_slice($declared, 0, 120),
        'duplicates' => array_slice($duplicates, 0, 40),
        'possibly_unused' => array_slice($possiblyUnused, 0, 80),
        'issues' => array_slice($functionIssues, 0, 140),
    ];
}

function ha_action_cleanup_scan(): array
{
    $files = ha_all_files(3000);
    $issues = [];
    $suspiciousFiles = [];
    $largeFiles = [];
    $oldBackups = [];
    $exposedSensitive = [];
    $byExtension = [];
    $totalSize = 0;

    foreach ($files as $file) {
        $rel = ha_rel_path($file);
        $base = basename($file);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $size = (int)(@filesize($file) ?: 0);
        $totalSize += $size;
        $byExtension[$ext ?: '[sans extension]'] = ($byExtension[$ext ?: '[sans extension]'] ?? 0) + 1;

        if ($size >= 10 * 1024 * 1024) {
            $largeFiles[] = ['file' => $rel, 'size_mb' => round($size / 1024 / 1024, 2)];
        }

        if (preg_match('/\.(bak|backup|old|orig|save|swp|tmp|sql|zip|tar|gz|rar|7z)$/i', $base) || preg_match('/(~|\.copy\.| copie | copy )/i', $base)) {
            $oldBackups[] = ['file' => $rel, 'size_mb' => round($size / 1024 / 1024, 2)];
            $issues[] = ha_issue('warning', 'backup_or_temp_file', 'Fichier temporaire/sauvegarde détecté', $base, $rel, null, 'Supprimer du serveur web ou déplacer hors public.');
        }

        if (preg_match('/^(\.env|composer\.json|composer\.lock|package\.json|package-lock\.json|yarn\.lock|phpunit\.xml|\.gitignore)$/i', $base)) {
            $exposedSensitive[] = ['file' => $rel, 'size_mb' => round($size / 1024 / 1024, 2)];
            if (preg_match('/^(\.env|phpunit\.xml)$/i', $base)) {
                $issues[] = ha_issue('critical', 'sensitive_file_in_webroot', 'Fichier sensible dans le webroot', $base, $rel, null, 'Bloquer l’accès HTTP via .htaccess/nginx ou déplacer hors webroot.');
            }
        }

        if (preg_match('/(debug|test|essai|temp|tmp|old|ancien|copie|backup)/i', $rel) && !preg_match('#(^|/)(vendor|node_modules)(/|$)#i', str_replace('\\', '/', $rel))) {
            $suspiciousFiles[] = ['file' => $rel, 'size_mb' => round($size / 1024 / 1024, 2)];
        }
    }

    arsort($byExtension);
    usort($largeFiles, fn($a, $b) => $b['size_mb'] <=> $a['size_mb']);

    $status = count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'critical')) > 0 ? 'fail' : (count($issues) > 0 ? 'warning' : 'pass');

    return [
        'success' => true,
        'action' => 'cleanup_scan',
        'status' => $status,
        'message' => count($oldBackups) . ' fichier(s) de sauvegarde/temporaire, ' . count($largeFiles) . ' gros fichier(s), ' . count($exposedSensitive) . ' fichier(s) sensibles potentiels.',
        'files_count' => count($files),
        'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        'extensions_top' => array_slice($byExtension, 0, 20, true),
        'large_files' => array_slice($largeFiles, 0, 50),
        'backup_or_temp_files' => array_slice($oldBackups, 0, 80),
        'suspicious_files' => array_slice($suspiciousFiles, 0, 80),
        'sensitive_files' => array_slice($exposedSensitive, 0, 40),
        'issues' => array_slice($issues, 0, 120),
    ];
}

function ha_action_consistency_scan(): array
{
    $db = ha_action_db_discovery();
    $files = ha_php_files();
    $issues = [];
    $tables = [];
    $columnsByTable = [];
    $content = '';

    foreach ((array)($db['schema_sample'] ?? []) as $row) {
        $table = (string)($row['table'] ?? '');
        if ($table === '') {
            continue;
        }
        $tables[] = $table;
        $columnsByTable[$table] = (array)($row['columns_sample'] ?? []);
    }

    foreach ($files as $file) {
        if (!is_readable($file)) {
            continue;
        }
        $size = @filesize($file);
        if ($size !== false && $size > HEALTH_MAX_FILE_SIZE) {
            continue;
        }
        $content .= "\n" . (string)@file_get_contents($file);
    }

    $tablesNotReferenced = [];
    foreach ($tables as $table) {
        if (!preg_match('/\b' . preg_quote($table, '/') . '\b/i', $content)) {
            $tablesNotReferenced[] = $table;
        }
    }

    foreach (array_slice($tablesNotReferenced, 0, 50) as $table) {
        $issues[] = ha_issue('info', 'table_not_referenced_in_code', 'Table non retrouvée dans le code', 'La table ' . $table . ' existe en BDD mais aucun usage évident n’a été trouvé dans les fichiers PHP.', null, null, 'À vérifier : table réellement utile, générée dynamiquement ou ancienne table ?');
    }

    return [
        'success' => true,
        'action' => 'consistency_scan',
        'status' => ($db['status'] ?? '') === 'fail' ? 'warning' : (count($issues) > 0 ? 'warning' : 'pass'),
        'message' => count($tablesNotReferenced) . ' table(s) existante(s) non retrouvée(s) clairement dans le code.',
        'tables_count' => count($tables),
        'tables_not_referenced_count' => count($tablesNotReferenced),
        'tables_not_referenced' => array_slice($tablesNotReferenced, 0, 80),
        'issues' => array_slice($issues, 0, 80),
    ];
}


// -----------------------------------------------------------------------------
// EXPLORATION ARBORESCENCE / LECTURE FICHIERS
// -----------------------------------------------------------------------------
function ha_is_excluded_path(string $path): bool
{
    $normalized = str_replace('\\', '/', $path);
    $excluded = ['/.git/', '/vendor/', '/node_modules/', '/storage/', '/cache/', '/tmp/', '/logs/', '/var/cache/', '/backup/', '/backups/'];
    foreach ($excluded as $part) {
        if (stripos($normalized, $part) !== false) {
            return true;
        }
    }
    return false;
}

function ha_resolve_safe_relative_path(string $relative): ?string
{
    $relative = trim(str_replace('\\', '/', $relative));
    $relative = ltrim($relative, '/');
    if ($relative === '') {
        return ha_root();
    }
    if (str_contains($relative, "\0") || preg_match('#(^|/)\.\.(/|$)#', $relative)) {
        return null;
    }
    $full = ha_root() . DIRECTORY_SEPARATOR . $relative;
    $real = realpath($full);
    if ($real === false) {
        return null;
    }
    $root = rtrim(ha_root(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if ($real !== rtrim($root, DIRECTORY_SEPARATOR) && !str_starts_with($real, $root)) {
        return null;
    }
    return $real;
}

function ha_is_probably_text_file(string $file): bool
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $allowed = ['php','phtml','html','htm','css','js','json','xml','txt','md','sql','env','ini','conf','htaccess','yml','yaml','csv','log'];
    if (in_array($ext, $allowed, true)) {
        return true;
    }
    $sample = @file_get_contents($file, false, null, 0, 512);
    if ($sample === false) {
        return false;
    }
    return !preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $sample);
}

function ha_mask_sensitive_content(string $content): string
{
    $patterns = [
        '/(DB_PASS\s*=\s*)([^\r\n]+)/i' => '$1********',
        '/(PASSWORD\s*=\s*)([^\r\n]+)/i' => '$1********',
        '/(SECRET\s*=\s*)([^\r\n]+)/i' => '$1********',
        '/(TOKEN\s*=\s*)([^\r\n]+)/i' => '$1********',
        '/(API_KEY\s*=\s*)([^\r\n]+)/i' => '$1********',
        '/(\$dbPass\s*=\s*[\'\"])(.*?)([\'\"]\s*;)/i' => '$1********$3',
        '/(\$password\s*=\s*[\'\"])(.*?)([\'\"]\s*;)/i' => '$1********$3',
        '/(\$pass\s*=\s*[\'\"])(.*?)([\'\"]\s*;)/i' => '$1********$3',
    ];
    return preg_replace(array_keys($patterns), array_values($patterns), $content) ?? $content;
}

function ha_tree_node(string $path, int $depth, int $maxDepth, int &$count, int $maxNodes): array
{
    $isDir = is_dir($path);
    $node = [
        'name' => basename($path) ?: '.',
        'path' => ha_rel_path($path),
        'type' => $isDir ? 'dir' : 'file',
    ];

    if (!$isDir) {
        $node['size'] = @filesize($path) ?: 0;
        $node['extension'] = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $node['viewable'] = ha_is_probably_text_file($path) && (($node['size'] ?? 0) <= 1048576);
        return $node;
    }

    if ($depth >= $maxDepth || $count >= $maxNodes) {
        $node['truncated'] = true;
        return $node;
    }

    $children = [];
    $items = @scandir($path) ?: [];
    $items = array_values(array_filter($items, fn($v) => $v !== '.' && $v !== '..'));
    usort($items, function ($a, $b) use ($path) {
        $pa = $path . DIRECTORY_SEPARATOR . $a;
        $pb = $path . DIRECTORY_SEPARATOR . $b;
        if (is_dir($pa) && !is_dir($pb)) return -1;
        if (!is_dir($pa) && is_dir($pb)) return 1;
        return strcasecmp($a, $b);
    });

    foreach ($items as $item) {
        if ($count >= $maxNodes) {
            $node['truncated'] = true;
            break;
        }
        $child = $path . DIRECTORY_SEPARATOR . $item;
        if (ha_is_excluded_path($child)) {
            continue;
        }
        $count++;
        $children[] = ha_tree_node($child, $depth + 1, $maxDepth, $count, $maxNodes);
    }

    $node['children'] = $children;
    $node['children_count'] = count($children);
    return $node;
}

function ha_action_file_tree(): array
{
    $maxDepth = max(1, min(8, (int)($_GET['depth'] ?? 5)));
    $maxNodes = max(50, min(2500, (int)($_GET['max'] ?? 900)));
    $path = (string)($_GET['path'] ?? '');
    $root = ha_resolve_safe_relative_path($path);
    if ($root === null || !is_dir($root)) {
        return ['success' => false, 'action' => 'file_tree', 'status' => 'fail', 'message' => 'Dossier introuvable ou non autorisé.'];
    }
    $count = 1;
    return [
        'success' => true,
        'action' => 'file_tree',
        'status' => 'pass',
        'root' => ha_rel_path($root),
        'max_depth' => $maxDepth,
        'max_nodes' => $maxNodes,
        'tree' => ha_tree_node($root, 0, $maxDepth, $count, $maxNodes),
        'nodes_count' => $count,
    ];
}

function ha_action_file_view(): array
{
    $relative = (string)($_GET['path'] ?? '');
    $file = ha_resolve_safe_relative_path($relative);
    if ($file === null || !is_file($file) || !is_readable($file)) {
        return ['success' => false, 'action' => 'file_view', 'status' => 'fail', 'message' => 'Fichier introuvable ou non autorisé.'];
    }
    if (ha_is_excluded_path($file)) {
        return ['success' => false, 'action' => 'file_view', 'status' => 'fail', 'message' => 'Fichier dans un dossier exclu.'];
    }
    $size = @filesize($file) ?: 0;
    if ($size > 1048576) {
        return ['success' => false, 'action' => 'file_view', 'status' => 'warning', 'message' => 'Fichier trop volumineux pour aperçu.', 'size' => $size];
    }
    if (!ha_is_probably_text_file($file)) {
        return ['success' => false, 'action' => 'file_view', 'status' => 'warning', 'message' => 'Fichier binaire ou non textuel.', 'size' => $size];
    }
    $content = (string)@file_get_contents($file);
    return [
        'success' => true,
        'action' => 'file_view',
        'status' => 'pass',
        'file' => ha_rel_path($file),
        'size' => $size,
        'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
        'content' => ha_mask_sensitive_content($content),
    ];
}

// -----------------------------------------------------------------------------
// ACTIONS
// -----------------------------------------------------------------------------
function ha_action_ping(): array
{
    return [
        'success' => true,
        'action' => 'ping',
        'message' => 'Agent enfant joignable.',
        'generated_at' => date('Y-m-d H:i:s'),
        'host' => $_SERVER['HTTP_HOST'] ?? null,
        'root' => ha_root(),
        'client_ip' => ha_client_ip(),
    ];
}

function ha_action_env(): array
{
    $bootstrap = ha_load_project_bootstrap();

    return [
        'success' => true,
        'action' => 'env',
        'status' => 'pass',
        'generated_at' => date('Y-m-d H:i:s'),
        'host' => $_SERVER['HTTP_HOST'] ?? null,
        'root' => ha_root(),
        'php_version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? null,
        'open_basedir' => ini_get('open_basedir'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'extensions' => [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'curl' => extension_loaded('curl'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
        ],
        'bootstrap' => $bootstrap,
    ];
}

function ha_score(array $health): array
{
    $score = 100.0;
    $reasons = [];

    $dbStatus = (string)($health['db_discovery']['status'] ?? 'warning');
    if ($dbStatus === 'fail') {
        $score -= 18;
        $reasons[] = 'BDD en erreur';
    } elseif ($dbStatus === 'warning') {
        $score -= 7;
        $reasons[] = 'BDD partielle ou non détectée';
    }

    $syntaxErrors = (int)($health['php_syntax']['errors_count'] ?? 0);
    if ($syntaxErrors > 0) {
        $score -= min(40, $syntaxErrors * 12);
        $reasons[] = $syntaxErrors . ' erreur(s) de syntaxe PHP';
    }

    $issues = array_merge(
        (array)($health['files_scan']['issues'] ?? []),
        (array)($health['functions_scan']['issues'] ?? []),
        (array)($health['cleanup_scan']['issues'] ?? []),
        (array)($health['consistency_scan']['issues'] ?? [])
    );
    $critical = 0;
    $warning = 0;
    $info = 0;
    foreach ($issues as $issue) {
        $sev = (string)($issue['severity'] ?? 'info');
        if ($sev === 'critical') {
            $critical++;
        } elseif ($sev === 'warning') {
            $warning++;
        } else {
            $info++;
        }
    }

    $score -= min(35, ($critical * 8) + ($warning * 1.2) + ($info * 0.15));
    if ($critical || $warning || $info) {
        $reasons[] = $critical . ' critique(s), ' . $warning . ' alerte(s), ' . $info . ' info(s)';
    }

    $score = max(0, min(100, (int)round($score)));
    $grade = $score >= 90 ? 'A' : ($score >= 75 ? 'B' : ($score >= 60 ? 'C' : ($score >= 40 ? 'D' : 'E')));

    return [
        'score' => $score,
        'grade' => $grade,
        'reasons' => $reasons,
        'counts' => [
            'critical_issues' => $critical,
            'warning_issues' => $warning,
            'info_issues' => $info,
            'syntax_errors' => $syntaxErrors,
        ],
    ];
}

function ha_action_health(): array
{
    $started = microtime(true);

    $env = ha_action_env();
    $db = ha_action_db_discovery();
    $files = ha_action_files_scan();
    $syntax = ha_action_php_syntax();
    $structure = ha_action_structure_scan();
    $functions = ha_action_functions_scan();
    $cleanup = ha_action_cleanup_scan();
    $consistency = ha_action_consistency_scan();
    $fileTree = ha_action_file_tree();

    $health = [
        'env' => $env,
        'db_discovery' => $db,
        'files_scan' => $files,
        'php_syntax' => $syntax,
        'structure_scan' => $structure,
        'functions_scan' => $functions,
        'cleanup_scan' => $cleanup,
        'consistency_scan' => $consistency,
        'file_tree' => $fileTree,
    ];

    $score = ha_score($health);

    return [
        'success' => true,
        'action' => 'health',
        'generated_at' => date('Y-m-d H:i:s'),
        'duration_ms' => (int)round((microtime(true) - $started) * 1000),
        'score' => $score['score'],
        'grade' => $score['grade'],
        'score_details' => $score,
        'summary' => [
            'host' => $_SERVER['HTTP_HOST'] ?? null,
            'php_version' => PHP_VERSION,
            'db_status' => $db['status'] ?? 'unknown',
            'db_source' => $db['pdo_detection']['source'] ?? null,
            'db_database' => $db['database'] ?? null,
            'db_tables_count' => $db['summary']['tables_count'] ?? null,
            'files_count' => $files['files_count'] ?? 0,
            'issues_count' => $files['issues_count'] ?? 0,
            'syntax_errors' => $syntax['errors_count'] ?? 0,
            'functions_count' => $functions['functions_count'] ?? 0,
            'possibly_unused_functions' => $functions['possibly_unused_count'] ?? 0,
            'empty_dirs_count' => $structure['empty_dirs_count'] ?? 0,
            'cleanup_issues_count' => count((array)($cleanup['issues'] ?? [])),
            'total_size_mb' => $cleanup['total_size_mb'] ?? null,
            'tables_not_referenced_count' => $consistency['tables_not_referenced_count'] ?? null,
        ],
        'health' => $health,
    ];
}

// -----------------------------------------------------------------------------
// ROUTER
// -----------------------------------------------------------------------------
ha_check_access();

$action = (string)($_GET['action'] ?? 'health');
$allowed = ['ping', 'env', 'files_scan', 'php_syntax', 'db_discovery', 'structure_scan', 'functions_scan', 'cleanup_scan', 'consistency_scan', 'file_tree', 'file_view', 'health'];

if (!in_array($action, $allowed, true)) {
    ha_out([
        'success' => false,
        'message' => 'Action non autorisée.',
        'allowed_actions' => $allowed,
    ], 400);
}

try {
    $fn = 'ha_action_' . $action;
    ha_out($fn());
} catch (Throwable $e) {
    ha_out([
        'success' => false,
        'action' => $action,
        'message' => $e->getMessage(),
    ], 500);
}
