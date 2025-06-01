<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в адмін-панель | Онлайн-магазин ноутбуків</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/admin-login.css">
</head>
<body>
<div class="login-container">
    <div class="login-logo">
        <i class="fas fa-laptop fa-3x" style="color: #3498db;"></i>
    </div>
    <h1 class="login-title">Вхід в адміністративну панель</h1>

    <div id="login-alert" class="alert alert-danger"></div>

    <form id="login-form" method="post">
        <div class="form-group">
            <label for="username">Логін:</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn-login">Увійти</button>
    </form>

    <a href="http://localhost/backend-kurswork" class="back-link">
        <i class="fas fa-arrow-left"></i> Повернутися на сайт
    </a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const loginAlert = document.getElementById('login-alert');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(loginForm);

            fetch('admin.php?action=authenticate', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.ok || response.status === 401) {
                        return response.json();
                    }
                    throw new Error('Помилка мережі');
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        loginAlert.textContent = data.message;
                        loginAlert.style.display = 'block';

                        setTimeout(() => {
                            loginAlert.style.display = 'none';
                        }, 5000);
                    }
                })
                .catch(error => {
                    loginAlert.textContent = 'Виникла помилка при спробі входу.';
                    loginAlert.style.display = 'block';
                    console.error('Error:', error);
                });
        });
    });
</script>
</body>
</html>