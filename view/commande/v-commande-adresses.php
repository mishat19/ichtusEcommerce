<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Étapes de la commande (même code que précédemment) -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step">
                    <div class="step-icon">1</div>
                    <div class="step-label">Récapitulatif</div>
                </div>
                <div class="step active">
                    <div class="step-icon">2</div>
                    <div class="step-label">Adresses</div>
                </div>
                <div class="step">
                    <div class="step-icon">3</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step">
                    <div class="step-icon">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <h2 class="mb-4">Adresses de livraison et facturation</h2>

            <form method="POST" action="/commande-paiement">
                <!-- Adresse de facturation -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Adresse de Facturation</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $adressesFacturation = getAdressesByType($_SESSION['idClient'], 'facturation');
                        if (empty($adressesFacturation)): ?>
                            <div class="alert alert-warning">
                                Vous n'avez pas encore d'adresse de facturation enregistrée.
                                <a href="/profil" class="btn btn-sm btn-primary">Ajouter une adresse</a>
                            </div>
                        <?php else:
                            foreach ($adressesFacturation as $adresse): ?>
                                <div class="form-check mb-3 p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="id_adresse_facturation"
                                           id="facturation-<?= $adresse['id'] ?>" value="<?= $adresse['id'] ?>"
                                        <?= empty($selectedFacturation) && $adresse['est_par_defaut'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="facturation-<?= $adresse['id'] ?>">
                                        <strong><?= htmlspecialchars($adresse['prenom'] . ' ' . $adresse['nom']) ?></strong><br>
                                        <?= htmlspecialchars($adresse['adresse']) ?><br>
                                        <?= htmlspecialchars($adresse['complement']) ?><br>
                                        <?= htmlspecialchars($adresse['code_postal'] . ' ' . $adresse['ville']) ?><br>
                                        <?= htmlspecialchars($adresse['telephone']) ?>
                                    </label>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- Adresse de livraison -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Adresse de Livraison</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $adressesLivraison = getAdressesByType($_SESSION['idClient'], 'livraison');
                        if (empty($adressesLivraison)): ?>
                            <div class="alert alert-warning">
                                Vous n'avez pas encore d'adresse de livraison enregistrée.
                                <a href="/profil" class="btn btn-sm btn-primary">Ajouter une adresse</a>
                            </div>
                        <?php else:
                            foreach ($adressesLivraison as $adresse): ?>
                                <div class="form-check mb-3 p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="id_adresse_livraison"
                                           id="livraison-<?= $adresse['id'] ?>" value="<?= $adresse['id'] ?>"
                                        <?= empty($selectedLivraison) && $adresse['est_par_defaut'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="livraison-<?= $adresse['id'] ?>">
                                        <strong><?= htmlspecialchars($adresse['prenom'] . ' ' . $adresse['nom']) ?></strong><br>
                                        <?= htmlspecialchars($adresse['adresse']) ?><br>
                                        <?= htmlspecialchars($adresse['complement']) ?><br>
                                        <?= htmlspecialchars($adresse['code_postal'] . ' ' . $adresse['ville']) ?><br>
                                        <?= htmlspecialchars($adresse['telephone']) ?>
                                    </label>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>

                <!-- Boutons de navigation -->
                <div class="d-flex justify-content-between">
                    <a href="/commande/recapitulatif" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continuer vers le paiement <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
