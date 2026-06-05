<body>
<!-- Header -->
<header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light container">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="/accueil">
                <img src="/images/logo.png" alt="Les Délices Fruités" height="45" class="me-2">
                <span class="fw-bold" style="font-family: 'Outfit', sans-serif; color: var(--primary-color);">Les Délices Fruités</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php e(str_starts_with($currentPage, '/accueil') ? 'active' : ''); ?>" href="/accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php e(str_starts_with($currentPage, '/produit') ? 'active' : ''); ?>" href="/produit">Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php e(str_starts_with($currentPage, '/about') ? 'active' : ''); ?>" href="/about">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php e(str_starts_with($currentPage, '/contact') ? 'active' : ''); ?>" href="/contact">Contact</a>                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') : ?>
                        <a href="/backoffice" class="btn btn-outline-dark me-2" title="Backoffice">
                            <i class="fas fa-cog"></i>
                        </a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['idClient'])) : ?>
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php e($_SESSION['prenomClient']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profil"><i class="fas fa-id-card me-2"></i>Mon Profil</a></li>
                                <li><a class="dropdown-item" href="/commandes"><i class="fas fa-box me-2"></i>Mes Commandes</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/deconnexion"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else : ?>
                        <a href="/connexion" class="btn btn-outline-dark me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    <?php endif; ?>

                    <a href="/panier" class="btn btn-primary shadow-sm">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Panier
                        <?php
                        $nbArticles = getNombreArticlesDansPanier();
                        if ($nbArticles > 0) :
                            ?>
                            <span class="ms-2 badge bg-white text-primary rounded-circle">
                                <?php e($nbArticles); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Main -->
<main>