<div class="container py-5 text-center">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h2 class="mb-3">Merci pour votre commande !</h2>
                <p class="lead">
                    Votre commande a été enregistrée avec succès.
                </p>
            </div>
            <p class="mb-4">
                Un email de confirmation vous a été envoyé à
                <strong><?= htmlspecialchars($_SESSION['email'] ?? '') ?></strong>.
            </p>
            <div class="d-grid gap-2">
                <a href="/accueil" class="btn btn-primary btn-lg">
                    Retour à l'accueil
                </a>
                <a href="/commandes" class="btn btn-outline-secondary btn-lg">
                    Voir mes commandes
                </a>
            </div>
        </div>
    </div>
</div>
