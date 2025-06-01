<?php

use core\Database;

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function safeRedirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href = '$url';</script>";
        exit;
    }
}

function formatProductPrice($price) {
    return number_format(max(0, floatval($price)), 2, '.', ' ');
}

function getProductAvailabilityClass($stock) {
    if ($stock > 10) return 'text-success';
    if ($stock > 0) return 'text-warning';
    return 'text-danger';
}

function renderProductBadges($product) {
    $badges = [];
    if (isset($product['is_new']) && $product['is_new']) {
        $badges[] = '<span class="badge bg-success me-2">Новинка</span>';
    }
    if (isset($product['discount']) && $product['discount'] > 0) {
        $badges[] = '<span class="badge bg-danger me-2">-' . $product['discount'] . '%</span>';
    }
    return implode('', $badges);
}

try {
    require_once '../../backend/core/Database.php';

    $db = new Database();

    $pdo = $db->getConnection();

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Не передано ідентифікатор товару');
    }

    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id === null) {
        throw new Exception('Некоректний ідентифікатор товару');
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Товар не знайдений');
        }
    } catch (PDOException $e) {
        error_log('Помилка бази даних: ' . $e->getMessage());
        throw new Exception('Не вдалося отримати дані про товар');
    }
} catch (Exception $e) {
    error_log('Помилка на сторінці товару: ' . $e->getMessage());

    $_SESSION['error_message'] = $e->getMessage();

    safeRedirect('../../index.php');
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
    <link href="../../public/assets/css/product.css" rel="stylesheet">
</head>
<body>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php
        echo htmlspecialchars($_SESSION['error_message']);
        unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                    <a class="nav-link" href="../orders/cart.php">
                        <i class="fas fa-shopping-cart me-1"></i> Кошик
                        <span class="badge bg-light text-dark">
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <?php try { ?>
        <?php
        try {
            $images = explode("\n", $product['image']);
            $mainImage = trim($images[0]);

            if (empty($mainImage)) {
                throw new Exception('Зображення товару відсутнє');
            }

            $allImages = htmlspecialchars(json_encode(array_map('trim', $images)));
        } catch (Exception $e) {
            error_log('Помилка обробки зображень: ' . $e->getMessage());
            $mainImage = '../../public/assets/img/placeholder.png';
            $allImages = '[]';
        }
        ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="gallery-slider position-relative">
                    <img src="<?php echo htmlspecialchars($mainImage); ?>"
                         class="img-fluid rounded main-product-image"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="mainProductImage"
                         onclick="openModal()">

                    <?php if (count($images) > 1): ?>
                        <div class="image-thumbnails mt-3 d-flex">
                            <?php foreach ($images as $img): ?>
                                <?php $img = trim($img); if (empty($img)) continue; ?>
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                     class="img-thumbnail"
                                     alt="Thumbnail"
                                     onclick="changeMainImage(this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-header mb-4">
                    <?php echo renderProductBadges($product); ?>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                </div>

                <p class="text-primary fs-3 fw-bold">
                    <?php echo formatProductPrice($product['price']); ?> грн
                    <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <span class="text-secondary text-decoration-line-through ms-2" style="font-size: 1.2rem; opacity: 0.7;">
                            <?php echo formatProductPrice($product['old_price']); ?> грн
                        </span>
                    <?php endif; ?>
                </p>

                <div class="product-meta mb-4">
                    <p><?php echo htmlspecialchars($product['description'] ?? 'Опис відсутній'); ?></p>

                    <div class="stock-info">
                        <p class="<?php echo getProductAvailabilityClass($product['stock'] ?? 0); ?>">
                            <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                <i class="fas fa-check-circle me-1"></i> В наявності: <?php echo intval($product['stock']); ?> шт.
                            <?php else: ?>
                                <i class="fas fa-times-circle me-1"></i> Немає в наявності
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="product-actions">
                    <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                        <form action="../orders/cart.php" method="get">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                            <div class="quantity-selector mb-4">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-sort-amount-up me-1"></i> Кількість:
                                </label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                           class="form-control text-center"
                                           id="quantity"
                                           name="quantity"
                                           value="1"
                                           min="1"
                                           max="<?php echo $product['stock']; ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-cart-plus me-2"></i> Додати в кошик
                                </button>
                                <a href="../../index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Повернутися до списку товарів
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-ban me-2"></i> Немає в наявності
                            </button>
                            <a href="../../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Повернутися до списку товарів
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-gradient-primary py-3">
                        <h3 class="card-title text-white mb-0 font-weight-bold">
                            <i class="fas fa-info-circle me-2"></i>Детальний опис
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        $full_description = $product['full_description'] ?? 'Детальний опис відсутній';
                        ?>
                        <div class="description-container">
                            <p class="description-text">
                                <?php echo nl2br(htmlspecialchars($full_description)); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } catch (Exception $e) {
        error_log('Помилка рендерингу сторінки товару: ' . $e->getMessage());
        ?>
        <div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Технічна проблема!</strong>
            Виникла помилка при відображенні сторінки товару.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
</div>

<div id="imageModal" class="image-gallery-modal">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <div class="modal-content">
        <img id="modalImage" class="modal-image" src="" alt="Product Image">
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
                    <a href="https://t.me/mikmik2492" class="text-white me-3"><i class="fab fa-telegram fa-lg"></i></a>
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

<div class="image-zoom-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../../public/assets/js/main.js"></script>

<script>
    function changeMainImage(thumbnail) {
        const mainImage = document.querySelector('.main-product-image');
        mainImage.src = thumbnail.src;

        document.getElementById('modalImage').src = thumbnail.src;
    }

    function changeQuantity(delta) {
        const quantityInput = document.getElementById('quantity');
        let currentValue = parseInt(quantityInput.value);
        let newValue = currentValue + delta;

        if (newValue >= parseInt(quantityInput.min) && newValue <= parseInt(quantityInput.max)) {
            quantityInput.value = newValue;
        }
    }

    function openModal() {
        const modal = document.getElementById('imageModal');
        const mainImage = document.getElementById('mainProductImage');
        const modalImage = document.getElementById('modalImage');

        modalImage.src = mainImage.src;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    const mainImage = document.querySelector('.main-product-image');
    const zoomContainer = document.querySelector('.image-zoom-container');

    mainImage.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();

        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;

        zoomContainer.style.backgroundImage = `url(${this.src})`;
        zoomContainer.style.backgroundPosition = `${x * 100}% ${y * 100}%`;
        zoomContainer.style.opacity = 1;
    });

    mainImage.addEventListener('mouseout', function() {
        zoomContainer.style.opacity = 0;
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
</body>
</html>