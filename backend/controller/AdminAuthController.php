<?php

namespace controller;

use Models\AdminAuthModel;
use backend\DTO\AdminDTO;

class AdminAuthController extends AdminBaseController
{
    private AdminAuthModel $authModel;

    public function __construct(AdminAuthModel $authModel)
    {
        $this->authModel = $authModel;
    }

    public function authenticate(AdminDTO $adminDTO): array
    {
        try {
            $admin = $this->authModel->authenticate($adminDTO->username, $adminDTO->password);

            if ($admin) {
                return $this->successResponse('Успішна аутентифікація', ['admin_id' => $admin['id']]);
            }

            return $this->errorResponse('Невірні облікові дані');
        } catch (Exception $e) {
            return $this->errorResponse('Помилка аутентифікації', $e);
        }
    }
}