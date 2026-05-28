<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-envelope me-2"></i> Messages</h2>
    </div>

    <!-- FILTRES ET RECHERCHE -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-4">
                    <form method="GET" action="/backoffice/messages/" onsubmit="event.preventDefault();">
                        <label class="form-label fw-semibold text-secondary">Filtrer par statut</label>
                        <select name="filter" id="filter-select" class="form-select">
                            <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>
                                Tous les messages
                            </option>
                            <option value="unread" <?= ($filter ?? '') === 'unread' ? 'selected' : '' ?>>
                                Non lus
                            </option>
                            <option value="read" <?= ($filter ?? '') === 'read' ? 'selected' : '' ?>>
                                Lus
                            </option>
                            <option value="processed" <?= ($filter ?? '') === 'processed' ? 'selected' : '' ?>>
                                Traités
                            </option>
                            <option value="archived" <?= ($filter ?? '') === 'archived' ? 'selected' : '' ?>>
                                Archivés
                            </option>
                        </select>
                    </form>
                </div>
                <div class="col-md-6 col-lg-8">
                    <div>
                        <label class="form-label fw-semibold text-secondary">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="search-messages" class="form-control border-start-0 ps-0" placeholder="Rechercher par nom, email, sujet, contenu du message...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>

                    <!-- Ligne d'état vide (masquée par défaut si des messages existent) -->
                    <tr id="no-messages-row" style="display: <?= empty($messages) ? '' : 'none' ?>;">
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                            <h5 class="fw-semibold">Aucun message trouvé</h5>
                            <p class="small mb-0">Essayez d'ajuster vos filtres ou vos termes de recherche.</p>
                        </td>
                    </tr>

                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <tr id="row-msg-<?= $message['id'] ?>" 
                                class="row-msg <?= $message['statut'] === 'unread' ? 'table-warning' : '' ?>"
                                data-id="<?= $message['id'] ?>"
                                data-statut="<?= htmlspecialchars($message['statut']) ?>"
                                data-search="<?= htmlspecialchars(strtolower($message['nom'] . ' ' . $message['email'] . ' ' . $message['sujet'] . ' ' . ($message['message'] ?? ''))) ?>">

                                <td><?= htmlspecialchars($message['id']) ?></td>
                                <td><?= htmlspecialchars($message['nom']) ?></td>
                                <td><?= htmlspecialchars($message['email']) ?></td>
                                <td><?= htmlspecialchars($message['sujet']) ?></td>

                                <td>
                                    <?= (new DateTime($message['date_envoi']))->format('d/m/Y H:i') ?>
                                </td>

                                <!-- STATUT -->
                                <td class="status-badge-cell">
                                    <span class="badge
                                        <?php
                                    switch ($message['statut']) {
                                        case 'unread': echo 'bg-danger'; break;
                                        case 'read': echo 'bg-warning'; break;
                                        case 'processed': echo 'bg-success'; break;
                                        case 'archived': echo 'bg-secondary'; break;
                                    }
                                    ?>">
                                        <?php
                                        switch ($message['statut']) {
                                            case 'unread': echo 'Non lu'; break;
                                            case 'read': echo 'Lu'; break;
                                            case 'processed': echo 'Traité'; break;
                                            case 'archived': echo 'Archivé'; break;
                                        }
                                        ?>
                                    </span>
                                </td>

                                <!-- ACTIONS -->
                                <td>
                                    <div class="btn-group">

                                        <!-- NON LU -->
                                        <form method="POST" style="display:inline;" class="status-form">
                                            <input type="hidden" name="action" value="updateStatut">
                                            <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                            <input type="hidden" name="statut" value="unread">
                                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Marquer comme non lu">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </form>

                                        <!-- LU -->
                                        <form method="POST" style="display:inline;" class="status-form">
                                            <input type="hidden" name="action" value="updateStatut">
                                            <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                            <input type="hidden" name="statut" value="read">
                                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                            <button class="btn btn-sm btn-outline-warning" type="submit" title="Marquer comme lu">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </form>

                                        <!-- TRAITÉ -->
                                        <form method="POST" style="display:inline;" class="status-form">
                                            <input type="hidden" name="action" value="updateStatut">
                                            <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                            <input type="hidden" name="statut" value="processed">
                                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                            <button class="btn btn-sm btn-outline-success" type="submit" title="Marquer comme traité">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- ARCHIVER -->
                                        <form method="POST" style="display:inline;" class="status-form">
                                            <input type="hidden" name="action" value="updateStatut">
                                            <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                            <input type="hidden" name="statut" value="archived">
                                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="Archiver">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>

                                        <!-- DETAILS -->
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#msg-<?= $message['id'] ?>"
                                                title="Voir les détails">
                                            <i class="fas fa-info-circle"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <!-- MODAL -->
                            <div class="modal fade" id="msg-<?= $message['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Message #<?= $message['id'] ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <p><strong>Nom :</strong> <?= htmlspecialchars($message['nom']) ?></p>
                                            <p><strong>Email :</strong> <?= htmlspecialchars($message['email']) ?></p>
                                            <p><strong>Sujet :</strong> <?= htmlspecialchars($message['sujet']) ?></p>
                                            <p><strong>Date :</strong>
                                                <?= (new DateTime($message['date_envoi']))->format('d/m/Y H:i') ?>
                                            </p>

                                            <hr>

                                            <h6>Message :</h6>

                                            <div class="bg-light p-3 rounded">
                                                <?= nl2br(htmlspecialchars($message['message'] ?? '')) ?>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                                Fermer
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-messages');
    const rows = document.querySelectorAll('.row-msg');
    const noMessagesRow = document.getElementById('no-messages-row');
    const filterSelect = document.getElementById('filter-select');

    // 🔎 Fonction de filtrage combiné (statut + recherche)
    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = filterSelect ? filterSelect.value : 'all';
        let visibleCount = 0;

        rows.forEach(row => {
            // Si la ligne a été masquée de force (par exemple, suite à une action AJAX et qu'on ne veut plus la voir du tout)
            if (row.getAttribute('data-hidden-permanently') === 'true') {
                row.style.display = 'none';
                return;
            }

            const rowStatus = row.getAttribute('data-statut');
            const searchText = row.getAttribute('data-search') || '';

            const matchStatus = (selectedStatus === 'all' || rowStatus === selectedStatus);
            const matchSearch = (query === '' || searchText.includes(query));

            if (matchStatus && matchSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noMessagesRow.style.display = '';
        } else {
            noMessagesRow.style.display = 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (filterSelect) {
        filterSelect.addEventListener('change', applyFilters);
    }

    // Appliquer les filtres initiaux au chargement
    applyFilters();

    // ✉️ Interception des formulaires de changement de statut (AJAX)
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const originalHtml = button.innerHTML;
            
            // État de chargement
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            const id = form.querySelector('input[name="id"]').value;
            const statut = form.querySelector('input[name="statut"]').value;

            // Envoi de la requête AJAX au contrôleur
            fetch('/backoffice/messages/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    action: 'updateStatut',
                    id: id,
                    statut: statut
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                button.disabled = false;
                button.innerHTML = originalHtml;

                if (data.success) {
                    const row = document.getElementById('row-msg-' + id);
                    if (row) {
                        // Mise à jour de l'attribut de statut
                        row.setAttribute('data-statut', statut);

                        // Mise à jour du badge de statut dans la ligne
                        const badge = row.querySelector('.status-badge-cell .badge');
                        if (badge) {
                            badge.className = 'badge'; // reset
                            switch (statut) {
                                case 'unread':
                                    badge.classList.add('bg-danger');
                                    badge.textContent = 'Non lu';
                                    row.classList.add('table-warning');
                                    break;
                                case 'read':
                                    badge.classList.add('bg-warning');
                                    badge.textContent = 'Lu';
                                    row.classList.remove('table-warning');
                                    break;
                                case 'processed':
                                    badge.classList.add('bg-success');
                                    badge.textContent = 'Traité';
                                    row.classList.remove('table-warning');
                                    break;
                                case 'archived':
                                    badge.classList.add('bg-secondary');
                                    badge.textContent = 'Archivé';
                                    row.classList.remove('table-warning');
                                    break;
                            }
                        }

                        // Mise à jour du compteur global de messages non lus
                        if (typeof window.fetchUnreadMessagesCount === 'function') {
                            window.fetchUnreadMessagesCount();
                        }

                        // Gestion de la disparition si filtre de statut actif
                        const activeFilter = filterSelect ? filterSelect.value : 'all';
                        if (activeFilter !== 'all' && activeFilter !== statut) {
                            // Effet de transition de disparition
                            row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateY(-10px)';
                            
                            setTimeout(() => {
                                row.setAttribute('data-hidden-permanently', 'true');
                                row.style.display = 'none';
                                applyFilters(); // Recalculer l'état de la table et de la recherche
                            }, 400);
                        } else {
                            applyFilters();
                        }
                    }
                } else {
                    alert('Erreur lors de la mise à jour : ' + (data.error || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                button.disabled = false;
                button.innerHTML = originalHtml;
                console.error(error);
                alert('Une erreur réseau est survenue lors de la mise à jour.');
            });
        });
    });
});
</script>