<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Étapes -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step"><div class="step-icon">1</div><div class="step-label">Récapitulatif</div></div>
                <div class="step"><div class="step-icon">2</div><div class="step-label">Adresses</div></div>
                <div class="step"><div class="step-icon">3</div><div class="step-label">Paiement</div></div>
                <div class="step active"><div class="step-icon">4</div><div class="step-label">Confirmation</div></div>
            </div>

            <div class="card text-center p-5">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h2>Paiement validé</h2>
                <p class="lead">Votre commande a été confirmée.</p>

                <p>
                    Commande n° <strong><?= htmlspecialchars($_SESSION['commande']['numero']) ?></strong>
                </p>

                <div class="mt-4">
                    <a href="/commandes" class="btn btn-primary">Voir mes commandes</a>
                    <a href="/accueil" class="btn btn-outline-secondary">Accueil</a>
                </div>
            </div>

        </div>
    </div>
</div>