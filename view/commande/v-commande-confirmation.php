<?php
    $icon = "fa-check-circle text-success";
    $titre = "Commande confirmée";
    $message = "Votre paiement a été accepté.";

    if ($etat === "refuse") {
        $icon = "fa-times-circle text-danger";
        $titre = "Paiement refusé";
        $message = "Votre paiement a été refusé.";
    }

    if ($etat === "annule") {
        $icon = "fa-exclamation-circle text-warning";
        $titre = "Paiement annulé";
        $message = "Votre paiement a été annulé.";
    }
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- STEPS -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step"><div class="step-icon">1</div><div class="step-label">Récapitulatif</div></div>
                <div class="step"><div class="step-icon">2</div><div class="step-label">Adresses</div></div>
                <div class="step"><div class="step-icon">3</div><div class="step-label">Paiement</div></div>
                <div class="step active"><div class="step-icon">4</div><div class="step-label">Confirmation</div></div>
            </div>

            <!-- CONTENU -->
            <div class="card text-center p-5">
                <i class="fas <?= $icon ?> fa-4x mb-3"></i>
                <h2><?= $titre ?></h2>
                <p class="lead"><?= $message ?></p>

                <?php if ($etat === "confirme"): ?>
                    <p>
                        Commande n° <strong><?= htmlspecialchars($_SESSION['commande']['numero_facture']) ?></strong>
                    </p>
                <?php endif; ?>

                <div class="mt-4">
                    <?php if ($etat !== "confirme"): ?>
                        <a href="/recapitulatif" class="btn btn-warning">Réessayer</a>
                    <?php else: ?>
                        <a href="/commandes" class="btn btn-primary">Mes commandes</a>
                    <?php endif; ?>

                    <a href="/accueil" class="btn btn-outline-secondary">Accueil</a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    if (window.top !== window.self) {
        document.documentElement.style.display = 'none';
        window.top.location.href = window.self.location.href;
    }
</script>