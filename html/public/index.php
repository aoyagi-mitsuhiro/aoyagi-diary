<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Aoyagi\AoyagiDiary\Controller;

$action = $_GET['action'] ?? 'home';

$controller = new Controller();

switch ($action) {
    case 'home':
        $controller->showHome();
        break;
    case 'form':
        $controller->showForm();
        break;
    case 'detail':
        $controller->showDetail();
        break;
    case 'confirm':
        $controller->showConfirm();
        break;

    case 'insert':
        $controller->insertDiary();
        break;
    case 'update':
        $controller->updateDiary();
        break;
    case 'delete':
        $controller->deleteDiary();
        break;

    default:
        $controller->showHome();
        break;
}
