<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/backend-kurswork/backend/core/track_previous_url.php';

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Сторінку не знайдено</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/backend-kurswork/public/assets/css/404.css">
</head>
<style>

</style>
<body>
<div class="particles"></div>
<div class="container">
    <div class="error-container">
        <img src="https://cdn-icons-png.flaticon.com/512/1199/1199121.png" alt="404 error image" class="error-image">
        <h1>404</h1>
        <h2>Ой! Сторінка не знайдена</h2>
        <p>Ми не можемо знайти сторінку, яку ви шукаєте. Це може бути через неправильне посилання або сторінка більше не існує.</p>
        <a href="<?php echo isset($_SESSION['previous_url']) ? $_SESSION['previous_url'] : '/backend-kurswork'; ?>" class="btn btn-primary">Повернутись на начальну сторінку</a>
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