<?php

namespace Models;

use Exception;
use PDO;

class ProductModel extends AdminModel
{
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM products ORDER BY name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get all products error', $e);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch();
            return $product ?: null;
        } catch (Exception $e) {
            $this->logError('Get product by ID error', $e);
            return null;
        }
    }

    public function getCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logError('Get products count error', $e);
            return 0;
        }
    }

    public function create(array $data): int
    {
        try {
            error_log("ProductModel create called with data: " . print_r($data, true));

            $stmt = $this->pdo->prepare("DESCRIBE products");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Available columns: " . print_r($columns, true));

            $availableFields = [];
            $values = [];
            $params = [];

            $fieldMapping = [
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'price' => $data['price'] ?? 0,
                'brand' => $data['brand'] ?? '',
                'category' => $data['category'] ?? '',
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'stock' => $data['stock_quantity'] ?? 0,
                'image' => $data['image'] ?? null
            ];

            $optionalFields = [
                'full_description' => $data['full_description'] ?? null,
                'screen_size' => $data['screen_size'] ?? null,
                'video_card_type' => $data['video_card_type'] ?? null,
                'storage_type' => $data['storage_type'] ?? null,
                'device_weight' => $data['device_weight'] ?? null
            ];

            foreach ($optionalFields as $field => $value) {
                if (in_array($field, $columns) && $value !== null) {
                    $fieldMapping[$field] = $value;
                }
            }

            foreach ($fieldMapping as $field => $value) {
                if (in_array($field, $columns)) {
                    $availableFields[] = $field;
                    $values[] = ":$field";
                    $params[$field] = $value;
                }
            }

            if (empty($availableFields)) {
                throw new Exception("No valid fields found for insert");
            }

            $sql = "INSERT INTO products (" . implode(', ', $availableFields) . ") VALUES (" . implode(', ', $values) . ")";
            error_log("Generated SQL: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                error_log("SQL execution failed. Error info: " . print_r($stmt->errorInfo(), true));
                return 0;
            }

            $newId = (int)$this->pdo->lastInsertId();
            error_log("New product created with ID: " . $newId);
            return $newId;

        } catch (Exception $e) {
            error_log("ProductModel create error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->logError('Create product error', $e);
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            error_log("ProductModel update called for ID $id with data: " . print_r($data, true));

            $stmt = $this->pdo->prepare("DESCRIBE products");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Available columns: " . print_r($columns, true));

            $updateFields = [];
            $params = ['id' => $id];

            $fieldMapping = [
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'price' => $data['price'] ?? 0,
                'brand' => $data['brand'] ?? '',
                'category' => $data['category'] ?? '',
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'stock' => $data['stock_quantity'] ?? 0,
                'image' => $data['image'] ?? null
            ];

            $optionalFields = [
                'full_description' => $data['full_description'] ?? null,
                'screen_size' => $data['screen_size'] ?? null,
                'video_card_type' => $data['video_card_type'] ?? null,
                'storage_type' => $data['storage_type'] ?? null,
                'device_weight' => $data['device_weight'] ?? null
            ];

            foreach ($optionalFields as $field => $value) {
                if (in_array($field, $columns)) {
                    $fieldMapping[$field] = $value;
                }
            }

            foreach ($fieldMapping as $field => $value) {
                if (in_array($field, $columns)) {
                    $updateFields[] = "$field = :$field";
                    $params[$field] = $value;
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No valid fields found for update");
            }

            $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = :id";
            error_log("Generated SQL: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if (!$result) {
                error_log("SQL execution failed. Error info: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            error_log("Product updated successfully");
            return true;

        } catch (Exception $e) {
            error_log("ProductModel update error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->logError('Update product error', $e);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->pdo->prepare("DELETE FROM product_images WHERE product_id = :id")->execute(['id' => $id]);

            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            $this->logError('Delete product error', $e);
            return false;
        }
    }

    public function getImages(int $productId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, image_filename FROM product_images WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError('Get product images error', $e);
            return [];
        }
    }

    public function addImage(int $productId, string $imageUrl): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, image_filename) VALUES (:product_id, :image_filename)");
            return $stmt->execute([
                'product_id' => $productId,
                'image_filename' => $imageUrl
            ]);
        } catch (Exception $e) {
            $this->logError('Add product image error', $e);
            return false;
        }
    }

    public function removeImages(array $imageIds): bool
    {
        if (empty($imageIds)) {
            return true;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
            $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE id IN ($placeholders)");
            return $stmt->execute($imageIds);
        } catch (Exception $e) {
            $this->logError('Remove product images error', $e);
            return false;
        }
    }
}