<?php

namespace controller;

use Models\CustomerModel;

class AdminCustomerController extends AdminBaseController
{
    private CustomerModel $customerModel;

    public function __construct(CustomerModel $customerModel)
    {
        $this->customerModel = $customerModel;
    }

    public function getAll(): array
    {
        return $this->customerModel->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->customerModel->getById($id);
    }

    public function getOrders(int $customerId): array
    {
        return $this->customerModel->getOrders($customerId);
    }
}