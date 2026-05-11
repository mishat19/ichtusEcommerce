<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Diagnostic & Statistiques</h2>
        <p class="text-muted">Santé du système et métriques avancées</p>
    </div>
    <div class="text-end">
        <p class="small text-muted mb-1">Dernier passage auto (CRON) : <span class="fw-bold"><?php e($GLOBALS['bo_last_cron_date']); ?></span></p>
        <button onclick="window.location.reload()" class="bo-btn-outline">
            <i class="fas fa-sync-alt"></i> Actualiser les tests
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Colonne Gauche : Tests & Alertes -->
    <div class="col-lg-7">
        
        <!-- Alertes d'Intégrité -->
        <?php if (!empty($GLOBALS['bo_alerts'])): ?>
            <div class="bo-card border-warning">
                <h5 class="bo-card-title text-warning">
                    <i class="fas fa-exclamation-triangle"></i> Alertes d'Intégrité
                </h5>
                <div class="list-group list-group-flush">
                    <?php foreach ($GLOBALS['bo_alerts'] as $alert): ?>
                        <div class="list-group-item d-flex align-items-center gap-3 border-0 px-0">
                            <span class="badge bg-<?php e($alert['level']); ?> rounded-pill">&nbsp;</span>
                            <span class="small"><?php e($alert['message']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Liste des Tests Unitaires -->
        <div class="bo-card">
            <h5 class="bo-card-title">
                <i class="fas fa-check-circle"></i> État de Santé du Système
            </h5>
            <div class="bo-table-container">
                <table class="bo-table">
                    <thead>
                        <tr>
                            <th>Composant</th>
                            <th>Statut</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_cat = '';
                        foreach ($GLOBALS['bo_tests'] as $test): 
                            if ($current_cat !== $test['category']):
                                $current_cat = $test['category'];
                        ?>
                            <tr class="bg-light">
                                <td colspan="3" class="fw-bold text-uppercase small py-2" style="background: #f1f5f9; color: #475569; letter-spacing: 0.05em;">
                                    <?php e($current_cat); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                            <tr>
                                <td class="fw-medium ps-3"><?php e($test['name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php e($test['status']); ?>-subtle text-<?php e($test['status']); ?> bo-badge">
                                        <?php e(strtoupper($test['status'])); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php e($test['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Informations Système -->
        <div class="bo-card">
            <h5 class="bo-card-title">
                <i class="fas fa-info-circle"></i> Informations Environnement
            </h5>
            <div class="row g-3">
                <div class="col-6">
                    <label class="text-muted small">Version PHP</label>
                    <p class="fw-bold mb-0"><?php e(PHP_VERSION); ?></p>
                </div>
                <div class="col-6">
                    <label class="text-muted small">Serveur</label>
                    <p class="fw-bold mb-0"><?php e($_SERVER['SERVER_SOFTWARE']); ?></p>
                </div>
                <div class="col-6">
                    <label class="text-muted small">Timezone</label>
                    <p class="fw-bold mb-0"><?php e(date_default_timezone_get()); ?></p>
                </div>
                <div class="col-6">
                    <label class="text-muted small">Limite Mémoire</label>
                    <p class="fw-bold mb-0"><?php e(ini_get('memory_limit')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Colonne Droite : Stats Détaillées -->
    <div class="col-lg-5">
        
        <!-- Métriques Produits -->
        <div class="bo-card">
            <h5 class="bo-card-title">
                <i class="fas fa-box-open"></i> Catalogue
            </h5>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Total produits</span>
                <span class="fw-bold"><?php e($GLOBALS['bo_stats_tests']['produits_total']); ?></span>
            </div>
            <div class="progress mb-3" style="height: 6px;">
                <?php 
                $percent_actif = $GLOBALS['bo_stats_tests']['produits_total'] > 0 ? ($GLOBALS['bo_stats_tests']['produits_actifs'] / $GLOBALS['bo_stats_tests']['produits_total']) * 100 : 0;
                ?>
                <div class="progress-bar bg-success" style="width: <?php e($percent_actif); ?>%"></div>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <div class="p-2 bg-light rounded text-center">
                        <small class="d-block text-muted">Produits Actifs</small>
                        <span class="fw-bold text-success"><?php e($GLOBALS['bo_stats_tests']['produits_actifs']); ?> / <?php e($GLOBALS['bo_stats_tests']['produits_total']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métriques Business -->
        <div class="bo-card">
            <h5 class="bo-card-title">
                <i class="fas fa-hand-holding-usd"></i> Business & Revenus
            </h5>
            <div class="mb-4 text-center">
                <div class="bo-stat-label">Chiffre d'Affaires Total</div>
                <div class="bo-stat-value text-primary"><?php e(number_format($GLOBALS['bo_stats_tests']['ca_total'], 2, ',', ' ')); ?> €</div>
            </div>
            <div class="row g-3">
                <div class="col-6 border-end">
                    <small class="d-block text-muted">Panier Moyen</small>
                    <span class="fw-bold fs-5"><?php e(number_format($GLOBALS['bo_stats_tests']['panier_moyen'], 2, ',', ' ')); ?> €</span>
                </div>
                <div class="col-6">
                    <small class="d-block text-muted">Taux Conversion</small>
                    <?php 
                    $conv = $GLOBALS['bo_stats_tests']['clients_total'] > 0 ? ($GLOBALS['bo_stats_tests']['clients_actifs'] / $GLOBALS['bo_stats_tests']['clients_total']) * 100 : 0;
                    ?>
                    <span class="fw-bold fs-5"><?php e(round($conv, 1)); ?> %</span>
                </div>
            </div>
        </div>

        <!-- Métriques Fichiers -->
        <div class="bo-card">
            <h5 class="bo-card-title">
                <i class="fas fa-file-image"></i> Médias (Images)
            </h5>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Nombre de fichiers</span>
                <span class="fw-bold"><?php e($GLOBALS['bo_stats_tests']['nb_images']); ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span>Espace utilisé</span>
                <span class="fw-bold"><?php e($GLOBALS['bo_stats_tests']['size_images']); ?> Mo</span>
            </div>
            <div class="mt-3 p-3 border rounded border-dashed text-center small text-muted">
                Les images sont stockées dans <br><code>/images/</code>
            </div>
        </div>

    </div>
</div>
