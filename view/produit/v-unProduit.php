<div class="container py-5">

    <!-- Bouton retour en haut -->
    <div class="mb-4">
        <a href="/produit" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la boutique
        </a>
    </div>

    <div class="row g-5">
        <div class="col-md-6">
            <img src="/images/<?= htmlspecialchars($unProduit['image']) ?>" class="img-fluid product-img" alt="<?= htmlspecialchars($unProduit['nom']) ?>">
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($unProduit['nom']) ?></h1>

            <p class="price my-3">
                <?= number_format(($unProduit['prix_ht'] / 100) * (1 + $unProduit['taux'] / 100), 2, ',', ' ') ?>€ TTC
                <br>
                <small class="text-muted">
                    (<?= number_format($unProduit['prix_ht'] / 100, 2, ',', ' ') ?>€ HT - TVA <?= $unProduit['taux'] ?>%)
                </small>
            </p>

            <div class="mb-4">
                <h4>Description</h4>
                <p><?= nl2br(htmlspecialchars($unProduit['description'])) ?></p>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <form method="POST" action="/panier" class="d-flex gap-2 align-items-center">
                    <div class="input-group" style="width: 120px;">
                        <input type="number" name="quantite" class="form-control" value="1" min="1" max="99" required>
                    </div>
                    <input type="hidden" name="id_produit" value="<?= htmlspecialchars($unProduit['id']) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i> Ajouter au panier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>