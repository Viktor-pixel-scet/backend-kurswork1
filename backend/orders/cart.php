<?php

use core\Database;

session_start();
require_once '../../backend/core/Database.php';

$db = new Database();

$pdo = $db->getConnection();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            if ($_GET['action'] === 'add') {
                $product_id = intval($_GET['id']);
                $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $product = $stmt->fetch();

                    if ($product) {
                        if ($quantity > $product['stock']) {
                            $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                            $quantity = $product['stock'];
                        }

                        $found = false;
                        foreach ($_SESSION['cart'] as &$item) {
                            if ($item['id'] == $product_id) {
                                $total_requested = $item['quantity'] + $quantity;

                                if ($total_requested <= $product['stock']) {
                                    $item['quantity'] += $quantity;
                                } else {
                                    $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                                    $item['quantity'] = $product['stock'];
                                }

                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            if ($product['stock'] >= $quantity) {
                                $_SESSION['cart'][] = [
                                    'id' => $product['id'],
                                    'name' => $product['name'],
                                    'price' => $product['price'],
                                    'quantity' => $quantity
                                ];
                            } else {
                                $_SESSION['error'] = "Товар тимчасово відсутній на складі.";
                            }
                        }

                        header('Location: cart.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log('Error processing cart action: ' . $e->getMessage());
                    $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
                    header('Location: cart.php');
                    exit;
                }
            }

            if ($_GET['action'] === 'remove') {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $product_id) {
                        unset($_SESSION['cart'][$key]);
                        break;
                    }
                }

                $_SESSION['cart'] = array_values($_SESSION['cart']);
                header('Location: cart.php');
                exit;
            }

            if ($_GET['action'] === 'increase') {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $product_id) {
                        if ($item['quantity'] < $product['stock']) {
                            $item['quantity']++;
                        } else {
                            $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                        }
                        break;
                    }
                }

                header('Location: cart.php');
                exit;
            }

            if ($_GET['action'] === 'decrease') {
                foreach ($_SESSION['cart'] as $key => &$item) {
                    if ($item['id'] == $product_id) {
                        $item['quantity']--;

                        if ($item['quantity'] <= 0) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                        }
                        break;
                    }
                }

                header('Location: cart.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Error processing cart action: ' . $e->getMessage());
        $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
        header('Location: cart.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];

    header('Location: cart.php');
    exit;
}

$cart_items = array_values($_SESSION['cart']);

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
    <link href="../../public/assets/css/cart.css" rel="stylesheet">
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
                    <a class="nav-link active" href="cart.php">
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
    <a href="../../index.php" class="back-to-shop mb-4">
        <i class="fas fa-arrow-left"></i> Повернутися до покупок
    </a>

    <h1>Кошик</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart-container">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="alert alert-info">
                Ваш кошик порожній.
                <a href="../../index.php" class="alert-link">Перейти до магазину</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Товар</th>
                            <th class="text-center">Ціна</th>
                            <th class="text-center">Кількість</th>
                            <th class="text-center">Сума</th>
                            <th class="text-center">Дії</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-laptop text-primary me-3"></i>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    </div>
                                </td>
                                <td class="text-center price-column"><?php echo number_format($item['price'], 2, '.', ' '); ?> грн</td>
                                <td class="text-center">
                                    <div class="quantity-control">
                                        <a href="cart.php?action=decrease&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-minus"></i>
                                        </a>
                                        <span class="mx-2"><?php echo $item['quantity']; ?></span>
                                        <a href="cart.php?action=increase&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center price-column"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</td>
                                <td class="text-center">
                                    <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-remove" title="Видалити">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Загальна сума:</td>
                            <td class="text-center total-price"><?php echo number_format($total_price, 2, '.', ' '); ?> грн</td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3 mb-md-0">
                <div class="card">
                    <div class="card-body info-card">
                        <h5 class="mb-3">Інформація про замовлення</h5>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Товарів:</strong></p>
                            </div>
                            <div class="col-6 text-end">
                                <p><?php echo count($cart_items); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Загальна кількість:</strong></p>
                            </div>
                            <div class="col-6 text-end">
                                <p>
                                    <?php
                                    $total_quantity = 0;
                                    foreach ($cart_items as $item) {
                                        $total_quantity += $item['quantity'];
                                    }
                                    echo $total_quantity;
                                    ?>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>До сплати:</strong></p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="total-price"><?php echo number_format($total_price, 2, '.', ' '); ?> грн</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="d-grid gap-3">
                    <a href="checkout.php" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i> Оформити замовлення
                    </a>
                    <a href="cart.php?action=clear" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i> Очистити кошик
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                <p><i class="bi bi-telephone me-2"></i>+380 98 60 57 400</p>
                <p><i class="bi bi-envelope me-2"></i>ipz235_mva@student.ztu.edu.ua</p>
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