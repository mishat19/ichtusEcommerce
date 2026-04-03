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

            <h2 class="mb-4">Adresses</h2>

            <form method="POST" action="/paiement">

                <!-- FACTURATION -->
                <div class="card mb-4" id="facturationAdresses">
                    <div class="card-header bg-light">
                        <h5>Adresse de facturation</h5>
                    </div>
                    <div class="card-body">

                        <?php if (!empty($adressesFacturation)): ?>
                        <?php foreach ($adressesFacturation as $adresse): ?>
                            <div class="form-check mb-3 p-3 border rounded" data-id="<?= $adresse['id'] ?>">
                                <input class="form-check-input"
                                       type="radio"
                                       name="id_adresse_facturation"
                                       value="<?= $adresse['id'] ?>">

                                <label class="form-check-label w-100">
                                    <strong><?= $adresse['prenom'] ?> <?= $adresse['nom'] ?></strong><br>
                                    <?= $adresse['adresse'] ?><br>
                                    <?= $adresse['code_postal'] ?> <?= $adresse['ville'] ?><br>
                                    <?= $adresse['telephone'] ?>
                                </label>

                                <button type="button"
                                        class="btn btn-sm btn-outline-primary mt-2 edit-adresse"
                                        data-id="<?= $adresse['id'] ?>"
                                        data-type="facturation"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAdresseModal">
                                    Modifier
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <button type="button" class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#addAdresseModal"
                                data-type="facturation">
                            Ajouter une adresse
                        </button>

                    </div>
                </div>

                <!-- LIVRAISON -->
                <div class="card mb-4" id="livraisonAdresses">
                    <div class="card-header bg-light">
                        <h5>Adresse de livraison</h5>
                    </div>
                    <div class="card-body">

                        <?php if (!empty($adressesLivraison)): ?>
                        <?php foreach ($adressesLivraison as $adresse): ?>
                            <div class="form-check mb-3 p-3 border rounded" data-id="<?= $adresse['id'] ?>">
                                <input class="form-check-input"
                                       type="radio"
                                       name="id_adresse_livraison"
                                       value="<?= $adresse['id'] ?>">

                                <label class="form-check-label w-100">
                                    <strong><?= $adresse['prenom'] ?> <?= $adresse['nom'] ?></strong><br>
                                    <?= $adresse['adresse'] ?><br>
                                    <?= $adresse['code_postal'] ?> <?= $adresse['ville'] ?><br>
                                    <?= $adresse['telephone'] ?>
                                </label>

                                <button type="button"
                                        class="btn btn-sm btn-outline-primary mt-2 edit-adresse"
                                        data-id="<?= $adresse['id'] ?>"
                                        data-type="livraison"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAdresseModal">
                                    Modifier
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <button type="button" class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#addAdresseModal"
                                data-type="livraison">
                            Ajouter une adresse
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

<!-- ADD -->
<div class="modal fade" id="addAdresseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAdresseForm">
                <input type="hidden" name="type" id="addType">

                <div class="modal-body">
                    <input name="prenom" placeholder="Prénom" class="form-control mb-2">
                    <input name="nom" placeholder="Nom" class="form-control mb-2">
                    <input name="telephone" placeholder="Téléphone" class="form-control mb-2">
                    <input name="adresse" placeholder="Adresse" class="form-control mb-2">
                    <input name="code_postal" placeholder="CP" class="form-control mb-2">
                    <input name="ville" placeholder="Ville" class="form-control mb-2">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT -->
<div class="modal fade" id="editAdresseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAdresseForm">
                <input type="hidden" name="type" id="editType">

                <div class="modal-body">
                    <input name="prenom" id="editPrenom" class="form-control mb-2">
                    <input name="nom" id="editNom" class="form-control mb-2">
                    <input name="telephone" id="editTelephone" class="form-control mb-2">
                    <input name="adresse" id="editAdresse" class="form-control mb-2">
                    <input name="code_postal" id="editCP" class="form-control mb-2">
                    <input name="ville" id="editVille" class="form-control mb-2">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // ADD
        document.querySelectorAll('[data-bs-target="#addAdresseModal"]').forEach(btn => {
            btn.onclick = () => {
                document.getElementById("addType").value = btn.dataset.type;
            };
        });

        document.getElementById("addAdresseForm").onsubmit = async (e) => {
            e.preventDefault();

            const data = new FormData(e.target);

            const res = await fetch("/commande/ajouter-adresse", {
                method: "POST",
                body: data
            });

            const json = await res.json();

            if (json.success) {
                location.reload();
            }
        };

        // EDIT (préremplissage)
        document.querySelectorAll('.edit-adresse').forEach(btn => {
            btn.onclick = async () => {

                const id = btn.dataset.id;
                const type = btn.dataset.type;

                document.getElementById("editType").value = type;

                const res = await fetch(`/profil/adresse/${id}`);
                const data = await res.json();

                editPrenom.value = data.prenom;
                editNom.value = data.nom;
                editTelephone.value = data.telephone;
                editAdresse.value = data.adresse;
                editCP.value = data.code_postal;
                editVille.value = data.ville;
            };
        });

        // EDIT submit (INSERT)
        document.getElementById("editAdresseForm").onsubmit = async (e) => {
            e.preventDefault();

            const data = new FormData(e.target);

            const res = await fetch("/profil/modifier-adresse", {
                method: "POST",
                body: data
            });

            const json = await res.json();

            if (json.success) {
                location.reload();
            }
        };
    });
</script>