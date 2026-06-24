<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Aoyagi\AoyagiDiary\controller\DiaryController;
use Aoyagi\AoyagiDiary\controller\AuthController;


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = require_once __DIR__ . '/../config/container.php';
$authController = $container->get(AuthController::class);
$diaryController = $container->get(DiaryController::class);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', $requestUri);

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
$firstPart = $parts[1] ?? '';
$secondPart = $parts[2] ?? '';
$thirdPart = $parts[3] ?? '';

if (!$isLoggedIn) {
    if ($firstPart === 'login') {
        if ($method === 'POST') {
            $authController->login();
        } else {
            $authController->showLogin();
        }
    } else if ($firstPart === 'signup') {
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
    $authController->checkAuth();

    if ($firstPart === 'logout') {
        $authController->logout();
        exit;
    }

    if ($firstPart === 'diaries') {

        if ($secondPart === '') {
            $diaryController->showHome();
        } else if ($secondPart === 'form') {
            $diaryController->showForm();
        } else if ($secondPart === 'confirm') {
            $diaryController->showConfirm();
        } else if ($secondPart === 'new') {
            if ($method === 'POST') {
                $diaryController->insertDiary();
            } else {
                http_response_code(405);
                die('method not allowed');
            }
        } else if ($secondPart === 'download') {
            $diaryController->downloadDiaries();
        } else if (is_numeric($secondPart)) {
            $id = (int)$secondPart;

            if ($thirdPart === '') {
                $diaryController->showDetail($id);
            } else if ($thirdPart === 'update') {
                if ($method === 'PUT') {
                    $diaryController->updateDiary($id);
                } else {
                    http_response_code(405);
                    die('method not allowed');
                }
            } else if ($thirdPart === 'delete') {
                if ($method === 'DELETE') {
                    $diaryController->deleteDiary($id);
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
