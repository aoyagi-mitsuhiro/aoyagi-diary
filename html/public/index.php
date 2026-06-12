<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Aoyagi\AoyagiDiary\Controller;
use Aoyagi\AoyagiDiary\AuthController;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$controller = new Controller();
$authController = new AuthController();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uri = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $uri);

$method = $_SERVER['REQUEST_METHOD'];
if (isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('CSRF Token Invalid');
    }
}

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    if ($parts[0] === 'login') {
        if ($method === 'POST') {
            $authController->login();
        } else {
            $authController->showLogin();
        }
    } else if ($parts[0] === 'signup') {
        if ($method === 'POST') {
            $authController->signUp();
        } else {
            $authController->showSignUp();
        }
    } else {
        header('Location: /login');
        exit;
    }
} else {

    if ($parts[0] === 'logout') {
        $authController->logout();
        exit;
    }

    if ($parts[0] === 'diaries') {
        $secondPart = $parts[1] ?? '';
        $thirdPart = $parts[2] ?? '';

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
    } else {
        header('Location: /diaries');
        exit;
    }
}
