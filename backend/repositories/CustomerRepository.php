<?php

namespace Repositories;

use PDO;

class CustomerRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $customer): int
    {
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['address']
        ]);
        return $this->db->lastInsertId();
    }
    /**
     * Знаходить всіх клієнтів
     */
    public function findAll(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM customers ORDER BY name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Find all customers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Знаходить клієнта за ID
     */
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $customer = $stmt->fetch();
            return $customer ?: null;
        } catch (Exception $e) {
            error_log('Find customer by ID error: ' . $e->getMessage());
            return null;
        }
    }
}