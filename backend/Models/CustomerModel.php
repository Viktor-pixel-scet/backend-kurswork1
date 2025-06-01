<?php

namespace Models;

use Exception;
use PDO;

class CustomerModel extends AdminModel
{
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM customers ORDER BY name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get all customers error', $e);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $customer = $stmt->fetch();
            return $customer ?: null;
        } catch (Exception $e) {
            $this->logError('Get customer by ID error', $e);
            return null;
        }
    }

    public function getOrders(int $customerId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM orders 
                WHERE customer_id = :customer_id 
                ORDER BY created_at DESC
            ");
            $stmt->execute(['customer_id' => $customerId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get customer orders error', $e);
            return [];
        }
    }

    public function getCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM customers");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logError('Get customers count error', $e);
            return 0;
        }
    }
}