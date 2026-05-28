<!-- Topbar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">Bonjour 👋</h2>
        <p class="text-muted small mb-0">Voici ce qui se passe sur votre boutique aujourd'hui.</p>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="vr d-none d-md-block"></div>
        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>
            <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>
</div>

<div class="bo-content">

    <!-- STATS -->
    <div class="row g-4 mb-4">

        <?php
        $count_current = $bo_stats['evo_commandes']['count_current'];
        $count_prev = $bo_stats['evo_commandes']['count_prev'];
        $evo_percent = 0;
        if ($count_prev > 0) {
            $evo_percent = (($count_current - $count_prev) / $count_prev) * 100;
        } elseif ($count_current > 0) {
            $evo_percent = 100;
        }
        $evo_class = $evo_percent >= 0 ? 'text-success' : 'text-danger';
        $evo_icon = $evo_percent >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right';

        $statsCards = [
                ['label' => "Chiffre d'affaires", 'value' => number_format((float)($bo_stats['ca_total'] / 100), 2, ',', ' ') . ' €', 'icon' => 'bi-currency-euro', 'color' => 'primary'],
                ['label' => "Panier Moyen", 'value' => number_format((float)$bo_stats['panier_moyen'], 2, ',', ' ') . ' €', 'icon' => 'bi-basket', 'color' => 'info'],
                ['label' => "Commandes du mois", 'value' => $count_current, 'icon' => 'bi-cart-check', 'color' => 'success', 'evo' => $evo_percent],
                ['label' => "En attente", 'value' => $bo_stats['commandes_attente'], 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
        ];
        ?>

        <?php foreach ($statsCards as $s): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="bo-stat">
                    <div>
                        <div class="bo-stat-label"><?php echo $s['label']; ?></div>
                        <div class="bo-stat-value"><?php echo $s['value']; ?></div>
                        <?php if (isset($s['evo'])): ?>
                            <div class="extra-small mt-2 <?php echo $evo_class; ?> fw-bold">
                                <i class="bi <?php echo $evo_icon; ?>"></i>
                                <?php echo number_format(abs($s['evo']), 1); ?>% vs mois dernier
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="bo-stat-icon" style="background: rgba(var(--bs-<?php echo $s['color']; ?>-rgb), 0.1); color: var(--bs-<?php echo $s['color']; ?>);">
                        <i class="bi <?php echo $s['icon']; ?>"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- GRAPHS -->
    <div class="row mb-5 g-4">
        <!-- GRAPH CA -->
        <div class="col-lg-8">
            <div class="bo-card h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="bo-card-title mb-0">
                        <i class="bi bi-graph-up-arrow text-primary"></i>
                        Évolution du Chiffre d'Affaires
                    </div>
                    <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                        <button type="button" class="btn btn-white rounded-pill px-3 shadow-sm chart-toggle active" data-period="jour">Jour</button>
                        <button type="button" class="btn btn-transparent rounded-pill px-3 chart-toggle" data-period="mois">Mois</button>
                        <button type="button" class="btn btn-transparent rounded-pill px-3 chart-toggle" data-period="an">Année</button>
                    </div>
                </div>
                <div style="height: 300px;">
                    <canvas id="caChart"></canvas>
                </div>
            </div>
        </div>

        <!-- GRAPH STATUS -->
        <div class="col-lg-4">
            <div class="bo-card h-100 mb-0">
                <div class="bo-card-title mb-4">
                    <i class="bi bi-pie-chart-fill text-info"></i>
                    Statuts des Commandes
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- COMMANDES -->
        <div class="col-lg-8">
            <div class="bo-card h-100">

                <div class="bo-card-title">
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                    Dernières commandes
                    <a href="/backoffice/commandes" class="ms-auto btn btn-link btn-sm text-decoration-none fw-semibold">
                        Voir tout <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="bo-table-container">
                    <table class="bo-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach (($dernieres_commandes ?? []) as $c): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $c['id']; ?></td>

                                <td>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars(($c['facturation_prenom'] ?? '') . ' ' . ($c['facturation_nom'] ?? '')); ?>
                                    </div>
                                    <div class="text-muted extra-small" style="font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($c['facturation_email'] ?? ''); ?>
                                    </div>
                                </td>

                                <td class="fw-bold text-dark">
                                    <?php echo number_format((float)$c['total_ttc'], 2, ',', ' '); ?> €
                                </td>

                                <td>
                                    <span class="bo-badge bo-badge-<?php echo $c['statut']; ?>">
                                        <?php echo str_replace('_', ' ', $c['statut']); ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="/backoffice/commande/<?php echo $c['id']; ?>" class="btn btn-light btn-sm fw-medium">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <!-- PAIEMENTS -->
        <div class="col-lg-4">
            <div class="bo-card h-100">

                <div class="bo-card-title">
                    <i class="bi bi-shield-check text-success"></i>
                    Flux de paiement
                </div>

                <div class="list-group list-group-flush mt-2">
                    <?php foreach (($derniers_paiements ?? []) as $p): ?>                        <div class="list-group-item px-0 border-0 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-credit-card text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?php echo htmlspecialchars($p['facturation_prenom'] . ' ' . $p['facturation_nom']); ?></div>
                                        <div class="text-muted extra-small" style="font-size: 0.7rem;">Ref: <?php echo substr($p['ref_banque'] ?? 'N/A', 0, 10); ?>...</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small"><?php echo number_format((float)($p['montant'] / 100), 2, ',', ' '); ?> €</div>
                                    <div class="small">
                                        <span class="badge rounded-pill <?php echo $p['statut'] === 'accepte' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>" style="font-size: 0.6rem;">
                                            <?php echo strtoupper($p['statut']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <a href="/backoffice/paiements" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                    Historique complet
                </a>
            </div>
        </div>

        <!-- API SECTION -->
        <div class="col-12 mt-2">
            <div class="bo-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: none;">

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="text-white fw-bold mb-1"><i class="bi bi-terminal me-2 text-info"></i> API Les Délices Fruités</h4>
                    </div>
                    <span class="badge bg-info text-dark fw-bold">v1.0</span>
                </div>

                <div class="row g-3">

                    <?php
                    $base = 'https://b2-gp97.kevinpecro.info/api/';
                    $endpoints = [
                            ['url' => $base . 'liste/', 'desc' => 'Structure et schéma de la base de données.', 'icon' => 'bi-hdd-network', 'method' => 'GET'],
                            ['url' => $base . 'commandes/', 'desc' => 'Récupération du flux de commandes temps réel.', 'icon' => 'bi-cloud-arrow-down', 'method' => 'POST'],
                            ['url' => $base . 'paiements/', 'desc' => 'Vérification des transactions bancaires.', 'icon' => 'bi-shield-lock', 'method' => 'POST'],
                            ['url' => $base . 'dashboard/', 'desc' => 'Affichage des statistiques de ventes et des derniers paiements/commandes.', 'icon' => 'bi-cloud-arrow-down', 'method' => 'POST'],
                            ['url' => $base . 'stock/', 'desc' => 'Récupération des stockages et gestion des stocks.', 'icon' => 'bi-cloud-arrow-down', 'method' => 'POST'],
                            ['url' => $base . 'messages/', 'desc' => 'Récupération des messages et gestion des messages.', 'icon' => 'bi-cloud-arrow-down', 'method' => 'POST']
                    ];
                    ?>

                    <?php foreach ($endpoints as $ep): ?>
                        <div class="col-md-4">
                            <div class="bo-api-box h-100">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi <?php echo $ep['icon']; ?> text-info fs-5"></i>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.65rem;"><?php echo $ep['method']; ?></span>
                                </div>
                                <div class="bo-api-url mb-2" style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 0.75rem;">
                                    <?php echo $ep['url']; ?>
                                </div>
                                <div class="bo-api-desc">
                                    <?php echo $ep['desc']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- CA Chart (Line) ---
    const caCtx = document.getElementById('caChart').getContext('2d');
    const chartData = <?php echo json_encode($bo_stats['charts']); ?>;
    let currentCAChart;

    function updateCAChart(period) {
        const data = chartData[period];
        const labels = data.map(item => {
            if (period === 'jour') {
                return new Date(item.label).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
            }
            return item.label;
        });
        const values = data.map(item => item.total / 100);

        if (currentCAChart) currentCAChart.destroy();

        currentCAChart = new Chart(caCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chiffre d\'Affaires (€)',
                    data: values,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: (context) => context.parsed.y.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { callback: (value) => value.toLocaleString('fr-FR') + ' €' } 
                    }
                }
            }
        });
    }

    // --- Status Chart (Doughnut) ---
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Payées', 'En attente', 'Annulées'],
            datasets: [{
                data: [
                    <?php echo $bo_stats['commandes_payees']; ?>,
                    <?php echo $bo_stats['commandes_attente']; ?>,
                    <?php echo $bo_stats['commandes_annulees']; ?>
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, font: { size: 11 } }
                }
            },
            cutout: '70%'
        }
    });

    // Toggle logic for CA Chart
    document.querySelectorAll('.chart-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-toggle').forEach(b => {
                b.classList.remove('active', 'btn-white', 'shadow-sm');
                b.classList.add('btn-transparent');
            });
            this.classList.add('active', 'btn-white', 'shadow-sm');
            this.classList.remove('btn-transparent');
            updateCAChart(this.dataset.period);
        });
    });

    // Initial load
    updateCAChart('jour');
});
</script>