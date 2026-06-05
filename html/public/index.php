<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

use Aoyagi\AoyagiDiary\Controller;

$uri = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $uri);

$method = $_SERVER['REQUEST_METHOD'];
if (isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$controller = new Controller();

if ($parts[0] === 'diaries') {
    $secondPart = $parts[1] ?? '';
    $thirdPart = $parts[2] ?? '';

    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('CSRF Token Invalid');
        }
    }

    if ($secondPart === '') {
        $controller->showHome();
    } else if ($secondPart === 'form') {
        $controller->showForm();
    } else if ($secondPart === 'confirm') {
        $controller->showConfirm();
    } else if ($secondPart === 'new') {
        if ($method === 'POST') {
            $controller->insertDiary();
        } else {
            http_response_code(405);
            die('method not allowed');
        }
    } else if (is_numeric($secondPart)) {
        $id = (int)$secondPart;

        if ($thirdPart === '') {
            $controller->showDetail($id);
        } else if ($thirdPart === 'update') {
            if ($method === 'PUT') {
                $controller->updateDiary($id);
            } else {
                http_response_code(405);
                die('method not allowed');
            }
        } else if ($thirdPart === 'delete') {
            if ($method === 'DELETE') {
                $controller->deleteDiary($id);
            } else {
                http_response_code(405);
                die('method not allowed');
            }
        } else {
            header('Location: /diaries');
            exit;
        }
    } else {
        header('Location: /diaries');
        exit;
    }
}
