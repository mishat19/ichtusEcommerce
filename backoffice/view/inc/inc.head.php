<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f7fb;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #1e293b;
            color: white;
            padding: 20px;
        }

        .sidebar a {
            color: #cbd5f5;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background: #334155;
            color: white;
        }

        /* Content */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        /* Cards */
        .card {
            border-radius: 12px;
        }

        /* Table */
        table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>