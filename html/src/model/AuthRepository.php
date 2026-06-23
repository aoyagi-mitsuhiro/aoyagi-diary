<?php

namespace Aoyagi\AoyagiDiary\model;

use PDO;
use PDOException;

class AuthRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTable(): void
    {
        try {
            $userTableSql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->pdo->query($userTableSql);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return;
        }
    }

    public function getUserByUsername(string $username): ?array
    {
        $sql = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $sql->execute(['username' => $username]);
        $user = $sql->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function signup(string $username, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $result = $sql->execute([
            'username' => $username,
            'password' => $hashedPassword
        ]);

        return $result;
    }
}
