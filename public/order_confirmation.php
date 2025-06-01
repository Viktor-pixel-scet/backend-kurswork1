<?php

use core\Database;

session_start();
require_once '../backend/core/Database.php';

$db = new Database();

$pdo = $db->getConnection();

if (!isset($_SESSION['order_number']) || empty($_SESSION['order_number'])) {
    header('Location: index.php');
    exit;
}

$order_number = $_SESSION['order_number'];

try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.name, c.email, c.phone, c.address 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.order_number = ?
    ");
    $stmt->execute([$order_number]);
    $order_data = $stmt->fetch();

    if (!$order_data) {
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_data['id']]);
    $order_items = $stmt->fetchAll();

    $order = [
        'total_price' => $order_data['total_amount'],
        'customer' => [
            'name' => $order_data['name'],
            'email' => $order_data['email'],
            'phone' => $order_data['phone'],
            'address' => $order_data['address'],
            'payment_method' => $order_data['payment_method']
        ],
        'items' => $order_items
    ];

} catch (PDOException $e) {
    error_log('Error retrieving order: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Замовлення підтверджено - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/order_confirmation.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <i class="bi bi-laptop me-2"></i>Ноутбук-Маркет
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="bi bi-house-door me-1"></i>Головна
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../backend/orders/cart.php">
                        <i class="bi bi-cart3 me-1"></i>Кошик
                        <span class="badge bg-danger rounded-pill">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card success-card">
                <div class="card-header text-white text-center">
                    <h3 class="mb-0 fw-bold">Замовлення успішно оформлено!</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h2 class="mt-3 fw-bold">Дякуємо за ваше замовлення!</h2>
                        <p class="lead">Ваше замовлення успішно оформлено та прийнято до обробки.</p>
                    </div>

                    <div class="order-number mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-receipt text-success fs-1"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Номер вашого замовлення:</h5>
                                <h4 class="mb-1"><strong><?php echo $order_number; ?></strong></h4>
                                <p class="mb-0 text-muted">Будь ласка, збережіть цей номер для подальшого відстеження.</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h5><i class="bi bi-bag-check me-2"></i>Деталі замовлення</h5>
                        <ul class="list-group mb-3">
                            <?php foreach ($order['items'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?php echo $item['name']; ?></h6>
                                        <div class="d-flex gap-3">
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-hash"></i>
                                                Арт: <?php echo $item['product_id']; ?>
                                            </span>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-box"></i>
                                                <?php echo $item['quantity']; ?> шт
                                            </span>
                                        </div>
                                    </div>
                                    <span class="item-price"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between total-row">
                                <span class="fw-bold">Загальна сума:</span>
                                <span class="fs-5 text-success"><?php echo number_format($order['total_price'], 2, '.', ' '); ?> грн</span>
                            </li>
                        </ul>
                    </div>

                    <div class="info-section">
                        <h5><i class="bi bi-truck me-2"></i>Інформація про доставку</h5>
                        <div class="customer-info-item">
                            <div class="info-label"><i class="bi bi-person me-2"></i>Ім'я:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer']['name']); ?></div>
                        </div>
                        <div class="customer-info-item">
                            <div class="info-label"><i class="bi bi-envelope me-2"></i>Email:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer']['email']); ?></div>
                        </div>
                        <div class="customer-info-item">
                            <div class="info-label"><i class="bi bi-telephone me-2"></i>Телефон:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer']['phone']); ?></div>
                        </div>
                        <div class="customer-info-item">
                            <div class="info-label"><i class="bi bi-geo-alt me-2"></i>Адреса:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer']['address']); ?></div>
                        </div>
                        <div class="customer-info-item">
                            <div class="info-label"><i class="bi bi-credit-card me-2"></i>Оплата:</div>
                            <div class="info-value">
                                <?php if ($order['customer']['payment_method'] === 'cash'): ?>
                                    <span class="payment-method payment-cash">
                                        <i class="bi bi-cash-coin me-1"></i>Готівкою при отриманні
                                    </span>
                                <?php else: ?>
                                    <span class="payment-method payment-card">
                                        <i class="bi bi-credit-card-2-front me-1"></i>Оплата карткою онлайн
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>На головну
                        </a>
                        <a href="../index.php" class="btn continue-btn btn-primary">
                            Продовжити покупки
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-white mt-5">
    <div class="container">
        <div class="row py-4">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-laptop me-2"></i>НоутбукМаркет
                </h5>
                <p class="mb-3">Найкращі ноутбуки за найкращими цінами</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-telegram"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">Контакти</h5>
                <p><i class="bi bi-geo-alt me-2"></i>м. Житомир, вул. Чуднівська, 103</p>
                <p><i class="bi bi-telephone me-2"></i>+380 98 60 57 400</p>
                <p><i class="bi bi-envelope me-2"></i>ipz235_mva@student.ztu.edu.ua</p>
            </div>
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Допомога</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none mb-2 d-block">Доставка та оплата</a></li>
                    <li><a href="#" class="text-white text-decoration-none mb-2 d-block">Гарантія та повернення</a></li>
                    <li><a href="#" class="text-white text-decoration-none mb-2 d-block">Сервісний центр</a></li>
                    <li><a href="#" class="text-white text-decoration-none mb-2 d-block">Контакти</a></li>
                </ul>
            </div>
        </div>
        <div class="border-top border-secondary pt-4 pb-2">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 Ноутбук-Маркет. Всі права захищені.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Дизайн та розробка: НоутбукМаркет</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>