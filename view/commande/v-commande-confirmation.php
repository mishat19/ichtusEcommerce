<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h2 class="mb-3">Merci pour votre commande !</h2>
                <p class="lead">
                    Votre commande n°<strong><?= htmlspecialchars($_SESSION['commande']['numero']) ?></strong>
                    a été enregistrée avec succès.
                </p>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Récapitulatif de votre commande</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Adresse de facturation</h6>
                        <?php
                        $adresseFacturation = getAdresseById($_SESSION['commande']['adresse_facturation']);
                        if ($adresseFacturation): ?>
                            <p class="mb-0">
                                <strong><?= htmlspecialchars($adresseFacturation['prenom'] . ' ' . $adresseFacturation['nom']) ?></strong><br>
                                <?= htmlspecialchars($adresseFacturation['adresse']) ?><br>
                                <?= htmlspecialchars($adresseFacturation['complement']) ?><br>
                                <?= htmlspecialchars($adresseFacturation['code_postal'] . ' ' . $adresseFacturation['ville']) ?><br>
                                <?= htmlspecialchars($adresseFacturation['telephone']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h6>Adresse de livraison</h6>
                        <?php
                        $adresseLivraison = getAdresseById($_SESSION['commande']['adresse_livraison']);
                        if ($adresseLivraison): ?>
                            <p class="mb-0">
                                <strong><?= htmlspecialchars($adresseLivraison['prenom'] . ' ' . $adresseLivraison['nom']) ?></strong><br>
                                <?= htmlspecialchars($adresseLivraison['adresse']) ?><br>
                                <?= htmlspecialchars($adresseLivraison['complement']) ?><br>
                                <?= htmlspecialchars($adresseLivraison['code_postal'] . ' ' . $adresseLivraison['ville']) ?><br>
                                <?= htmlspecialchars($adresseLivraison['telephone']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/accueil" class="btn btn-primary">Retour à l'accueil</a>
                        <a href="/commandes" class="btn btn-outline-secondary">Voir mes commandes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
