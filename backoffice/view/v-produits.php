<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des produits</h1>
        <a href="/bo/produit/ajouter" class="btn btn-primary">
            + Ajouter un produit
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php if (empty($produits)): ?>
                <div class="alert alert-info">Aucun produit disponible</div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Identifiant</th>
                            <th>Prix HT</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($produits as $produit): ?>

                            <?php
                            $badge = $produit['statut'] === 'actif'
                                ? 'bg-success'
                                : 'bg-secondary';
                            ?>

                            <tr>
                                <td>#<?= $produit['id'] ?></td>

                                <td>
                                    <?php if ($produit['image']): ?>
                                        <img src="<?= htmlspecialchars($produit['image']) ?>"
                                             style="width:50px; height:50px; object-fit:cover;">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($produit['nom']) ?></td>

                                <td><?= htmlspecialchars($produit['identifiant']) ?></td>

                                <td>
                                    <?= number_format($produit['prix_ht'] / 100, 2, ',', ' ') ?> €
                                </td>

                                <td>
                                    <span class="badge <?= $badge ?>">
                                        <?= ucfirst($produit['statut']) ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="/bo/produit/<?= $produit['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        Modifier
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
</div>