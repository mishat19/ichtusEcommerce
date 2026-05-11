<div class="container py-5">
    <h1 class="text-center mb-5">Nos Pâtes de Fruits</h1>
    <div class="row g-4">
        <?php foreach ($lProduit as $produit): ?>
            <div class="col-md-4">
                <div class="card product-card h-100">
                    <img src="/images/<?php e($produit['image']); ?>" class="card-img-top" alt="<?php e($produit['nom']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-center"><?php e($produit['nom']); ?></h5>
                        <div class="mt-auto">
                            <p class="card-text text-center">
                                <?php e(number_format($produit['prix_ht'] / 100, 2, ',', ' ')); ?>€ HT
                                <br>
                                <small class="text-muted">TVA <?php e($produit['taux']); ?>%</small>
                            </p>
                            <p class="price text-center fw-bold fs-5 mb-0">
                                <?php e(number_format(($produit['prix_ht'] / 100) * (1 + $produit['taux'] / 100), 2, ',', ' ')); ?>€ TTC
                            </p>
                            <a href="/produit/<?php e($produit['identifiant']); ?>" class="btn btn-primary w-100 mt-3">
                                <i class="fas fa-eye me-2"></i> Voir le produit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
