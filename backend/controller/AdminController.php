<?php
namespace Backend\controller;
use Backend\Services\AdminService;
use Backend\DTO\AdminDTO;

class AdminController {
    private AdminService $adminService;
    private array $config;
    private $cache = [];

    public function __construct() {
        $this->adminService = new AdminService();
        $this->config = [
            'cache_enabled' => true,
            'cache_ttl' => 3600 // 1 година
        ];
    }

    /**
     * Обробляє запити до адмін-панелі
     */
    public function handleRequest(): void {
        session_start();

        $action = $_GET['action'] ?? 'login';

        if ($action !== 'login' && $action !== 'authenticate' && !$this->isLoggedIn()) {
            $this->redirectToLogin();
            return;
        }

        switch ($action) {
            case 'login':
                $this->showLoginForm();
                break;

            case 'authenticate':
                $this->authenticate();
                break;

            case 'logout':
                $this->logout();
                break;

            case 'dashboard':
                $this->showDashboard();
                break;

            case 'products':
                $this->handleProducts();
                break;

            case 'orders':
                $this->handleOrders();
                break;

            case 'customers':
                $this->handleCustomers();
                break;

            case 'games':
                $this->handleGames();
                break;

            default:
                $this->showDashboard();
                break;
        }
    }

    /**
     * Показує форму логіну
     */
    private function showLoginForm(): void {
        $this->renderView('admin/login');
    }

    /**
     * Аутентифікація адміністратора
     */
    private function authenticate(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $adminDTO = new AdminDTO();
            $adminDTO->username = $username;
            $adminDTO->password = $password;

            $result = $this->adminService->authenticate($adminDTO);

            if ($result['success']) {
                $_SESSION['admin_id'] = $result['admin_id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['is_admin'] = true;

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'admin.php?action=dashboard']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Невірний логін або пароль']);
                http_response_code(401);
                exit;
            }
        }

