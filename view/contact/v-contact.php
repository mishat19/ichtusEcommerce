<div class="container py-5">
    <!-- Section Titre -->
    <section class="mb-5 text-center">
        <h1 class="mb-2">Contactez-nous</h1>
        <p class="text-muted">Une question, une demande spéciale ? Nous sommes là pour vous aider !</p>
    </section>

    <!-- Section Informations de contact -->
    <section class="mb-5">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-light rounded-4">
                    <i class="fas fa-map-marker-alt fa-2x mb-3" style="color: var(--accent-color);"></i>
                    <h5>Adresse</h5>
                    <p class="mb-0">1 Rue du Général de Gaulle<br>80000 Amiens, France</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-light rounded-4">
                    <i class="fas fa-envelope fa-2x mb-3" style="color: var(--accent-color);"></i>
                    <h5>Email</h5>
                    <p class="mb-0">contact@patesdefruits.fr</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-light rounded-4">
                    <i class="fas fa-phone fa-2x mb-3" style="color: var(--accent-color);"></i>
                    <h5>Téléphone</h5>
                    <p class="mb-0">+33 3 23 45 67 89</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Formulaire de contact -->
    <section>
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <h3 class="mb-4">Envoyez-nous un message</h3>

                <!-- Message de succès -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px; border-left: 4px solid #22c55e;">
                        <i class="fas fa-check-circle me-3 fs-4" style="color: #22c55e;"></i>
                        <div>
                            <strong>Succès !</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Erreurs -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-1"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div class="row">
                        <!-- Nom -->
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control <?php echo isset($errors) && in_array("Le nom est obligatoire.", $errors) ? 'is-invalid' : ''; ?>"
                                   id="nom" name="nom"
                                   value="<?php echo htmlspecialchars(($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? '')); ?>"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control <?php echo isset($errors) && (in_array("L'email est invalide.", $errors) || in_array("Le nom est obligatoire.", $errors)) ? 'is-invalid' : ''; ?>"
                                   id="email" name="email"
                                   value="<?php echo htmlspecialchars($client['email'] ?? $_POST['email'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Sujet -->
                        <div class="col-12 mb-3">
                            <label for="sujet" class="form-label">Sujet *</label>
                            <select class="form-select <?php echo isset($errors) && in_array("Le sujet est obligatoire.", $errors) ? 'is-invalid' : ''; ?>"
                                    id="sujet" name="sujet" required>
                                <option value="" disabled <?php echo empty($_POST['sujet']) ? 'selected' : ''; ?>>Sélectionnez un sujet</option>
                                <option value="question" <?php echo (isset($_POST['sujet']) && $_POST['sujet'] === 'question') ? 'selected' : ''; ?>>Question sur un produit</option>
                                <option value="commande" <?php echo (isset($_POST['sujet']) && $_POST['sujet'] === 'commande') ? 'selected' : ''; ?>>Suivi de commande</option>
                                <option value="retour" <?php echo (isset($_POST['sujet']) && $_POST['sujet'] === 'retour') ? 'selected' : ''; ?>>Retour ou remboursement</option>
                                <option value="autre" <?php echo (isset($_POST['sujet']) && $_POST['sujet'] === 'autre') ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="col-12 mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control <?php echo isset($errors) && in_array("Le message est obligatoire.", $errors) ? 'is-invalid' : ''; ?>"
                                      id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <!-- Bouton d'envoi -->
                        <div class="col-12">
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Envoyer le message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>