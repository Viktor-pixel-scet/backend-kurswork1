<?php

namespace Services;

use DTO\OrderDTO;
use Repositories\CustomerRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use PDO;
use PDOException;

class OrderService
{
    private PDO $db;
    private CustomerRepository $customerRepo;
    private OrderRepository $orderRepo;
    private ProductRepository $productRepo;

    public function __construct(
        PDO $db,
        CustomerRepository $customerRepo,
        OrderRepository $orderRepo,
        ProductRepository $productRepo
    ) {
        $this->db = $db;
        $this->customerRepo = $customerRepo;
        $this->orderRepo = $orderRepo;
        $this->productRepo = $productRepo;
    }

    public function process(OrderDTO $orderDTO): string
    {
        $orderNumber = 'ORDER-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

        try {
            $this->db->beginTransaction();

            $customerId = $this->customerRepo->create($orderDTO->customer);
            $orderId = $this->orderRepo->create(
                $orderNumber,
                $customerId,
                $orderDTO->totalPrice,
                $orderDTO->customer['payment_method']
            );

            foreach ($orderDTO->items as $item) {
                $this->orderRepo->addItem($orderId, $item['id'], $item['quantity'], $item['price']);
                $this->productRepo->reduceStock($item['id'], $item['quantity']);
            }

            $this->db->commit();
            return $orderNumber;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Помилка обробки замовлення: ' . $e->getMessage());
            throw new \Exception('Обробка замовлення не вдалася!');
        }
    }
}
