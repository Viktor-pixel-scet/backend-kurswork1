<?php

namespace controller;

use Models\ProductModel;
use Models\OrderModel;
use Models\CustomerModel;
use Models\GameModel;

class AdminDashboardController extends AdminBaseController
{
    private ProductModel $productModel;
    private OrderModel $orderModel;
    private CustomerModel $customerModel;
    private GameModel $gameModel;

    public function __construct(ProductModel $productModel, OrderModel $orderModel, CustomerModel $customerModel, GameModel $gameModel)
    {
        $this->productModel = $productModel;
        $this->orderModel = $orderModel;
        $this->customerModel = $customerModel;
        $this->gameModel = $gameModel;
    }

    public function getDashboardStats(): array
    {
        try {
            return [
                'products_count' => $this->productModel->getCount(),
                'orders_count' => $this->orderModel->getCount(),
                'customers_count' => $this->customerModel->getCount(),
                'games_count' => $this->gameModel->getCount(),
                'total_sales' => $this->orderModel->getTotalSales(),
                'recent_orders' => $this->orderModel->getRecent(5),
                'top_products' => $this->orderModel->getTopProducts(5)
            ];
        } catch (Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            return [];
        }
    }
}