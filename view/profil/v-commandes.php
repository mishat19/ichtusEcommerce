<div class="container py-5">
    <h1 class="mb-4">Mon Historique de Commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info text-center">
            Vous n'avez pas encore passé de commande.
        </div>
    <?php else: ?>
        <?php foreach ($commandes as $commande): ?>
            <!-- Card Commande -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Commande n°<?= htmlspecialchars($commande['numero_facture']) ?></h5>
                        <small class="text-muted">
                            Passée le <?= (new DateTime($commande['date_commande']))->format('d/m/Y') ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0"><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</h5>
                        <span class="badge bg-success">Payée</span>
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="mb-3">Produits commandés</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="border-bottom">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Prix unitaire</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($commande['produits'] as $produit): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img
                                                src="/images/<?= htmlspecialchars($produit['image']) ?>"
                                                alt="<?= htmlspecialchars($produit['nom']) ?>"
                                                class="img-thumbnail me-3"
                                                style="width: 60px; height: 60px; object-fit: cover;"
                                            >
                                            <span><?= htmlspecialchars($produit['nom']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($produit['prix_ht'] * (1 + $produit['taux_tva'] / 100), 2, ',', ' ') ?> €
                                    </td>
                                    <td class="text-center"><?= htmlspecialchars($produit['quantite']) ?></td>
                                    <td class="text-end">
                                        <?= number_format($produit['prix_ht'] * (1 + $produit['taux_tva'] / 100) * $produit['quantite'], 2, ',', ' ') ?> €
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex container-fluid justify-right">
                    <a href="/commande/details/<?= $commande['id'] ?>" class="btn btn-sm btn-outline-primary">
                        Voir les détails <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
