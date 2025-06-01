<?php

use core\Database;

session_start();

require_once 'backend/core/Database.php';

// --- Глобальний перехоплювач фатальних помилок ---
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Location: /backend-kurswork/public/views/errors/500.php');
        exit;
    }
});

$db = new Database();
$pdo = $db->getConnection();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$min_price = 0;
$max_price = 100000;
$screen_sizes = [];
$video_card_types = [];
$storage_types = [];
$min_weight = 0;
$max_weight = 5;
$brands = [];

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] > 0) {
    $max_price = floatval($_GET['max_price']);
}

if (isset($_GET['screen_sizes']) && is_array($_GET['screen_sizes'])) {
    $screen_sizes = array_map('floatval', $_GET['screen_sizes']);
}

if (isset($_GET['video_card_types']) && is_array($_GET['video_card_types'])) {
    $video_card_types = $_GET['video_card_types'];
}

if (isset($_GET['storage_types']) && is_array($_GET['storage_types'])) {
    $storage_types = $_GET['storage_types'];
}

if (isset($_GET['min_weight']) && is_numeric($_GET['min_weight'])) {
    $min_weight = floatval($_GET['min_weight']);
}

if (isset($_GET['max_weight']) && is_numeric($_GET['max_weight'])) {
    $max_weight = floatval($_GET['max_weight']);
}

if (isset($_GET['brands']) && is_array($_GET['brands'])) {
    $brands = $_GET['brands'];
}

