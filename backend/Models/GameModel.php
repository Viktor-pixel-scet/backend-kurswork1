<?php

namespace Models;

use Exception;
use PDO;

class GameModel extends AdminModel
{
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM games ORDER BY game_name");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError('Get all games error', $e);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $game = $stmt->fetch();
            return $game ?: null;
        } catch (Exception $e) {
            $this->logError('Get game by ID error', $e);
            return null;
        }
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO games (game_code, game_name, min_fps, max_fps, category) 
                VALUES (:game_code, :game_name, :min_fps, :max_fps, :category)
            ");

            $stmt->execute([
                'game_code' => $data['game_code'],
                'game_name' => $data['game_name'],
                'min_fps' => (int)$data['min_fps'],
                'max_fps' => (int)$data['max_fps'],
                'category' => $data['category']
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (Exception $e) {
            $this->logError('Create game error', $e);
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE games 
                SET game_code = :game_code, 
                    game_name = :game_name, 
                    min_fps = :min_fps, 
                    max_fps = :max_fps, 
                    category = :category 
                WHERE id = :id
            ");

            return $stmt->execute([
                'id' => $id,
                'game_code' => $data['game_code'],
                'game_name' => $data['game_name'],
                'min_fps' => (int)$data['min_fps'],
                'max_fps' => (int)$data['max_fps'],
                'category' => $data['category']
            ]);
        } catch (Exception $e) {
            $this->logError('Update game error', $e);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM games WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            $this->logError('Delete game error', $e);
            return false;
        }
    }

    public function getCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM games");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $this->logError('Get games count error', $e);
            return 0;
        }
    }
}