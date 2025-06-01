<?php

namespace Repositories;

use PDO;

class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $orderNumber, int $customerId, float $total, string $paymentMethod): int
    {
        $stmt = $this->db->prepare("INSERT INTO orders (order_number, customer_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderNumber, $customerId, $total, $paymentMethod]);
        return $this->db->lastInsertId();
    }

    public function addItem(int $orderId, int $productId, int $quantity, float $price): void
    {
        $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, $quantity, $price]);
    }
    /**
     * Знаходить всі замовлення з інформацією про клієнтів
     */
    public function findAllWithCustomers(): array {
        try {
            $stmt = $this->db->query("
                SELECT o.*, c.name as customer_name 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Find all orders with customers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Знаходить замовлення за ID
     */
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $order = $stmt->fetch();
            return $order ?: null;
        } catch (Exception $e) {
            error_log('Find order by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Знаходить елементи замовлення за ID замовлення
     */
    public function findOrderItems(int $orderId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id
            ");
            $stmt->execute(['order_id' => $orderId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Find order items error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Оновлює статус замовлення
     */
    public function updateStatus(int $id, string $status): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = :status, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id
            ");
            return $stmt->execute([
                'id' => $id,
                'status' => $status
            ]);
        } catch (Exception $e) {
            error_log('Update order status error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Знаходить замовлення за ID клієнта
     */
    public function findByCustomerId(int $customerId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM orders 
                WHERE customer_id = :customer_id 
                ORDER BY created_at DESC
            ");
            $stmt->execute(['customer_id' => $customerId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Find orders by customer ID error: ' . $e->getMessage());
            return [];
        }
    }
}