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

    <!-- Section Adresses -->
    <div class="container py-5">
        <h2 class="mb-4">Mes Adresses</h2>

        <!-- Adresses de facturation -->
        <div class="card mb-4" id="facturationAdresses">
            <div class="card-header bg-light">
                <h5 class="mb-0">Adresses de Facturation</h5>
            </div>
            <div class="card-body">
                <?php if (empty($adressesFacturation)): ?>
                    <div class="alert alert-info">
                        Aucune adresse de facturation enregistrée.
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAdresseModal" data-type="facturation">
                            Ajouter une adresse
                        </button>
                    </div>
                <?php else:
                    foreach ($adressesFacturation as $adresse): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded" data-id="<?= $adresse['id'] ?>">
                            <div>
                                <strong><?= htmlspecialchars($adresse['prenom'] . ' ' . $adresse['nom']) ?></strong><br>
                                <?= htmlspecialchars($adresse['adresse']) ?><br>
                                <?= !empty($adresse['complement']) ? htmlspecialchars($adresse['complement']) . '<br>' : '' ?>
                                <?= htmlspecialchars($adresse['code_postal'] . ' ' . $adresse['ville']) ?><br>
                                <?= htmlspecialchars($adresse['telephone']) ?>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary edit-adresse"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAdresseModal"
                                        data-id="<?= $adresse['id'] ?>"
                                        data-type="facturation">
                                    Modifier
                                </button>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>

        <!-- Adresses de livraison -->
        <div class="card" id="livraisonAdresses">
            <div class="card-header bg-light">
                <h5 class="mb-0">Adresses de Livraison</h5>
            </div>
            <div class="card-body">
                <?php if (empty($adressesLivraison)): ?>
                    <div class="alert alert-info">
                        Aucune adresse de livraison enregistrée.
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAdresseModal" data-type="livraison">
                            Ajouter une adresse
                        </button>
                    </div>
                <?php else:
                    foreach ($adressesLivraison as $adresse): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded" data-id="<?= $adresse['id'] ?>">
                            <div>
                                <strong><?= htmlspecialchars($adresse['prenom'] . ' ' . $adresse['nom']) ?></strong><br>
                                <?= htmlspecialchars($adresse['adresse']) ?><br>
                                <?= !empty($adresse['complement']) ? htmlspecialchars($adresse['complement']) . '<br>' : '' ?>
                                <?= htmlspecialchars($adresse['code_postal'] . ' ' . $adresse['ville']) ?><br>
                                <?= htmlspecialchars($adresse['telephone']) ?>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary edit-adresse"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAdresseModal"
                                        data-id="<?= $adresse['id'] ?>"
                                        data-type="livraison">
                                    Modifier
                                </button>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter une adresse -->
    <div class="modal fade" id="addAdresseModal" tabindex="-1" aria-labelledby="addAdresseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addAdresseForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAdresseModalLabel">Ajouter une adresse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="type" id="modalAdresseType" value="">

                        <div class="mb-3">
                            <label for="addPrenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="addPrenom" name="prenom" value="<?= htmlspecialchars($client['prenom'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="addNom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="addNom" name="nom" value="<?= htmlspecialchars($client['nom'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="addEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="addEmail" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="addTelephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="addTelephone" name="telephone" required>
                        </div>

                        <div class="mb-3">
                            <label for="addAdresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="addAdresse" name="adresse" required>
                        </div>

                        <div class="mb-3">
                            <label for="addComplement" class="form-label">Complément (optionnel)</label>
                            <input type="text" class="form-control" id="addComplement" name="complement">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="addCodePostal" class="form-label">Code Postal</label>
                                <input type="text" class="form-control" id="addCodePostal" name="code_postal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addVille" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="addVille" name="ville" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour modifier une adresse -->
    <div class="modal fade" id="editAdresseModal" tabindex="-1" aria-labelledby="editAdresseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAdresseForm" method="POST">
                    <input type="hidden" name="id" id="editAdresseId">
                    <input type="hidden" name="type" id="editAdresseType">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAdresseModalLabel">Modifier l'adresse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editPrenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="editPrenom" name="prenom" required>
                        </div>

                        <div class="mb-3">
                            <label for="editNom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="editNom" name="nom" required>
                        </div>

                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="editTelephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="editTelephone" name="telephone" required>
                        </div>

                        <div class="mb-3">
                            <label for="editAdresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="editAdresse" name="adresse" required>
                        </div>

                        <div class="mb-3">
                            <label for="editComplement" class="form-label">Complément (optionnel)</label>
                            <input type="text" class="form-control" id="editComplement" name="complement">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editCodePostal" class="form-label">Code Postal</label>
                                <input type="text" class="form-control" id="editCodePostal" name="code_postal" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editVille" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="editVille" name="ville" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialise les modals Bootstrap
        const addAdresseModal = new bootstrap.Modal(document.getElementById('addAdresseModal'));
        const editAdresseModal = new bootstrap.Modal(document.getElementById('editAdresseModal'));

        // Gestion du modal d'ajout
        document.querySelectorAll('[data-bs-target="#addAdresseModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                document.querySelector('#addAdresseModalLabel').textContent = `Ajouter une adresse de ${type}`;
                document.querySelector('#modalAdresseType').value = type;

                // Réinitialise le formulaire
                document.getElementById('addAdresseForm').reset();

                // Préremplit avec les infos du client
                document.getElementById('addPrenom').value = '<?= htmlspecialchars($client['prenom'] ?? "") ?>';
                document.getElementById('addNom').value = '<?= htmlspecialchars($client['nom'] ?? "") ?>';
                document.getElementById('addEmail').value = '<?= htmlspecialchars($client['email'] ?? "") ?>';
            });
        });

        // Soumission du formulaire d'ajout
        document.getElementById('addAdresseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/profil/ajouter-adresse', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        addAdresseModal.hide();
                        showSuccessMessage(data.message);
                        updateAdresseList(data.adresse, formData.get('type'));
                    } else {
                        showErrorMessage(data.message || "Une erreur est survenue.");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage("Une erreur est survenue.");
                });
        });

        // Gestion du modal de modification
        document.querySelectorAll('.edit-adresse').forEach(button => {
            button.addEventListener('click', function() {
                const adresseId = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');

                document.querySelector('#editAdresseModalLabel').textContent = `Modifier l'adresse de ${type}`;
                document.querySelector('#editAdresseType').value = type;
                document.querySelector('#editAdresseId').value = adresseId;

                // Charge les données de l'adresse
                fetch(`/profil/adresse/${adresseId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('editPrenom').value = data.prenom;
                        document.getElementById('editNom').value = data.nom;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editTelephone').value = data.telephone;
                        document.getElementById('editAdresse').value = data.adresse;
                        document.getElementById('editComplement').value = data.complement || '';
                        document.getElementById('editCodePostal').value = data.code_postal;
                        document.getElementById('editVille').value = data.ville;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage("Une erreur est survenue lors du chargement de l'adresse.");
                    });
            });
        });

        // Soumission du formulaire de modification
        document.getElementById('editAdresseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/profil/modifier-adresse', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        editAdresseModal.hide();
                        showSuccessMessage(data.message);
                        updateAdresseList(data.adresse, formData.get('type'));
                    } else {
                        showErrorMessage(data.message || "Une erreur est survenue.");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage("Une erreur est survenue.");
                });
        });

        // Fonction pour afficher un message de succès
        function showSuccessMessage(message) {
            const alertContainer = document.createElement('div');
            alertContainer.className = 'alert alert-success alert-dismissible fade show';
            alertContainer.role = 'alert';
            alertContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            const container = document.querySelector('.container.py-5');
            container.insertBefore(alertContainer, container.firstChild);

            setTimeout(() => {
                alertContainer.remove();
            }, 5000);
        }

        // Fonction pour afficher un message d'erreur
        function showErrorMessage(message) {
            const alertContainer = document.createElement('div');
            alertContainer.className = 'alert alert-danger alert-dismissible fade show';
            alertContainer.role = 'alert';
            alertContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            const container = document.querySelector('.container.py-5');
            container.insertBefore(alertContainer, container.firstChild);
        }

        // Fonction pour mettre à jour la liste des adresses
        function updateAdresseList(adresse, type) {
            const cardBody = document.querySelector(`#${type}Adresses .card-body`);
            let existingAdresse = cardBody.querySelector(`[data-id="${adresse.id}"]`);

            const html = `
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded" data-id="${adresse.id}">
                <div>
                    <strong>${adresse.prenom} ${adresse.nom}</strong><br>
                    ${adresse.adresse}<br>
                    ${adresse.complement ? adresse.complement + '<br>' : ''}
                    ${adresse.code_postal} ${adresse.ville}<br>
                    ${adresse.telephone}
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary edit-adresse"
                            data-bs-toggle="modal"
                            data-bs-target="#editAdresseModal"
                            data-id="${adresse.id}"
                            data-type="${type}">
                        Modifier
                    </button>
                </div>
            </div>
        `;

            if (existingAdresse) {
                existingAdresse.outerHTML = html;
            } else {
                const alert = cardBody.querySelector('.alert');
                if (alert) alert.remove();
                cardBody.insertAdjacentHTML('beforeend', html);
            }

            // Réattache les événements aux nouveaux boutons "Modifier"
            document.querySelectorAll('.edit-adresse').forEach(button => {
                button.addEventListener('click', function() {
                    const adresseId = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');

                    document.querySelector('#editAdresseModalLabel').textContent = `Modifier l'adresse de ${type}`;
                    document.querySelector('#editAdresseType').value = type;
                    document.querySelector('#editAdresseId').value = adresseId;

                    // Charge les données de l'adresse
                    fetch(`/profil/adresse/${adresseId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('editPrenom').value = data.prenom;
                            document.getElementById('editNom').value = data.nom;
                            document.getElementById('editEmail').value = data.email;
                            document.getElementById('editTelephone').value = data.telephone;
                            document.getElementById('editAdresse').value = data.adresse;
                            document.getElementById('editComplement').value = data.complement || '';
                            document.getElementById('editCodePostal').value = data.code_postal;
                            document.getElementById('editVille').value = data.ville;
                        });
                });
            });
        }
    });
</script>
