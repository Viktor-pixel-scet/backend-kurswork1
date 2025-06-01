<?php

namespace controller;

use Exception;

abstract class AdminBaseController
{
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function successResponse(string $message, array $data = []): array
    {
        return array_merge(['success' => true, 'message' => $message], $data);
    }

    protected function errorResponse(string $message, Exception $e = null): array
    {
        if ($e) {
            error_log($message . ': ' . $e->getMessage());
        }
        return ['success' => false, 'message' => $message];
    }

    protected function validateRequired(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}