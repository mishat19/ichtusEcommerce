<div class="container py-5">
    <!-- Section Profil Utilisateur -->
    <section class="mb-5">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <!-- Photo de profil + Infos -->
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <!-- Photo de profil arrondie (par défaut) -->
                            <img
                                    src="https://via.placeholder.com/150"
                                    alt="Photo de profil"
                                    class="rounded-circle me-4"
                                    style="width: 120px; height: 120px; object-fit: cover; border: 3px solid var(--accent-color);"
                            >
                            <!-- Infos utilisateur -->
                            <div>
                                <h2 class="mb-1">
                                    <?= htmlspecialchars($client['prenom'] ?? 'Prénom') ?>
                                    <?= htmlspecialchars($client['nom'] ?? 'Nom') ?>
                                </h2>
                                <p class="text-muted mb-2">
                                    Âge non renseigné
                                </p>
                                <div class="d-flex align-items-center">
                                    <span class="fi fi-fr fis me-2"></span>
                                    <span>France</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Wallet -->
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="bg-light p-3 rounded-3">
                            <h5 class="mb-1">Solde Wallet</h5>
                            <p class="fs-3 fw-bold mb-0">
                                0,00 €
                            </p>
                            <a href="/wallet" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-wallet me-1"></i> Recharger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Mes Commandes -->
    <section class="mb-5">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Mes Commandes</h4>
                        <p class="text-muted mb-0">Historique de vos achats</p>
                    </div>
                    <a href="/commandes" class="btn btn-primary">
                        Voir l'historique <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Mes Adresses -->
    <section class="mb-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h4 class="mb-3">Mes adresses</h4>

                <div class="row">
                    <?php if (empty($adresses)) : ?>
                        <!-- 🟢 Aucune adresse -->
                        <div class="col-md-4">
                            <div class="card border-dashed text-center p-4">
                                <p class="mb-3">Aucune adresse enregistrée</p>
                                <button class="btn btn-primary" onclick="document.getElementById('formAdresse').style.display='block'">
                                    Ajouter une adresse
                                </button>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- 🟢 Liste des adresses -->
                        <?php foreach ($adresses as $adresse) : ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h6>
                                            <?= htmlspecialchars($adresse['prenom']) ?>
                                            <?= htmlspecialchars($adresse['nom']) ?>
                                        </h6>

                                        <p class="mb-1"><?= htmlspecialchars($adresse['adresse']) ?></p>
                                        <p class="mb-1"><?= htmlspecialchars($adresse['code_postal']) ?> <?= htmlspecialchars($adresse['ville']) ?></p>

                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($adresse['type']) ?>
                                        </span>

                                        <?php if ($adresse['est_par_defaut']) : ?>
                                            <span class="badge bg-success">Par défaut</span>
                                        <?php endif; ?>

                                        <!-- Boutons Modifier et Supprimer -->
                                        <div class="mt-2 d-flex gap-2">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?= $adresse['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    Modifier
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $adresse['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette adresse ?')">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- ➕ Ajouter -->
                        <div class="col-md-4">
                            <div class="card border-dashed text-center p-4">
                                <button class="btn btn-outline-primary" onclick="document.getElementById('formAdresse').style.display='block'">
                                    + Ajouter
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 🧾 FORMULAIRE -->
                <div id="formAdresse" style="display:<?= $adresseEdit ? 'block' : 'none' ?>;" class="mt-4">
                    <form method="POST">
                        <?php if ($adresseEdit) : ?>
                            <input type="hidden" name="id" value="<?= $adresseEdit['id'] ?>">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" name="prenom" class="form-control" placeholder="Prénom"
                                       value="<?= htmlspecialchars($adresseEdit['prenom'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" name="nom" class="form-control" placeholder="Nom"
                                       value="<?= htmlspecialchars($adresseEdit['nom'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="email" name="email" class="form-control" placeholder="Email"
                                       value="<?= htmlspecialchars($adresseEdit['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="text" name="telephone" class="form-control" placeholder="Téléphone"
                                       value="<?= htmlspecialchars($adresseEdit['telephone'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-12 mb-2">
                                <input type="text" name="adresse" class="form-control" placeholder="Adresse"
                                       value="<?= htmlspecialchars($adresseEdit['adresse'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-12 mb-2">
                                <input type="text" name="complement" class="form-control" placeholder="Complément"
                                       value="<?= htmlspecialchars($adresseEdit['complement'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="text" name="code_postal" class="form-control" placeholder="Code postal"
                                       value="<?= htmlspecialchars($adresseEdit['code_postal'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="text" name="ville" class="form-control" placeholder="Ville"
                                       value="<?= htmlspecialchars($adresseEdit['ville'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <select name="type" class="form-select" required>
                                    <option value="livraison" <?= (isset($adresseEdit['type']) && $adresseEdit['type'] === 'livraison') ? 'selected' : '' ?>>Livraison</option>
                                    <option value="facturation" <?= (isset($adresseEdit['type']) && $adresseEdit['type'] === 'facturation') ? 'selected' : '' ?>>Facturation</option>
                                </select>
                            </div>

                            <!-- ⭐ Adresse par défaut -->
                            <div class="col-md-6 mb-2 d-flex align-items-center">
                                <input type="checkbox" name="est_par_defaut" class="form-check-input me-2"
                                        <?= (isset($adresseEdit['est_par_defaut']) && $adresseEdit['est_par_defaut']) ? 'checked' : '' ?>>
                                <label>Adresse par défaut</label>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-success">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>