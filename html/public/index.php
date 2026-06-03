<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Aoyagi\AoyagiDiary\Controller;

$uri = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $uri);

$controller = new Controller();

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
        $controller->insertDiary();
    } else if (is_numeric($secondPart)) {
        $id = (int)$secondPart;

        if ($thirdPart === '') {
            $controller->showDetail($id);
        } else if ($thirdPart === 'update') {
            $controller->updateDiary($id);
        } else if ($thirdPart === 'delete') {
            $controller->deleteDiary($id);
        } else {
            header('Location: /diaries');
            exit;
        }
    } else {
        header('Location: /diaries');
        exit;
    }
}
