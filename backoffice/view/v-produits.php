    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">Catalogue Produits</h2>
            <p class="text-muted small mb-0">Gérez votre inventaire et les détails de vos articles.</p>
        </div>
        <a href="/bo/produit/ajouter" class="bo-btn-outline">
            <i class="bi bi-plus-lg me-1"></i> Nouveau produit
        </a>
    </div>

    <div class="bo-content">

        <div class="bo-card p-0 overflow-hidden">
            <?php if (empty($produits)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-box-seam fs-1 mb-3 d-block"></i>
                    <p>Aucun produit n'a été trouvé dans votre catalogue.</p>
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="bo-table mb-0">
                        <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Aperçu</th>
                            <th>Nom du produit</th>
                            <th>Identifiant (SKU)</th>
                            <th>Prix HT</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($produits as $produit): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $produit['id']; ?></td>

                                <td>
                                    <?php if ($produit['image']): ?>
                                        <img src="/<?php echo htmlspecialchars($produit['image']); ?>"
                                             class="rounded border"
                                             style="width:40px; height:40px; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                            <i class="bi bi-image text-muted small"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($produit['nom']); ?></td>

                                <td><code class="small text-muted"><?php echo htmlspecialchars($produit['identifiant']); ?></code></td>

                                <td class="fw-bold text-dark">
                                    <?php echo number_format($produit['prix_ht'] / 100, 2, ',', ' '); ?> €
                                </td>

                                <td>
                                    <span class="badge rounded-pill <?php echo $produit['statut'] === 'actif' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>" style="font-size: 0.7rem; padding: 0.4em 0.8em;">
                                        <?php echo strtoupper($produit['statut']); ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    <a href="/bo/produit/<?php echo $produit['id']; ?>"
                                       class="btn btn-light btn-sm fw-medium">
                                        <i class="bi bi-pencil me-1"></i> Modifier
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>