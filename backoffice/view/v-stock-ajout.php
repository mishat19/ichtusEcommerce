<?php
// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Génère un token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">
            <i class="fas fa-boxes-stacked me-2" style="color: var(--bo-primary);"></i> Gestion du Stock
        </h2>
        <p class="text-muted small mb-0">Ajoutez ou visualisez le contenu de vos stacks.</p>
    </div>
    <a href="/backoffice/entrepots" class="btn btn-outline-secondary" style="border-radius: 10px;">
        <i class="fas fa-arrow-left me-2"></i> Retour aux entrepôts
    </a>
</div>

<!-- Messages -->
<?php if (!empty($messageSucces)): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px;">
        <i class="fas fa-check-circle me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($messageSucces); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($messageErreur)): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px;">
        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($messageErreur); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Onglets -->
<ul class="nav nav-tabs mb-4" id="stockTabs" role="tablist" style="border-bottom: none;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ajout-tab" data-bs-toggle="tab" data-bs-target="#ajout" type="button" role="tab" style="border-radius: 10px 10px 0 0; border: 1px solid var(--bo-border);">
            <i class="fas fa-boxes-stacked me-2"></i> Gérer le stock
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="visualisation-tab" data-bs-toggle="tab" data-bs-target="#visualisation" type="button" role="tab" style="border-radius: 10px 10px 0 0; border: 1px solid var(--bo-border);">
            <i class="fas fa-eye me-2"></i> Visualiser un stack
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="scanner-tab" data-bs-toggle="tab" data-bs-target="#scanner" type="button" role="tab" style="border-radius: 10px 10px 0 0; border: 1px solid var(--bo-border);">
            <i class="fas fa-qrcode me-2"></i> Scanner
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-historique" data-bs-toggle="tab" data-bs-target="#historique" type="button" role="tab" style="border-radius: 10px 10px 0 0; border: 1px solid var(--bo-border);">
            <i class="fas fa-history me-2"></i> Historique
        </button>
    </li>
</ul>

