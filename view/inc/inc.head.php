<?php
    $currentPage = strtok(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '?');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Délices Fruités - Pâtes de Fruits Haut de Gamme</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #2D5A27;    /* Vert Forêt */
            --secondary-color: #FCF8F3;  /* Crème */
            --accent-color: #FF6B6B;     /* Corail */
            --text-dark: #2C3E50;        /* Ardoise */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdfdfd;
            color: var(--text-dark);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
        }
        .nav-link.active {
            font-weight: 600;
            color: var(--primary-color) !important;
            border-bottom: 2px solid var(--primary-color);
        }
        .btn-primary, .btn-outline-dark, .dropdown-toggle, .btn-outline-secondary {
            padding: 0.6rem 1.5rem !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 48px !important;
            white-space: nowrap !important;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #24491f;
            border-color: #24491f;
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/random/1600x900/?fruit') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 150px 0;
        }
        /* Alignement de la croix de fermeture dans les alertes */
        #error-container .alert {
            position: relative;
            padding-right: 3rem; /* Espace pour la croix */
            border-radius: 0.5rem;
            border-left: 4px solid #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }

        #error-container .btn-close {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%); /* Centre verticalement */
            padding: 0.5rem;
            margin: 0;
        }
        .product-card {
            transition: transform 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card img {
            height: 200px;
            object-fit: cover;
        }
        .hero-section {
            position: relative;
            padding: 150px 0;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            filter: blur(4px);
            z-index: -1;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }
        footer {
            background-color: var(--primary-color);
            color: rgba(255, 255, 255, 0.8);
        }
        footer h1, footer h2, footer h3, footer h4, footer h5, footer h6 {
            color: white !important;
        }
        footer a {
            color: white;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        footer a:hover {
            color: white;
            opacity: 0.7;
        }
        footer .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        footer .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: white;
            box-shadow: none;
            color: white;
        }
        .testimonial {
            background-color: var(--secondary-color);
            padding: 30px;
            border-radius: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        .testimonial:hover {
            transform: scale(1.02);
        }
        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: none;
            background-color: var(--primary-color);
            opacity: 0.3;
        }
        .carousel-indicators .active {
            opacity: 1;
        }
        /* Style des contrôles du carrousel */
        .carousel-control-prev, .carousel-control-next {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 1;
            transition: all 0.3s ease;
        }
        .carousel-control-prev {
            left: -60px;
        }
        .carousel-control-next {
            right: -60px;
        }
        .carousel-control-prev:hover, .carousel-control-next:hover {
            background-color: var(--text-dark);
            transform: translateY(-50%) scale(1.1);
        }
        .carousel-control-prev-icon, .carousel-control-next-icon {
            width: 20px;
            height: 20px;
        }
        
        /* Ajustement du conteneur pour laisser de la place aux flèches sur PC */
        @media (min-width: 1200px) {
            #testimonialCarousel {
                padding: 0 60px;
            }
            .carousel-control-prev {
                left: 0;
            }
            .carousel-control-next {
                right: 0;
            }
        }
        
        @media (max-width: 991px) {
            .carousel-control-prev, .carousel-control-next {
                display: none !important;
            }
            #testimonialCarousel {
                padding: 0;
            }
        }
        .search-bar {
            width: 200px;
        }

        /* Style pour les étapes */
        .step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }
        .step.active .step-icon {
            background-color: #0d6efd;
            color: white;
        }
        .step.completed .step-icon {
            background-color: #198754;
            color: white;
        }
        .step-icon {
            position: relative;
            z-index: 2;         /* au-dessus du trait */

            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }
        .step-label {
            z-index: 10;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .step.active .step-label,
        .step.completed .step-label {
            color: #212529;
            font-weight: 500;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: #e9ecef;
            z-index: 0; /* derrière */
        }
        .step.active::after,
        .step.completed::after {
            background-color: #0d6efd;
        }

        @media (max-width: 768px) {
            .footer-column {
                text-align: center;
                margin-bottom: 20px;
            }
        }

        /* Style Adresses */
         .address-card {
             border: 1px solid #e0e0e0;
             border-radius: 8px;
             padding: 15px;
             margin-bottom: 10px;
             cursor: pointer;
             transition: all 0.2s;
         }
        .address-card:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.1);
        }
        .address-card.selected {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .modal-header {
            border-bottom: 1px solid #e0e0e0;
            padding: 15px;
        }
        .modal-title {
            font-weight: 600;
            color: #333;
        }
        .btn-light-blue {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        .btn-light-blue:hover {
            background-color: #bbdefb;
        }
        .filter-white {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
