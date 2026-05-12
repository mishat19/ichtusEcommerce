<div class="container py-5">
    <h1 class="text-center mb-5">Nos Pâtes de Fruits</h1>
    <div class="row g-4">
        <?php foreach ($lProduit as $produit): ?>
            <?php $stockDispo = (int)($produit['stock_disponible'] ?? 0); ?>
            <div class="col-md-4">
                <div class="card product-card h-100" style="position: relative; <?php echo $stockDispo <= 0 ? 'opacity: 0.75;' : ''; ?>">

                    <?php if ($stockDispo <= 0): ?>
                        <div style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                            <span class="badge" style="background: #dc3545; color: white; font-size: 0.75rem; padding: 0.5em 0.8em; border-radius: 8px;">
                                <i class="fas fa-times-circle me-1"></i> Rupture de stock
                            </span>
                        </div>
                    <?php endif; ?>

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

                            <?php if ($stockDispo <= 0): ?>
                                <button class="btn btn-secondary w-100 mt-3" disabled style="border-radius: 10px;">
                                    <i class="fas fa-ban me-2"></i> En rupture de stock
                                </button>
                            <?php else: ?>
                                <?php if ($stockDispo <= 15): ?>
                                    <p class="text-center mt-2 mb-0">
                                        <small style="color: #e65100; font-weight: 600;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Plus que <?php e($stockDispo); ?> en stock
                                        </small>
                                    </p>
                                <?php endif; ?>
                                <a href="/produit/<?php e($produit['identifiant']); ?>" class="btn btn-primary w-100 mt-3" style="border-radius: 10px;">
                                    <i class="fas fa-eye me-2"></i> Voir le produit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
