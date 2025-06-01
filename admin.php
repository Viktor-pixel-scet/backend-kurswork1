<?php

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);

    $baseDir = __DIR__ . '/';

    $possiblePaths = [
        $baseDir . $class . '.php',
        $baseDir . 'backend/' . $class . '.php',
        $baseDir . strtolower($class) . '.php',
        $baseDir . 'backend/' . strtolower($class) . '.php'
    ];

    foreach ($possiblePaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/backend/core/Database.php';

if (!class_exists('Backend\Controller\AdminController')) {
    die('Помилка: Не знайдено клас AdminController');
}

try {
    $adminController = new Backend\Controller\AdminController();
    $adminController->handleRequest();
} catch (Exception $e) {
    error_log('Admin panel error: ' . $e->getMessage());
    die('Виникла помилка в адміністративній панелі');
}