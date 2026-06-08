<div class="sidebar sidebar-scroll">
    <div class="sidebar-content">

        <h4 class="mb-4">Backoffice</h4>

        <!-- ===================== -->
        <!-- VUE GLOBALE -->
        <!-- ===================== -->
        <div class="sidebar-section">

            <div class="sidebar-title">
                Vue globale
            </div>

            <a href="/backoffice"
               class="<?= ($_SERVER['REQUEST_URI'] === '/backoffice') ? 'active' : '' ?>">
                <i class="fas fa-chart-line me-2"></i>
                Dashboard
            </a>

        </div>

        <!-- ===================== -->
        <!-- COMMERCE -->
        <!-- ===================== -->
        <div class="sidebar-section">

            <div class="sidebar-title">
                Commerce
            </div>

            <a href="/backoffice/commandes"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/commandes') ? 'active' : '' ?>">
                <i class="fas fa-box me-2"></i>
                Commandes
            </a>

            <a href="/backoffice/paiements"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/paiements') ? 'active' : '' ?>">
                <i class="fas fa-credit-card me-2"></i>
                Paiements
            </a>

            <a href="/backoffice/produits"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/produits') ? 'active' : '' ?>">
                <i class="fas fa-tag me-2"></i>
                Produits
            </a>

        </div>

        <!-- ===================== -->
        <!-- LOGISTIQUE -->
        <!-- ===================== -->
        <div class="sidebar-section">

            <div class="sidebar-title">
                Logistique
            </div>

            <a href="/backoffice/entrepots"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/entrepots') ? 'active' : '' ?>">
                <i class="fas fa-warehouse me-2"></i>
                Entrepôts
            </a>

            <a href="/backoffice/stock"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/stock') ? 'active' : '' ?>">
                <i class="fas fa-boxes-stacked me-2"></i>
                Stock
            </a>

        </div>

        <!-- ===================== -->
        <!-- MESSAGES -->
        <!-- ===================== -->
        <div class="sidebar-section">

            <div class="sidebar-title">
                Messages
            </div>

            <a href="/backoffice/messages"
               class="d-flex align-items-center <?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/messages') ? 'active' : '' ?>">

                <div>
                    <i class="fas fa-envelope me-2"></i>
                    Messages
                </div>

                <!-- Badge -->
                <span
                        id="unread-messages-badge"
                        class="badge bg-danger ms-auto"
                        style="
                            border-radius: 10px;
                            font-size: 0.75rem;
                            padding: 0.25em 0.5em;
                        "
                >
                    0
                </span>

            </a>

        </div>

        <!-- ===================== -->
        <!-- OUTILS -->
        <!-- ===================== -->
        <div class="sidebar-section">

            <div class="sidebar-title">
                Outils
            </div>

            <a href="/backoffice/tests"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/tests') ? 'active' : '' ?>">
                <i class="fas fa-vial me-2"></i>
                Tests & Stats
            </a>

            <a href="/backoffice/api-tester"
               class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/backoffice/api-tester') ? 'active' : '' ?>">
                <i class="fas fa-terminal me-2"></i>
                Testeur d'API
            </a>

        </div>

        <hr>

        <!-- Déconnexion -->
        <a href="/" class="text-danger sidebar-logout">
            <i class="fas fa-sign-out-alt me-2"></i>
            Déconnexion
        </a>

    </div>
</div>

<style>

    .sidebar {
        width: 280px;
        height: 100vh;
        background: #0f172a;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        overflow-y: auto;
        z-index: 1000;
    }

    .main-content {
        margin-left: 280px;
    }

    .sidebar-content {
        padding: 24px 18px;
    }

    .sidebar-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        margin-bottom: 12px;
        margin-top: 24px;
        font-weight: 700;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        border-radius: 12px;
        color: #cbd5e1;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 6px;
        font-size: 0.95rem;
    }

    .sidebar a:hover {
        background: rgba(255,255,255,0.08);
        color: white;
        transform: translateX(2px);
    }

    .sidebar a.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .sidebar a.active i {
        color: white;
    }

    .sidebar-section {
        margin-bottom: 10px;
    }

    .sidebar hr {
        border-color: rgba(255,255,255,0.08);
        margin: 24px 0;
    }

    .sidebar-logout:hover {
        background: rgba(239, 68, 68, 0.15) !important;
        color: #f87171 !important;
    }

    /* MOBILE */
    @media (max-width: 992px) {

        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }

        .sidebar-content {
            padding: 18px;
        }

        .main-content {
            margin-left: 0;
        }

    }

</style>

<script>

    // ==========================
    // Messages non lus
    // ==========================

    function fetchUnreadMessagesCount() {

        fetch('/index.php?pageAPI=messages&action=getUnreadCount', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'token=WDIhUThWMz9aN0Y0VDFwOUE2'
        })

            .then(response => {

                if (!response.ok) {
                    throw new Error('Erreur réseau : ' + response.status);
                }

                return response.json();

            })

            .then(data => {

                if (data.success && data.count !== undefined) {

                    const badge = document.getElementById('unread-messages-badge');

                    if (badge) {

                        badge.textContent = data.count;

                        // Cache le badge si aucun message
                        if (data.count <= 0) {
                            badge.style.display = 'none';
                        } else {
                            badge.style.display = 'inline-block';
                        }
                    }
                }

            })

            .catch(error => {
                console.error('Erreur :', error);
            });
    }

    // ==========================
    // Init
    // ==========================

    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', function() {

            fetchUnreadMessagesCount();

            // Refresh toutes les 30 sec
            setInterval(fetchUnreadMessagesCount, 30000);

        });

    } else {

        fetchUnreadMessagesCount();

        setInterval(fetchUnreadMessagesCount, 30000);

    }

</script>

<div class="main-content">
