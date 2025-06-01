<?php

use core\Database;

if (!isset($pdo)) {
    require_once __DIR__ . '/../../../../backend/core/Database.php';
    $database = new Database();
    $pdo = $database->getConnection();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - <?= $product ? 'Редагування товару' : 'Додавання товару' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-products.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1><?= $product ? 'Редагування товару' : 'Додавання товару' ?></h1>
            <a href="admin.php?action=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Повернутися до списку
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="productForm" enctype="multipart/form-data">
                <?php if ($product): ?>
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Назва товару*</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?= $product ? htmlspecialchars($product['name']) : '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="brand" class="form-label">Бренд*</label>
                            <input type="text" class="form-control" id="brand" name="brand" value="<?= $product ? htmlspecialchars($product['brand'] ?? '') : '' ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Ціна*</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" required
                                               value="<?= $product ? htmlspecialchars($product['price']) : '' ?>">
                                        <span class="input-group-text">грн</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Запас*</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required
                                           value="<?= $product ? htmlspecialchars($product['stock']) : '0' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Короткий опис</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= $product ? htmlspecialchars($product['description']) : '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="full_description" class="form-label">Повний опис</label>
                            <textarea class="form-control" id="full_description" name="full_description" rows="6"><?= $product ? htmlspecialchars($product['full_description']) : '' ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="screen_size" class="form-label">Розмір екрану</label>
                            <input type="text" class="form-control" id="screen_size" name="screen_size"
                                   value="<?= $product ? htmlspecialchars($product['screen_size']) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label for="video_card_type" class="form-label">Тип відеокарти</label>
                            <select class="form-select" id="video_card_type" name="video_card_type">
                                <option value="Integrated" <?= $product && $product['video_card_type'] == 'Integrated' ? 'selected' : '' ?>>Інтегрована</option>
                                <option value="Dedicated" <?= $product && $product['video_card_type'] == 'Dedicated' ? 'selected' : '' ?>>Дискретна</option>
                                <option value="Hybrid" <?= $product && $product['video_card_type'] == 'Hybrid' ? 'selected' : '' ?>>Гібридна</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="storage_type" class="form-label">Тип накопичувача</label>
                            <select class="form-select" id="storage_type" name="storage_type">
                                <option value="SSD" <?= $product && $product['storage_type'] == 'SSD' ? 'selected' : '' ?>>SSD</option>
                                <option value="HDD" <?= $product && $product['storage_type'] == 'HDD' ? 'selected' : '' ?>>HDD</option>
                                <option value="Hybrid" <?= $product && $product['storage_type'] == 'Hybrid' ? 'selected' : '' ?>>Гібридний (SSD+HDD)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="device_weight" class="form-label">Вага пристрою (кг)</label>
                            <input type="number" class="form-control" id="device_weight" name="device_weight" step="0.01" min="0"
                                   value="<?= $product ? htmlspecialchars($product['device_weight']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Зображення товару</h5>
                    </div>
                    <div class="card-body">
                        <!-- (для таблиці products) -->
                        <div class="mb-4">
                            <h6>URL основного зображення</h6>
                            <p class="text-muted small">Додайте кілька URL через Enter. Кожен URL повинен бути на новому рядку.</p>

                            <?php
                            $existingImages = [];
                            if ($product && !empty($product['image'])) {
                                $existingImages = explode("\n", $product['image']);
                            }
                            ?>

                            <?php if (!empty($existingImages)): ?>
                                <div class="image-urls-container mb-3">
                                    <h6>Поточні URL зображень:</h6>
                                    <?php foreach($existingImages as $imageUrl): ?>
                                        <?php if (trim($imageUrl)): ?>
                                            <div class="image-url-preview">
                                                <?= htmlspecialchars(trim($imageUrl)) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="product_image_url" class="form-label">URL зображень</label>
                                <textarea class="form-control" id="product_image_url" name="product_image_url" rows="5" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"><?= $product ? htmlspecialchars($product['image']) : '' ?></textarea>
                                <div class="form-text">
                                    Вставте кілька URL зображень, кожен з нового рядка.
                                    Усі вони будуть збережені в одному полі бази даних.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6>Додаткове зображення</h6>

                            <?php
                            $additionalImage = null;
                            if ($product && isset($product['id'])) {
                                try {
                                    $stmt = $pdo->prepare("SELECT id, image_filename FROM product_images WHERE product_id = :product_id LIMIT 1");
                                    $stmt->execute(['product_id' => $product['id']]);
                                    $additionalImage = $stmt->fetch(PDO::FETCH_ASSOC);
                                } catch (Exception $e) {

                                }
                            }
                            ?>

                            <div class="existing-additional-image mb-3">
                                <?php if ($additionalImage): ?>
                                    <div class="image-preview-container additional-image-container">
                                        <img src="<?= htmlspecialchars($additionalImage['image_filename']) ?>" class="image-preview" alt="Додаткове зображення">
                                        <div class="remove-image" data-image-type="additional" data-image-id="<?= $additionalImage['id'] ?>"><i class="fas fa-times"></i></div>
                                    </div>
                                    <input type="hidden" name="keep_additional_images[]" value="<?= $additionalImage['id'] ?>" class="keep-image-input">
                                <?php endif; ?>
                            </div>

                            <input type="hidden" name="images_to_remove" id="images_to_remove" value="">

                            <div class="new-additional-image mb-3">
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label for="additional_image_url" class="form-label">URL додаткового зображення</label>
                                        <input type="text" class="form-control" id="additional_image_url" name="additional_image_url[]" placeholder="https://example.com/additional_image.jpg">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='admin.php?action=products'">Скасувати</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $product ? 'Оновити товар' : 'Створити товар' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/admin_footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        let imagesToRemove = [];

        $('#product_image_url').on('change keyup paste', function() {
            const urls = $(this).val().trim().split('\n');
            let validUrls = [];

            // Перевірка чи всі URL дійсні
            for (let url of urls) {
                url = url.trim();
                if (url && isValidUrl(url)) {
                    validUrls.push(url);
                }
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        $(document).on('click', '.remove-image[data-image-type="additional"]', function() {
            const confirmRemove = confirm("Ви дійсно хочете видалити додаткове зображення?");
            if (confirmRemove) {
                const imageId = $(this).data('image-id');
                if (imageId) {
                    imagesToRemove.push(imageId);
                    $('#images_to_remove').val(imagesToRemove.join(','));
                }
                $(this).closest('.image-preview-container').css('opacity', '0.5');
                $('.keep-image-input').remove();
            }
        });

        $('#additional_image_url').on('input', function() {
            const newImageUrl = $(this).val().trim();
            const existingAdditionalImage = $('.additional-image-container').length > 0;

            if (existingAdditionalImage && newImageUrl) {
                const confirmChange = confirm("Ви хочете замінити поточне додаткове зображення на нове?");
                if (confirmChange) {
                    // Користувач підтвердив заміну - додаємо ID до списку для видалення
                    const imageId = $('.remove-image[data-image-type="additional"]').data('image-id');
                    if (imageId) {
                        imagesToRemove.push(imageId);
                        $('#images_to_remove').val(imagesToRemove.join(','));
                    }
                    $('.additional-image-container').css('opacity', '0.5');
                    $('.keep-image-input').remove(); // делет приховане поле
                } else {
                    // Користувач скасував заміну, очищаємо поле
                    $(this).val('');
                }
            }
        });

        $('#productForm').on('submit', function(e) {
            e.preventDefault();

            // Для відлагодження
            console.log("Відправляємо форму");

            var formData = new FormData(this);

            console.log("Дані форми:");
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: 'admin.php?action=products&subaction=save',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    console.log("Успішна відповідь:", response);
                    if (response.success) {
                        alert(response.message);
                        window.location.href = 'admin.php?action=products';
                    } else {
                        alert(response.message || 'Помилка збереження товару');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Помилка:", xhr.responseText);
                    alert('Виникла помилка при виконанні запиту: ' + error);
                }
            });
        });
    });
</script>
</body>
</html>