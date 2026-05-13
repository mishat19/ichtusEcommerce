<?php
// Démarre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour générer un token CSRF (si elle n'existe pas déjà)
if (!function_exists('csrf_field')) {
    function csrf_field() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}
?>

<!-- EN-TÊTE -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-0">
            <i class="fas fa-warehouse me-2" style="color: var(--bo-primary);"></i>
            Entrepôts & Stockage
        </h2>
        <p class="text-muted small mb-0">
            Gestion des entrepôts, meubles et stacks.
        </p>
    </div>
    <div class="d-flex gap-2">
        <button
                type="button"
                class="btn"
                data-bs-toggle="modal"
                data-bs-target="#modalEntrepot"
                style="background: var(--bo-primary); color: #fff; border-radius: 10px;"
        >
            <i class="fas fa-plus me-2"></i>
            Nouvel entrepôt
        </button>
        <a href="/backoffice/stock-ajout" class="btn" style="background: var(--bo-primary); color: #fff; border-radius: 10px; padding: 0.6rem 1.2rem; font-weight: 600;">
            <i class="fas fa-boxes-stacked me-2"></i> Ajouter du stock
        </a>
    </div>
</div>

<!-- ALERTES -->
<?php if (!empty($messageSucces)): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($messageSucces); ?>
    </div>
<?php endif; ?>