        $this->showLoginForm();
    }

    /**
     * Вихід з адмін-панелі та повернення в режим користувача
     */
    private function logout(): void {

        $userId = $_SESSION['user_id'] ?? null;

        session_start();

        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['is_admin']);

        if ($userId) {
            $_SESSION['user_id'] = $userId;
            header('Location: http://localhost/backend-kurswork/');
        } else {
            session_unset();
            session_destroy();
            header('Location: http://localhost/backend-kurswork/');
        }

        exit;
    }

    /**
     * Показує головну сторінку адмін-панелі
     */
    private function showDashboard(): void {
        $stats = $this->adminService->getDashboardStats();
        $this->renderView('admin/dashboard', ['stats' => $stats]);
    }

    /**
     * Обробляє запити зі сторінки товарів
     */
    private function handleProducts(): void {
        $subAction = $_GET['subaction'] ?? 'list';

        switch ($subAction) {
            case 'list':
                $this->listProducts();
                break;

            case 'add':
                $this->addProduct();
                break;

            case 'edit':
                $this->editProduct();
                break;

            case 'delete':
                $this->deleteProduct();
                break;

            case 'save':
                $this->saveProduct();
                break;

            default:
                $this->listProducts();
                break;
        }
    }

    /**
     * Список товарів
     */
    private function listProducts(): void {
        $cacheKey = 'products_list';
        $data = $this->getCachedData($cacheKey);

        if ($data === null) {
            $products = $this->adminService->getAllProducts();
            $data = ['products' => $products];
            $this->cacheData($cacheKey, $data);
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

        $this->renderView('admin/products/list', $data);
    }

    /**
     * Форма додавання товару
     */
    private function addProduct(): void {
        $this->renderView('admin/products/form', ['product' => null]);
    }

    /**
     * Форма редагування товару
     */
    private function editProduct(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('admin.php?action=products');
            return;
        }

        $product = $this->adminService->getProductById($id);
        $this->renderView('admin/products/form', ['product' => $product]);
    }

    /**
     * Видалення товару
     */
    private function deleteProduct(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $result = $this->adminService->deleteProduct($id);

            $this->clearCache('products_list');

            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Невірний метод запиту']);
        exit;
    }

    /**
     * Збереження товару
     */
    private function saveProduct(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productData = $_POST;
            $id = (int)($productData['id'] ?? 0);

            if (!isset($productData['brand']) || empty($productData['brand'])) {
                $productData['brand'] = '';
            }

            // Обробка завантаження файлу зображення
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $productData['image'] = 'uploads/' . $fileName;
                }
            }

            $result = $this->adminService->saveProduct($productData);

            $this->clearCache('products_list');

            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Невірний метод запиту']);
        exit;
    }

    /**
     * Обробляє запити зі сторінки замовлень
     */
    private function handleOrders(): void {
        $subAction = $_GET['subaction'] ?? 'list';

        switch ($subAction) {
            case 'list':
                $this->listOrders();
                break;

            case 'view':
                $this->viewOrder();
                break;

            case 'update-status':
                $this->updateOrderStatus();
                break;

            default:
                $this->listOrders();
                break;
        }
    }

    /**
     * Список замовлень
     */
    private function listOrders(): void {
        $cacheKey = 'orders_list';
        $data = $this->getCachedData($cacheKey);

        if ($data === null) {
            $orders = $this->adminService->getAllOrders();
            $data = ['orders' => $orders];
            $this->cacheData($cacheKey, $data);
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

        $this->renderView('admin/orders/list', $data);
    }

    /**
     * Перегляд деталей замовлення
     */
    private function viewOrder(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('admin.php?action=orders');
            return;
        }

        $order = $this->adminService->getOrderById($id);
        $orderItems = $this->adminService->getOrderItems($id);
        $customer = $this->adminService->getCustomerById($order['customer_id']);

        $this->renderView('admin/orders/view', [
            'order' => $order,
            'orderItems' => $orderItems,
            'customer' => $customer
        ]);
    }

    /**
     * Оновлення статусу замовлення
     */
    private function updateOrderStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';

            $result = $this->adminService->updateOrderStatus($id, $status);

            $this->clearCache('orders_list');

            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Невірний метод запиту']);
        exit;
    }

    /**
     * Обробляє запити зі сторінки клієнтів
     */
    private function handleCustomers(): void {
        $subAction = $_GET['subaction'] ?? 'list';

        switch ($subAction) {
            case 'list':
                $this->listCustomers();
                break;

            case 'view':
                $this->viewCustomer();
                break;

            default:
                $this->listCustomers();
                break;
        }
    }

    /**
     * Список клієнтів
     */
    private function listCustomers(): void {
        $cacheKey = 'customers_list';
        $data = $this->getCachedData($cacheKey);

        if ($data === null) {
            $customers = $this->adminService->getAllCustomers();
            $data = ['customers' => $customers];
            $this->cacheData($cacheKey, $data);
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

        $this->renderView('admin/customers/list', $data);
    }

    /**
     * Перегляд деталей клієнта
     */
    private function viewCustomer(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('admin.php?action=customers');
            return;
        }

        $customer = $this->adminService->getCustomerById($id);
        $customerOrders = $this->adminService->getCustomerOrders($id);

        $this->renderView('admin/customers/view', [
            'customer' => $customer,
            'orders' => $customerOrders
        ]);
    }

    /**
     * Обробляє запити зі сторінки ігор
     */
    private function handleGames(): void {
        $subAction = $_GET['subaction'] ?? 'list';

        switch ($subAction) {
            case 'list':
                $this->listGames();
                break;

            case 'add':
                $this->addGame();
                break;

            case 'edit':
                $this->editGame();
                break;

            case 'delete':
                $this->deleteGame();
                break;

            case 'save':
                $this->saveGame();
                break;

            default:
                $this->listGames();
                break;
        }
    }

    /**
     * Список ігор
     */
    private function listGames(): void {
        $cacheKey = 'games_list';
        $data = $this->getCachedData($cacheKey);

        if ($data === null) {
            $games = $this->adminService->getAllGames();
            $data = ['games' => $games];
            $this->cacheData($cacheKey, $data);
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

        $this->renderView('admin/games/list', $data);
    }

    /**
     * Форма додавання гри
     */
    private function addGame(): void {
        $this->renderView('admin/games/form', ['game' => null]);
    }

    /**
     * Форма редагування гри
     */
    private function editGame(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('admin.php?action=games');
            return;
        }

        $game = $this->adminService->getGameById($id);
        $this->renderView('admin/games/form', ['game' => $game]);
    }

    /**
     * Видалення гри
     */
    private function deleteGame(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $result = $this->adminService->deleteGame($id);

            $this->clearCache('games_list');

            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Невірний метод запиту']);
        exit;
    }

    /**
     * Збереження гри
     */
    private function saveGame(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gameData = $_POST;
            $id = (int)($gameData['id'] ?? 0);

            $result = $this->adminService->saveGame($gameData);

            $this->clearCache('games_list');

            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Невірний метод запиту']);
        exit;
    }

    /**
     * Перевіряє, чи користувач авторизований як адміністратор
     */
    private function isLoggedIn(): bool {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Перенаправляє на сторінку логіну
     */
    private function redirectToLogin(): void {
        header('Location: admin.php?action=login');
        exit;
    }

    /**
     * Перенаправлення на вказаний URL
     */
    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    /**
     * Рендерить представлення з вказаними даними
     */
    private function renderView(string $view, array $data = []): void {
        extract($data);

        ob_start();

        // Підключення шаблону
        $viewPath = __DIR__ . "/../../public/views/$view.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            http_response_code(404);
            echo "View not found: $view";
        }

        $content = ob_get_clean();

        // Вивід контенту
        echo $content;
    }

    /**
     * Перевіряє, чи запит є AJAX-запитом
     */
    private function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Отримує дані з кешу за ключем
     */
    private function getCachedData(string $key) {
        if (!$this->config['cache_enabled']) {
            return null;
        }

        $cacheFile = __DIR__ . "/../../cache/{$key}.json";

        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);

            // Перевірка терміну дії кешу
            if (time() - $cacheTime < $this->config['cache_ttl']) {
                $cachedData = file_get_contents($cacheFile);
                return json_decode($cachedData, true);
            }
        }

        return null;
    }

    /**
     * Зберігає дані в кеш за ключем
     */
    private function cacheData(string $key, $data): void {
        if (!$this->config['cache_enabled']) {
            return;
        }

        $cacheDir = __DIR__ . "/../../cache";
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = "{$cacheDir}/{$key}.json";
        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Очищає кеш за ключем
     */
    private function clearCache(string $key): void {
        if (!$this->config['cache_enabled']) {
            return;
        }

        $cacheFile = __DIR__ . "/../../cache/{$key}.json";
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}