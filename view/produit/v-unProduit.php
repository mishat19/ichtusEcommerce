<div class="container py-5">

    <!-- Bouton retour en haut -->
    <div class="mb-4">
        <a href="/produit" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à la boutique
        </a>
    </div>

    <?php if (isset($_SESSION['erreur_stock'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php e($_SESSION['erreur_stock']); unset($_SESSION['erreur_stock']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <div class="col-md-6">
            <img src="/images/<?php e($unProduit['image']); ?>" class="img-fluid product-img" alt="<?php e($unProduit['nom']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php e($unProduit['nom']); ?></h1>

            <p class="price my-3">
                <?php e(number_format(($unProduit['prix_ht'] / 100) * (1 + $unProduit['taux'] / 100), 2, ',', ' ')); ?>€ TTC
                <br>
                <small class="text-muted">
                    (<?php e(number_format($unProduit['prix_ht'] / 100, 2, ',', ' ')); ?>€ HT - TVA <?php e($unProduit['taux']); ?>%)
                </small>
            </p>

            <div class="mb-4">
                <h4>Description</h4>
                <p><?php e(nl2br(htmlspecialchars($unProduit['description']))); ?></p>
            </div>

            <?php
            $stockDispo = (int)($unProduit['stock_disponible'] ?? 0);
            $seuilAlerte = (int)($unProduit['seuil_alerte'] ?? 15);
            ?>

            <?php if ($stockDispo <= 0): ?>
                <!-- RUPTURE DE STOCK -->
                <div class="alert alert-danger d-flex align-items-center mb-3" role="alert" style="border-radius: 12px;">
                    <i class="fas fa-times-circle me-2 fs-5"></i>
                    <div>
                        <strong>Produit indisponible</strong> — Ce produit est actuellement en rupture de stock.
                    </div>
                </div>
                <button type="button" class="btn btn-secondary w-100 py-2" disabled style="border-radius: 10px; font-size: 1.05rem;">
                    <i class="fas fa-ban me-2"></i> En rupture de stock
                </button>

            <?php else: ?>

                <?php if ($stockDispo <= $seuilAlerte): ?>
                    <!-- STOCK FAIBLE -->
                    <div class="alert d-flex align-items-center mb-3" role="alert"
                         style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border: 1px solid #ffb74d; border-radius: 12px; color: #e65100;">
                        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                        <div>
                            <strong>Stock limité !</strong> — Plus que <strong><?php e($stockDispo); ?></strong> unité<?php echo $stockDispo > 1 ? 's' : ''; ?> en stock.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2 align-items-center">
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <div class="input-group" style="width: 120px;">
                            <input type="number" name="quantite" class="form-control" value="1" min="1" max="<?php e($stockDispo); ?>" required>
                        </div>
                        <input type="hidden" name="id_produit" value="<?php e($unProduit['id']); ?>">
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px;">
                            <i class="fas fa-shopping-cart me-2"></i> Ajouter au panier
                        </button>
                    </form>
                </div>

                <p class="text-muted small mt-2">
                    <i class="fas fa-box me-1"></i> <?php e($stockDispo); ?> en stock
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>