<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/backend-kurswork/backend/core/track_previous_url.php';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Внутрішня помилка сервера</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/500.css">
</head>
<body>
<div class="particles"></div>
<div class="container">
    <div class="error-container">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png" alt="500 error image" class="error-image">
        <h1>500</h1>
        <h2>Упс! Щось пішло не так на сервері</h2>
        <p>Сервер зіткнувся з внутрішньою помилкою та не зміг обробити ваш запит. Ми вже працюємо над вирішенням цієї проблеми.</p>
        <a href="<?php echo htmlspecialchars($_SESSION['previous_url'] ?? '/backend-kurswork'); ?>" class="btn btn-danger btn-lg mt-4">Повернутися назад</a>
    </div>
</div>

<script>
    function createParticles() {
        const particles = document.querySelector('.particles');
        const particleCount = window.innerWidth < 768 ? 25 : 50;
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = 15 + Math.random() * 10 + 's';
            particles.appendChild(particle);
        }
    }
    window.addEventListener('load', createParticles);
</script>
</body>
</html>
