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

    public function getUserByUsername(string $username): ?array
    {
        try {
            $sql = $this->pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
            $sql->execute(['username' => $username]);
            $user = $sql->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    public function signup(string $username, string $password): bool
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $result = $sql->execute([
                'username' => $username,
                'password' => $hashedPassword
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
}
