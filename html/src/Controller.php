<?php

namespace Aoyagi\AoyagiDiary;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    private Model $model;
    private FilesystemLoader $loader;
    private Environment $twig;

    public function __construct()
    {
        $this->model = new Model();
        $this->loader = new FilesystemLoader(__DIR__ . '/views');
        $this->twig = new Environment($this->loader);
    }

    public function showHome(): void
    {
        $diaries = $this->model->getDiaries();

        echo $this->twig->render('homeScreen.html.twig', ['diaries' => $diaries]);
    }

    public function showForm(): void
    {
        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';

        echo $this->twig->render('formScreen.html.twig', [
            'title' => $title,
            'date' => $date,
            'contents' => $contents,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function showDetail(int $id): void
    {
        $diary = $this->model->getDiaryById($id);

        if (!$diary) {
            header('Location: /diaries');
            exit;
        }

        echo $this->twig->render('detailScreen.html.twig', [
            'diary' => $diary,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function showConfirm(): void
    {
        $_SESSION['tmp_title'] = $_POST['title'] ?? '';
        $_SESSION['tmp_date'] = $_POST['date'] ?? '';
        $_SESSION['tmp_contents'] = $_POST['contents'] ?? '';

        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';

        echo $this->twig->render('confirmScreen.html.twig', [
            'title' => $title,
            'date' => $date,
            'contents' => $contents,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
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
        header('Location: /diaries');
        exit;
    }


    public function updateDiary(int $id): void
    {
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $contents = $_POST['contents'] ?? '';

        $this->model->updateDiary($id, $title, $date, $contents);
        header('Location: /diaries');
        exit;
    }

    public function deleteDiary(int $id): void
    {
        $this->model->deleteDiary($id);
        header('Location: /diaries');
        exit;
    }
}
