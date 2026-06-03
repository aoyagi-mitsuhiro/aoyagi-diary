<?php

namespace Aoyagi\AoyagiDiary;

use PDO;
use PDOException;

class Model
{
    private array $diaries = [];
    private PDO $pdo;
    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $username, $password);

        $this->createTable();
    }

    public function getDiaries()
    {
        $sql = "SELECT id, title, date, contents FROM diaries ORDER BY date DESC";
        $stmt = $this->pdo->query($sql);
        $this->diaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->diaries;
    }

    public function getDiaryById(int $id)
    {
        try {
            $sql = "SELECT id, title, date, contents FROM diaries WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $diary = $stmt->fetch(PDO::FETCH_ASSOC);
            return $diary;
        } catch (PDOException $e) {
            // Handle the error, e.g., log it or display an error message
            error_log('Database error: ' . $e->getMessage());
            exit;
        }
    }

    public function saveDiary(string $title, string $date, string $contents): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO diaries (title, date, contents) VALUES (:title, :date, :contents)");
            $result = $stmt->execute([
                ':title' => $title,
                ':date' => $date,
                ':contents' => $contents,
            ]);

            return $result;
        } catch (PDOException $e) {
            // Handle the error, e.g., log it or display an error message
            error_log('Database error: ' . $e->getMessage());
            exit;
        }
    }

    public function createTable(): void
    {
        $tableSql = "CREATE TABLE IF NOT EXISTS diaries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            contents TEXT NOT NULL
        )";
        $this->pdo->query($tableSql);
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
