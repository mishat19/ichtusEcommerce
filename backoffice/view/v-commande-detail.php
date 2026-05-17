<div class="bo-topbar mb-3">
    <h2 class="bo-topbar-title m-0">
        COMMANDE #<?php echo $bo_commande['id']; ?>
    </h2>

    <div class="mt-2">
        <span class="bo-badge bo-badge-<?php echo $bo_commande['statut']; ?>">
            <?php echo $bo_commande['statut']; ?>
        </span>
    </div>

    <div class="bo-breadcrumb mt-3">
        <a href="/backoffice">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="/backoffice/commandes">Commandes</a>
        <i class="bi bi-chevron-right"></i>
        <span>#<?php echo $bo_commande['id']; ?></span>
    </div>

</div>

    <div class="bo-content">

        <div class="row g-4">
            <!-- Infos facturation -->
            <div class="col-md-6">
                <div class="bo-card">
                    <div class="bo-card-title"><i class="bi bi-receipt"></i> Facturation</div>
                    <div class="bo-info-row">
                        <span class="label">Nom :</span>
                        <span class="value">
                            <strong><?php echo htmlspecialchars($bo_commande['fact_prenom'].' '.$bo_commande['fact_nom']); ?></strong>
                        </span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Email :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['fact_email']); ?></strong></span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Téléphone :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['fact_tel']); ?></strong></span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Adresse :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['fact_adresse']); ?></strong></span>
                    </div>

                    <?php if (!empty($bo_commande['fact_complement'])): ?>
                        <div class="bo-info-row">
                            <span class="label">Complément :</span>
                            <span class="value"><strong><?php echo htmlspecialchars($bo_commande['fact_complement']); ?></strong></span>
                        </div>
                    <?php endif; ?>

                    <div class="bo-info-row">
                        <span class="label">Ville :</span>
                        <span class="value">
                            <strong><?php echo htmlspecialchars($bo_commande['fact_cp'].' '.$bo_commande['fact_ville']); ?></strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Infos livraison -->
            <div class="col-md-6">
                <div class="bo-card">
                    <div class="bo-card-title"><i class="bi bi-truck"></i> Livraison</div>
                    <div class="bo-info-row">
                        <span class="label">Nom :</span>
                        <span class="value">
                            <strong><?php echo htmlspecialchars($bo_commande['liv_prenom'].' '.$bo_commande['liv_nom']); ?></strong>
                        </span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Email :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['liv_email']); ?></strong></span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Téléphone :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['liv_tel']); ?></strong></span>
                    </div>

                    <div class="bo-info-row">
                        <span class="label">Adresse :</span>
                        <span class="value"><strong><?php echo htmlspecialchars($bo_commande['liv_adresse']); ?></strong></span>
                    </div>

                    <?php if (!empty($bo_commande['liv_complement'])): ?>
                        <div class="bo-info-row">
                            <span class="label">Complément :</span>
                            <span class="value"><strong><?php echo htmlspecialchars($bo_commande['liv_complement']); ?></strong></span>
                        </div>
                    <?php endif; ?>

                    <div class="bo-info-row">
                        <span class="label">Ville :</span>
                        <span class="value">
                            <strong><?php echo htmlspecialchars($bo_commande['liv_cp'].' '.$bo_commande['liv_ville']); ?></strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Produits -->
            <div class="col-12">
                <div class="bo-card">
                    <div class="bo-card-title"><i class="bi bi-bag"></i> Produits commandés</div>
                    <table class="bo-table">
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>Produit</th>
                            <th>Identifiant</th>
                            <th>Qté</th>
                            <th>Prix HT</th>
                            <th>TVA</th>
                            <th>Prix TTC</th>
                            <th>Total TTC</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bo_commande_produits as $p) : ?>
                            <tr>
                                <td>
                                    <?php if ($p['image']) : ?>
                                        <img src="/images/<?php echo htmlspecialchars($p['image']); ?>"
                                             alt="" class="rounded border"
                                             style="width:40px; height:40px; object-fit:cover;" />
                                    <?php else : ?>
                                        <div style="width:40px; height:50px; border-radius:2px;
                                                display:flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($p['nom']); ?></td>
                                <td style="font-size:.72rem;"><?php echo htmlspecialchars($p['identifiant']); ?></td>
                                <td style="font-size:1.1rem;"><?php echo $p['quantite']; ?></td>
                                <td><?php echo number_format($p['prix_ht'], 2, ',', ' '); ?>€</td>
                                <td><?php echo $p['taux_tva']; ?>%</td>
                                <td><?php echo number_format($p['prix_ttc'], 2, ',', ' '); ?>€</td>
                                <td style="font-weight:700;">
                                    <?php echo number_format($p['prix_ttc'] * $p['quantite'], 2, ',', ' '); ?>€
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Récap financier + Paiement -->
            <div class="col-md-6">
                <div class="bo-card">
                    <div class="bo-card-title"><i class="bi bi-calculator"></i> Récapitulatif</div>
                    <div class="bo-info-row">
                        <span class="label">Frais de livraison</span>
                        <span class="value">
                            <?php echo number_format((float)($bo_commande['frais_livraison'] ?? 0), 2, ',', ' '); ?>€
                        </span>
                    </div>

                    <div class="bo-info-row mt-3">
                        <span class="label">Total :</span>
                        <span class="value" style="font-size:1.5rem; color: var(--bo-primary);">
                            <?php echo number_format((float)$bo_commande['total_ttc'], 2, ',', ' '); ?>€
                        </span>
                    </div>
                    <div class="bo-info-row">
                        <span class="label">Statut commande :</span>
                        <span class="value"><span class="bo-badge bo-badge-<?php echo $bo_commande['statut']; ?>"><?php echo $bo_commande['statut']; ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>