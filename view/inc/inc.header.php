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
                        <a class="nav-link <?= str_starts_with($currentPage, '/accueil') ? 'active' : '' ?>" href="/accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentPage, '/produit') ? 'active' : '' ?>" href="/produit">Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentPage, '/about') ? 'active' : '' ?>" href="/about">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentPage, '/contact') ? 'active' : '' ?>" href="/contact">Contact</a>                    </li>
                </ul>
                <form class="d-flex mb-3 mb-lg-0 me-lg-3">
                    <input class="form-control search-bar" type="search" placeholder="Rechercher...">
                </form>
                <div class="d-flex">
                    <a href="/backoffice" class="btn btn-outline-dark me-2">
                        <i class="">BO</i>
                    </a>
                    <a href="/profil" class="btn btn-outline-dark me-2">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="/panier" class="btn btn-outline-dark d-flex align-items-center">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Panier
                        <?php
                        $nbArticles = getNombreArticlesDansPanier();
                        if ($nbArticles > 0) :
                            ?>
                            <span class="ms-2 badge bg-danger rounded-circle">
                                <?= $nbArticles ?>
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