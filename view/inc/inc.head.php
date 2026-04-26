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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #F5DEB3;
            --accent-color: #D2691E;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }
        .nav-link.active {
            font-weight: bold;
            color: var(--accent-color) !important;
            border-bottom: 2px solid var(--accent-color);
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
            color: white;
        }
        .testimonial {
            background-color: var(--secondary-color);
            padding: 20px;
            border-radius: 10px;
            height: 100%;
            display: flex;
            flex-direction: column;
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
    </style>
</head>
