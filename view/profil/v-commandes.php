<div class="container py-5">
    <h1 class="mb-4">Mon Historique de Commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info text-center">
            Vous n'avez pas encore passé de commande.
        </div>
    <?php else: ?>

        <?php foreach ($commandes as $commande): ?>

            <?php
            $statut = $commande['statut'];

            $badgeClass = "bg-secondary";
            $label = htmlspecialchars($statut);

            if ($statut === "payee") {
                $badgeClass = "bg-success";
                $label = "Confirmée";
            }
            elseif ($statut === "refusee") {
                $badgeClass = "bg-danger";
                $label = "Refusée";
            }
            elseif ($statut === "annulee") {
                $badgeClass = "bg-warning text-dark";
                $label = "Annulée";
            }
            elseif ($statut === "en_attente") {
                $badgeClass = "bg-warning text-dark";
                $label = "En attente";
            }
            ?>

            <!-- Card Commande -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-0">
                            Commande n°<?php e($commande['numero_facture']); ?>
                        </h5>
                        <small class="text-muted">
                            Passée le <?php e((new DateTime($commande['date_commande']))->format('d/m/Y')); ?>
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-3 text-md-end">
                        <span class="fw-bold">
                            <?php e(number_format($commande['total_ttc'], 2, ',', ' ')); ?> €
                        </span>
                        <span class="badge <?php e($badgeClass); ?>">
                            <?php e($label); ?>
                        </span>
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
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($commande['produits'] as $produit): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img
                                                    src="/images/<?php e($produit['image']); ?>"
                                                    class="img-thumbnail me-3"
                                                    style="width: 60px; height: 60px; object-fit: cover;"
                                            >
                                            <span><?php e($produit['nom']); ?></span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <?php e(number_format(
                                                ($produit['prix_ht'] / 100) * (1 + $produit['taux_tva'] / 100),
                                                2,
                                                ',',
                                                ' '
                                        )); ?> €
                                    </td>

                                    <td class="text-center">
                                        <?php e($produit['quantite']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>
</div>