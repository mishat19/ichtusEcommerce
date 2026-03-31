<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Étapes de la commande -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step active">
                    <div class="step-icon">1</div>
                    <div class="step-label">Récapitulatif</div>
                </div>
                <div class="step">
                    <div class="step-icon">2</div>
                    <div class="step-label">Adresses</div>
                </div>
                <div class="step">
                    <div class="step-icon">3</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step">
                    <div class="step-icon">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <h2 class="mb-4">Récapitulatif de votre commande</h2>

            <!-- Produits du panier -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Produits</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lignes_panier as $ligne): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="/images/<?= htmlspecialchars($ligne['image']) ?>" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span><?= htmlspecialchars($ligne['nom']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $ligne['quantite'] ?></td>
                                    <td class="text-end"><?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100), 2, ',', ' ') ?>€</td>
                                    <td class="text-end"><?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100) * $ligne['quantite'], 2, ',', ' ') ?>€</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Sous-total</th>
                                <td class="text-end"><?= number_format($panier['total_ht'] / 100, 2, ',', ' ') ?>€</td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Frais de livraison</th>
                                <td class="text-end">
                                    <?php $fraisLivraison = ($panier['total_ttc'] / 100 >= 50) ? 0 : 4.99; ?>
                                    <?= ($fraisLivraison == 0) ? 'Gratuits' : number_format($fraisLivraison, 2, ',', ' ') . '€' ?>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total TTC</th>
                                <td class="text-end fw-bold">
                                    <?= number_format(($panier['total_ttc'] / 100) + $fraisLivraison, 2, ',', ' ') ?>€
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Boutons de navigation -->
            <div class="d-flex justify-content-between">
                <a href="/panier" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Retour au panier
                </a>
                <a href="/commande-adresses" class="btn btn-primary">
                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Style pour les étapes */
    .step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .step.active .step-icon {
        background-color: #0d6efd;
        color: white;
    }
    .step.completed .step-icon {
        background-color: #198754;
        color: white;
    }
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-weight: bold;
    }
    .step-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .step.active .step-label,
    .step.completed .step-label {
        color: #212529;
        font-weight: 500;
    }
    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-color: #e9ecef;
        z-index: 1;
    }
    .step.active::after,
    .step.completed::after {
        background-color: #0d6efd;
    }
</style>
