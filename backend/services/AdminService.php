<?php

namespace Backend\Services;

require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/OrderModel.php';
require_once __DIR__ . '/../Models/CustomerModel.php';
require_once __DIR__ . '/../Models/GameModel.php';
require_once __DIR__ . '/../Models/AdminAuthModel.php';

require_once __DIR__ . '/../controller/AdminAuthController.php';
require_once __DIR__ . '/../controller/AdminDashboardController.php';
require_once __DIR__ . '/../controller/AdminProductController.php';
require_once __DIR__ . '/../controller/AdminOrderController.php';
require_once __DIR__ . '/../controller/AdminCustomerController.php';
require_once __DIR__ . '/../controller/AdminGameController.php';

use backend\DTO\AdminDTO;
use controller\AdminAuthController;
use controller\AdminDashboardController;
use controller\AdminProductController;
use controller\AdminOrderController;
use controller\AdminCustomerController;
use controller\AdminGameController;
use Models\ProductModel;
use Models\OrderModel;
use Models\CustomerModel;
use Models\GameModel;
use Models\AdminAuthModel;
use PDO;

class AdminService
{
    private PDO $pdo;

    // Models
    private ProductModel $productModel;
    private OrderModel $orderModel;
    private CustomerModel $customerModel;
    private GameModel $gameModel;
    private AdminAuthModel $authModel;

    private AdminAuthController $authController;
    private AdminDashboardController $dashboardController;
    private AdminProductController $productController;
    private AdminOrderController $orderController;
    private AdminCustomerController $customerController;
    private AdminGameController $gameController;

    public function __construct()
    {
        $database = new \core\Database();
        $this->pdo = $database->getConnection();

        $this->initializeModels();
        $this->initializeControllers();
    }

    private function initializeModels(): void
    {
        $this->productModel = new ProductModel($this->pdo);
        $this->orderModel = new OrderModel($this->pdo);
        $this->customerModel = new CustomerModel($this->pdo);
        $this->gameModel = new GameModel($this->pdo);
        $this->authModel = new AdminAuthModel($this->pdo);
    }

    private function initializeControllers(): void
    {
        $this->authController = new AdminAuthController($this->authModel);
        $this->dashboardController = new AdminDashboardController(
            $this->productModel,
            $this->orderModel,
            $this->customerModel,
            $this->gameModel
        );
        $this->productController = new AdminProductController($this->productModel);
        $this->orderController = new AdminOrderController($this->orderModel);
        $this->customerController = new AdminCustomerController($this->customerModel);
        $this->gameController = new AdminGameController($this->gameModel);
    }

    public function authenticate(AdminDTO $adminDTO): array
    {
        return $this->authController->authenticate($adminDTO);
    }

    public function getDashboardStats(): array
    {
        return $this->dashboardController->getDashboardStats();
    }

    public function getAllProducts(): array
    {
        return $this->productController->getAll();
    }

    public function getProductById(int $id): ?array
    {
        return $this->productController->getById($id);
    }

    public function saveProduct(array $data): array
    {
        return $this->productController->save($data);
    }

    public function deleteProduct(int $id): array
    {
        return $this->productController->delete($id);
    }

    public function getProductImages(int $productId): array
    {
        return $this->productController->getImages($productId);
    }

    public function getAllOrders(): array
    {
        return $this->orderController->getAll();
    }

    public function getOrderById(int $id): ?array
    {
        return $this->orderController->getById($id);
    }

    public function getOrderItems(int $orderId): array
    {
        return $this->orderController->getItems($orderId);
    }

    public function updateOrderStatus(int $id, string $status): array
    {
        return $this->orderController->updateStatus($id, $status);
    }

    public function getAllCustomers(): array
    {
        return $this->customerController->getAll();
    }

    public function getCustomerById(int $id): ?array
    {
        return $this->customerController->getById($id);
    }

    public function getCustomerOrders(int $customerId): array
    {
        return $this->customerController->getOrders($customerId);
    }

    public function getAllGames(): array
    {
        return $this->gameController->getAll();
    }

    public function getGameById(int $id): ?array
    {
        return $this->gameController->getById($id);
    }

    public function saveGame(array $data): array
    {
        return $this->gameController->save($data);
    }

    public function deleteGame(int $id): array
    {
        return $this->gameController->delete($id);
    }

    public function getProductModel(): ProductModel
    {
        return $this->productModel;
    }

    public function getOrderModel(): OrderModel
    {
        return $this->orderModel;
    }

    public function getCustomerModel(): CustomerModel
    {
        return $this->customerModel;
    }

    public function getGameModel(): GameModel
    {
        return $this->gameModel;
    }
}