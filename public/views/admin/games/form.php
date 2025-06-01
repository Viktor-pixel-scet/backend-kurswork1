<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адміністрування - <?= $game ? 'Редагування гри' : 'Додавання гри' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-games.css">
</head>
<body class="bg-light">
<?php include __DIR__ . '/../../../includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1><?= $game ? 'Редагування гри' : 'Додавання нової гри' ?></h1>
            <a href="admin.php?action=games" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Повернутися до списку
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="gameForm" action="admin.php?action=games&subaction=save" method="post">
                <?php if ($game): ?>
                    <input type="hidden" name="id" value="<?= $game['id'] ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="game_code" class="form-label">Код гри *</label>
                        <input type="text" class="form-control" id="game_code" name="game_code" required
                               value="<?= $game ? htmlspecialchars($game['game_code']) : '' ?>">
                        <div class="form-text">Унікальний код гри (наприклад, CSGO, DOTA2, тощо)</div>
                    </div>
                    <div class="col-md-6">
                        <label for="game_name" class="form-label">Назва гри *</label>
                        <input type="text" class="form-control" id="game_name" name="game_name" required
                               value="<?= $game ? htmlspecialchars($game['game_name']) : '' ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="min_fps" class="form-label">Мінімальний FPS *</label>
                        <input type="number" class="form-control" id="min_fps" name="min_fps" required
                               value="<?= $game ? $game['min_fps'] : '30' ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="max_fps" class="form-label">Максимальний FPS *</label>
                        <input type="number" class="form-control" id="max_fps" name="max_fps" required
                               value="<?= $game ? $game['max_fps'] : '60' ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Категорія *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Виберіть категорію</option>
                            <option value="Шутер" <?= ($game && $game['category'] == 'Шутер') ? 'selected' : '' ?>>Шутер</option>
                            <option value="ММОРПГ" <?= ($game && $game['category'] == 'ММОРПГ') ? 'selected' : '' ?>>ММОРПГ</option>
                            <option value="Стратегія" <?= ($game && $game['category'] == 'Стратегія') ? 'selected' : '' ?>>Стратегія</option>
                            <option value="Симулятор" <?= ($game && $game['category'] == 'Симулятор') ? 'selected' : '' ?>>Симулятор</option>
                            <option value="Гонки" <?= ($game && $game['category'] == 'Гонки') ? 'selected' : '' ?>>Гонки</option>
                            <option value="Спортивна" <?= ($game && $game['category'] == 'Спортивна') ? 'selected' : '' ?>>Спортивна</option>
                            <option value="Інша" <?= ($game && $game['category'] == 'Інша') ? 'selected' : '' ?>>Інша</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label for="description" class="form-label">Опис</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $game ? htmlspecialchars($game['description'] ?? '') : '' ?></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="recommended_cpu" class="form-label">Рекомендований CPU</label>
                        <input type="text" class="form-control" id="recommended_cpu" name="recommended_cpu"
                               value="<?= $game ? htmlspecialchars($game['recommended_cpu'] ?? '') : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="recommended_gpu" class="form-label">Рекомендована відеокарта</label>
                        <input type="text" class="form-control" id="recommended_gpu" name="recommended_gpu"
                               value="<?= $game ? htmlspecialchars($game['recommended_gpu'] ?? '') : '' ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="recommended_ram" class="form-label">Рекомендована RAM (GB)</label>
                        <input type="number" class="form-control" id="recommended_ram" name="recommended_ram"
                               value="<?= $game ? ($game['recommended_ram'] ?? '8') : '8' ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="recommended_storage" class="form-label">Рекомендований об'єм накопичувача (GB)</label>
                        <input type="number" class="form-control" id="recommended_storage" name="recommended_storage"
                               value="<?= $game ? ($game['recommended_storage'] ?? '50') : '50' ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1"
                                <?= ($game && isset($game['is_popular']) && $game['is_popular'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_popular">
                                Популярна гра (відображатиметься на головній сторінці)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning" id="saveGameBtn">
                            <i class="fas fa-save"></i> Зберегти
                        </button>
                    </div>
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
        $('#gameForm').on('submit', function(e) {
            e.preventDefault();

            const minFps = parseInt($('#min_fps').val());
            const maxFps = parseInt($('#max_fps').val());

            if (minFps >= maxFps) {
                alert('Мінімальний FPS повинен бути менше, ніж максимальний FPS');
                return;
            }

            const formData = $(this).serialize();

            // Відладка - показуємо що відправляємо
            console.log('Відправляємо дані:', formData);

            $.ajax({
                url: 'admin.php?action=games&subaction=save',
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#saveGameBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Збереження...');
                },
                success: function(response) {
                    console.log('Отримана відповідь:', response);

                    if (response && response.success) {
                        alert(response.message || 'Гру успішно збережено!');
                        window.location.href = 'admin.php?action=games';
                    } else {
                        alert(response.message || 'Помилка при збереженні гри');
                    }
                },
                error: function(xhr, status, error) {
                    // Відладка - показуємо детальну інформацію про помилку
                    console.error('AJAX Error Details:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response Status:', xhr.status);
                    console.error('Response Text:', xhr.responseText);

                    // Перевіряємо чи це HTML помилка (наприклад PHP Fatal Error)
                    if (xhr.responseText && xhr.responseText.includes('<html>')) {
                        console.warn('Сервер повернув HTML замість JSON. Можлива PHP помилка.');
                        alert('Помилка сервера. Перевірте консоль браузера для деталей.');
                    } else {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            alert(errorResponse.message || 'Помилка при збереженні гри');
                        } catch (parseError) {
                            alert('Виникла помилка при виконанні запиту. Код помилки: ' + xhr.status);
                        }
                    }
                },
                complete: function() {
                    $('#saveGameBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Зберегти');
                }
            });
        });

        $('#min_fps, #max_fps').on('change', function() {
            const minFps = parseInt($('#min_fps').val());
            const maxFps = parseInt($('#max_fps').val());

            if (minFps >= maxFps && maxFps > 0) {
                $(this).addClass('is-invalid');
                $('#saveGameBtn').prop('disabled', true);
            } else {
                $('#min_fps, #max_fps').removeClass('is-invalid');
                $('#saveGameBtn').prop('disabled', false);
            }
        });
    });
</script>
</body>
</html>