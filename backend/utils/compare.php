<?php

use core\Database;

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    require_once '../../backend/core/Database.php';

    $db = new Database();

    $pdo = $db->getConnection();

    $product_ids = isset($_GET['products']) ? explode(',', $_GET['products']) : [];
    $product_ids = array_map(function($id) {
        $sanitized_id = filter_var($id, FILTER_VALIDATE_INT);
        if ($sanitized_id === false) {
            throw new Exception('Некоректний ідентифікатор товару');
        }
        return $sanitized_id;
    }, $product_ids);

    $products = [];
    if (!empty($product_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("
                SELECT p.*, 
                (SELECT image_filename FROM product_images WHERE product_id = p.id LIMIT 1) as image_filename 
                FROM products p 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Товари не знайдені');
            }
        } catch (PDOException $e) {
            error_log('Помилка бази даних: ' . $e->getMessage());
            throw new Exception('Не вдалося отримати дані про товари');
        }
    }
} catch (Exception $e) {
    error_log('Помилка в скрипті порівняння: ' . $e->getMessage());

    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Порівняння товарів | Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../../public/assets/css/compare.css" rel="stylesheet">
</head>
<body>
<header class="page-header">
    <div class="container">
        <h1 class="page-title text-center">Порівняння ноутбуків</h1>
    </div>
</header>

<div class="container">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Помилка:</strong> <?php echo htmlspecialchars($error_message); ?>
                <p class="mb-0">Виникла технічна проблема. Будь ласка, спробуйте пізніше або зв'яжіться з підтримкою.</p>
            </div>
        </div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>
                <strong>Увага!</strong> Немає товарів для порівняння. Виберіть товари в каталозі для порівняння їх характеристик.
            </div>
        </div>
    <?php else: ?>
        <div class="comparison-container">
            <div class="table-responsive">
                <table class="table table-compare">
                    <thead>
                    <tr>
                        <th style="width: 180px;">Характеристика</th>
                        <?php foreach ($products as $product): ?>
                            <th>
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <button class="btn btn-sm btn-remove remove-compare" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="bi bi-trash"></i> Видалити
                                </button>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Зображення</th>
                        <?php foreach ($products as $product): ?>
                            <th>
                                <div class="product-image-container">
                                    <?php if (!empty($product['image_filename'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_filename']); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-image">
                                    <?php else: ?>
                                        <div class="text-muted"><i class="bi bi-image" style="font-size: 2rem;"></i></div>
                                    <?php endif; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><i class="bi bi-tags me-1"></i> Бренд</td>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <?php echo htmlspecialchars($product['brand'] ?? '<em class="text-muted">Невідомо</em>'); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-currency-dollar me-1"></i> Ціна</td>
                        <?php foreach ($products as $product): ?>
                            <td class="price-value"><?php echo number_format($product['price'], 2, '.', ' '); ?> грн</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-info-circle me-1"></i> Опис</td>
                        <?php foreach ($products as $product): ?>
                            <td class="text-start"><?php echo !empty($product['description']) ? htmlspecialchars($product['description']) : '<em class="text-muted">Опис відсутній</em>'; ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <td><i class="bi bi-display me-1"></i> Екран</td>
                        <?php
                        $screen_sizes = array_column($products, 'screen_size');
                        $max_screen = !empty($screen_sizes) ? max($screen_sizes) : 0;
                        ?>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <span class="spec-highlight <?php echo $product['screen_size'] == $max_screen ? 'better' : ''; ?>">
                                    <?php echo htmlspecialchars($product['screen_size']); ?>"
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-gpu-card me-1"></i> Відеокарта</td>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <span class="spec-highlight <?php echo $product['video_card_type'] == 'Discrete' ? 'better' : ''; ?>">
                                    <?php
                                    $video_type = $product['video_card_type'];
                                    if ($video_type == 'Integrated') echo 'Вбудована';
                                    elseif ($video_type == 'Discrete') echo 'Дискретна';
                                    else echo htmlspecialchars($video_type);
                                    ?>
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-device-hdd me-1"></i> Тип накопичувача</td>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <?php
                                $storage_type = $product['storage_type'];
                                $icon = '';
                                $class = '';

                                if ($storage_type == 'SSD') {
                                    $icon = '<i class="bi bi-lightning-charge-fill me-1"></i>';
                                    $class = 'better';
                                } elseif ($storage_type == 'SSD+HDD') {
                                    $icon = '<i class="bi bi-layers-fill me-1"></i>';
                                    $class = 'better';
                                } elseif ($storage_type == 'HDD') {
                                    $icon = '<i class="bi bi-disc me-1"></i>';
                                }

                                echo '<span class="spec-highlight ' . $class . '">' . $icon . htmlspecialchars($storage_type) . '</span>';
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-box-seam me-1"></i> Вага</td>
                        <?php
                        $weights = array_column($products, 'device_weight');
                        $min_weight = !empty($weights) ? min($weights) : 0;
                        ?>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <span class="spec-highlight <?php echo $product['device_weight'] == $min_weight ? 'better' : ''; ?>">
                                    <?php echo htmlspecialchars($product['device_weight']); ?> кг
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td><i class="bi bi-boxes me-1"></i> Наявність</td>
                        <?php foreach ($products as $product): ?>
                            <td>
                                <?php
                                $stock = intval($product['stock']);
                                if ($stock > 10) {
                                    echo '<span class="stock-badge in-stock">В наявності</span><br>';
                                    echo '<small class="text-muted mt-1">' . $stock . ' шт.</small>';
                                } elseif ($stock > 0) {
                                    echo '<span class="stock-badge low-stock">Закінчується</span><br>';
                                    echo '<small class="text-muted mt-1">' . $stock . ' шт.</small>';
                                } else {
                                    echo '<span class="stock-badge out-of-stock">Немає в наявності</span>';
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4 mb-5 text-center">
        <a href="../../index.php" class="btn btn-primary back-button">
            <i class="bi bi-arrow-left"></i> Повернутися до каталогу
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-compare');

        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                try {
                    const productId = this.getAttribute('data-product-id');

                    let comparisonList;
                    try {
                        comparisonList = JSON.parse(localStorage.getItem('productComparison') || '[]');
                    } catch (e) {
                        console.error('Помилка читання localStorage:', e);
                        comparisonList = [];
                    }

                    const updatedList = comparisonList.filter(id => id != productId);

                    try {
                        localStorage.setItem('productComparison', JSON.stringify(updatedList));
                    } catch (e) {
                        console.error('Помилка запису в localStorage:', e);
                        alert('Не вдалося оновити список порівняння');
                        return;
                    }

                    const currentProducts = new URLSearchParams(window.location.search).get('products');
                    const newProducts = currentProducts.split(',')
                        .filter(id => id != productId)
                        .join(',');

                    if (newProducts) {
                        window.location.href = `compare.php?products=${newProducts}`;
                    } else {
                        window.location.href = '../../index.php';
                    }
                } catch (e) {
                    console.error('Критична помилка:', e);
                    alert('Виникла непередбачена помилка');
                }
            });
        });
    });
</script>
</body>
</html>