<?php

?>

<link rel="stylesheet" href="/backend-kurswork/public/assets/css/includes.css">

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="admin.php?action=dashboard">
        <i class="fas fa-laptop me-2"></i>Адмін-панель
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Відкрити меню">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-nav">
        <div class="nav-item text-nowrap d-flex">
            <a class="nav-link px-3 text-white" href="http://localhost/backend-kurswork" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i> Перегляд сайту
            </a>
            <a class="nav-link px-3 btn btn-outline-secondary text-white" href="admin.php?action=logout">
                <i class="fas fa-sign-out-alt me-1"></i> Вийти з режиму адміністратора
            </a>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($_GET['action']) || $_GET['action'] === 'dashboard' ? 'active' : '' ?>" href="admin.php?action=dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($_GET['action']) && $_GET['action'] === 'products' ? 'active' : '' ?>" href="admin.php?action=products">
                            <i class="fas fa-laptop me-1"></i> Товари
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($_GET['action']) && $_GET['action'] === 'orders' ? 'active' : '' ?>" href="admin.php?action=orders">
                            <i class="fas fa-shopping-cart me-1"></i> Замовлення
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($_GET['action']) && $_GET['action'] === 'customers' ? 'active' : '' ?>" href="admin.php?action=customers">
                            <i class="fas fa-users me-1"></i> Клієнти
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= isset($_GET['action']) && $_GET['action'] === 'games' ? 'active' : '' ?>" href="admin.php?action=games">
                            <i class="fas fa-gamepad me-1"></i> Ігри
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Сервіси</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="admin.php?action=logout">
                            <i class="fas fa-sign-out-alt me-1"></i> Вийти
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">