<?php if (!empty($messageErreur)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($messageErreur); ?>
    </div>
<?php endif; ?>

<!-- LISTE ENTREPÔTS -->
<?php if (empty($entrepotsList)): ?>
    <div class="bo-card text-center p-5">
        <i class="fas fa-warehouse fs-1 text-muted mb-3 d-block"></i>
        <p class="text-muted">Aucun entrepôt configuré.</p>
    </div>
<?php else: ?>
    <?php foreach ($entrepotsList as $entrepot): ?>
        <?php
        $tauxE = $entrepot['taux_occupation'];
        $colorE = $tauxE >= 80 ? '#ef4444' : ($tauxE >= 50 ? '#f59e0b' : '#10b981');
        ?>
        <div class="bo-card mb-4">
            <!-- En-tête de l'entrepôt avec actions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($entrepot['nom']); ?></h4>
                    <div class="text-muted small">
                        <?php echo htmlspecialchars($entrepot['adresse']); ?> — <?php echo htmlspecialchars($entrepot['ville']); ?>
                        <?php if ($entrepot['capacite_totale'] > 0): ?>
                            <br>
                            <span class="text-muted">
                    <?php echo count($entrepot['meubles']); ?> / <?php echo htmlspecialchars($entrepot['capacite_totale']); ?> meubles
                </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <!-- Bouton pour ajouter un meuble -->
                    <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAjoutMeuble-<?php echo $entrepot['id']; ?>"
                    >
                        <i class="fas fa-plus me-1"></i> Ajouter meuble
                    </button>
                    <!-- Boutons Modifier/Supprimer entrepôt -->
                    <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditEntrepot-<?php echo $entrepot['id']; ?>"
                    >
                        <i class="fas fa-edit me-1"></i> Modifier
                    </button>
                    <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDeleteEntrepot-<?php echo $entrepot['id']; ?>"
                    >
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </div>
            </div>

            <!-- Barre de progression -->
            <div class="progress mb-4" style="height:8px;">
                <div class="progress-bar" style="width: <?php echo $tauxE; ?>%; background: <?php echo $colorE; ?>;"></div>
            </div>

            <!-- Affichage des meubles -->
            <div class="row g-3">
                <?php if (empty($entrepot['meubles'])): ?>
                    <div class="col-12">
                        <div class="bo-card text-center p-4">
                            <i class="fas fa-couch fs-3 text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucun meuble dans cet entrepôt.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($entrepot['meubles'] as $meuble): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="border rounded p-3 h-100">
                                <!-- En-tête du meuble avec actions -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($meuble['nom']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo count($meuble['stacks']); ?> / <?php echo htmlspecialchars($meuble['capacite_max'] ?? '∞'); ?> stacks
                                        </small>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <!-- Boutons existants -->
                                        <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAjoutStack-<?php echo $meuble['id']; ?>">
                                            <i class="fas fa-plus"></i> Stack
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditMeuble-<?php echo $meuble['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteMeuble-<?php echo $meuble['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Affichage des stacks -->
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if (empty($meuble['stacks'])): ?>
                                        <div class="text-muted small text-center w-100 py-2">
                                            Aucun stack dans ce meuble.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($meuble['stacks'] as $stack): ?>
                                            <?php
                                            $tauxS = $stack['taux_occupation'];
                                            $bg = $tauxS >= 80 ? '#fee2e2' : ($tauxS >= 50 ? '#fef3c7' : '#dcfce7');
                                            $border = $tauxS >= 80 ? '#ef4444' : ($tauxS >= 50 ? '#f59e0b' : '#10b981');
                                            ?>
                                            <div style="background: <?php echo $bg; ?>; border:2px solid <?php echo $border; ?>; border-radius:12px; padding:.75rem; min-width:90px; text-align:center; position: relative;">
                                                <div class="small text-muted"><?php echo htmlspecialchars($stack['nom']); ?></div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($stack['capacite_utilisee']); ?> / <?php echo htmlspecialchars($stack['capacite_max']); ?>
                                                </div>
                                                <!-- Boutons Modifier/Supprimer stack -->
                                                <div class="mt-1 d-flex justify-content-center gap-1">
                                                    <button
                                                            type="button"
                                                            class="btn btn-xs btn-outline-secondary p-0"
                                                            style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditStack-<?php echo $stack['id']; ?>"
                                                    >
                                                        <i class="fas fa-edit" style="font-size: 0.6rem;"></i>
                                                    </button>
                                                    <button
                                                            type="button"
                                                            class="btn btn-xs btn-outline-danger p-0"
                                                            style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalDeleteStack-<?php echo $stack['id']; ?>"
                                                    >
                                                        <i class="fas fa-trash" style="font-size: 0.6rem;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- MODALES POUR CET ENTREPÔT -->
        <!-- Modale pour ajouter un meuble -->
        <div class="modal fade" id="modalAjoutMeuble-<?php echo $entrepot['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="add_meuble">
                        <input type="hidden" name="id_entrepot" value="<?php echo $entrepot['id']; ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter un meuble</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nom du meuble *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Capacité maximale (nombre de stacks) *</label>
                                <input type="number" name="capacite_max" class="form-control" min="1" value="10" required>
                                <small class="text-muted">Nombre maximum de stacks que ce meuble peut contenir.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modale pour modifier un entrepôt -->
        <div class="modal fade" id="modalEditEntrepot-<?php echo $entrepot['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="edit_entrepot">
                        <input type="hidden" name="id" value="<?php echo $entrepot['id']; ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'entrepôt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($entrepot['nom']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Capacité totale</label>
                                    <input type="number" name="capacite_totale" class="form-control" min="0" value="<?php echo htmlspecialchars($entrepot['capacite_totale']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Adresse *</label>
                                    <input type="text" name="adresse" class="form-control" value="<?php echo htmlspecialchars($entrepot['adresse']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Code postal *</label>
                                    <input type="text" name="code_postal" class="form-control" value="<?php echo htmlspecialchars($entrepot['code_postal']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ville *</label>
                                    <input type="text" name="ville" class="form-control" value="<?php echo htmlspecialchars($entrepot['ville']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pays</label>
                                    <input type="text" name="pays" class="form-control" value="<?php echo htmlspecialchars($entrepot['pays']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modale pour supprimer un entrepôt -->
        <div class="modal fade" id="modalDeleteEntrepot-<?php echo $entrepot['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="delete_entrepot">
                        <input type="hidden" name="id" value="<?php echo $entrepot['id']; ?>">

                        <div class="modal-header">
                            <h5 class="modal-title">Supprimer l'entrepôt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Êtes-vous sûr de vouloir supprimer l'entrepôt <strong><?php echo htmlspecialchars($entrepot['nom']); ?></strong> ?</p>
                            <p class="text-danger">Cette action est irréversible et supprimera tous les meubles et stacks associés.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modales pour les meubles de cet entrepôt -->
        <?php foreach ($entrepot['meubles'] as $meuble): ?>
            <!-- Modale pour ajouter un stack -->
            <div class="modal fade" id="modalAjoutStack-<?php echo $meuble['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="add_stack">
                            <input type="hidden" name="id_meuble" value="<?php echo $meuble['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Ajouter un stack</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nom du stack *</label>
                                    <input type="text" name="nom" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Capacité maximale *</label>
                                    <input type="number" name="capacite_max" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modale pour modifier un meuble -->
            <div class="modal fade" id="modalEditMeuble-<?php echo $meuble['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="edit_meuble">
                            <input type="hidden" name="id" value="<?php echo $meuble['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Modifier le meuble</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nom du meuble *</label>
                                    <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($meuble['nom']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Capacité maximale (nombre de stacks) *</label>
                                    <input type="number" name="capacite_max" class="form-control" min="1" value="<?php echo htmlspecialchars($meuble['capacite_max'] ?? 10); ?>" required>
                                    <small class="text-muted">Nombre maximum de stacks que ce meuble peut contenir.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modale pour supprimer un meuble -->
            <div class="modal fade" id="modalDeleteMeuble-<?php echo $meuble['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="delete_meuble">
                            <input type="hidden" name="id" value="<?php echo $meuble['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Supprimer le meuble</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer le meuble <strong><?php echo htmlspecialchars($meuble['nom']); ?></strong> ?</p>
                                <p class="text-danger">Cette action supprimera tous les stacks associés.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modales pour les stacks de ce meuble -->
            <?php foreach ($meuble['stacks'] as $stack): ?>
                <!-- Modale pour modifier un stack -->
                <div class="modal fade" id="modalEditStack-<?php echo $stack['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="edit_stack">
                                <input type="hidden" name="id" value="<?php echo $stack['id']; ?>">

                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier le stack</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nom du stack *</label>
                                        <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($stack['nom']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Capacité maximale *</label>
                                        <input type="number" name="capacite_max" class="form-control" min="1" value="<?php echo htmlspecialchars($stack['capacite_max']); ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modale pour supprimer un stack -->
                <div class="modal fade" id="modalDeleteStack-<?php echo $stack['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_stack">
                                <input type="hidden" name="id" value="<?php echo $stack['id']; ?>">

                                <div class="modal-header">
                                    <h5 class="modal-title">Supprimer le stack</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir supprimer le stack <strong><?php echo htmlspecialchars($stack['nom']); ?></strong> ?</p>
                                    <p class="text-danger">Cette action est irréversible.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- MODALE CRÉATION ENTREPÔT -->
<div class="modal fade" id="modalEntrepot" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="create_entrepot" value="1">

                <div class="modal-header">
                    <h5 class="modal-title">Nouvel entrepôt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacité totale</label>
                            <input type="number" name="capacite_totale" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse *</label>
                            <input type="text" name="adresse" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code postal *</label>
                            <input type="text" name="code_postal" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ville *</label>
                            <input type="text" name="ville" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pays</label>
                            <input type="text" name="pays" class="form-control" value="France">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>