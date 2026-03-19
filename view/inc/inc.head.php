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
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/random/1600x900/?fruit') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 150px 0;
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
        @media (max-width: 768px) {
            .footer-column {
                text-align: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
</html>
