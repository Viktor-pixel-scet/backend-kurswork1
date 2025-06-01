<?php

namespace controller;

use Models\OrderModel;

class AdminOrderController extends AdminBaseController
{
    private OrderModel $orderModel;

    public function __construct(OrderModel $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    public function getAll(): array
    {
        return $this->orderModel->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->orderModel->getById($id);
    }

    public function getItems(int $orderId): array
    {
        return $this->orderModel->getItems($orderId);
    }

    public function updateStatus(int $id, string $status): array
    {
        try {
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

            if (!in_array($status, $validStatuses)) {
                return $this->errorResponse('Невірний статус замовлення');
            }

            $result = $this->orderModel->updateStatus($id, $status);

            if ($result) {
                return $this->successResponse('Статус успішно оновлено');
            }

            return $this->errorResponse('Помилка під час оновлення статусу');
        } catch (Exception $e) {
            return $this->errorResponse('Виникла помилка під час оновлення статусу', $e);
        }
    }
}
