<div class="container py-5">
    <!-- Titre de la page -->
    <h1 class="mb-4">Votre panier</h1>

    <!-- Bouton retour -->
    <div class="mb-4">
        <a href="/produit" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Continuer vos achats
        </a>
    </div>

    adresse
    complement
    code_postal
    ville

    <!-- Message si le panier est vide -->
    <?php if ($panier['nb_lignes'] == 0): ?>
        <div class="alert alert-info text-center">
            Votre panier est vide.
        </div>
    <?php else: ?>
        <!-- Layout en deux colonnes pour PC -->
        <div class="row g-4">
            <!-- Conteneur pour les messages d'erreur -->
            <div id="error-container" class="mt-10"></div>
            <!-- Colonne de gauche : Liste des produits -->
            <div class="col-lg-8">
                <div class="table-responsive bg-white rounded-3 shadow-sm p-4">
                    <table class="table table-borderless align-middle">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/images/<?= htmlspecialchars($ligne['image']) ?>" alt="<?= htmlspecialchars($ligne['nom']) ?>" class="img-thumbnail me-3" style="width: 80px;">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($ligne['nom']) ?></h6>
                                            <small class="text-muted">Réf: <?= htmlspecialchars($ligne['identifiant']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100), 2, ',', ' ') ?>€
                                </td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="id_ligne" value="<?= $ligne['id_ligne'] ?>">
                                        <div class="input-group" style="width: 100px;">
                                            <input
                                                    type="number"
                                                    name="quantite"
                                                    class="form-control text-center"
                                                    value="<?= $ligne['quantite'] ?>"
                                                    min="0"
                                                    max="99"
                                                    required
                                            >
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100) * $ligne['quantite'], 2, ',', ' ') ?>€
                                </td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                                        <input type="hidden" name="id_ligne" value="<?= $ligne['id_ligne'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-2">
                                            <i class="fas fa-trash-alt"></i>
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
                        <h5 class="card-title mb-4">Résumé de la commande</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Nombre d'articles</span>
                                <span><?= $panier['nb_articles'] ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Total HT</span>
                                <span><?= number_format($panier['total_ht'] / 100, 2, ',', ' ') ?>€</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Frais de livraison</span>
                                <?php
                                $totalTTC = $panier['total_ttc'] / 100;
                                $fraisLivraison = ($totalTTC >= 50) ? 0 : 4.99;
                                ?>
                                <span><?= ($fraisLivraison == 0) ? 'Gratuits' : number_format($fraisLivraison, 2, ',', ' ') . '€' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span>Total TTC</span>
                                <span class="fw-bold fs-5">
                                    <?= number_format($totalTTC + $fraisLivraison, 2, ',', ' ') ?>€
                                </span>
                            </li>
                        </ul>

                        <!-- Barre de progression pour la livraison gratuite -->
                        <?php if ($totalTTC < 50): ?>
                            <div class="mb-4">
                                <p class="small text-muted mb-2">
                                    Il vous manque <?= number_format(50 - $totalTTC, 2, ',', ' ') ?>€ pour bénéficier de la livraison gratuite.
                                </p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?= min(100, ($totalTTC / 50) * 100) ?>%;"
                                         aria-valuenow="<?= min(100, ($totalTTC / 50) * 100) ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Bouton Valider la commande -->
                        <form method="POST" class="mb-3">
                            <button type="submit" class="btn btn-primary w-100 py-2" name="valider_commande">
                                Valider la commande
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="small text-muted mb-1"><i class="fa-solid fa-lock me-1"></i> Paiement 100% sécurisé</p>
                            <div class="d-flex justify-content-center gap-2">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Visa_Inc._logo_%282021%E2%80%93present%29.svg" alt="Visa" style="height: 20px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b7/MasterCard_Logo.svg/1200px-MasterCard_Logo.svg.png" alt="Mastercard" style="height: 20px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" alt="PayPal" style="height: 20px;">
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
        const errorContainer = document.getElementById('error-container');
        const quantityForms = document.querySelectorAll('form.d-flex.align-items-center');

        // Fonction pour afficher une erreur
        function showError(message) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            // Fait défiler la page vers le haut pour afficher l'erreur
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        quantityForms.forEach(form => {
            const quantityInput = form.querySelector('input[name="quantite"]');

            // Écouteur pour l'événement 'submit'
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

            // Écouteur pour la touche Entrée
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

            // Écouteur pour le 'change' (correction visuelle uniquement)
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


