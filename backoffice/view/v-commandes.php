<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des commandes</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php if (empty($commandes)) : ?>
                <div class="alert alert-info">Aucune commande trouvé</div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="table" id="tableCommandes">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", async () => {

        const response = await fetch('/api/commandes');
        const commandes = await response.json();

        const tbody = document.querySelector('#tableCommandes tbody');

        commandes.forEach(cmd => {

            let badge = "bg-secondary";
            let label = cmd.statut;

            if (cmd.statut === "confirme") {
                badge = "bg-success";
                label = "Confirmée";
            } else if (cmd.statut === "refuse") {
                badge = "bg-danger";
                label = "Refusée";
            }

            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td>#${cmd.numero_facture}</td>
            <td>${cmd.prenom} ${cmd.nom}</td>
            <td>${new Date(cmd.date_commande).toLocaleDateString()}</td>
            <td>${cmd.total_ttc} €</td>
            <td><span class="badge ${badge}">${label}</span></td>
        `;

            tbody.appendChild(tr);
        });
    });
</script>