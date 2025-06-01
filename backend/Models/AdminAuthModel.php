<?php

namespace Models;

use Exception;
use PDO;

class AdminAuthModel extends AdminModel
{
    public function authenticate(string $username, string $password): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, password FROM admins WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                return ['id' => $admin['id'], 'username' => $username];
            }

            return null;
        } catch (Exception $e) {
            $this->logError('Authentication error', $e);
            return null;
        }
    }
}