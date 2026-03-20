<!-- Carrousel -->
<section class="hero-section">
    <div class="hero-background" style="background-image: url('https://s2.qwant.com/thumbr/474x258/d/c/5114ed50880aaa9bc153bbb3f8596f82d19b95202bafd6712b8c2266e4f7db/OIP.NOnenxm6RnboC5qdCL0j-QHaEC.jpg?u=https%3A%2F%2Ftse.mm.bing.net%2Fth%2Fid%2FOIP.NOnenxm6RnboC5qdCL0j-QHaEC%3Fpid%3DApi&q=0&b=1&p=0&a=0');"></div>
    <div class="container text-center position-relative">
        <h1 class="display-4 fw-bold text-white">Pâtes de Fruits Artisanales</h1>
        <p class="lead text-white">Découvrez nos créations 100% naturelles, sans colorants ni conservateurs.</p>
        <a href="/produit" class="btn btn-light btn-lg mt-3">Découvrir la boutique</a>
    </div>
</section>

<!-- Best-Sellers -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Nos Best-Sellers</h2>
        <div class="row g-4">
            <?php
            $bestSellers = getBestSellers();
            foreach ($bestSellers as $produit) :
                ?>
                <div class="col-md-4">
                    <div class="card product-card h-100">
                        <img src="/images/<?= htmlspecialchars($produit['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produit['nom']) ?>">
                        <div class="card-body d-flex flex-column align-items-center text-center">
                            <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                            <p class="fw-bold mt-2"><?= number_format($produit['prix_ttc'] / 100, 2, ',', ' ') ?>€ / 100g</p>
                            <form method="GET" action="/produit/<?= $produit['identifiant'] ?>" class="mt-3 w-100">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i> Voir le produit
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- À Propos -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2>Notre Engagement</h2>
                <p class="lead">
                    Depuis 1985, nous sélectionnons les meilleurs fruits pour créer des pâtes de fruits d'exception.
                    Nos recettes sont élaborées sans colorants, sans conservateurs et avec un savoir-faire artisanal.
                </p>
                <a href="#" class="btn btn-outline-primary">En savoir plus</a>
            </div>
            <div class="col-lg-6">
                <img src="https://s1.qwant.com/thumbr/474x289/1/c/a248b3eb11dec256e5385678d0922cc8c4b0b3036b61c2cc774449623ee768/OIP.b2c6KlOebGPEcasnIcgdegHaEh.jpg?u=https%3A%2F%2Ftse.mm.bing.net%2Fth%2Fid%2FOIP.b2c6KlOebGPEcasnIcgdegHaEh%3Fpid%3DApi&q=0&b=1&p=0&a=0" alt="Artisan au travail" class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>

<!-- Témoignages -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Ils nous font confiance</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial h-100 d-flex flex-column">
                    <p class="text-center flex-grow-1">"Les meilleures pâtes de fruits que j'aie jamais goûtées !"</p>
                    <div class="text-center mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="text-center mt-auto fw-bold">Marie L.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial h-100 d-flex flex-column">
                    <p class="text-center flex-grow-1">"Un vrai délice, et un emballage magnifique pour offrir."</p>
                    <div class="text-center mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star-half-alt text-warning"></i>
                    </div>
                    <p class="text-center mt-auto fw-bold">Pierre D.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial h-100 d-flex flex-column">
                    <p class="text-center flex-grow-1">"Service impeccable et produits toujours frais."</p>
                    <div class="text-center mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="text-center mt-auto fw-bold">Sophie R.</p>
                </div>
            </div>
        </div>
    </div>
</section>
