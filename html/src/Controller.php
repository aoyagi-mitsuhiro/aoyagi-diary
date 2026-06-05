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

        $errorMessage = '';
        $dateParts = explode('-', $date);
        $inputDate = new \DateTime($date);
        $today = new \DateTime();

        if (count($dateParts) === 3) {
            $year = (int)$dateParts[0];
            $month = (int)$dateParts[1];
            $day = (int)$dateParts[2];

            if (!checkdate($month, $day, $year)) {
                http_response_code(400);
                $errorMessage = "Invalid date: {$date}";
            }
        } else {
            http_response_code(400);
            $errorMessage = 'Invalid date format';
        }

        if ($title === '' || $date === '' || $contents === '') {
            http_response_code(400);
            $errorMessage = 'All fields are required';
        }

        if (strlen($title) > 255) {
            http_response_code(400);
            $errorMessage = 'Title must be 255 characters or less';
        }

        if ($inputDate > $today) {
            http_response_code(400);
            $errorMessage = 'Date cannot be in the future';
        }

        if ($errorMessage !== '') {

            echo $this->twig->render('formScreen.html.twig', [
                'title' => $title,
                'date' => $date,
                'contents' => $contents,
                'error_message' => $errorMessage,
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
            return;
        }


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

        if ($title !== '' && $date !== '' && $contents !== '') {
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

        $errorMessage = '';
        $inputDate = new \DateTime($date);
        $today = new \DateTime();
        $dateParts = explode('-', $date);

        // 日付の形式と妥当性をチェック
        if (count($dateParts) === 3) {
            $year = (int)$dateParts[0];
            $month = (int)$dateParts[1];
            $day = (int)$dateParts[2];

            if (!checkdate($month, $day, $year)) {
                http_response_code(400);
                $errorMessage = "Invalid date: {$date}";
            }
        } else {
            http_response_code(400);
            $errorMessage = 'Invalid date format';
        }

        // 必須項目のチェック
        if ($title === '' || $date === '' || $contents === '') {
            http_response_code(400);
            $errorMessage = 'All fields are required';
        }

        // タイトルの長さのチェック
        if (strlen($title) > 255) {
            http_response_code(400);
            $errorMessage = 'Title must be 255 characters or less';
        }

        // 日付が未来でないことのチェック
        if ($inputDate > $today) {
            http_response_code(400);
            $errorMessage = 'Date cannot be in the future';
        }

        if ($errorMessage !== '') {
            $diary = [
                'id' => $id,
                'title' => $title,
                'date' => $date,
                'contents' => $contents
            ];

            echo $this->twig->render('detailScreen.html.twig', [
                'diary' => $diary,
                'error_message' => $errorMessage,
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
            return;
        }

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
