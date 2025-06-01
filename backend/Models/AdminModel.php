<?php

namespace Models;

use Exception;
use PDO;

abstract class AdminModel
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function logError(string $message, Exception $e): void
    {
        error_log($message . ': ' . $e->getMessage());
    }
}