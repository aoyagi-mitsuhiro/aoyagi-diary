<?php

namespace Aoyagi\AoyagiDiary\model;

use PDO;
use PDOException;

class DiaryRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getHomeDiaries(int $user_id): array
    {
        try {
            $sql = "SELECT id, title, date, contents, is_private FROM diaries WHERE is_private = 0 OR user_id = :user_id ORDER BY id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDiaryById(int $id): ?array
    {
        try {
            $sql = "SELECT id, title, date, contents, is_private, user_id FROM diaries WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $diary = $stmt->fetch(PDO::FETCH_ASSOC);
            return $diary ?: null;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    public function saveDiary(string $title, string $date, string $contents, bool $is_private, int $user_id): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO diaries (title, date, contents, is_private, user_id) 
            VALUES (:title, :date, :contents, :is_private, :user_id)");
            $result = $stmt->execute([
                ':title' => $title,
                ':date' => $date,
                ':contents' => $contents,
                ':is_private' => (int)$is_private,
                ':user_id' => (int)$user_id,
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDiary(int $id, string $title, string $date, string $contents): bool
    {
        try {
            $sql = "UPDATE diaries SET title = :title, date = :date, contents = :contents WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':id' => $id,
                ':title' => $title,
                ':date' => $date,
                ':contents' => $contents,
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDiary(int $id): bool
    {
        try {
            $sql = "DELETE FROM diaries WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            return $result;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
}
