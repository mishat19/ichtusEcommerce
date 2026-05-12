    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">
                <i class="fas fa-warehouse me-2" style="color: var(--bo-primary);"></i> Entrepôts & Stockage
            </h2>
            <p class="text-muted small mb-0">Vue d'ensemble de vos entrepôts, meubles et stacks avec taux d'occupation.</p>
        </div>
        <a href="/backoffice/stock-ajout" class="btn" style="background: var(--bo-primary); color: #fff; border-radius: 10px; padding: 0.6rem 1.2rem; font-weight: 600;">
            <i class="fas fa-boxes-stacked me-2"></i> Ajouter du stock
        </a>
    </div>

    <?php if (empty($entrepotsList)): ?>
        <div class="bo-card text-center p-5">
            <i class="fas fa-warehouse fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted">Aucun entrepôt configuré. Créez un entrepôt dans votre base de données pour commencer.</p>
        </div>
    <?php else: ?>

        <?php foreach ($entrepotsList as $entrepot): ?>
            <!-- ═══════ ENTREPÔT ═══════ -->
            <div class="bo-card mb-4" style="border-left: 4px solid var(--bo-primary);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--bo-text-main);">
                            <i class="fas fa-warehouse me-2" style="color: var(--bo-primary);"></i>
                            <?php e($entrepot['nom']); ?>
                        </h4>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php e($entrepot['adresse']); ?>, <?php e($entrepot['code_postal']); ?> <?php e($entrepot['ville']); ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <?php
                        $tauxE = $entrepot['taux_occupation'];
                        $colorE = $tauxE >= 80 ? '#ef4444' : ($tauxE >= 50 ? '#f59e0b' : '#10b981');
                        $bgE = $tauxE >= 80 ? '#fee2e2' : ($tauxE >= 50 ? '#fef3c7' : '#dcfce7');
                        ?>
                        <span class="badge" style="background: <?php echo $bgE; ?>; color: <?php echo $colorE; ?>; font-size: 0.9rem; padding: 0.5em 1em; border-radius: 10px; font-weight: 700;">
                            <?php e($entrepot['total_utilise']); ?> / <?php e($entrepot['total_capacite']); ?>
                            <small>(<?php e($tauxE); ?>%)</small>
                        </span>
                    </div>
                </div>

                <!-- Barre globale entrepôt -->
                <div class="progress mb-4" style="height: 8px; border-radius: 6px; background: #f1f5f9;">
                    <div class="progress-bar" role="progressbar"
                         style="width: <?php e($tauxE); ?>%; background: <?php echo $colorE; ?>; border-radius: 6px;"
                         aria-valuenow="<?php e($tauxE); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <!-- MEUBLES -->
                <?php if (empty($entrepot['meubles'])): ?>
                    <p class="text-muted text-center small">Aucun meuble dans cet entrepôt.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($entrepot['meubles'] as $meuble): ?>
                            <div class="col-md-6 col-lg-4">
                                <div style="background: #f8fafc; border: 1px solid var(--bo-border); border-radius: 12px; padding: 1rem; height: 100%;">
                                    <!-- Meuble header -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0" style="font-size: 0.95rem;">
                                                <?php
                                                $iconeMeuble = [
                                                    'rayonnage' => 'fa-grip-lines',
                                                    'etagere'   => 'fa-layer-group',
                                                    'armoire'   => 'fa-door-closed',
                                                    'palette'   => 'fa-pallet',
                                                    'autre'     => 'fa-cube'
                                                ];
                                                $icone = $iconeMeuble[$meuble['type']] ?? 'fa-cube';
                                                ?>
                                                <i class="fas <?php echo $icone; ?> me-1" style="color: var(--bo-secondary);"></i>
                                                <?php e($meuble['nom']); ?>
                                            </h6>
                                            <?php if ($meuble['emplacement']): ?>
                                                <small class="text-muted"><?php e($meuble['emplacement']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                        $tauxM = $meuble['taux_occupation'];
                                        $colorM = $tauxM >= 80 ? '#ef4444' : ($tauxM >= 50 ? '#f59e0b' : '#10b981');
                                        ?>
                                        <span style="font-size: 0.8rem; font-weight: 700; color: <?php echo $colorM; ?>;">
                                            <?php e($meuble['total_utilise']); ?>/<?php e($meuble['total_capacite']); ?>
                                        </span>
                                    </div>

                                    <!-- Barre meuble -->
                                    <div class="progress mb-3" style="height: 5px; border-radius: 4px; background: #e2e8f0;">
                                        <div class="progress-bar" style="width: <?php e($tauxM); ?>%; background: <?php echo $colorM; ?>; border-radius: 4px;"></div>
                                    </div>

                                    <!-- STACKS -->
                                    <?php if (empty($meuble['stacks'])): ?>
                                        <p class="text-muted small text-center mb-0">Aucun stack</p>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($meuble['stacks'] as $stack): ?>
                                                <?php
                                                $tauxS = $stack['taux_occupation'];
                                                $colorS = $tauxS >= 80 ? '#ef4444' : ($tauxS >= 50 ? '#f59e0b' : '#10b981');
                                                $bgS = $tauxS >= 80 ? '#fee2e2' : ($tauxS >= 50 ? '#fef3c7' : '#dcfce7');
                                                $borderS = $tauxS >= 80 ? '#fca5a5' : ($tauxS >= 50 ? '#fcd34d' : '#86efac');

                                                // Tooltip avec les produits
                                                $tooltipProduits = '';
                                                if (!empty($stack['produits'])) {
                                                    foreach ($stack['produits'] as $sp) {
                                                        $tooltipProduits .= $sp['produit_nom'] . ' (x' . $sp['quantite'] . ')&#10;';
                                                    }
                                                } else {
                                                    $tooltipProduits = 'Vide';
                                                }
                                                ?>
                                                <div class="stack-box" title="<?php echo $tooltipProduits; ?>"
                                                     style="background: <?php echo $bgS; ?>; border: 2px solid <?php echo $borderS; ?>; border-radius: 10px;
                                                            padding: 0.5rem 0.75rem; text-align: center; min-width: 70px; cursor: default;
                                                            transition: transform 0.15s, box-shadow 0.15s;"
                                                     onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                                                     onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                                    <div style="font-size: 0.7rem; color: var(--bo-text-muted); font-weight: 500;">
                                                        <?php e($stack['nom']); ?>
                                                    </div>
                                                    <div style="font-size: 1rem; font-weight: 800; color: <?php echo $colorS; ?>;">
                                                        <?php e($stack['capacite_utilisee']); ?>/<?php e($stack['capacite_max']); ?>
                                                    </div>
                                                    <!-- Mini progress bar -->
                                                    <div style="height: 3px; background: rgba(0,0,0,0.1); border-radius: 2px; margin-top: 4px;">
                                                        <div style="height: 100%; width: <?php e($tauxS); ?>%; background: <?php echo $colorS; ?>; border-radius: 2px;"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <!-- Légende -->
    <div class="bo-card" style="background: #f8fafc;">
        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2" style="color: var(--bo-info);"></i> Légende des couleurs</h6>
        <div class="d-flex gap-4 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <div style="width: 20px; height: 20px; background: #dcfce7; border: 2px solid #86efac; border-radius: 6px;"></div>
                <span class="small" style="color: #166534; font-weight: 600;">0–50% — Disponible</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div style="width: 20px; height: 20px; background: #fef3c7; border: 2px solid #fcd34d; border-radius: 6px;"></div>
                <span class="small" style="color: #92400e; font-weight: 600;">50–80% — Attention</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div style="width: 20px; height: 20px; background: #fee2e2; border: 2px solid #fca5a5; border-radius: 6px;"></div>
                <span class="small" style="color: #991b1b; font-weight: 600;">80–100% — Plein</span>
            </div>
        </div>
    </div>
