<?php
// Modale pour ajouter un meuble à un entrepôt
?>
    <div class="modal fade" id="modalAjoutMeuble-<?php echo $entrepot['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="">
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
                <form method="POST" action="">
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
                <form method="POST" action="">
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
    <!-- Modale pour ajouter un stack à un meuble -->
    <div class="modal fade" id="modalAjoutStack-<?php echo $meuble['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="">
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
                <form method="POST" action="">
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
                <form method="POST" action="">
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
                    <form method="POST" action="">
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
                    <form method="POST" action="">
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