<!-- Contenu des onglets -->
<div class="tab-content" id="stockTabsContent">
    <!-- Onglet Ajouter du stock -->
    <div class="tab-pane fade show active" id="ajout" role="tabpanel">
        <form method="POST" id="formAjoutBatch">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="ajout_batch" value="1">

            <div class="bo-card mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-sliders-h me-2" style="color: var(--bo-primary);"></i>
                    Action
                </h5>

                <select id="stock-action" name="stock_action"
                        class="form-select"
                        style="border-radius: 10px; padding: 0.7rem;">
                    <option value="add">➕ Ajouter du stock</option>
                    <option value="remove">➖ Retirer du stock</option>
                    <option value="move">🔁 Déplacer du stock</option>
                </select>
            </div>

            <div class="row g-4">
                <!-- Sélection du stack -->
                <div class="col-lg-4">
                    <div class="bo-card" style="border-left: 4px solid var(--bo-primary);">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-layer-group me-2" style="color: var(--bo-primary);"></i> 1. Sélectionner un Stack
                        </h5>
                        <select name="id_stack" id="select-stack-ajout" class="form-select mb-3" required style="border-radius: 10px; padding: 0.7rem;">
                            <option value="">— Choisir un stack —</option>
                            <?php
                            $currentEntrepot = '';
                            foreach ($stacksList as $s):
                                if ($s['entrepot_nom'] !== $currentEntrepot):
                                    if ($currentEntrepot !== '') echo '</optgroup>';
                                    $currentEntrepot = $s['entrepot_nom'];
                                    echo '<optgroup label="🏭 ' . htmlspecialchars($s['entrepot_nom']) . '">';
                                endif;
                                ?>
                                <option value="<?php echo $s['id']; ?>"
                                        data-capacite="<?php echo $s['capacite_max']; ?>"
                                        data-utilise="<?php echo $s['capacite_utilisee']; ?>">
                                    <?php echo htmlspecialchars($s['meuble_nom']); ?> → <?php echo htmlspecialchars($s['nom']); ?>
                                    (<?php echo $s['capacite_utilisee']; ?>/<?php echo $s['capacite_max']; ?>)
                                </option>
                            <?php endforeach; ?>
                            <?php if ($currentEntrepot !== '') echo '</optgroup>'; ?>
                        </select>
                        <div id="stack-info-ajout" style="display: none; background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid var(--bo-border);">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Capacité restante</span>
                                <span class="fw-bold" id="stack-restant-ajout" style="color: var(--bo-primary);">—</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 6px; background: #e2e8f0;">
                                <div id="stack-progress-ajout" class="progress-bar" style="border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sélection du stack de destination -->
                <div class="col-lg-4" id="destination-stack-container" style="display:none;">
                    <div class="bo-card" style="border-left: 4px solid #f59e0b;">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-arrow-right me-2" style="color: #f59e0b;"></i>
                            Stack de destination
                        </h5>

                        <select name="id_stack_destination"
                                id="select-stack-destination"
                                class="form-select"
                                style="border-radius: 10px; padding: 0.7rem;">

                            <option value="">— Choisir un stack —</option>

                            <?php
                            $currentEntrepot = '';

                            foreach ($stacksList as $s):

                                if ($s['entrepot_nom'] !== $currentEntrepot):

                                    if ($currentEntrepot !== '') echo '</optgroup>';

                                    $currentEntrepot = $s['entrepot_nom'];

                                    echo '<optgroup label="🏭 ' . htmlspecialchars($s['entrepot_nom']) . '">';
                                endif;
                                ?>

                                <option value="<?php echo $s['id']; ?>"
                                        data-capacite="<?php echo $s['capacite_max']; ?>"
                                        data-utilise="<?php echo $s['capacite_utilisee']; ?>">
                                    <?php echo htmlspecialchars($s['meuble_nom']); ?>
                                    →
                                    <?php echo htmlspecialchars($s['nom']); ?>
                                </option>

                            <?php endforeach; ?>

                            <?php if ($currentEntrepot !== '') echo '</optgroup>'; ?>
                        </select>
                        <div id="stack-info-destination"
                             style="display: none; background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid var(--bo-border); margin-top: 1rem;">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Capacité restante</span>
                                <span class="fw-bold" id="stack-restant-destination" style="color: #f59e0b;">—</span>
                            </div>

                            <div class="progress" style="height: 8px; border-radius: 6px; background: #e2e8f0;">
                                <div id="stack-progress-destination"
                                     class="progress-bar"
                                     style="border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ajout/Suppression/Changement de stack des produits -->
                <div class="col-lg-8">
                    <div class="bo-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" id="title-interaction-action">
                                <i class="fas fa-cart-plus me-2" style="color: var(--bo-success);"></i> 2. Ajouter des produits
                            </h5>
                            <button type="button" id="btn-add-line" class="btn btn-sm" style="background: var(--bo-success); color: #fff; border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-plus me-1"></i> Ajouter une ligne
                            </button>
                        </div>
                        <div id="product-lines"></div>
                        <div id="batch-summary" class="mt-3 p-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid var(--bo-border); display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Total produits à ajouter :</span>
                                <span class="fw-bold fs-5" id="total-quantity" style="color: var(--bo-primary);">0</span>
                            </div>
                            <div id="capacity-warning" class="mt-2" style="display: none;">
                                <div class="alert alert-danger mb-0 p-2 small" style="border-radius: 8px;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span id="capacity-warning-text">Capacité dépassée !</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" id="btn-submit" class="btn btn-lg" style="background: var(--bo-primary); color: #fff; border-radius: 12px; font-weight: 700; padding: 0.7rem 2rem;" disabled>
                                <i class="fas fa-check me-2"></i> Valider l'ajout en stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Onglet Visualiser un stack -->
    <div class="tab-pane fade" id="visualisation" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="bo-card" style="border-left: 4px solid var(--bo-primary);">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-layer-group me-2" style="color: var(--bo-primary);"></i> Sélectionner un Stack
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <select id="select-stack-visu" class="form-select" style="border-radius: 10px; padding: 0.7rem;">
                                <option value="">— Choisir un stack —</option>
                                <?php
                                $currentEntrepot = '';
                                foreach ($stacksList as $s):
                                    if ($s['entrepot_nom'] !== $currentEntrepot):
                                        if ($currentEntrepot !== '') echo '</optgroup>';
                                        $currentEntrepot = $s['entrepot_nom'];
                                        echo '<optgroup label="🏭 ' . htmlspecialchars($s['entrepot_nom']) . '">';
                                    endif;
                                    ?>
                                    <option value="<?php echo $s['id']; ?>"
                                            data-capacite="<?php echo $s['capacite_max']; ?>"
                                            data-utilise="<?php echo $s['capacite_utilisee']; ?>"
                                            data-meuble="<?php echo htmlspecialchars($s['meuble_nom']); ?>"
                                            data-entrepot="<?php echo htmlspecialchars($s['entrepot_nom']); ?>"
                                            data-stack-nom="<?php echo htmlspecialchars($s['nom']); ?>">
                                        <?php echo htmlspecialchars($s['entrepot_nom']); ?> → <?php echo htmlspecialchars($s['meuble_nom']); ?> → <?php echo htmlspecialchars($s['nom']); ?>
                                        (<?php echo $s['capacite_utilisee']; ?>/<?php echo $s['capacite_max']; ?>)
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($currentEntrepot !== '') echo '</optgroup>'; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button id="btn-load-stack" class="btn w-100" style="background: var(--bo-primary); color: #fff; border-radius: 10px; font-weight: 600;">
                                <i class="fas fa-eye me-2"></i> Visualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affichage du stack -->
            <div class="col-lg-12">
                <div id="stack-visualisation" style="display: none;">
                    <div class="bo-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1" id="stack-visu-title">—</h4>
                                <p class="text-muted small mb-0" id="stack-visu-location">—</p>
                            </div>
                            <a id="btn-add-to-stack" href="#" class="btn btn-sm" style="background: var(--bo-success); color: #fff; border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-plus me-1"></i> Ajouter du stock
                            </a>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small">Taux d'occupation</span>
                            <span class="fw-bold fs-5" id="stack-visu-taux" style="color: var(--bo-primary);">—%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div id="stack-visu-progress" class="progress-bar" style="border-radius: 6px;"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small text-muted">
                            <span>0</span>
                            <span id="stack-visu-capacite-max">—</span>
                        </div>
                    </div>

                    <!-- Tableau des produits (style v-produits.php) -->
                    <div class="bo-card p-0 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-list me-2" style="color: var(--bo-primary);"></i>
                                Produits dans ce stack (<span id="stack-visu-nb-produits">0</span>)
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="bo-table mb-0">
                                <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Aperçu</th>
                                    <th>Nom du produit</th>
                                    <th>Identifiant (SKU)</th>
                                    <th>Prix HT</th>
                                    <th>Quantité</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="stack-visu-produits">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fs-3 mb-2 d-block"></i>
                                        Sélectionnez un stack pour voir son contenu.
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="bo-card mt-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-chart-bar me-2" style="color: var(--bo-primary);"></i>
                            Statistiques
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3 p-3" style="background: #f8fafc; border-radius: 10px;">
                                    <div style="width: 48px; height: 48px; background: rgba(79, 70, 229, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-boxes" style="color: var(--bo-primary); font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Produits différents</div>
                                        <div class="fw-bold fs-4" id="stack-visu-stats-produits">0</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3 p-3" style="background: #f8fafc; border-radius: 10px;">
                                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-weight-hanging" style="color: var(--bo-success); font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Quantité totale</div>
                                        <div class="fw-bold fs-4" id="stack-visu-stats-quantite">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== ONGLET SCANNER ==================== -->
    <div class="tab-pane fade" id="scanner" role="tabpanel">

        <!-- Carte principale du scanner -->
        <div class="bo-card">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-qrcode me-2" style="color: var(--bo-primary);"></i>
                Scanner un produit
            </h5>

            <div class="row g-4">
                <div class="col-lg-7">

                    <!-- Résultat du scan (caché par défaut) -->
                    <div id="scan-result" class="p-3 border rounded mb-3" style="display:none; background: #f8fafc; border-color: var(--bo-border) !important;">
                        <h5 id="scan-product-name" class="mb-2">—</h5>
                        <p class="text-muted small mb-3" id="scan-product-id">—</p>

                        <!-- Quantité -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantité</label>
                            <input type="number" id="scan-qty" class="form-control" value="1" min="1">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-success" onclick="scanEntree()">
                                <i class="fas fa-plus me-1"></i> Entrée
                            </button>
                            <button class="btn btn-danger" onclick="scanSortie()">
                                <i class="fas fa-minus me-1"></i> Sortie
                            </button>
                            <button class="btn btn-warning" onclick="scanMove()">
                                <i class="fas fa-exchange-alt me-1"></i> Déplacer
                            </button>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-info mt-3 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Scanne un QR code produit pour afficher les actions.
                    </div>

                    <!-- Boutons caméra -->
                    <div class="text-center mb-3">
                        <button id="btn-start-scan" class="btn btn-primary">
                            <i class="fas fa-camera me-2"></i> Activer la caméra
                        </button>
                        <button id="btn-stop-scan" class="btn btn-danger ms-2" style="display:none;">
                            <i class="fas fa-stop me-2"></i> Stop
                        </button>
                    </div>

                    <!-- Zone de scan (CACHÉE PAR DÉFAUT + CARRÉE) -->
                    <div id="qr-reader" style="width: 100%; background: #000; border-radius: 12px; overflow: hidden; display: none;"></div>
                </div>
            </div>
        </div>

        <!-- ==================== MODALE BOOTSTRAP ==================== -->
        <div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span id="modal-action">Action</span> :
                            <span id="modal-product" class="fw-bold">Produit</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <form id="stockForm" method="POST">
                            <!-- Champs cachés -->
                            <input type="hidden" id="modal-action-type" name="action">
                            <input type="hidden" name="produit_id" value="">

                            <!-- Quantité -->
                            <div class="mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" id="modal-quantity" name="quantite" class="form-control" min="1" value="1" required>
                            </div>

                            <!-- Sélection du stack -->
                            <div class="mb-3">
                                <label class="form-label">Stack de destination</label>
                                <select name="id_stack" class="form-select" required>
                                    <option value="">— Choisir un stack —</option>
                                    <?php foreach ($stacksList as $stack): ?>
                                        <option value="<?php echo $stack['id']; ?>">
                                            <?php echo htmlspecialchars(
                                                    $stack['entrepot_nom'] . ' → ' .
                                                    $stack['meuble_nom'] . ' → ' .
                                                    $stack['nom']
                                            ); ?>
                                            (<?php echo $stack['capacite_utilisee']; ?>/<?php echo $stack['capacite_max']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Annuler
                        </button>
                        <button type="submit" form="stockForm" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglet Historique -->
    <div class="tab-pane fade" id="historique">

        <div class="bo-card mt-3">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-clock-rotate-left me-2" style="color:#6366f1;"></i>
                Historique des mouvements de stock
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Avant</th>
                        <th>Après</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Commentaire</th>
                        <th>Utilisateur</th>
                    </tr>
                    </thead>

                    <tbody id="historique-table">
                    <?php if (!empty($mouvementsStock)): ?>

                        <?php foreach ($mouvementsStock as $m): ?>

                            <tr>
                                <td class="text-muted small">
                                    <?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?>
                                </td>

                                <td>
                                    <?php
                                    $color = match($m['type_mouvement']) {
                                        'entree' => 'success',
                                        'sortie' => 'danger',
                                        'retour' => 'info',
                                        'ajustement' => 'secondary',
                                        'deplacement' => 'warning',
                                        'swap' => 'dark',
                                        default => 'secondary'
                                    };
                                    ?>

                                    <span class="badge bg-<?= $color ?>">
                                        <?= htmlspecialchars($m['type_mouvement']) ?>
                                    </span>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($m['produit_nom'] ?? 'Produit #' . $m['id_produit']) ?>
                                </td>

                                <td class="fw-bold">
                                    <?= (int)$m['quantite'] ?>
                                </td>

                                <td><?= (int)$m['quantite_avant'] ?></td>
                                <td><?= (int)$m['quantite_apres'] ?></td>

                                <td>
                                    <?= $m['stack_source_nom'] ?? '-' ?>
                                </td>

                                <td>
                                    <?= $m['stack_destination_nom'] ?? '-' ?>
                                </td>

                                <td class="text-muted small">
                                    <?= htmlspecialchars($m['commentaire'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= $m['utilisateur_nom'] ?? 'Système' ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Aucun mouvement enregistré
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<script>
   const btnAddLine = document.getElementById("btn-add-line");
    const btnSubmit = document.getElementById("btn-submit");
    const batchTitle = document.querySelector("#batch-summary .text-muted");
    const titleAction = document.getElementById('title-interaction-action');

    function updateUIByAction(action) {

        if (action === 'add') {

            titleAction.innerHTML =
                `<i class="fas fa-cart me-1" style="color: var(--bo-success);"></i> 2. Ajouter des produits`;

            btnAddLine.innerHTML =
                `<i class="fas fa-plus me-1"></i> Ajouter une ligne`;

            btnSubmit.innerHTML =
                `<i class="fas fa-check me-2"></i> Valider l'ajout en stock`;
        }

        else if (action === 'remove') {
            titleAction.innerHTML =
                `<i class="fas fa-trash me-1" style="color: var(--bo-danger);"></i> 2. Supprimer des produits`;

            btnAddLine.innerHTML =
                `<i class="fas fa-plus me-1"></i> Ajouter une ligne de suppression`;

            btnSubmit.innerHTML =
                `<i class="fas fa-trash me-2"></i> Valider la suppression`;
        }

        else if (action === 'move') {
            titleAction.innerHTML =
                `<i class="fas fa-exchange-alt me-1" style="color: var(--bo-warning);"></i> 2. Déplacer des produits`;

            btnAddLine.innerHTML =
                `<i class="fas fa-plus me-1"></i> Ajouter une ligne de déplacement`;

            btnSubmit.innerHTML =
                `<i class="fas fa-exchange-alt me-2"></i> Valider le déplacement`;
        }
    }

    function updateSubmitButton() {

        const stockAction = document.getElementById('stock-action');
        const btnSubmit = document.getElementById('btn-submit');

        const action = stockAction.value;

        if (action === 'add') {
            btnSubmit.innerHTML =
                '<i class="fas fa-plus me-2"></i> Valider l\'ajout';
        }

        else if (action === 'remove') {
            btnSubmit.innerHTML =
                '<i class="fas fa-minus me-2"></i> Valider la suppression';
        }

        else if (action === 'move') {
            btnSubmit.innerHTML =
                '<i class="fas fa-arrow-right me-2"></i> Valider le déplacement';
        }
    }

    function updateBatchLabel(action) {

        if (action === 'add') {
            batchTitle.textContent = "Total produits à ajouter :";
        }

        else if (action === 'remove') {
            batchTitle.textContent = "Total produits à supprimer :";
        }

        else if (action === 'move') {
            batchTitle.textContent = "Total produits à déplacer :";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Données des produits et stacks (PHP → JS)
        const produitsJSON = <?php echo json_encode($produitsActifs); ?>;
        const stacksJSON = <?php echo json_encode($stacksList); ?>;

        const stockAction = document.getElementById('stock-action');
        const destinationContainer = document.getElementById('destination-stack-container');
        const selectStackDestination = document.getElementById('select-stack-destination');

        const stackInfoDestination = document.getElementById('stack-info-destination');

        const stackRestantDestination = document.getElementById('stack-restant-destination');

        const stackProgressDestination = document.getElementById('stack-progress-destination');

        let stackCapaciteMaxDestination = 0;
        let stackCapaciteUtiliseeDestination = 0;

        stockAction.addEventListener('change', function () {
            updateUIByAction(this.value);
            updateBatchLabel(this.value);

            productLines.innerHTML = '';
            addLineAjout();
            updateProductOptions();

            if (this.value === 'move') {
                destinationContainer.style.display = 'block';
            } else {
                destinationContainer.style.display = 'none';
            }

            updateSubmitButton();
        });

        selectStackDestination.addEventListener('change', function () {

            const selected = this.options[this.selectedIndex];

            if (this.value) {

                stackCapaciteMaxDestination =
                    parseInt(selected.dataset.capacite) || 0;

                stackCapaciteUtiliseeDestination =
                    parseInt(selected.dataset.utilise) || 0;

                const restant =
                    stackCapaciteMaxDestination -
                    stackCapaciteUtiliseeDestination;

                const taux =
                    stackCapaciteMaxDestination > 0
                        ? (stackCapaciteUtiliseeDestination / stackCapaciteMaxDestination * 100)
                        : 0;

                stackRestantDestination.textContent =
                    restant + ' places';

                stackProgressDestination.style.width =
                    taux + '%';

                stackProgressDestination.style.background =
                    taux >= 80
                        ? '#ef4444'
                        : (taux >= 50 ? '#f59e0b' : '#10b981');

                stackInfoDestination.style.display = 'block';

            } else {

                stackInfoDestination.style.display = 'none';
            }

            updateSummaryAjout();
        });

        // ====================== ONGLET AJOUT ======================
        const productLines = document.getElementById('product-lines');
        const btnAddLine = document.getElementById('btn-add-line');
        const selectStackAjout = document.getElementById('select-stack-ajout');
        const stackInfoAjout = document.getElementById('stack-info-ajout');
        const stackRestantAjout = document.getElementById('stack-restant-ajout');
        const stackProgressAjout = document.getElementById('stack-progress-ajout');
        const batchSummary = document.getElementById('batch-summary');
        const totalQuantityEl = document.getElementById('total-quantity');
        const capacityWarning = document.getElementById('capacity-warning');
        const capacityWarningText = document.getElementById('capacity-warning-text');
        const btnSubmit = document.getElementById('btn-submit');

        let stackCapaciteMaxAjout = 0;
        let stackCapaciteUtiliseeAjout = 0;

        // Sélection du stack (onglet Ajout)
        selectStackAjout.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (this.value) {
                stackCapaciteMaxAjout = parseInt(selected.dataset.capacite) || 0;
                stackCapaciteUtiliseeAjout = parseInt(selected.dataset.utilise) || 0;
                const restant = stackCapaciteMaxAjout - stackCapaciteUtiliseeAjout;
                const taux = stackCapaciteMaxAjout > 0 ? (stackCapaciteUtiliseeAjout / stackCapaciteMaxAjout * 100) : 0;

                stackRestantAjout.textContent = restant + ' places';
                stackProgressAjout.style.width = taux + '%';
                stackProgressAjout.style.background = taux >= 80 ? '#ef4444' : (taux >= 50 ? '#f59e0b' : '#10b981');
                stackInfoAjout.style.display = 'block';
            } else {
                stackInfoAjout.style.display = 'none';
            }

            // Recharge les lignes produits selon le stack
            productLines.innerHTML = '';
            addLineAjout();
            updateDestinationOptions();
            updateSummaryAjout();
        });

        function updateProductOptions() {

            const selects =
                document.querySelectorAll('#product-lines select');

            let selectedValues = [];

            // récupère toutes les valeurs sélectionnées
            selects.forEach(s => {
                if (s.value) selectedValues.push(s.value);
            });

            selects.forEach(select => {

                const currentValue = select.value;

                Array.from(select.options).forEach(option => {

                    if (!option.value) return;

                    // ❌ désactive si déjà choisi ailleurs
                    if (
                        selectedValues.includes(option.value) &&
                        option.value !== currentValue
                    ) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        function updateDestinationOptions() {

            const source = selectStackAjout.value;
            const options = selectStackDestination.querySelectorAll('option');

            options.forEach(opt => {

                if (!opt.value) return;

                if (opt.value === source) {
                    opt.disabled = true;
                    opt.style.opacity = "0.4";
                } else {
                    opt.disabled = false;
                    opt.style.opacity = "1";
                }
            });
        }

        // Ajouter une ligne de produit
        function addLineAjout() {

            const div = document.createElement('div');
            div.className = 'product-line d-flex gap-2 align-items-center mb-2';

            const action = stockAction.value;
            const selectedStackId = selectStackAjout.value;

            let produitsDisponibles = produitsJSON;

            // =========================================
            // Filtrage pour remove / move (stack only)
            // =========================================
            if ((action === 'remove' || action === 'move') && selectedStackId) {

                const stack = stacksJSON.find(s => s.id == selectedStackId);

                if (stack && stack.produits) {

                    const idsProduitsStack =
                        stack.produits.map(p => parseInt(p.id_produit));

                    produitsDisponibles =
                        produitsJSON.filter(p =>
                            idsProduitsStack.includes(parseInt(p.id))
                        );
                }
            }

            let options = '<option value="">— Choisir un produit —</option>';

            produitsDisponibles.forEach(p => {
                options += `
            <option value="${p.id}">
                ${p.nom} (${p.identifiant})
            </option>
        `;
            });

            div.innerHTML = `
                <select
                    name="produit_id[]"
                    class="form-select"
                    required
                    style="border-radius: 10px; flex: 2;"
                >
                    ${options}
                </select>

                <input
                    type="number"
                    name="produit_qte[]"
                    class="form-control produit-qte"
                    placeholder="Qté"
                    min="1"
                    value="1"
                    required
                    style="border-radius: 10px; flex: 0.5; text-align: center;"
                >

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger btn-remove-line"
                    style="border-radius: 8px; flex: 0 0 auto;"
                >
                    <i class="fas fa-trash"></i>
                </button>
            `;

            const select = div.querySelector('select');
            const qty = div.querySelector('.produit-qte');
            const removeBtn = div.querySelector('.btn-remove-line');

            // =========================================
            // Events
            // =========================================

            select.addEventListener('change', () => {
                updateSummaryAjout();
                updateProductOptions(); // évite doublons
            });

            qty.addEventListener('input', updateSummaryAjout);

            removeBtn.addEventListener('click', () => {
                div.remove();
                updateSummaryAjout();
                updateProductOptions(); // réactive options
            });

            // =========================================
            // Ajout DOM
            // =========================================

            productLines.appendChild(div);

            // MAJ globale
            updateSummaryAjout();
            updateProductOptions();
        }

        btnAddLine.addEventListener('click', addLineAjout);
        addLineAjout(); // Ajoute une ligne par défaut

        // Mettre à jour le résumé
        function updateSummaryAjout() {

            const action = stockAction.value;

            const lines =
                document.querySelectorAll('#product-lines .product-line');

            let total = 0;

            lines.forEach(line => {

                const qte =
                    parseInt(
                        line.querySelector('.produit-qte').value
                    ) || 0;

                total += qte;
            });

            totalQuantityEl.textContent = total;

            batchSummary.style.display =
                lines.length > 0 ? 'block' : 'none';

            // ===============================
            // AJOUT
            // ===============================
            if (action === 'add') {

                const selectedStack = selectStackAjout.value;

                const lines = document.querySelectorAll('#product-lines .product-line');

                let total = 0;
                let hasSelectedProduct = false;

                lines.forEach(line => {
                    const select = line.querySelector('select');
                    const qte = parseInt(line.querySelector('.produit-qte').value) || 0;

                    if (select.value) {
                        hasSelectedProduct = true;
                        total += qte;
                    }
                });

                // ❌ Aucun stack ou aucun produit sélectionné → on reset proprement
                if (!selectedStack || !hasSelectedProduct) {
                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = true;
                    return;
                }

                const restant =
                    stackCapaciteMaxAjout - stackCapaciteUtiliseeAjout;

                // sécurité anti NaN
                if (isNaN(restant) || restant < 0) {
                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = true;
                    return;
                }

                if (total > restant) {

                    capacityWarning.style.display = 'block';

                    capacityWarningText.textContent =
                        `Capacité dépassée ! Vous essayez d'ajouter ${total} produit(s) mais il ne reste que ${restant} place(s).`;

                    btnSubmit.disabled = true;

                } else {

                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = !(total > 0);
                }
            }

                // ===============================
                // SUPPRESSION
            // ===============================

            else if (action === 'remove') {

                const selectedStackId = selectStackAjout.value;
                const lines = document.querySelectorAll('#product-lines .product-line');

                if (!selectedStackId) {
                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = true;
                    return;
                }

                const stack = stacksJSON.find(s => s.id == selectedStackId);

                let errors = [];
                let isValid = true;

                lines.forEach(line => {

                    const select = line.querySelector('select');
                    const qte = parseInt(line.querySelector('.produit-qte').value) || 0;

                    if (!select.value || qte <= 0) return;

                    const produitStack =
                        stack?.produits?.find(p => p.id_produit == select.value);

                    const stockDispo = produitStack ? parseInt(produitStack.quantite) : 0;

                    const nomProduit =
                        select.options[select.selectedIndex]?.text || 'Produit inconnu';

                    if (qte > stockDispo) {
                        isValid = false;
                        errors.push(
                            `• ${nomProduit} : ${qte} demandés mais seulement ${stockDispo} disponibles`
                        );
                    }
                });

                if (errors.length > 0) {

                    capacityWarning.style.display = 'block';

                    capacityWarningText.innerHTML =
                        `Problème de stock :<br>` + errors.join('<br>');

                    btnSubmit.disabled = true;

                } else {

                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = false;
                }
            }

                // ===============================
                // DEPLACEMENT
            // ===============================

            else if (action === 'move') {

                const sourceStackId = selectStackAjout.value;
                const destinationStackId = selectStackDestination.value;

                if (!sourceStackId || !destinationStackId) {
                    capacityWarning.style.display = 'none';
                    btnSubmit.disabled = true;
                    return;
                }

                // ❌ sécurité même stack (tu l'as déjà mais on renforce)
                if (sourceStackId === destinationStackId) {
                    capacityWarning.style.display = 'block';
                    capacityWarningText.textContent =
                        `Impossible de déplacer vers le même stack.`;

                    btnSubmit.disabled = true;
                    return;
                }

                const sourceStack = stacksJSON.find(s => s.id == sourceStackId);

                const lines = document.querySelectorAll('#product-lines .product-line');

                let errors = [];
                let total = 0;

                lines.forEach(line => {

                    const select = line.querySelector('select');
                    const qte = parseInt(line.querySelector('.produit-qte').value) || 0;

                    if (!select.value || qte <= 0) return;

                    total += qte;

                    const produitStack = sourceStack?.produits?.find(
                        p => p.id_produit == select.value
                    );

                    const stockDispo = produitStack
                        ? parseInt(produitStack.quantite)
                        : 0;

                    const nomProduit =
                        select.options[select.selectedIndex]?.text || 'Produit inconnu';

                    // ❌ VERIF STOCK SOURCE
                    if (qte > stockDispo) {
                        errors.push(
                            `• ${nomProduit} : ${qte} demandés mais seulement ${stockDispo} disponibles`
                        );
                    }
                });

                // ❌ erreur stock source
                if (errors.length > 0) {
                    capacityWarning.style.display = 'block';
                    capacityWarningText.innerHTML =
                        `Stock insuffisant dans le stack source :<br>` + errors.join('<br>');

                    btnSubmit.disabled = true;
                    return;
                }

                // ❌ capacité destination
                const restantDestination =
                    stackCapaciteMaxDestination - stackCapaciteUtiliseeDestination;

                if (total > restantDestination) {
                    capacityWarning.style.display = 'block';
                    capacityWarningText.textContent =
                        `Le stack destination n'a pas assez de place (${restantDestination} restante(s)).`;

                    btnSubmit.disabled = true;
                    return;
                }

                // ✅ tout OK
                capacityWarning.style.display = 'none';
                btnSubmit.disabled = (total <= 0);
            }
        }

        // Validation du formulaire
        document.getElementById('formAjoutBatch').addEventListener('submit', function(e) {
            const lines = document.querySelectorAll('#product-lines .product-line');
            if (lines.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un produit.');
            } else if (!selectStackAjout.value) {
                e.preventDefault();
                alert('Veuillez sélectionner un stack.');
            }
        });

        // ====================== ONGLET VISUALISATION ======================
        const selectStackVisu = document.getElementById('select-stack-visu');
        const btnLoadStack = document.getElementById('btn-load-stack');
        const stackVisualisation = document.getElementById('stack-visualisation');
        const stackVisuTitle = document.getElementById('stack-visu-title');
        const stackVisuLocation = document.getElementById('stack-visu-location');
        const stackVisuTaux = document.getElementById('stack-visu-taux');
        const stackVisuProgress = document.getElementById('stack-visu-progress');
        const stackVisuCapaciteMax = document.getElementById('stack-visu-capacite-max');
        const stackVisuProduits = document.getElementById('stack-visu-produits');
        const stackVisuNbProduits = document.getElementById('stack-visu-nb-produits');
        const stackVisuStatsProduits = document.getElementById('stack-visu-stats-produits');
        const stackVisuStatsQuantite = document.getElementById('stack-visu-stats-quantite');
        const btnAddToStack = document.getElementById('btn-add-to-stack');

        // Charger les produits du stack (sans AJAX, données déjà en PHP)
        btnLoadStack.addEventListener('click', function() {
            const idStack = selectStackVisu.value;
            if (!idStack) {
                alert('Veuillez sélectionner un stack.');
                return;
            }

            const selectedOption = selectStackVisu.options[selectStackVisu.selectedIndex];
            const entrepot = selectedOption.dataset.entrepot;
            const meuble = selectedOption.dataset.meuble;
            const stackNom = selectedOption.dataset.stackNom;
            const capaciteMax = parseInt(selectedOption.dataset.capacite);
            const capaciteUtilisee = parseInt(selectedOption.dataset.utilise);
            const taux = capaciteMax > 0 ? (capaciteUtilisee / capaciteMax * 100) : 0;

            // Met à jour l'en-tête
            stackVisuTitle.textContent = stackNom;
            stackVisuLocation.textContent = `${entrepot} → ${meuble} → ${stackNom}`;
            stackVisuTaux.textContent = `${taux.toFixed(1)}%`;
            stackVisuCapaciteMax.textContent = capaciteMax;
            stackVisuProgress.style.width = `${taux}%`;
            stackVisuProgress.style.background = taux >= 80 ? '#ef4444' : (taux >= 50 ? '#f59e0b' : '#10b981');
            btnAddToStack.href = `/backoffice/stock?id_stack=${idStack}`;

            // Trouve le stack dans stacksJSON
            const stack = stacksJSON.find(s => s.id == idStack);
            if (!stack) {
                stackVisuProduits.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Stack introuvable.</td></tr>';
                return;
            }

            // Affiche le conteneur
            stackVisualisation.style.display = 'block';

            // Récupère les produits du stack (déjà chargés dans stacksJSON)
            const produitsDuStack = stack.produits || [];

            if (produitsDuStack.length === 0) {
                stackVisuProduits.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-box-open fs-3 mb-2 d-block"></i>Ce stack est vide.</td></tr>';
                stackVisuNbProduits.textContent = '0';
                stackVisuStatsProduits.textContent = '0';
                stackVisuStatsQuantite.textContent = '0';
                return;
            }

            // Met à jour les stats
            const totalQuantite = produitsDuStack.reduce((sum, p) => sum + p.quantite, 0);
            stackVisuNbProduits.textContent = produitsDuStack.length;
            stackVisuStatsProduits.textContent = produitsDuStack.length;
            stackVisuStatsQuantite.textContent = totalQuantite;

            // Affiche les produits
            let html = '';
            produitsDuStack.forEach(p => {
                // Trouve les infos complètes du produit dans produitsJSON
                const produitComplete = p;
                console.log(produitComplete);
                // Image
                let imageHtml = '';
                if (produitComplete.image) {
                    imageHtml = `<img src="/images/${produitComplete.image}" class="rounded border" style="width:40px; height:40px; object-fit:cover;">`;
                } else {
                    imageHtml = `<div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><i class="bi bi-image text-muted small"></i></div>`;
                }

                // Prix
                const prixHT = produitComplete.prix_ht
                    ? (produitComplete.prix_ht / 100).toFixed(2).replace('.', ',')
                    : '0,00';

                // Stock
                const stockDispo = p.quantite;
                const seuilAlerte = 15; // Valeur par défaut
                let stockBadge = '';
                if (stockDispo <= 0) {
                    stockBadge = `<span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;"><i class="fas fa-times-circle me-1"></i> Rupture</span>`;
                } else if (stockDispo <= seuilAlerte) {
                    stockBadge = `<span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;"><i class="fas fa-exclamation-triangle me-1"></i> ${stockDispo}</span>`;
                } else {
                    stockBadge = `<span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;"><i class="fas fa-check-circle me-1"></i> ${stockDispo}</span>`;
                }

                html += `
                <tr>
                    <td class="ps-4 fw-bold">#${produitComplete.id || p.id_produit}</td>
                    <td>${imageHtml}</td>
                    <td class="fw-semibold text-dark">${produitComplete.nom || 'Inconnu'}</td>
                    <td><code class="small text-muted">${produitComplete.identifiant || 'N/A'}</code></td>
                    <td class="fw-bold text-dark">${prixHT} €</td>
                    <td>${stockBadge}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-xs btn-outline-secondary" style="border-radius: 6px;">
                                <i class="fas fa-edit" style="font-size: 0.7rem;"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger" style="border-radius: 6px;">
                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            });

            stackVisuProduits.innerHTML = html;
        });

        // ====================== ONGLET SCANNER ======================
        let qrScanner = null;

// Fonction appelée après un scan réussi
        async function onScanSuccess(decodedText) {
            // 1. Arrêter le scanner et cacher la zone
            if (qrScanner) {
                await qrScanner.stop();
                await qrScanner.clear();
                qrScanner = null;
            }
            document.getElementById("qr-reader").style.display = "none"; // 👈 Cache la zone
            document.getElementById("btn-start-scan").style.display = "inline-block";
            document.getElementById("btn-stop-scan").style.display = "none";

            console.log("decodedURL", decodedText);

            const response = await fetch(`/controller/api/c-apiStock.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'parseQr',
                    url: decodedText
                })
            });

            console.log("STATUS:", response.status);
            console.log("OK:", response.ok);
            console.log("HEADERS:", [...response.headers.entries()]);

// 🔥 IMPORTANT : lire le body en texte AVANT JSON
            const rawText = await response.text();
            console.log("RAW RESPONSE:", rawText);

            let product;
            try {
                product = JSON.parse(rawText);
            } catch (e) {
                console.error("JSON PARSE ERROR:", e);
                throw new Error("Réponse API invalide (pas du JSON)");
            }

            console.log("product", product);
        }

// Fonctions pour les boutons (Entrée/Sortie/Déplacer)
        function scanEntree() {
            const productId = document.getElementById("scan-product-id").textContent;
            if (!productId || productId === "—") return alert("Scannez un produit d'abord !");
            document.getElementById("modal-action").textContent = "➕ Entrée de stock";
            document.getElementById("modal-product").textContent = productId;
            document.getElementById("modal-quantity").value = document.getElementById("scan-qty").value;
            new bootstrap.Modal(document.getElementById("stockModal")).show();
        }

        function scanSortie() {
            const productId = document.getElementById("scan-product-id").textContent;
            if (!productId || productId === "—") return alert("Scannez un produit d'abord !");
            document.getElementById("modal-action").textContent = "➖ Sortie de stock";
            document.getElementById("modal-product").textContent = productId;
            document.getElementById("modal-quantity").value = document.getElementById("scan-qty").value;
            new bootstrap.Modal(document.getElementById("stockModal")).show();
        }

        function scanMove() {
            const productId = document.getElementById("scan-product-id").textContent;
            if (!productId || productId === "—") return alert("Scannez un produit d'abord !");
            document.getElementById("modal-action").textContent = "🔁 Déplacement de stock";
            document.getElementById("modal-product").textContent = productId;
            document.getElementById("modal-quantity").value = document.getElementById("scan-qty").value;
            new bootstrap.Modal(document.getElementById("stockModal")).show();
        }

// Gestion du scanner
        document.getElementById("btn-start-scan").addEventListener("click", async () => {
            try {
                // 1. Charger la librairie si nécessaire
                if (typeof Html5Qrcode === 'undefined') {
                    await new Promise(resolve => {
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/html5-qrcode';
                        script.onload = resolve;
                        document.head.appendChild(script);
                    });
                }

                // 2. Initialiser le scanner
                if (!qrScanner) {
                    qrScanner = new Html5Qrcode("qr-reader");
                }

                // 3. Démarrer le scanner et AFFICHER la zone
                document.getElementById("qr-reader").style.display = "block"; // 👈 Affiche la zone
                await qrScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    onScanSuccess
                );

                document.getElementById("btn-start-scan").style.display = "none";
                document.getElementById("btn-stop-scan").style.display = "inline-block";

            } catch (err) {
                alert(err.name === "NotAllowedError" ? "Autorisez la caméra dans la popup du navigateur !" : "Erreur : " + err.message);
            }
        });

        document.getElementById("btn-stop-scan").addEventListener("click", async () => {
            if (qrScanner) {
                await qrScanner.stop();
                await qrScanner.clear();
                qrScanner = null;
            }
            document.getElementById("qr-reader").style.display = "none"; // 👈 Cache la zone
            document.getElementById("btn-start-scan").style.display = "inline-block";
            document.getElementById("btn-stop-scan").style.display = "none";
        });
    });
</script>

<style>
    .btn-xs { padding: 0.2rem 0.4rem; font-size: 0.75rem; line-height: 1; }
    .nav-tabs .nav-link { color: var(--bo-text-muted); font-weight: 500; }
    .nav-tabs .nav-link.active { color: var(--bo-primary); background: transparent; border-bottom-color: var(--bo-primary); }
    .nav-tabs .nav-link:hover { color: var(--bo-primary); }
    .bo-table th { background: #f8fafc; padding: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--bo-text-muted); border-bottom: 1px solid var(--bo-border); }
    .bo-table td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--bo-border); }
    .bo-table tr:last-child td { border-bottom: none; }
    .bo-table tr:hover td { background-color: #fcfcfd; }
</style>