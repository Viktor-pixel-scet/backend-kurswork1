<?php

namespace controller;

use Models\GameModel;

class AdminGameController extends AdminBaseController
{
    private GameModel $gameModel;

    public function __construct(GameModel $gameModel)
    {
        $this->gameModel = $gameModel;
    }

    public function getAll(): array
    {
        return $this->gameModel->getAll();
    }

    public function getById(int $id): ?array
    {
        return $this->gameModel->getById($id);
    }

    public function save(array $data): array
    {
        try {
            $id = (int)($data['id'] ?? 0);

            if (!$this->validateRequired($data, ['game_code', 'game_name', 'min_fps', 'max_fps', 'category'])) {
                return $this->errorResponse('Необхідні поля не заповнені');
            }

            if ($id > 0) {
                $result = $this->gameModel->update($id, $data);

                if ($result) {
                    return $this->successResponse('Гру успішно оновлено');
                }

                return $this->errorResponse('Помилка під час оновлення гри');
            } else {
                $newId = $this->gameModel->create($data);

                if ($newId > 0) {
                    return $this->successResponse('Гру успішно створено', ['id' => $newId]);
                }

                return $this->errorResponse('Помилка під час створення гри');
            }
        } catch (Exception $e) {
            return $this->errorResponse('Виникла помилка під час збереження гри', $e);
        }
    }

    public function delete(int $id): array
    {
        try {
            $result = $this->gameModel->delete($id);

            if ($result) {
                return $this->successResponse('Гру успішно видалено');
            }

            return $this->errorResponse('Помилка під час видалення гри');
        } catch (Exception $e) {
            return $this->errorResponse('Виникла помилка під час видалення гри', $e);
        }
    }
}