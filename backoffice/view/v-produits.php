    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="letter-spacing: -0.025em;">Catalogue Produits</h2>
            <p class="text-muted small mb-0">Gérez votre inventaire et les détails de vos articles.</p>
        </div>
        <button class="btn" style="background: var(--bo-primary); color: #fff; border-radius: 10px; padding: 0.6rem 1.2rem; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#modalAjoutProduit">
            <i class="fas fa-plus me-2"></i> Ajouter un produit
        </button>
    </div>

    <!-- Messages -->
    <?php if (!empty($messageSucces)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i> <?php echo $messageSucces; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($messageErreur)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $messageErreur; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="bo-content">
        
        <!-- FILTRES -->
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <button class="filtre-btn active" data-statut="tous">Tous</button>
            <button class="filtre-btn" data-statut="actif">Actifs</button>
            <button class="filtre-btn" data-statut="inactif">Inactifs</button>
            
            <input type="text" id="search-produit" class="bo-search-input" placeholder="Rechercher un nom, identifiant...">
        </div>

        <div class="bo-card p-0 overflow-hidden">
            <?php if (empty($produits)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-box-seam fs-1 mb-3 d-block"></i>
                    <p>Aucun produit n'a été trouvé dans votre catalogue.</p>
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="bo-table mb-0">
                        <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Aperçu</th>
                            <th>Nom du produit</th>
                            <th>Identifiant (SKU)</th>
                            <th>Prix HT</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>QR Code</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($produits as $produit): ?>
                            <?php
                            $stockDispo = (int)($produit['stock_disponible'] ?? 0);
                            $stockReserve = (int)($produit['stock_reserve'] ?? 0);
                            $stockReel = $stockDispo - $stockReserve;
                            $seuilAlerte = (int)($produit['seuil_alerte'] ?? 15);
                            ?>
                            <tr class="row-produit" 
                                data-statut="<?php echo $produit['statut']; ?>"
                                data-search="<?php echo strtolower($produit['nom'] . ' ' . $produit['identifiant']); ?>">
                                <td class="ps-4 fw-bold">#<?php echo $produit['id']; ?></td>

                                <td>
                                    <?php if ($produit['image']): ?>
                                        <img src="/<?php echo htmlspecialchars($produit['image']); ?>"
                                             class="rounded border"
                                             style="width:40px; height:40px; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                            <i class="bi bi-image text-muted small"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($produit['nom']); ?></td>

                                <td><code class="small text-muted"><?php echo htmlspecialchars($produit['identifiant']); ?></code></td>

                                <td class="fw-bold text-dark">
                                    <?php echo number_format($produit['prix_ht'] / 100, 2, ',', ' '); ?> €
                                </td>

                                <!-- Colonne Stock -->
                                <td>
                                    <?php if ($stockDispo <= 0): ?>
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;">
                                            <i class="fas fa-times-circle me-1"></i> Rupture
                                        </span>
                                    <?php elseif ($stockReel <= $seuilAlerte): ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> <?php e($stockDispo); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 0.4em 0.8em; border-radius: 6px;">
                                            <i class="fas fa-check-circle me-1"></i> <?php e($stockDispo); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($stockReserve > 0): ?>
                                        <br><small class="text-muted" style="font-size: 0.7rem;"><?php e($stockReserve); ?> réservé(s)</small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge rounded-pill <?php echo $produit['statut'] === 'actif' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>" style="font-size: 0.7rem; padding: 0.4em 0.8em;">
                                        <?php echo strtoupper($produit['statut']); ?>
                                    </span>
                                </td>

                                <!-- Colonne QR Code -->
                                <td>
                                    <?php if (!empty($produit['qr_code'])): ?>
                                        <?php
                                        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($produit['qr_url']);
                                        ?>
                                        <button class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.75rem;"
                                                data-bs-toggle="modal" data-bs-target="#modalQr"
                                                onclick="showQrModal('<?php echo htmlspecialchars($produit['nom']); ?>', '<?php echo $qrImageUrl; ?>', '<?php echo htmlspecialchars($produit['qr_url']); ?>')">
                                            <i class="fas fa-qrcode me-1"></i> Voir
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL : AJOUT PRODUIT
         ══════════════════════════════════════════ -->
    <div class="modal fade" id="modalAjoutProduit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--bo-primary) 0%, #6366f1 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Ajouter un produit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="ajout_produit" value="1">
                    <div class="modal-body p-4">

                        <div class="row g-3">
                            <!-- Nom -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du produit <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" placeholder="Ex: Pâte de Fraise" required
                                       style="border-radius: 10px;">
                            </div>

                            <!-- Identifiant (slug) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Identifiant (slug) <span class="text-danger">*</span></label>
                                <input type="text" name="identifiant" class="form-control" placeholder="Ex: pate-fraise" required
                                       pattern="[a-z0-9\-]+" title="Minuscules, chiffres et tirets uniquement"
                                       style="border-radius: 10px;">
                                <small class="text-muted">Utilisé dans l'URL. Minuscules et tirets uniquement.</small>
                            </div>

                            <!-- Prix HT -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prix HT (centimes) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="prix_ht" class="form-control" placeholder="850" min="1" required
                                           style="border-radius: 10px 0 0 10px;">
                                    <span class="input-group-text" style="border-radius: 0 10px 10px 0;">cts</span>
                                </div>
                                <small class="text-muted">Ex: 850 = 8,50 €</small>
                            </div>

                            <!-- TVA -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Taux TVA <span class="text-danger">*</span></label>
                                <select name="id_tva" class="form-select" required style="border-radius: 10px;">
                                    <option value="">— Choisir —</option>
                                    <?php foreach ($listeTva as $tva): ?>
                                        <option value="<?php e($tva['id']); ?>">
                                            <?php e($tva['nom']); ?> (<?php e($tva['taux']); ?>%)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Statut -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Statut</label>
                                <select name="statut" class="form-select" style="border-radius: 10px;">
                                    <option value="actif" selected>Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>

                            <!-- Image -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Image du produit</label>
                                <input type="file" name="image" class="form-control" accept="image/*" style="border-radius: 10px;">
                                <small class="text-muted">JPG, PNG, GIF ou WebP. Sera enregistrée dans /images/</small>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Description du produit..."
                                          style="border-radius: 10px;"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--bo-border);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Annuler</button>
                        <button type="submit" class="btn" style="background: var(--bo-primary); color: #fff; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-save me-2"></i> Créer le produit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL : PRÉVISUALISATION QR CODE
         ══════════════════════════════════════════ -->
    <div class="modal fade" id="modalQr" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid var(--bo-border);">
                    <h6 class="modal-title" id="qr-modal-title">QR Code</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="qr-modal-image" src="" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px; border-radius: 8px;">
                    <p class="small text-muted mb-0" id="qr-modal-url"></p>
                </div>
                <div class="modal-footer justify-content-center" style="border-top: 1px solid var(--bo-border);">
                    <a id="qr-download-link" href="" download="qrcode.png" class="btn btn-sm" style="background: var(--bo-primary); color: #fff; border-radius: 8px;">
                        <i class="fas fa-download me-1"></i> Télécharger
                    </a>
                </div>
            </div>
        </div>
    </div>

<script>
/* QR Code Modal */
function showQrModal(nom, imageUrl, url) {
    document.getElementById('qr-modal-title').textContent = 'QR Code — ' + nom;
    document.getElementById('qr-modal-image').src = imageUrl;
    document.getElementById('qr-modal-url').textContent = url;
    document.getElementById('qr-download-link').href = imageUrl;
}

/* Filtres produits */
document.addEventListener('DOMContentLoaded', function() {
    const filtres = document.querySelectorAll('.filtre-btn');
    const rows = document.querySelectorAll('.row-produit');
    const searchInput = document.getElementById('search-produit');

    function applyFilters() {
        const activeBtn = document.querySelector('.filtre-btn.active');
        const statut = activeBtn ? activeBtn.dataset.statut : 'tous';
        const search = searchInput ? searchInput.value.toLowerCase() : '';

        rows.forEach(row => {
            const rowStatut = row.dataset.statut;
            const rowSearch = row.dataset.search;

            const matchStatut = (statut === 'tous' || rowStatut === statut);
            const matchSearch = (!search || rowSearch.includes(search));

            row.style.display = (matchStatut && matchSearch) ? '' : 'none';
        });
    }

    filtres.forEach(btn => {
        btn.addEventListener('click', function() {
            filtres.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    /* Auto-generate slug from product name */
    const nomInput = document.querySelector('input[name="nom"]');
    const slugInput = document.querySelector('input[name="identifiant"]');
    if (nomInput && slugInput) {
        nomInput.addEventListener('input', function() {
            if (!slugInput.dataset.manual) {
                slugInput.value = this.value
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        });
        slugInput.addEventListener('input', function() {
            this.dataset.manual = '1';
        });
    }
});
</script>