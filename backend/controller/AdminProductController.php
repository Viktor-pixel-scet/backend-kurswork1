<?php

namespace controller;

use Models\ProductModel;
use Exception;

class AdminProductController extends AdminBaseController
{
    private ProductModel $productModel;

    public function __construct(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }

    public function getAll(): array
    {
        return $this->productModel->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->productModel->getById($id);
    }

    public function save(array $data): array
    {
        try {
            error_log("AdminProductController save called with data: " . print_r($data, true));

            $id = (int)($data['id'] ?? 0);
            error_log("Product ID: " . $id);

            if (!$this->validateRequired($data, ['name', 'price'])) {
                error_log("Validation failed for required fields");
                return $this->errorResponse('Необхідні поля не заповнені');
            }

            $productData = $this->prepareProductData($data);
            error_log("Prepared product data: " . print_r($productData, true));

            if ($id > 0) {
                error_log("Attempting to update product with ID: " . $id);
                $result = $this->productModel->update($id, $productData);
                error_log("Update result: " . ($result ? 'success' : 'failed'));

                if ($result) {
                    $this->handleProductImages($id, $data);
                    return $this->successResponse('Товар успішно оновлено');
                }

                return $this->errorResponse('Помилка під час оновлення товару');
            } else {
                error_log("Attempting to create new product");
                $newId = $this->productModel->create($productData);
                error_log("Create result - new ID: " . $newId);

                if ($newId > 0) {
                    $this->handleProductImages($newId, $data);
                    return $this->successResponse('Товар успішно створено', ['id' => $newId]);
                }

                return $this->errorResponse('Помилка під час створення товару');
            }
        } catch (Exception $e) {
            error_log("AdminProductController save error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->errorResponse('Виникла помилка під час збереження товару: ' . $e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->productModel->delete($id);

            if ($result) {
                return $this->successResponse('Товар успішно видалено');
            }

            return $this->errorResponse('Помилка під час видалення товару');
        } catch (Exception $e) {
            error_log("AdminProductController delete error: " . $e->getMessage());
            return $this->errorResponse('Виникла помилка під час видалення товару: ' . $e->getMessage());
        }
    }

    public function getImages(int $productId): array
    {
        return $this->productModel->getImages($productId);
    }

    private function prepareProductData(array $data): array
    {
        $productData = [
            'name' => trim($data['name'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'price' => (float)($data['price'] ?? 0),
            'brand' => trim($data['brand'] ?? ''),
            'category' => trim($data['category'] ?? ''),
            'stock_quantity' => (int)($data['stock'] ?? 0),
            'image' => null
        ];

        if (!empty($data['product_image_url'])) {
            $imageUrls = array_filter(
                array_map('trim', explode("\n", $data['product_image_url'])),
                function($url) {
                    return !empty($url) && filter_var($url, FILTER_VALIDATE_URL);
                }
            );

            if (!empty($imageUrls)) {
                $productData['image'] = implode("\n", $imageUrls);
            }
        }

        $additionalFields = [
            'full_description',
            'screen_size',
            'video_card_type',
            'storage_type',
            'device_weight'
        ];

        foreach ($additionalFields as $field) {
            if (isset($data[$field])) {
                $productData[$field] = trim($data[$field]);
            }
        }

        return $productData;
    }

    private function handleProductImages(int $productId, array $data): void
    {
        try {
            $imagesToRemove = [];
            if (!empty($data['images_to_remove'])) {
                $imagesToRemove = array_filter(
                    array_map('intval', explode(',', $data['images_to_remove'])),
                    function($id) { return $id > 0; }
                );
            }

            if (!empty($imagesToRemove)) {
                $this->productModel->removeImages($imagesToRemove);
            }

            $additionalImageUrls = $data['additional_image_url'] ?? [];

            if (!is_array($additionalImageUrls)) {
                $additionalImageUrls = [$additionalImageUrls];
            }

            foreach ($additionalImageUrls as $url) {
                $url = trim($url);
                if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->productModel->addImage($productId, $url);
                }
            }

        } catch (Exception $e) {
            error_log("Error handling product images: " . $e->getMessage());
        }
    }

    public function validateRequired(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}