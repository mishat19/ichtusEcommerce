<div class="sidebar sidebar-scroll">
    <div class="sidebar-content">
    <h4 class="mb-4">Backoffice</h4>

    <!-- ===================== -->
    <!-- VUE GLOBALE -->
    <!-- ===================== -->
    <div class="sidebar-section">
        <div class="sidebar-title">Vue globale</div>

        <a href="/backoffice">
            <i class="fas fa-chart-line me-2"></i> Dashboard
        </a>
    </div>

    <!-- ===================== -->
    <!-- COMMERCE -->
    <!-- ===================== -->
    <div class="sidebar-section">
        <div class="sidebar-title">Commerce</div>

        <a href="/backoffice/commandes">
            <i class="fas fa-box me-2"></i> Commandes
        </a>

        <a href="/backoffice/paiements">
            <i class="fas fa-credit-card me-2"></i> Paiements
        </a>

        <a href="/backoffice/produits">
            <i class="fas fa-tag me-2"></i> Produits
        </a>
    </div>

    <!-- ===================== -->
    <!-- LOGISTIQUE -->
    <!-- ===================== -->
    <div class="sidebar-section">
        <div class="sidebar-title">Logistique</div>

        <a href="/backoffice/entrepots">
            <i class="fas fa-warehouse me-2"></i> Entrepôts
        </a>

        <a href="/backoffice/stock">
            <i class="fas fa-boxes-stacked me-2"></i> Stock
        </a>
    </div>

    <!-- ===================== -->
    <!-- Messages -->
    <!-- ===================== -->
    <div class="sidebar-section">
        <div class="sidebar-title">Messages</div>
        <a href="/backoffice/messages/" class="d-flex align-items-center">
            <i class="fas fa-envelope me-2"></i> Messages
            <!-- Badge pour les messages non lus -->
            <span id="unread-messages-badge" class="badge bg-danger ms-auto"
                  style="border-radius: 10px; font-size: 0.75rem; padding: 0.25em 0.5em;">
            0
        </span>
        </a>
    </div>

    <!-- ===================== -->
    <!-- OUTILS -->
    <!-- ===================== -->
    <div class="sidebar-section">
        <div class="sidebar-title">Outils</div>

        <a href="/backoffice/tests">
            <i class="fas fa-vial me-2"></i> Tests & Stats
        </a>

        <a href="/backoffice/api-tester">
            <i class="fas fa-terminal me-2"></i> Testeur d'API
        </a>
    </div>

    <hr>

    <a href="/" class="text-danger">
        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
    </a>
</div>
</div>

<script>
    // Récupérer le nombre de messages non lus
    function fetchUnreadMessagesCount() {
        fetch('/index.php?pageAPI=messages&action=getUnreadCount', {  // <-- /index.php au lieu de /backoffice
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'token=WDIhUThWMz9aN0Y0VDFwOUE2'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.count !== undefined) {
                    const badge = document.getElementById('unread-messages-badge');
                    if (badge) {
                        badge.textContent = data.count;
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }

    // Surligner l'élément actif dans la barre latérale
    function highlightActiveMenu() {
        const currentPath = window.location.pathname.replace(/\/$/, '');
        document.querySelectorAll('.sidebar a').forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;
            const cleanHref = href.replace(/\/$/, '');
            if (currentPath === cleanHref || (currentPath.startsWith(cleanHref) && cleanHref !== '/backoffice')) {
                link.classList.add('active');
            }
        });
    }

    // Attends que le DOM soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            fetchUnreadMessagesCount();
            highlightActiveMenu();
            setInterval(fetchUnreadMessagesCount, 30000);
        });
    } else {
        fetchUnreadMessagesCount();
        highlightActiveMenu();
        setInterval(fetchUnreadMessagesCount, 30000);
    }
</script>

<div class="main-content">

