<div class="container py-5">
    <!-- Titre de la page -->
    <h1 class="mb-4">Votre panier</h1>

    <!-- Bouton retour -->
    <div class="mb-4">
        <a href="/produit" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Continuer vos achats
        </a>
    </div>

    <!-- Message si le panier est vide -->
    <?php if ($panier['nb_lignes'] == 0): ?>
        <div class="alert alert-info">
            Votre panier est vide.
        </div>
    <?php else: ?>
        <!-- Tableau des produits dans le panier -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-light">
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
                                    <h5 class="mb-1"><?= htmlspecialchars($ligne['nom']) ?></h5>
                                    <small class="text-muted">Réf: <?= htmlspecialchars($ligne['identifiant']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100), 2, ',', ' ') ?>€
                        </td>
                        <td>
                            <form method="POST" action="/panier" class="d-flex align-items-center">
                                <input type="hidden" name="id_ligne" value="<?= $ligne['id_ligne'] ?>">
                                <div class="input-group" style="width: 100px;">
                                    <input type="number" name="quantite" class="form-control" value="<?= $ligne['quantite'] ?>" min="1" max="99" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <?= number_format(($ligne['prix_ht'] / 100) * (1 + $ligne['taux_tva'] / 100) * $ligne['quantite'], 2, ',', ' ') ?>€
                        </td>
                        <td>
                            <form method="POST" action="/panier" onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                                <input type="hidden" name="id_ligne" value="<?= $ligne['id_ligne'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Résumé du panier -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Récapitulatif</h5>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Nombre d'articles</span>
                        <span><?= $panier['nb_articles'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total HT</span>
                        <span><?= number_format($panier['total_ht'] / 100, 2, ',', ' ') ?>€</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total TTC</span>
                        <span class="fw-bold"><?= number_format($panier['total_ttc'] / 100, 2, ',', ' ') ?>€</span>
                    </li>
                </ul>
                <div class="d-grid gap-2">
                    <a href="/panier" class="btn btn-primary">Valider le panier</a>
                    <form method="POST" action="/accueil" onsubmit="return confirm('Voulez-vous vraiment vider votre panier ?');">
                        <input type="hidden" name="vider_panier" value="1">
                        <button type="submit" class="btn btn-outline-danger">Vider le panier</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
