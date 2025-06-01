<?php

use core\Database;

session_start();
require_once '../../backend/core/Database.php';

$db = new Database();

$pdo = $db->getConnection();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total_price = 0;
$stock_error = false;

try {
    foreach ($_SESSION['cart'] as $cart_item) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$cart_item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            continue;
        }

        if ($product['stock'] < $cart_item['quantity']) {
            if ($product['stock'] > 0) {
                $cart_item['quantity'] = $product['stock'];
                $stock_error = true;
                $_SESSION['error'] = "Деякі товари були оновлені через обмежену доступність на складі.";
            } else {
                continue;
            }
        }

        $cart_item['name'] = $product['name'];
        $cart_item['price'] = $product['price'];

        $cart_items[] = $cart_item;
        $total_price += $cart_item['price'] * $cart_item['quantity'];
    }

    $_SESSION['cart'] = $cart_items;

    if (empty($cart_items)) {
        $_SESSION['error'] = "Ваш кошик порожній або товари недоступні.";
        header('Location: cart.php');
        exit;
    }

    if ($stock_error) {
        header('Location: cart.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error verifying cart: ' . $e->getMessage());
    $_SESSION['error'] = "Виникла помилка при перевірці кошика.";
    header('Location: cart.php');
    exit;
}

$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'payment_method' => 'cash'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        $errors['name'] = 'Введіть ваше ім\'я';
    } else {
        $form_data['name'] = trim($_POST['name']);
    }

    if (empty($_POST['email'])) {
        $errors['email'] = 'Введіть вашу електронну пошту';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введіть коректну електронну пошту';
    } else {
        $form_data['email'] = trim($_POST['email']);
    }

    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Введіть ваш номер телефону';
    } else {
        $form_data['phone'] = trim($_POST['phone']);
    }

    if (empty($_POST['address'])) {
        $errors['address'] = 'Введіть вашу адресу доставки';
    } else {
        $form_data['address'] = trim($_POST['address']);
    }

    if (isset($_POST['payment_method']) && in_array($_POST['payment_method'], ['cash', 'card'])) {
        $form_data['payment_method'] = $_POST['payment_method'];
    } else {
        $errors['payment_method'] = 'Виберіть спосіб оплати';
    }

    if (empty($errors)) {
        $_SESSION['order'] = [
            'items' => $cart_items,
            'total_price' => $total_price,
            'customer' => $form_data
        ];

        header('Location: ../../public/process_order.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
    <link href="../../public/assets/css/checkout.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <i class="fas fa-laptop me-2"></i>Ноутбук-Маркет
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../../index.php">
                        <i class="fas fa-home me-1"></i> Головна
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart me-1"></i> Кошик
                        <span class="badge bg-light text-dark">
                            <?php echo count($cart_items); ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <a href="cart.php" class="back-to-cart mb-4">
        <i class="fas fa-arrow-left"></i> Повернутися до кошика
    </a>

    <h1>Оформлення замовлення</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Інформація для доставки</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Ім'я та прізвище <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['name']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Електронна пошта <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['email']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Номер телефону <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="+380 XX XXX XX XX" required>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['phone']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" id="address" name="address" rows="3" placeholder="Місто, вулиця, будинок, квартира" required><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['address']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="fas fa-credit-card me-2"></i>Спосіб оплати</h5>

                        <div class="mb-4">
                            <div class="card border-0 shadow-sm p-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" <?php echo $form_data['payment_method'] === 'cash' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="payment_cash">
                                        <i class="fas fa-money-bill text-success me-2"></i> Готівкою при отриманні
                                    </label>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm p-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" <?php echo $form_data['payment_method'] === 'card' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="payment_card">
                                        <i class="fas fa-credit-card text-primary me-2"></i> Оплата карткою онлайн
                                    </label>
                                </div>
                            </div>

                            <?php if (isset($errors['payment_method'])): ?>
                                <div class="text-danger mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo $errors['payment_method']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i> Підтвердити замовлення
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Повернутися до кошика
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Ваше замовлення</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Кількість: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="price-column"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">Загальна сума</span>
                            <span class="total-price"><?php echo number_format($total_price, 2, '.', ' '); ?> грн</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Інформація про доставку</h5>
                    <p><i class="fas fa-truck me-2"></i> Безкоштовна доставка по Україні</p>
                    <p><i class="fas fa-clock me-2"></i> Термін доставки: 1-3 робочих дні</p>
                    <p><i class="fas fa-shield-alt me-2"></i> Гарантія на всі товари</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-laptop me-2"></i>Ноутбук-Маркет</h5>
                <p>Найкращі ноутбуки за найкращими цінами</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-telegram fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-3">
                <h5>Контакти</h5>
                <p><i class="fas fa-phone me-2"></i> +380 99 123 45 67</p>
                <p><i class="fas fa-envelope me-2"></i> info@notebook-market.ua</p>
            </div>
            <div class="col-md-3 text-md-end">
                <p>&copy; 2025 Ноутбук-Маркет. Всі права захищені.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../../public/assets/js/main.js"></script>
</body>
</html>