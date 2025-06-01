<?php

namespace Models;

use Exception;
use PDO;

class OrderModel extends AdminModel
{
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT o.*, c.name as customer_name 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get all orders error', $e);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, c.name as customer_name, c.email as customer_email 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                WHERE o.id = :id
            ");
            $stmt->execute(['id' => $id]);
            $order = $stmt->fetch();
            return $order ?: null;
        } catch (Exception $e) {
            $this->logError('Get order by ID error', $e);
            return null;
        }
    }

    public function getItems(int $orderId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT oi.*, p.name as product_name, p.price as product_price
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id
            ");
            $stmt->execute(['order_id' => $orderId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get order items error', $e);
            return [];
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            return $stmt->execute(['id' => $id, 'status' => $status]);
        } catch (Exception $e) {
            $this->logError('Update order status error', $e);
            return false;
        }
    }

    public function getRecent(int $limit = 5): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, c.name as customer_name 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get recent orders error', $e);
            return [];
        }
    }

    public function getCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logError('Get orders count error', $e);
            return 0;
        }
    }

    public function getTotalSales(): float
    {
        try {
            $stmt = $this->pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
            return (float)($stmt->fetchColumn() ?: 0);
        } catch (Exception $e) {
            $this->logError('Get total sales error', $e);
            return 0.0;
        }
    }

    public function getTopProducts(int $limit = 5): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.name, p.price, SUM(oi.quantity) as sold_count
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled'
                GROUP BY p.id
                ORDER BY sold_count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get top products error', $e);
            return [];
        }
    }
}