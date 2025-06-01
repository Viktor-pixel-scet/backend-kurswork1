<?php

namespace Controller;

use DTO\OrderDTO;
use Services\OrderService;
use Repositories\CustomerRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use PDO;

class OrderController
{
    private OrderService $orderService;

    public function __construct(PDO $db)
    {
        $this->orderService = new OrderService(
            $db,
            new CustomerRepository($db),
            new OrderRepository($db),
            new ProductRepository($db)
        );
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['customer'], $input['items'], $input['totalPrice'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $orderDTO = new OrderDTO(
            $input['customer'],
            $input['items'],
            $input['totalPrice']
        );

        try {
            $orderNumber = $this->orderService->process($orderDTO);
            echo json_encode(['success' => true, 'orderNumber' => $orderNumber]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function handleSessionOrder(array &$session): void
    {
        $orderData = $session['order'];

        $orderDTO = new OrderDTO(
            $orderData['customer'],
            $orderData['items'],
            $orderData['total_price']
        );

        try {
            $orderNumber = $this->orderService->process($orderDTO);
            $session['order_number'] = $orderNumber;
            $session['cart'] = [];

            header('Location: order_confirmation.php');
            exit;

        } catch (\Exception $e) {
            $session['error'] = 'Виникла помилка при обробці замовлення. Будь ласка, спробуйте ще раз.';
            header('Location: checkout.php');
            exit;
        }
    }
}
