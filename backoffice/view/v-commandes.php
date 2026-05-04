<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">Commandes</h2>
        <p class="text-muted small mb-0">Suivre l'état des commandes.</p>
    </div>
    <div class="text-end">
        <span class="badge bg-primary rounded-pill px-3"><?php echo count($bo_commandes); ?> commandes</span>
    </div>
</div>

<div class="bo-content">

    <!-- FILTRES -->
    <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">

        <button class="filtre-btn active" data-statut="tous">Toutes</button>
        <button class="filtre-btn" data-statut="payee">Payées</button>
        <button class="filtre-btn" data-statut="en_attente">En attente</button>
        <button class="filtre-btn" data-statut="annulee">Annulées</button>

        <input type="text" id="search"
               placeholder="Rechercher client ou email..."
               style="margin-left:auto; padding:.4rem .8rem; font-size:.8rem;" />
    </div>

    <!-- TABLE -->
    <div class="bo-card" style="padding:0;">
        <table class="bo-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Email</th>
                <th>Total</th>
                <th>Statut</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($bo_commandes as $c): ?>
                <tr class="row-cmd"
                    data-statut="<?php echo $c['statut']; ?>"
                    data-search="<?php echo strtolower($c['prenom'].' '.$c['nom'].' '.$c['email']); ?>">

                    <td>#<?php echo $c['id']; ?></td>

                    <td>
                        <strong>
                            <?php echo htmlspecialchars($c['prenom'].' '.$c['nom']); ?>
                        </strong>
                    </td>

                    <td><?php echo htmlspecialchars($c['email']); ?></td>

                    <td>
                        <?php echo number_format($c['total_ttc'], 2, ',', ' '); ?> €
                    </td>

                    <td>
                        <span class="bo-badge bo-badge-<?php echo $c['statut']; ?>">
                            <?php echo $c['statut']; ?>
                        </span>
                    </td>

                    <td>
                        <a href="/backoffice/commande/<?php echo $c['id']; ?>" class="bo-btn-outline">
                            Voir
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const buttons = document.querySelectorAll(".filtre-btn");
        const rows = document.querySelectorAll(".row-cmd");
        const searchInput = document.getElementById("search");

        function applyFilters() {
            const activeBtn = document.querySelector(".filtre-btn.active");
            const statut = activeBtn ? activeBtn.dataset.statut : "tous";
            const search = searchInput.value.toLowerCase();

            rows.forEach(row => {
                const rowStatut = row.dataset.statut;
                const rowSearch = row.dataset.search;

                const matchStatut = (statut === "tous" || rowStatut === statut);
                const matchSearch = (search === "" || rowSearch.includes(search));

                if (matchStatut && matchSearch) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 🔘 Gestion clic boutons filtres
        buttons.forEach(btn => {
            btn.addEventListener("click", () => {

                buttons.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                applyFilters();
            });
        });

        // 🔎 Recherche en live
        searchInput.addEventListener("input", applyFilters);

    });
</script>