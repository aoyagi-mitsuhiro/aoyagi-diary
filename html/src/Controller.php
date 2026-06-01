<?php

namespace Aoyagi\AoyagiDiary;

class Controller
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function showHome(): void
    {
        $diaries = $this->model->getDiaries();
        include __DIR__ . '/views/homeScreen.php';
    }

    public function showForm(): void
    {
        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';

        include __DIR__ . '/views/formScreen.php';
    }

    public function showDetail(): void
    {
        $id = $_GET['id'] ?? null;
        $id = (int)$id;
        $diary = $this->model->getDiaryById($id);

        if (!$diary) {
            header('Location: ?action=home');
            exit;
        }

        include __DIR__ . '/views/detailScreen.php';
    }

    public function showConfirm(): void
    {
        $_SESSION['tmp_title'] = $_POST['title'] ?? '';
        $_SESSION['tmp_date'] = $_POST['date'] ?? '';
        $_SESSION['tmp_contents'] = $_POST['contents'] ?? '';

        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';

        include __DIR__ . '/views/confirmScreen.php';
    }

    public function insertDiary(): void
    {
        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';

        if ($title !== '') {
            $this->model->saveDiary($title, $date, $contents);
        }

        unset($_SESSION['tmp_title'], $_SESSION['tmp_date'], $_SESSION['tmp_contents']);
        header('Location: ?action=home');
        exit;
    }


    public function updateDiary(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $contents = $_POST['contents'] ?? '';

        $this->model->updateDiary($id, $title, $date, $contents);
        header('Location: ?action=home');
        exit;
    }

    public function deleteDiary(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        $this->model->deleteDiary($id);
        header('Location: ?action=home');
        exit;
    }
}
