<?php

namespace Repositories;

use PDO;

class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function reduceStock(int $productId, int $quantity): void
    {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $productId]);
    }
    /**
     * Знаходить всі товари
     */
    public function findAll(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM products ORDER BY name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Find all products error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Знаходить товар за ID разом із зображеннями
     */
    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
            SELECT p.* 
            FROM products p
            WHERE p.id = :id
        ");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return null;
            }

            $imagesStmt = $this->db->prepare("
            SELECT id, image_filename 
            FROM product_images 
            WHERE product_id = :product_id
        ");
            $imagesStmt->execute(['product_id' => $id]);
            $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

            $product['additional_images'] = $images;

            return $product;
        } catch (Exception $e) {
            error_log('Find product by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Створює новий товар
     */
    public function create(array $data): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (
                    name, brand, price, description, full_description, 
                    image, stock, screen_size, video_card_type, 
                    storage_type, device_weight
                ) 
                VALUES (
                    :name, :brand, :price, :description, :full_description, 
                    :image, :stock, :screen_size, :video_card_type, 
                    :storage_type, :device_weight
                )
            ");

            $stmt->execute([
                'name' => $data['name'],
                'brand' => $data['brand'] ?? '',
                'price' => (float)$data['price'],
                'description' => $data['description'] ?? null,
                'full_description' => $data['full_description'] ?? null,
                'image' => $data['image'] ?? null,
                'stock' => (int)($data['stock'] ?? 0),
                'screen_size' => $data['screen_size'] ?? null,
                'video_card_type' => $data['video_card_type'] ?? 'Integrated',
                'storage_type' => $data['storage_type'] ?? 'SSD',
                'device_weight' => $data['device_weight'] ?? null
            ]);

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Create product error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Оновлює товар
     */
    public function update(int $id, array $data): bool {
        try {
            $stmt = $this->db->prepare("
            UPDATE products SET
                name = :name,
                brand = :brand,
                price = :price, 
                description = :description, 
                full_description = :full_description, 
                stock = :stock, 
                screen_size = :screen_size, 
                video_card_type = :video_card_type, 
                storage_type = :storage_type, 
                device_weight = :device_weight,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

            $params = [
                'id' => $id,
                'name' => $data['name'],
                'brand' => $data['brand'] ?? '',
                'price' => (float)$data['price'],
                'description' => $data['description'] ?? null,
                'full_description' => $data['full_description'] ?? null,
                'stock' => (int)($data['stock'] ?? 0),
                'screen_size' => $data['screen_size'] ?? null,
                'video_card_type' => $data['video_card_type'] ?? 'Integrated',
                'storage_type' => $data['storage_type'] ?? 'SSD',
                'device_weight' => $data['device_weight'] ?? null
            ];

            if (isset($data['image'])) {
                $imageStmt = $this->db->prepare("UPDATE products SET image = :image WHERE id = :id");
                $imageStmt->execute([
                    'id' => $id,
                    'image' => $data['image']
                ]);
            }

            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log('Update product error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Видаляє товар
     */
    public function delete(int $id): bool {
        try {
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) FROM order_items WHERE product_id = :id
            ");
            $checkStmt->execute(['id' => $id]);

            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('Неможливо видалити товар, який пов\'язаний із замовленнями');
            }

            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log('Delete product error: ' . $e->getMessage());
            throw $e;
        }
    }
}