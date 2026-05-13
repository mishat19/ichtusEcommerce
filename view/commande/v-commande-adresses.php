<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- STEPS -->
            <div class="d-flex justify-content-between mb-5">
                <div class="step"><div class="step-icon">1</div><div class="step-label">Récapitulatif</div></div>
                <div class="step active"><div class="step-icon">2</div><div class="step-label">Adresses</div></div>
                <div class="step"><div class="step-icon">3</div><div class="step-label">Paiement</div></div>
                <div class="step"><div class="step-icon">4</div><div class="step-label">Confirmation</div></div>
            </div>

            <?php if (isset($_SESSION['erreur'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="border-radius: 12px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div><?php echo htmlspecialchars($_SESSION['erreur']); unset($_SESSION['erreur']); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <h2 class="mb-4">Adresses</h2>

            <form method="POST" action="/paiement/">
    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <!-- FACTURATION -->
                <div class="card mb-4" id="facturationAdresses">
                    <div class="card-header bg-light">
                        <h5>Adresse de facturation</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($adresseFacturationSelected): ?>
                            <!-- Suppression du radio ici -->
                            <div class="p-3 border rounded mb-3" data-id="<?php e($adresseFacturationSelected['id']); ?>">
                                <strong><?php e($adresseFacturationSelected['prenom']); ?> <?php e($adresseFacturationSelected['nom']); ?></strong><br>
                                <?php e($adresseFacturationSelected['adresse']); ?><br>
                                <?php e($adresseFacturationSelected['code_postal']); ?> <?php e($adresseFacturationSelected['ville']); ?><br>
                                <?php e($adresseFacturationSelected['telephone']); ?>
                                <!-- Champ caché pour stocker l'ID de l'adresse sélectionnée -->
                                <input type="hidden" name="id_adresse_facturation" value="<?php e($adresseFacturationSelected['id']); ?>">
                            </div>
                        <?php else: ?>
                            <p>Aucune adresse de facturation enregistrée.</p>
                            <!-- Champ caché vide si aucune adresse -->
                            <input type="hidden" name="id_adresse_facturation" value="">
                        <?php endif; ?>

                        <button type="button"
                                class="btn btn-primary open-address-modal"
                                data-type="facturation"
                                data-bs-toggle="modal"
                                data-bs-target="#addressModal">
                            Modifier mon adresse
                        </button>
                    </div>
                </div>

                <!-- LIVRAISON -->
                <div class="card mb-4" id="livraisonAdresses">
                    <div class="card-header bg-light">
                        <h5>Adresse de livraison</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($adresseLivraisonSelected): ?>
                            <!-- Suppression du radio ici -->
                            <div class="p-3 border rounded mb-3" data-id="<?php e($adresseLivraisonSelected['id']); ?>">
                                <strong><?php e($adresseLivraisonSelected['prenom']); ?> <?php e($adresseLivraisonSelected['nom']); ?></strong><br>
                                <?php e($adresseLivraisonSelected['adresse']); ?><br>
                                <?php e($adresseLivraisonSelected['code_postal']); ?> <?php e($adresseLivraisonSelected['ville']); ?><br>
                                <?php e($adresseLivraisonSelected['telephone']); ?>
                                <!-- Champ caché pour stocker l'ID de l'adresse sélectionnée -->
                                <input type="hidden" name="id_adresse_livraison" value="<?php e($adresseLivraisonSelected['id']); ?>">
                            </div>
                        <?php else: ?>
                            <p>Aucune adresse de livraison enregistrée.</p>
                            <!-- Champ caché vide si aucune adresse -->
                            <input type="hidden" name="id_adresse_livraison" value="">
                        <?php endif; ?>

                        <button type="button"
                                class="btn btn-primary open-address-modal"
                                data-type="livraison"
                                data-bs-toggle="modal"
                                data-bs-target="#addressModal">
                            Modifier mon adresse
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/recapitulatif" class="btn btn-secondary">Retour</a>
                    <button class="btn btn-primary">Continuer</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- MODAL: Sélection/Modification d'adresse -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gérer mes adresses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Onglets pour Facturation/Livraison -->
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#facturationTab">Facturation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#livraisonTab">Livraison</a>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content">
                    <!-- Onglet Facturation -->
                    <div class="tab-pane fade show active" id="facturationTab">
                        <div id="facturationAddressesList">
                            <?php foreach ($adressesFacturation as $adresse): ?>
                                <div class="address-card mb-2 p-3 border rounded" data-id="<?php e($adresse['id']); ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="selectedFacturationAddress"
                                               value="<?php e($adresse['id']); ?>" <?php e($adresse['est_par_defaut'] ? 'checked' : ''); ?>>
                                        <label class="form-check-label w-100">
                                            <strong><?php e($adresse['prenom']); ?> <?php e($adresse['nom']); ?></strong><br>
                                            <?php e($adresse['adresse']); ?><br>
                                            <?php e($adresse['code_postal']); ?> <?php e($adresse['ville']); ?><br>
                                            <?php e($adresse['telephone']); ?>
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 edit-address-btn"
                                            data-id="<?php e($adresse['id']); ?>" data-type="facturation">
                                        Modifier
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-3" id="addNewFacturationAddress">
                            + Ajouter une adresse de facturation
                        </button>
                    </div>

                    <!-- Onglet Livraison -->
                    <div class="tab-pane fade" id="livraisonTab">
                        <div id="livraisonAddressesList">
                            <?php foreach ($adressesLivraison as $adresse): ?>
                                <div class="address-card mb-2 p-3 border rounded" data-id="<?php e($adresse['id']); ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="selectedLivraisonAddress"
                                               value="<?php e($adresse['id']); ?>" <?php e($adresse['est_par_defaut'] ? 'checked' : ''); ?>>
                                        <label class="form-check-label w-100">
                                            <strong><?php e($adresse['prenom']); ?> <?php e($adresse['nom']); ?></strong><br>
                                            <?php e($adresse['adresse']); ?><br>
                                            <?php e($adresse['code_postal']); ?> <?php e($adresse['ville']); ?><br>
                                            <?php e($adresse['telephone']); ?>
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 edit-address-btn"
                                            data-id="<?php e($adresse['id']); ?>" data-type="livraison">
                                        Modifier
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-3" id="addNewLivraisonAddress">
                            + Ajouter une adresse de livraison
                        </button>
                    </div>
                </div>

                <!-- Formulaire d'ajout/modification (caché par défaut) -->
                <div id="addressFormContainer" style="display: none; margin-top: 20px;">
                    <h6 id="addressFormTitle">Ajouter une nouvelle adresse</h6>

                    <form id="addressForm" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <input type="hidden" name="action" id="addressAction" value="add">
                        <input type="hidden" name="id" id="addressId">
                        <input type="hidden" name="type" id="addressType">

                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="prenom" id="addressPrenom" class="form-control" placeholder="Prénom" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="nom" id="addressNom" class="form-control" placeholder="Nom" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" id="addressEmail" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="telephone" id="addressTelephone" class="form-control" placeholder="Téléphone" required>
                            </div>
                            <div class="col-12">
                                <input type="text" name="adresse" id="addressAdresse" class="form-control" placeholder="Adresse" required>
                            </div>
                            <div class="col-12">
                                <input type="text" name="complement" id="addressComplement" class="form-control" placeholder="Complément">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="code_postal" id="addressCodePostal" class="form-control" placeholder="Code postal" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="ville" id="addressVille" class="form-control" placeholder="Ville" required>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="est_par_defaut" id="addressEstParDefaut" class="form-check-input">
                                    <label class="form-check-label">Adresse par défaut</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-light me-2" id="cancelAddressForm">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="confirmSelectedAddress">Valider la sélection</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        let currentType = null;
        let selectedAddressId = {
            facturation: null,
            livraison: null
        };

        const facturationTab = document.querySelector('[href="#facturationTab"]');
        const livraisonTab   = document.querySelector('[href="#livraisonTab"]');

        const facturationContent = document.getElementById('facturationTab');
        const livraisonContent   = document.getElementById('livraisonTab');

        const addressFormContainer = document.getElementById('addressFormContainer');

        // =========================
        // 🟢 OUVERTURE MODALE (TYPE)
        // =========================
        document.querySelectorAll('.open-address-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {

                const type = e.currentTarget.dataset.type;
                currentType = type;

                // RESET onglets
                facturationTab.classList.remove('active');
                livraisonTab.classList.remove('active');

                facturationContent.classList.remove('show', 'active');
                livraisonContent.classList.remove('show', 'active');

                // RESET affichage onglets
                facturationTab.parentElement.style.display = 'block';
                livraisonTab.parentElement.style.display = 'block';

                // ACTIVER BON ONGLET + cacher l'autre
                if (type === 'facturation') {
                    facturationTab.classList.add('active');
                    facturationContent.classList.add('show', 'active');

                    livraisonTab.parentElement.style.display = 'none';
                } else {
                    livraisonTab.classList.add('active');
                    livraisonContent.classList.add('show', 'active');

                    facturationTab.parentElement.style.display = 'none';
                }

                // Reset formulaire
                addressFormContainer.style.display = 'none';
            });
        });

        // =========================
        // 🟢 SELECTION RADIO
        // =========================
        document.querySelectorAll('input[name="selectedFacturationAddress"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                selectedAddressId.facturation = e.target.value;
            });
        });

        document.querySelectorAll('input[name="selectedLivraisonAddress"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                selectedAddressId.livraison = e.target.value;
            });
        });

        // =========================
        // 🟢 VALIDATION SELECTION
        // =========================
        document.getElementById('confirmSelectedAddress').addEventListener('click', () => {
            if (currentType === 'facturation') {
                const selected = document.querySelector('input[name="selectedFacturationAddress"]:checked');
                if (selected) {
                    const id = selected.value;
                    // Met à jour le champ caché dans le formulaire principal
                    document.querySelector('input[name="id_adresse_facturation"]').value = id;

                    // Met à jour l'affichage
                    const addresses = <?php echo json_encode(array_merge($adressesFacturation, $adressesLivraison)); ?>;
                    const address = addresses.find(a => a.id == id);
                    if (address) {
                        const container = document.querySelector('#facturationAdresses .card-body div.p-3');
                        if (container) {
                            container.innerHTML = `
                        <strong>${address.prenom} ${address.nom}</strong><br>
                        ${address.adresse}<br>
                        ${address.code_postal} ${address.ville}<br>
                        ${address.telephone}
                        <input type="hidden" name="id_adresse_facturation" value="${address.id}">
                    `;
                        }
                    }
                }
            }

            if (currentType === 'livraison') {
                const selected = document.querySelector('input[name="selectedLivraisonAddress"]:checked');
                if (selected) {
                    const id = selected.value;
                    // Met à jour le champ caché dans le formulaire principal
                    document.querySelector('input[name="id_adresse_livraison"]').value = id;

                    // Met à jour l'affichage
                    const addresses = <?php echo json_encode(array_merge($adressesFacturation, $adressesLivraison)); ?>;
                    const address = addresses.find(a => a.id == id);
                    if (address) {
                        const container = document.querySelector('#livraisonAdresses .card-body div.p-3');
                        if (container) {
                            container.innerHTML = `
                        <strong>${address.prenom} ${address.nom}</strong><br>
                        ${address.adresse}<br>
                        ${address.code_postal} ${address.ville}<br>
                        ${address.telephone}
                        <input type="hidden" name="id_adresse_livraison" value="${address.id}">
                    `;
                        }
                    }
                }
            }

            bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
        });

        // =========================
        // 🟢 AJOUT ADRESSE
        // =========================
        document.getElementById('addNewFacturationAddress').addEventListener('click', () => {
            currentType = 'facturation';
            showForm('add');
        });

        document.getElementById('addNewLivraisonAddress').addEventListener('click', () => {
            currentType = 'livraison';
            showForm('add');
        });

        function showForm(action) {

            document.getElementById('addressAction').value = action;
            document.getElementById('addressId').value = '';
            document.getElementById('addressType').value = currentType;

            document.getElementById('addressForm').reset();

            document.getElementById('addressFormTitle').textContent =
                action === 'add'
                    ? `Ajouter une adresse (${currentType})`
                    : 'Modifier l\'adresse';

            addressFormContainer.style.display = 'block';
        }

        // =========================
        // 🟢 ANNULER FORMULAIRE
        // =========================
        document.getElementById('cancelAddressForm').addEventListener('click', () => {
            addressFormContainer.style.display = 'none';
        });

        // =========================
        // 🟢 EDIT ADRESSE
        // =========================
        document.querySelectorAll('.edit-address-btn').forEach(btn => {
            btn.addEventListener('click', () => {

                const id = btn.dataset.id;
                const type = btn.dataset.type;

                currentType = type;

                const addresses = <?php echo json_encode(array_merge($adressesFacturation, $adressesLivraison)); ?>;
                const address = addresses.find(a => a.id == id);

                if (!address) return;

                // Activer bon onglet automatiquement
                facturationTab.classList.remove('active');
                livraisonTab.classList.remove('active');
                facturationContent.classList.remove('show', 'active');
                livraisonContent.classList.remove('show', 'active');

                if (type === 'facturation') {
                    facturationTab.classList.add('active');
                    facturationContent.classList.add('show', 'active');
                } else {
                    livraisonTab.classList.add('active');
                    livraisonContent.classList.add('show', 'active');
                }

                // Remplir formulaire
                document.getElementById('addressAction').value = 'edit';
                document.getElementById('addressId').value = address.id;
                document.getElementById('addressType').value = address.type;

                document.getElementById('addressPrenom').value = address.prenom;
                document.getElementById('addressNom').value = address.nom;
                document.getElementById('addressEmail').value = address.email;
                document.getElementById('addressTelephone').value = address.telephone;
                document.getElementById('addressAdresse').value = address.adresse;
                document.getElementById('addressComplement').value = address.complement || '';
                document.getElementById('addressCodePostal').value = address.code_postal;
                document.getElementById('addressVille').value = address.ville;
                document.getElementById('addressEstParDefaut').checked = !!address.est_par_defaut;

                document.getElementById('addressFormTitle').textContent = 'Modifier l\'adresse';

                addressFormContainer.style.display = 'block';
            });
        });

    });
</script>