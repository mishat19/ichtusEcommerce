    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">Paiements</h2>
            <p class="text-muted small mb-0">Suivre l'état des transactions.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary rounded-pill px-3"><?php echo count($bo_paiements); ?> transactions</span>
        </div>
    </div>

    <div class="bo-content">

        <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <button class="filtre-btn active" data-statut="tous">Tous</button>
            <button class="filtre-btn" data-statut="accepte">Acceptés</button>
            <button class="filtre-btn" data-statut="refuse">Refusés</button>

            <input type="text" id="search-paiement" class="bo-search-input"
                   placeholder="Rechercher une référence, un client..." />
        </div>

        <div class="bo-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="bo-table mb-0">
                    <thead>
                    <tr>
                        <th class="ps-4"># ID</th>
                        <th>Numéro de transaction</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut Paiement</th>
                        <th>Statut Commande</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bo_paiements as $p) : ?>
                        <tr class="paiement-row"
                            data-statut="<?php echo $p['statut']; ?>"
                            data-search="<?php echo strtolower(($p['numero_transaction'] ?? '') . ' ' . ($p['facturation_nom'] ?? '') . ' ' . ($p['facturation_prenom'] ?? '') . ' ' . ($p['facturation_email'] ?? '')); ?>">
                            <td class="ps-4 fw-bold">#<?php echo $p['id']; ?></td>
                            <td>
                                <code class="small text-primary bg-primary bg-opacity-10 px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($p['numero_transaction'] ?? 'N/A'); ?>
                                </code>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars(($p['facturation_prenom'] ?? '') . ' ' . ($p['facturation_nom'] ?? '')); ?></div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;"><?php echo htmlspecialchars($p['facturation_email'] ?? ''); ?></div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark fs-6">
                                    <?php echo number_format((float)$p['montant'], 2, ',', ' '); ?> €
                                </span>
                            </td>
                            <td>
                                <span class="bo-badge bo-badge-<?php echo $p['statut']; ?>">
                                    <?php echo $p['statut']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="bo-badge bo-badge-<?php echo $p['statut_commande']; ?>">
                                    <?php echo str_replace('_', ' ', $p['statut_commande']); ?>
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($p['date_paiement'])); ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="/backoffice/commande/<?php echo $p['id_commande']; ?>" class="btn btn-light" title="Détails du paiement">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.querySelectorAll('.filtre-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filtre-btn').forEach(b => {
                    b.classList.remove('active');
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.add('active');
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
                applyFilters();
            });
        });
        document.getElementById('search-paiement').addEventListener('input', applyFilters);

        function applyFilters() {
            const statut = document.querySelector('.filtre-btn.active').dataset.statut;
            const search = document.getElementById('search-paiement').value.toLowerCase();
            document.querySelectorAll('.paiement-row').forEach(row => {
                const matchS = statut === 'tous' || row.dataset.statut === statut;
                const matchQ = search === '' || row.dataset.search.includes(search);
                row.style.display = matchS && matchQ ? '' : 'none';
            });
        }
    </script>