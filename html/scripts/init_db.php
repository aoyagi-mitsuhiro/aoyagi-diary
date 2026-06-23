<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $container = require_once __DIR__ . '/../config/container.php';
    $pdo = $container->get(PDO::class);

    $pdo->exec("CREATE TABLE IF NOT EXISTS diaries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                contents TEXT NOT NULL,
                is_private BOOLEAN NOT NULL DEFAULT FALSE,
                user_id INT NOT NULL
            ) ENGINE=InnoDB ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB ");
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    return;
}
