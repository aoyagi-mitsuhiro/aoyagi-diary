<?php

namespace Aoyagi\AoyagiDiary\model;

use PDO;
use PDOException;

class DiaryRepository
{
    private PDO $pdo;
    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $this->createDiaryTable();
    }

    public function getDiaries(int $user_id): array
    {
        try {
            $sql = "SELECT id, title, date, contents FROM diaries WHERE is_private = 0 OR user_id = :user_id ORDER BY id DESC";
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

    public function createDiaryTable(): void
    {
        try {
            $diaryRableSql = "CREATE TABLE IF NOT EXISTS diaries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                contents TEXT NOT NULL,
                is_private BOOLEAN NOT NULL DEFAULT FALSE,
                user_id INT NOT NULL
            )";
            $this->pdo->query($diaryRableSql);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return;
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
