<section class="auth-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <!-- Branding / Logo Area -->
                <div class="text-center mb-5">
                    <h1 class="display-6 fw-bold" style="color: var(--primary-color);">Les Délices Fruités</h1>
                    <div class="mx-auto" style="width: 60px; height: 4px; background: var(--accent-color); border-radius: 2px;"></div>
                </div>

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h2 class="h4 fw-bold">Créer un compte</h2>
                            <p class="text-muted small">Rejoignez notre communauté de gourmets</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 rounded-3 mb-4 text-center">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php e($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="/inscription/" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="prenom" class="form-label small fw-bold text-uppercase tracking-wider">Prénom</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" id="prenom" name="prenom" placeholder="Jean" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="nom" class="form-label small fw-bold text-uppercase tracking-wider">Nom</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" id="nom" name="nom" placeholder="Dupont" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold text-uppercase tracking-wider">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control form-control-lg bg-light border-0" id="email" name="email" placeholder="votre@email.com" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-uppercase tracking-wider">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control form-control-lg bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                                </div>
                                <div class="form-text small mt-2">Minimum 8 caractères conseillés.</div>
                            </div>

                            <div class="d-grid mb-4 pt-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold py-3 rounded-pill shadow-sm transition-all">
                                    Créer mon compte <i class="fas fa-user-plus ms-2"></i>
                                </button>
                            </div>

                            <div class="text-center border-top pt-4">
                                <p class="text-muted small mb-0">Déjà client ? <a href="/connexion" class="fw-bold text-decoration-none" style="color: var(--accent-color);">Connectez-vous ici</a></p>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="/accueil" class="text-muted text-decoration-none small"><i class="fas fa-long-arrow-alt-left me-1"></i> Retour à la boutique</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .auth-wrapper {
        background-color: var(--secondary-color);
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
    }
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(45, 90, 39, 0.1);
        border-color: var(--primary-color) !important;
    }
    .tracking-wider {
        letter-spacing: 0.05em;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(45, 90, 39, 0.2);
    }
</style>
