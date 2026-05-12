    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">
                <i class="fas fa-boxes-stacked me-2" style="color: var(--bo-primary);"></i> Ajout de Stock
            </h2>
            <p class="text-muted small mb-0">Sélectionnez un rack et ajoutez plusieurs produits en une seule fois.</p>
        </div>
        <a href="/backoffice/entrepots" class="btn btn-outline-secondary" style="border-radius: 10px;">
            <i class="fas fa-arrow-left me-2"></i> Retour aux entrepôts
        </a>
    </div>

    <!-- Messages -->
    <?php if (!empty($messageSucces)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div><?php echo $messageSucces; ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($messageErreur)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px;">
            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
            <div><?php echo $messageErreur; ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="formAjoutBatch">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        <input type="hidden" name="ajout_batch" value="1">

        <div class="row g-4">
            <!-- Colonne gauche : Sélection du stack -->
            <div class="col-lg-4">
                <div class="bo-card" style="border-left: 4px solid var(--bo-primary);">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-layer-group me-2" style="color: var(--bo-primary);"></i> 1. Sélectionner un Stack
                    </h5>

                    <select name="id_stack" id="select-stack" class="form-select mb-3" required
                            style="border-radius: 10px; padding: 0.7rem;">
                        <option value="">— Choisir un stack —</option>
                        <?php
                        $currentEntrepot = '';
                        $currentMeuble = '';
                        foreach ($stacksList as $s):
                            if ($s['entrepot_nom'] !== $currentEntrepot):
                                if ($currentEntrepot !== '') echo '</optgroup>';
                                if ($currentMeuble !== '') $currentMeuble = '';
                                $currentEntrepot = $s['entrepot_nom'];
                                echo '<optgroup label="🏭 ' . htmlspecialchars($currentEntrepot) . '">';
                            endif;
                        ?>
                            <option value="<?php e($s['id']); ?>"
                                    data-capacite="<?php e($s['capacite_max']); ?>"
                                    data-utilise="<?php e($s['capacite_utilisee']); ?>">
                                <?php e($s['meuble_nom']); ?> → <?php e($s['nom']); ?>
                                (<?php e($s['capacite_utilisee']); ?>/<?php e($s['capacite_max']); ?>)
                            </option>
                        <?php endforeach; ?>
                        <?php if ($currentEntrepot !== '') echo '</optgroup>'; ?>
                    </select>

                    <!-- Info stack sélectionné -->
                    <div id="stack-info" style="display: none;">
                        <div style="background: #f8fafc; border-radius: 10px; padding: 1rem; border: 1px solid var(--bo-border);">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Capacité restante</span>
                                <span class="fw-bold" id="stack-restant" style="color: var(--bo-primary);">—</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 6px; background: #e2e8f0;">
                                <div id="stack-progress" class="progress-bar" style="border-radius: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Liste des produits à ajouter -->
            <div class="col-lg-8">
                <div class="bo-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-cart-plus me-2" style="color: var(--bo-success);"></i> 2. Ajouter des produits
                        </h5>
                        <button type="button" id="btn-add-line" class="btn btn-sm"
                                style="background: var(--bo-success); color: #fff; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-plus me-1"></i> Ajouter une ligne
                        </button>
                    </div>

                    <div id="product-lines">
                        <!-- Les lignes de produits seront ajoutées ici par JS -->
                    </div>

                    <!-- Résumé -->
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

                    <!-- Bouton Valider -->
                    <div class="mt-4 text-end">
                        <button type="submit" id="btn-submit" class="btn btn-lg"
                                style="background: var(--bo-primary); color: #fff; border-radius: 12px; font-weight: 700; padding: 0.7rem 2rem;"
                                disabled>
                            <i class="fas fa-check me-2"></i> Valider l'ajout en stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productLines = document.getElementById('product-lines');
    const btnAddLine = document.getElementById('btn-add-line');
    const selectStack = document.getElementById('select-stack');
    const stackInfo = document.getElementById('stack-info');
    const stackRestant = document.getElementById('stack-restant');
    const stackProgress = document.getElementById('stack-progress');
    const batchSummary = document.getElementById('batch-summary');
    const totalQuantityEl = document.getElementById('total-quantity');
    const capacityWarning = document.getElementById('capacity-warning');
    const capacityWarningText = document.getElementById('capacity-warning-text');
    const btnSubmit = document.getElementById('btn-submit');

    // Liste des produits pour le dropdown
    const produitsJSON = <?php echo json_encode($produitsActifs); ?>;

    let lineCount = 0;
    let stackCapaciteMax = 0;
    let stackCapaciteUtilisee = 0;

    // Mettre à jour l'info du stack sélectionné
    selectStack.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            stackCapaciteMax = parseInt(selected.dataset.capacite) || 0;
            stackCapaciteUtilisee = parseInt(selected.dataset.utilise) || 0;
            const restant = stackCapaciteMax - stackCapaciteUtilisee;
            const taux = stackCapaciteMax > 0 ? (stackCapaciteUtilisee / stackCapaciteMax * 100) : 0;

            stackRestant.textContent = restant + ' places';
            stackProgress.style.width = taux + '%';
            stackProgress.style.background = taux >= 80 ? '#ef4444' : (taux >= 50 ? '#f59e0b' : '#10b981');

            stackInfo.style.display = 'block';
        } else {
            stackInfo.style.display = 'none';
        }
        updateSummary();
    });

    // Ajouter une ligne de produit
    function addLine() {
        lineCount++;
        const div = document.createElement('div');
        div.className = 'product-line d-flex gap-2 align-items-center mb-2';
        div.dataset.index = lineCount;

        let options = '<option value="">— Choisir un produit —</option>';
        produitsJSON.forEach(p => {
            options += `<option value="${p.id}">${p.nom} (${p.identifiant})</option>`;
        });

        div.innerHTML = `
            <select name="produit_id[]" class="form-select" required style="border-radius: 10px; flex: 2;">
                ${options}
            </select>
            <input type="number" name="produit_qte[]" class="form-control produit-qte" placeholder="Qté" min="1" value="1" required
                   style="border-radius: 10px; flex: 0.5; text-align: center;">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" style="border-radius: 8px; flex: 0 0 auto;">
                <i class="fas fa-trash"></i>
            </button>
        `;

        productLines.appendChild(div);

        // Event listener pour le bouton supprimer
        div.querySelector('.btn-remove-line').addEventListener('click', function() {
            div.remove();
            updateSummary();
        });

        // Event listener pour la quantité
        div.querySelector('.produit-qte').addEventListener('input', updateSummary);

        updateSummary();
    }

    btnAddLine.addEventListener('click', addLine);

    // Ajouter une première ligne automatiquement
    addLine();

    // Mettre à jour le résumé
    function updateSummary() {
        const lines = document.querySelectorAll('.product-line');
        let total = 0;
        lines.forEach(line => {
            const qte = parseInt(line.querySelector('.produit-qte').value) || 0;
            total += qte;
        });

        totalQuantityEl.textContent = total;
        batchSummary.style.display = lines.length > 0 ? 'block' : 'none';

        const restant = stackCapaciteMax - stackCapaciteUtilisee;
        if (total > restant && selectStack.value) {
            capacityWarning.style.display = 'block';
            capacityWarningText.textContent = `Capacité dépassée ! Vous essayez d'ajouter ${total} produit(s) mais il ne reste que ${restant} place(s).`;
            btnSubmit.disabled = true;
        } else if (total > 0 && selectStack.value) {
            capacityWarning.style.display = 'none';
            btnSubmit.disabled = false;
        } else {
            capacityWarning.style.display = 'none';
            btnSubmit.disabled = true;
        }
    }

    // Validation avant soumission
    document.getElementById('formAjoutBatch').addEventListener('submit', function(e) {
        const lines = document.querySelectorAll('.product-line');
        if (lines.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit.');
            return;
        }

        if (!selectStack.value) {
            e.preventDefault();
            alert('Veuillez sélectionner un stack.');
            return;
        }
    });
});
</script>
