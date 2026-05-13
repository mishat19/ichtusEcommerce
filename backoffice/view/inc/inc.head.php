<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome & Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <style>
        :root {
            --bo-primary: #4f46e5;
            --bo-primary-hover: #4338ca;
            --bo-secondary: #64748b;
            --bo-success: #10b981;
            --bo-warning: #f59e0b;
            --bo-danger: #ef4444;
            --bo-info: #3b82f6;
            
            --bo-bg: #f8fafc;
            --bo-sidebar-bg: #0f172a;
            --bo-card-bg: #ffffff;
            --bo-border: #e2e8f0;
            
            --bo-text-main: #1e293b;
            --bo-text-muted: #64748b;
            
            --font-main: 'Inter', system-ui, -apple-system, sans-serif;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            background-color: var(--bo-bg);
            color: var(--bo-text-main);
            font-family: var(--font-main);
            font-size: 0.9375rem;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: var(--bo-sidebar-bg);
            color: white;
            padding: 1.5rem;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h4 {
            font-weight: 700;
            letter-spacing: -0.025em;
            color: #fff;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar a {
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar a i {
            width: 20px;
            font-size: 1.1rem;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .sidebar a.active {
            background: var(--bo-primary);
            color: white;
        }

        /* Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* Cards & UI Components */
        .bo-card {
            background: var(--bo-card-bg);
            border-radius: 1rem;
            border: 1px solid var(--bo-border);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .bo-card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--bo-text-main);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Stats Component */
        .bo-stat {
            background: var(--bo-card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--bo-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .bo-stat:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }

        .bo-stat-label {
            color: var(--bo-text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.5rem;
        }

        .bo-stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--bo-text-main);
            line-height: 1;
        }

        .bo-stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--bo-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        /* Tables */
        .bo-table-container {
            overflow-x: auto;
        }

        .bo-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .bo-table th {
            background: #f8fafc;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--bo-text-muted);
            border-bottom: 1px solid var(--bo-border);
        }

        .bo-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--bo-border);
        }

        .bo-table tr:last-child td {
            border-bottom: none;
        }

        .bo-table tr:hover td {
            background-color: #fcfcfd;
        }

        /* Badges */
        .bo-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            text-transform: capitalize;
        }

        .bo-badge-payee, .bo-badge-accepte { background: #dcfce7; color: #166534; }
        .bo-badge-en_attente { background: #fef9c3; color: #854d0e; }
        .bo-badge-annulee, .bo-badge-refuse { background: #fee2e2; color: #991b1b; }

        /* Buttons */
        .bo-btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bo-primary);
            background: transparent;
            border: 1px solid var(--bo-primary);
            border-radius: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .bo-btn-outline:hover {
            background: var(--bo-primary);
            color: white;
        }

        /* API UI */
        .bo-api-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1.25rem;
            border-radius: 0.75rem;
            font-family: 'Courier New', Courier, monospace;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .bo-api-url {
            color: #38bdf8;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .bo-api-desc {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .bo-method {
            font-weight: bold;
            color: #10b981;
            margin-right: 0.5rem;
        }

        .filtre-btn {
            background: #fff;
            border: 1px solid var(--bo-border);
            color: var(--bo-text-muted);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filtre-btn:hover {
            border-color: #2D5A27;
            color: #2D5A27;
            background: rgba(45, 90, 39, 0.05);
        }

        .filtre-btn.active {
            background: #2D5A27;
            color: #fff;
            border-color: #2D5A27;
            box-shadow: 0 4px 6px -1px rgba(45, 90, 39, 0.2);
        }

        .bo-search-input {
            margin-left: auto;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border: 1px solid var(--bo-border);
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.2s;
            width: 300px;
        }

        .bo-search-input:focus {
            border-color: #2D5A27;
            box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
        }

        /* Stock Visuals */
        .stack-box {
            user-select: none;
        }
        
        .product-line {
            background: #fff;
            border: 1px solid var(--bo-border);
            padding: 0.5rem;
            border-radius: 12px;
            transition: border-color 0.2s;
        }
        .product-line:focus-within {
            border-color: var(--bo-primary);
        }
    </style>
</head>
<body>