<div class="container py-5">
    <!-- Titre de la page -->
    <h1 class="mb-4">Votre panier</h1>

    <!-- Bouton retour -->
    <div class="mb-4">
        <a href="/produit" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Continuer vos achats
        </a>
    </div>

    <!-- Message si le panier a expiré -->
    <?php if (isset($_SESSION['panier_expire']) && $_SESSION['panier_expire']): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px; border-left: 4px solid #f59e0b;">
            <i class="fas fa-clock me-3 fs-4" style="color: #f59e0b;"></i>
            <div>
                <strong>Panier expiré !</strong> Votre réservation de 20 minutes a expiré. Les produits ont été remis en stock pour les autres clients. Vous pouvez les ajouter à nouveau.
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['panier_expire']); ?>
    <?php endif; ?>

    <!-- Message erreur panier -->
    <?php if (isset($_SESSION['erreur_panier'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php e($_SESSION['erreur_panier']); unset($_SESSION['erreur_panier']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Message si le panier est vide -->
    <?php if ($panier['nb_lignes'] == 0): ?>
        <div class="alert alert-info text-center">
            Votre panier est vide.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Conteneur pour les messages d'erreur -->
            <div id="error-container" class="mt-10"></div>
            <!-- Colonne de gauche : Liste des produits -->
            <div class="col-lg-8">
                <div class="bg-white rounded-3 shadow-sm p-3 p-md-4">
                    <table class="table table-borderless align-middle panier-table mb-0">
                        <thead class="border-bottom">
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire (TTC)</th>
                            <th>Quantité</th>
                            <th>Total (TTC)</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lignes_panier as $ligne): ?>
                            <tr>
                                <td class="panier-produit">
                                    <div class="d-flex align-items-center">
                                        <img src="/images/<?php e($ligne['image']); ?>" alt="<?php e($ligne['nom']); ?>" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?php e($ligne['nom']); ?></h6>
                                            <small class="text-muted">Réf: <?php e($ligne['identifiant']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Prix unitaire (TTC)">
                                    <?php e(number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100), 2, ',', ' ')); ?>€
                                </td>
                                <td data-label="Quantité">
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="id_ligne" value="<?php e($ligne['id_ligne']); ?>">
                                        <div class="input-group" style="width: 100px;">
                                            <input
                                                    type="number"
                                                    name="quantite"
                                                    class="form-control text-center"
                                                    value="<?php e($ligne['quantite']); ?>"
                                                    min="0"
                                                    max="99"
                                                    required
                                            >
                                        </div>
                                    </form>
                                </td>
                                <td data-label="Total (TTC)">
                                    <span class="fw-semibold"><?php e(number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100) * $ligne['quantite'], 2, ',', ' ')); ?>€</span>
                                </td>
                                <td class="panier-actions">
                                    <form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="id_ligne" value="<?php e($ligne['id_ligne']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-2">
                                            <i class="fas fa-trash-alt me-1"></i> <span class="d-inline d-lg-none">Supprimer</span><span class="d-none d-lg-inline"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Colonne de droite : Résumé du panier -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">

                        <!-- Timer de réservation -->
                        <?php if ($tempsRestant > 0): ?>
                            <div class="mb-3 p-3 text-center" id="timer-box"
                                 style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 12px; border: 1px solid #90caf9;">
                                <p class="mb-1 small" style="color: #1565c0;">
                                    <i class="fas fa-clock me-1"></i> Réservation expire dans
                                </p>
                                <span id="countdown-timer" class="fw-bold fs-4" style="color: #0d47a1;"
                                      data-seconds="<?php e($tempsRestant); ?>">
                                    <?php
                                    $min = floor($tempsRestant / 60);
                                    $sec = $tempsRestant % 60;
                                    echo sprintf('%02d:%02d', $min, $sec);
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <h5 class="card-title mb-4">Résumé de la commande</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Nombre d'articles</span>
                                <span><?php e($panier['nb_articles']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Total HT</span>
                                <span><?php e(number_format($panier['total_ht'] / 100, 2, ',', ' ')); ?>€</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Frais de livraison</span>
                                <?php
                                $totalTTC = $panier['total_ttc'] / 100;
                                $fraisLivraison = ($totalTTC >= 50) ? 0 : 4.99;
                                ?>
                                <span><?php e(($fraisLivraison == 0) ? 'Gratuits' : number_format($fraisLivraison, 2, ',', ' ') . '€'); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Total TTC</span>
                                <span class="fw-bold fs-5">
                                    <?php e(number_format($totalTTC + $fraisLivraison, 2, ',', ' ')); ?>€
                                </span>
                            </li>
                        </ul>

                        <!-- Barre de progression pour la livraison gratuite -->
                        <?php if ($totalTTC < 50): ?>
                            <div class="mb-4">
                                <p class="small text-muted mb-2">
                                    Il vous manque <?php e(number_format(50 - $totalTTC, 2, ',', ' ')); ?>€ pour bénéficier de la livraison gratuite.
                                </p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?php e(min(100, ($totalTTC / 50) * 100)); ?>%;"
                                         aria-valuenow="<?php e(min(100, ($totalTTC / 50) * 100)); ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Bouton Valider la commande -->
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <button type="submit" class="btn btn-primary w-100 py-2" name="valider_commande">
                                Valider la commande
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="small text-muted mb-1"><i class="fa-solid fa-lock me-1"></i> Paiement 100% sécurisé</p>
                            <div class="d-flex justify-content-center gap-2">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Visa_Inc._logo_%282021%E2%80%93present%29.svg" alt="Visa" style="height: 20px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" style="height: 20px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" style="height: 20px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* ── Countdown Timer ── */
        const timerEl = document.getElementById('countdown-timer');
        if (timerEl) {
            let seconds = parseInt(timerEl.dataset.seconds);
            const timerBox = document.getElementById('timer-box');

            const interval = setInterval(function() {
                seconds--;
                if (seconds <= 0) {
                    clearInterval(interval);
                    // Recharger la page pour déclencher l'expiration côté serveur
                    window.location.reload();
                    return;
                }

                const min = Math.floor(seconds / 60);
                const sec = seconds % 60;
                timerEl.textContent = String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');

                // Changer la couleur quand il reste peu de temps
                if (seconds <= 120) { // 2 minutes
                    timerBox.style.background = 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)';
                    timerBox.style.borderColor = '#ffb74d';
                    timerEl.style.color = '#e65100';
                }
                if (seconds <= 60) { // 1 minute
                    timerBox.style.background = 'linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%)';
                    timerBox.style.borderColor = '#ef9a9a';
                    timerEl.style.color = '#c62828';
                }
            }, 1000);
        }

        /* ── Quantity validation ── */
        const errorContainer = document.getElementById('error-container');
        const quantityForms = document.querySelectorAll('form.d-flex.align-items-center');

        function showError(message) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        quantityForms.forEach(form => {
            const quantityInput = form.querySelector('input[name="quantite"]');

            form.addEventListener('submit', function(e) {
                const quantity = parseInt(quantityInput.value);

                if (isNaN(quantity) || quantity < 0) {
                    e.preventDefault();
                    showError("La quantité doit être un nombre positif.");
                    quantityInput.value = 1;
                    quantityInput.focus();
                    return false;
                }

                if (quantity > 99) {
                    e.preventDefault();
                    showError("La quantité maximale autorisée est 99.");
                    quantityInput.value = 99;
                    quantityInput.focus();
                    return false;
                }

                return true;
            });

            quantityInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const quantity = parseInt(this.value);

                    if (isNaN(quantity) || quantity < 0) {
                        showError("La quantité doit être un nombre positif.");
                        this.value = 1;
                        this.focus();
                    } else if (quantity > 99) {
                        showError("La quantité maximale autorisée est 99.");
                        this.value = 99;
                        this.focus();
                    } else {
                        this.form.submit();
                    }
                }
            });

            quantityInput.addEventListener('change', function() {
                let quantity = parseInt(this.value);
                if (isNaN(quantity) || quantity < 0) {
                    this.value = 1;
                } else if (quantity > 99) {
                    this.value = 99;
                }
            });
        });
    });
</script>

