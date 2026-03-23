<?php
session_start();
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food Tizimi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#FF6B6B">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --secondary-gradient: linear-gradient(135deg, #4ECDC4 0%, #556270 100%);
            --accent-color: #FFD93D;
            --dark-color: #2D3436;
            --light-bg: #F0F2F5;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --card-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
            background-image: radial-gradient(at 0% 0%, rgba(255, 107, 107, 0.05) 0, transparent 50%), 
                              radial-gradient(at 100% 100%, rgba(78, 205, 196, 0.05) 0, transparent 50%);
            min-height: 100vh;
        }
        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--card-shadow);
        }
        .navbar-brand {
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }
        .nav-link {
            font-weight: 600;
            color: var(--dark-color) !important;
            margin: 0 8px;
            padding: 8px 16px !important;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-link:hover {
            background: rgba(255, 107, 107, 0.1);
            color: #FF6B6B !important;
            transform: translateY(-2px);
        }
        .nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        .card {
            border: none;
            border-radius: 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 24px;
            border-radius: 16px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.2);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-egg-fried"></i> FAST FOOD</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">Kassa</a></li>
                
                <?php if ($role == 'admin'): ?>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>" href="categories.php">Kategoriyalar</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="products.php">Maxsulotlar</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'active' : '' ?>" href="tables.php">Stollar</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'waiters.php' ? 'active' : '' ?>" href="waiters.php">Ishchilar</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">Foydalanuvchilar</a></li>
                <?php endif; ?>
                <?php if ($role == 'admin'): ?>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">Hisobot</a></li>
                <?php endif; ?>
                
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle bg-light rounded px-3" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= $_SESSION['username'] ?? 'User' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><span class="dropdown-item-text small text-muted"><?= ucfirst($role) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Chiqish</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