try {
    $sql = "SELECT p.*, pi.image_filename
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE p.stock > 0 
            AND p.price >= ? AND p.price <= ?
            AND p.device_weight >= ? AND p.device_weight <= ?";

    $params = [$min_price, $max_price, $min_weight, $max_weight];

    if (!empty($screen_sizes)) {
        $sql .= " AND p.screen_size IN (" . implode(',', array_fill(0, count($screen_sizes), '?')) . ")";
        $params = array_merge($params, $screen_sizes);
    }

    if (!empty($video_card_types)) {
        $sql .= " AND p.video_card_type IN (" . implode(',', array_fill(0, count($video_card_types), '?')) . ")";
        $params = array_merge($params, $video_card_types);
    }

    if (!empty($storage_types)) {
        $sql .= " AND p.storage_type IN (" . implode(',', array_fill(0, count($storage_types), '?')) . ")";
        $params = array_merge($params, $storage_types);
    }

    if (!empty($brands)) {
        $sql .= " AND p.brand IN (" . implode(',', array_fill(0, count($brands), '?')) . ")";
        $params = array_merge($params, $brands);
    }

    $sql .= " ORDER BY p.price";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL ORDER BY brand");
    $available_brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    header("Location: /backend-kurswork/public/views/errors/500.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ноутбук-Маркет - Головна</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="public/assets/css/style.css" rel="stylesheet">
    <link href="public/assets/css/index.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Ноутбук-Маркет</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="backend/orders/cart.php">
                <i class="bi bi-cart3"></i> Кошик
                <span class="badge bg-primary"><?php echo count($_SESSION['cart']); ?></span>
            </a>
            <a class="nav-link disabled" href="backend/utils/compare.php" id="compare-link">
                <i class="bi bi-arrows-angle-expand"></i> Порівняти
                <span class="badge bg-secondary" id="compare-count">0</span>
            </a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="page-header">
        <h1>Каталог ноутбуків</h1>
        <p>Знайдіть ідеальний ноутбук для ваших потреб за допомогою нашого зручного фільтра</p>
    </div>
    <div class="row">
        <div class="col-lg-3">
            <form id="advanced-filter" method="get">
                <h4>Фільтри</h4>

                <div class="filter-group">
                    <label>Бренд</label>
                    <div class="checkbox-container">
                        <?php foreach ($available_brands as $brand): ?>
                            <label class="custom-checkbox">
                                <input type="checkbox" name="brands[]" value="<?php echo htmlspecialchars($brand); ?>" <?php echo in_array($brand, $brands) ? 'checked' : ''; ?>>
                                <span class="checkmark"><?php echo htmlspecialchars($brand); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Ціна (грн)</label>
                    <div class="price-range">
                        <input type="number" name="min_price" class="form-control" placeholder="Від" value="<?php echo $min_price; ?>">
                        <span>-</span>
                        <input type="number" name="max_price" class="form-control" placeholder="До" value="<?php echo $max_price; ?>">
                    </div>
                    <input type="range" min="0" max="100000" value="<?php echo $max_price; ?>" class="price-slider" id="maxPriceSlider">
                    <div class="values">
                        <span>0 грн</span>
                        <span id="maxPriceValue"><?php echo $max_price; ?> грн</span>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Діагональ екрану</label>
                    <div class="checkbox-container">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="13.3" <?php echo in_array(13.3, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">13.3</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="14" <?php echo in_array(14, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">14</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="15.6" <?php echo in_array(15.6, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">15.6</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="16" <?php echo in_array(16, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">16</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="17.3" <?php echo in_array(17.3, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">17.3</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="screen_sizes[]" value="18" <?php echo in_array(18, $screen_sizes) ? 'checked' : ''; ?>>
                            <span class="checkmark">18</span>
                        </label>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Тип відеокарти</label>
                    <div class="checkbox-container">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="video_card_types[]" value="Integrated" <?php echo in_array('Integrated', $video_card_types) ? 'checked' : ''; ?>>
                            <span class="checkmark">Вбудована</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="video_card_types[]" value="Discrete" <?php echo in_array('Discrete', $video_card_types) ? 'checked' : ''; ?>>
                            <span class="checkmark">Дискретна</span>
                        </label>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Тип накопичувача</label>
                    <div class="checkbox-container">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="storage_types[]" value="SSD" <?php echo in_array('SSD', $storage_types) ? 'checked' : ''; ?>>
                            <span class="checkmark">SSD</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="storage_types[]" value="HDD" <?php echo in_array('HDD', $storage_types) ? 'checked' : ''; ?>>
                            <span class="checkmark">HDD</span>
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="storage_types[]" value="SSD+HDD" <?php echo in_array('SSD+HDD', $storage_types) ? 'checked' : ''; ?>>
                            <span class="checkmark">SSD+HDD</span>
                        </label>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Вага пристрою (кг)</label>
                    <div class="weight-range">
                        <input type="number" step="0.1" name="min_weight" class="form-control" placeholder="Від" value="<?php echo $min_weight; ?>">
                        <span>-</span>
                        <input type="number" step="0.1" name="max_weight" class="form-control" placeholder="До" value="<?php echo $max_weight; ?>">
                    </div>
                    <input type="range" min="0" max="5" step="0.1" value="<?php echo $max_weight; ?>" class="weight-slider" id="maxWeightSlider">
                    <div class="values">
                        <span>0 кг</span>
                        <span id="maxWeightValue"><?php echo $max_weight; ?> кг</span>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Застосувати
                    </button>
                    <button type="reset" class="btn btn-secondary" id="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i> Скинути
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-9">
            <?php if (!empty($screen_sizes) || !empty($video_card_types) || !empty($storage_types) || $min_price > 0 || $max_price < 100000 || $min_weight > 0 || $max_weight < 10): ?>
                <div class="active-filters mb-4">
                    <?php if ($min_price > 0 || $max_price < 100000): ?>
                        <div class="filter-tag">
                            Ціна: <?php echo $min_price; ?> - <?php echo $max_price; ?> грн
                            <span class="remove" data-filter="price">×</span>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($screen_sizes as $size): ?>
                        <div class="filter-tag">
                            Екран: <?php echo $size; ?>"
                            <span class="remove" data-filter="screen" data-value="<?php echo $size; ?>">×</span>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($video_card_types as $type): ?>
                        <div class="filter-tag">
                            Відеокарта: <?php echo $type == 'Integrated' ? 'Вбудована' : 'Дискретна'; ?>
                            <span class="remove" data-filter="video" data-value="<?php echo $type; ?>">×</span>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($storage_types as $type): ?>
                        <div class="filter-tag">
                            Накопичувач: <?php echo $type; ?>
                            <span class="remove" data-filter="storage" data-value="<?php echo $type; ?>">×</span>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($brands as $brand): ?>
                        <div class="filter-tag">
                            Бренд: <?php echo htmlspecialchars($brand); ?>
                            <span class="remove" data-filter="brand" data-value="<?php echo htmlspecialchars($brand); ?>">×</span>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($min_weight > 0 || $max_weight < 10): ?>
                        <div class="filter-tag">
                            Вага: <?php echo $min_weight; ?> - <?php echo $max_weight; ?> кг
                            <span class="remove" data-filter="weight">×</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <?php if (empty($products)): ?>

                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100" data-product-id="<?php echo $product['id']; ?>">
                                <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                    <div class="stock-indicator <?php echo $product['stock'] < 5 ? 'low-stock' : ''; ?>">
                                        <?php echo $product['stock'] < 5 ? 'Закінчується' : 'В наявності'; ?>
                                    </div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($product['image_filename'] ?? 'public/assets/img/placeholder.jpg'); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <?php if (!empty($product['brand'])): ?>
                                        <div class="brand-badge">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($product['brand']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="d-flex flex-column justify-content-between align-items-center">
                                        <span class="h5 text-primary">
                                            <?php echo number_format($product['price'], 2, '.', ' '); ?> грн
                                        </span>
                                        <div class="btn-group">
                                            <a href="backend/products/product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-info-circle"></i> Детальніше
                                            </a>
                                            <button
                                                    type="button"
                                                    class="btn btn-outline-secondary compare-toggle"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            >
                                                <i class="bi bi-arrows-angle-expand"></i> Порівняти
                                            </button>
                                            <a
                                                    href="backend/orders/cart.php?action=add&id=<?php echo $product['id']; ?>"
                                                    class="btn btn-primary"
                                            >
                                                <i class="bi bi-cart-plus"></i> В кошик
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <button
                                                type="button"
                                                class="btn btn-outline-info performance-test-btn w-100"
                                                data-product-id="<?php echo $product['id']; ?>"
                                        >
                                            <i class="bi bi-speedometer2"></i> Тест продуктивності
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="compareModal" tabindex="-1" role="dialog" aria-labelledby="compareModalTitle" aria-describedby="compareModalDescription">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compareModalTitle">Товари для порівняння</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="compareModalDescription">
                <div class="comparison-list row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                <a href="backend/utils/compare.php" class="btn btn-primary disabled" id="full-compare-link">Повна таблиця порівняння</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="public/assets/js/main.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const maxPriceSlider = document.getElementById('maxPriceSlider');
        const maxPriceValue = document.getElementById('maxPriceValue');
        const maxPriceInput = document.querySelector('input[name="max_price"]');

        if (maxPriceSlider && maxPriceValue && maxPriceInput) {
            maxPriceSlider.addEventListener('input', function() {
                const value = this.value;
                maxPriceValue.textContent = `${value} грн`;
                maxPriceInput.value = value;
            });

            maxPriceInput.addEventListener('input', function() {
                const value = this.value;
                maxPriceSlider.value = value;
                maxPriceValue.textContent = `${value} грн`;
            });
        }

        const maxWeightSlider = document.getElementById('maxWeightSlider');
        const maxWeightValue = document.getElementById('maxWeightValue');
        const maxWeightInput = document.querySelector('input[name="max_weight"]');

        if (maxWeightSlider && maxWeightValue && maxWeightInput) {
            maxWeightSlider.addEventListener('input', function() {
                const value = this.value;
                maxWeightValue.textContent = `${value} кг`;
                maxWeightInput.value = value;
            });

            maxWeightInput.addEventListener('input', function() {
                const value = this.value;
                maxWeightSlider.value = value;
                maxWeightValue.textContent = `${value} кг`;
            });
        }

        const resetButton = document.getElementById('resetFilters');
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'index.php';
            });
        }

        const filterTags = document.querySelectorAll('.filter-tag .remove');
        filterTags.forEach(tag => {
            tag.addEventListener('click', function() {
                const filterType = this.getAttribute('data-filter');
                const filterValue = this.getAttribute('data-value');

                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);

                switch(filterType) {
                    case 'price':
                        params.delete('min_price');
                        params.delete('max_price');
                        break;
                    case 'weight':
                        params.delete('min_weight');
                        params.delete('max_weight');
                        break;
                    case 'screen':
                        removeArrayParam(params, 'screen_sizes[]', filterValue);
                        break;
                    case 'video':
                        removeArrayParam(params, 'video_card_types[]', filterValue);
                        break;
                    case 'storage':
                        removeArrayParam(params, 'storage_types[]', filterValue);
                        break;
                }

                window.location.href = `${url.pathname}?${params.toString()}`;
            });
        });

        function removeArrayParam(params, paramName, value) {
            const values = params.getAll(paramName);
            params.delete(paramName);

            values.forEach(val => {
                if (val !== value) {
                    params.append(paramName, val);
                }
            });
        }

        let compareItems = JSON.parse(localStorage.getItem('compareItems')) || [];
        const compareCount = document.getElementById('compare-count');
        const compareLink = document.getElementById('compare-link');
        const fullCompareLink = document.getElementById('full-compare-link');

        updateCompareCount();

        const compareButtons = document.querySelectorAll('.compare-toggle');
        compareButtons.forEach(button => {
            const productId = button.getAttribute('data-product-id');
            const productName = button.getAttribute('data-product-name');

            if (compareItems.some(item => item.id === productId)) {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-secondary');
                button.innerHTML = '<i class="bi bi-check-lg"></i> В порівнянні';
            }

            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');

                if (compareItems.some(item => item.id === productId)) {
                    compareItems = compareItems.filter(item => item.id !== productId);
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-outline-secondary');
                    this.innerHTML = '<i class="bi bi-arrows-angle-expand"></i> Порівняти';
                } else {
                    if (compareItems.length >= 4) {
                        alert('Ви можете порівняти максимум 4 товари одночасно');
                        return;
                    }

                    compareItems.push({
                        id: productId,
                        name: productName
                    });

                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-secondary');
                    this.innerHTML = '<i class="bi bi-check-lg"></i> В порівнянні';
                }

                localStorage.setItem('compareItems', JSON.stringify(compareItems));
                updateCompareCount();
            });
        });

        const performanceButtons = document.querySelectorAll('.performance-test-btn');
        performanceButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                this.innerHTML = '<i class="bi bi-hourglass-split"></i> Тестування...';
                this.disabled = true;

                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Тест пройдено';
                    this.classList.remove('btn-outline-info');
                    this.classList.add('btn-success');

                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-speedometer2"></i> Тест продуктивності';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-info');
                        this.disabled = false;
                    }, 3000);
                }, 1500);
            });
        });
    });

    document.addEventListener('keydown', function(event) {
        console.log('Key pressed:', event.key, 'Ctrl:', event.ctrlKey, 'Alt:', event.altKey);

        if (event.ctrlKey && event.altKey && event.key.toLowerCase() === 'a') {
            console.log('Admin hotkey triggered!');
            event.preventDefault();

            const notification = document.createElement('div');
            notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="admin-icon">🔐</div>
                <div>
                    <div style="font-weight: 600; margin-bottom: 2px;">Адмін доступ активовано</div>
                    <div style="font-size: 12px; opacity: 0.8;">Перехід до панелі управління...</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        `;

            notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            z-index: 99999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-width: 280px;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        `;

            const style = document.createElement('style');
            style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            @keyframes progressBar {
                from { width: 0%; }
                to { width: 100%; }
            }

            .admin-icon {
                font-size: 24px;
                animation: spin 1s ease-in-out;
            }

            .progress-bar {
                width: 100%;
                height: 3px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                margin-top: 12px;
                overflow: hidden;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0.7));
                border-radius: 2px;
                animation: progressBar 2s ease-in-out;
            }
        `;

            document.head.appendChild(style);
            document.body.appendChild(notification);

            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(1200, audioContext.currentTime + 0.1);

                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } catch (e) {

            }

            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-in';

                setTimeout(() => {
                    window.location.href = 'admin.php';
                }, 300);
            }, 1700);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                if (style.parentNode) {
                    style.parentNode.removeChild(style);
                }
            }, 2500);
        }
    });
</script>
</body>
</html>