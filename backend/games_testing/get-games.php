<?php

use core\Database;

require_once __DIR__ . '/../core/Database.php';

class ResponseCache {
    private $cacheDir;
    private $cacheTime = 3600;

    public function __construct() {
        $this->cacheDir = __DIR__ . '/../../cache/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    private function generateCacheKey($endpoint) {
        return md5($endpoint);
    }

    public function hasValidCache($endpoint) {
        $cacheFile = $this->cacheDir . $this->generateCacheKey($endpoint) . '.json';

        if (file_exists($cacheFile)) {
            $modificationTime = filemtime($cacheFile);
            return (time() - $modificationTime) < $this->cacheTime;
        }

        return false;
    }

    public function getCache($endpoint) {
        $cacheFile = $this->cacheDir . $this->generateCacheKey($endpoint) . '.json';
        return file_get_contents($cacheFile);
    }

    public function saveCache($endpoint, $data, $statusCode) {
        $cacheFile = $this->cacheDir . $this->generateCacheKey($endpoint) . '.json';

        if ($statusCode >= 400) {
            $this->cacheTime = 300;
        }

        file_put_contents($cacheFile, $data);
    }
}

function getDatabaseConnection() {
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        throw new PDOException('Немає підключення до бази даних');
    }
    return $pdo;
}

function getGames($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT game_code, game_name, min_fps, max_fps, category FROM games");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Помилка запиту до таблиці games: ' . $e->getMessage());
        throw $e;
    }
}
function handleRequest() {
    $cache = new ResponseCache();
    $endpoint = 'games';
    $statusCode = 200;

    if ($cache->hasValidCache($endpoint)) {
        header('Content-Type: application/json');
        header('X-Cache: HIT');
        echo $cache->getCache($endpoint);
        exit;
    }

    ob_start();
    header('Content-Type: application/json');
    header('X-Cache: MISS');

    try {
        $pdo = getDatabaseConnection();
        $games = getGames($pdo);

        if (empty($games)) {
            $statusCode = 404;
            http_response_code($statusCode);
            echo json_encode([
                'error' => 'Список ігор порожній',
                'message' => 'Наразі немає доступних ігор'
            ]);
        } else {
            echo json_encode($games);
        }

    } catch(PDOException $e) {
        $statusCode = 500;
        http_response_code($statusCode);
        error_log('Помилка бази даних: ' . $e->getMessage());
        echo json_encode([
            'error' => 'Технічна помилка',
            'message' => 'Не вдалося отримати список ігор. Спробуйте пізніше.'
        ]);

    } catch(Exception $e) {
        $statusCode = 500;
        http_response_code($statusCode);
        error_log('Невідома помилка: ' . $e->getMessage());
        echo json_encode([
            'error' => 'Критична помилка',
            'message' => 'Виникла неочікувана помилка. Спробуйте пізніше.'
        ]);
    }

    $output = ob_get_contents();
    ob_end_flush();
    $cache->saveCache($endpoint, $output, $statusCode);
}

handleRequest();
?>