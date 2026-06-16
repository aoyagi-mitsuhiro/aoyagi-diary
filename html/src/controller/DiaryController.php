<?php

namespace Aoyagi\AoyagiDiary\controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Aoyagi\AoyagiDiary\model\DiaryRepository;
use Aoyagi\AoyagiDiary\validator\DiaryValidator;

class DiaryController
{
    private DiaryRepository $diaryRepository;
    private FilesystemLoader $loader;
    private Environment $twig;
    private DiaryValidator $validator;

    public function __construct()
    {
        $this->diaryRepository = new DiaryRepository();
        $this->loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->validator = new DiaryValidator();
        $this->twig = new Environment($this->loader);
    }

    public function showHome(?string $errorMessages = null): void
    {
        $user_id = $_SESSION['user_id'];
        $diaries = $this->diaryRepository->getDiaries((int)$user_id);

        if (isset($_SESSION['flash_error'])) {
            $errorMessages = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        echo $this->twig->render('homeScreen.html.twig', [
            'diaries' => $diaries,
            'error_messages' => $errorMessages,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function showForm(): void
    {
        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';
        $is_private = $_SESSION['tmp_is_private'] ?? '';

        echo $this->twig->render('formScreen.html.twig', [
            'title' => $title,
            'date' => $date,
            'contents' => $contents,
            'is_private' => $is_private,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function showDetail(int $id): void
    {
        $user_id = $_SESSION['user_id'];
        $diary = $this->diaryRepository->getDiaryById($id);

        if ($diary) {
            if ($diary['is_private'] && (int)$diary['user_id'] !== (int)$user_id) {
                $_SESSION['flash_error'] = 'This is private diary. No access rights';
                header('Location: /diaries');
                exit;
            }
        } else {
            $_SESSION['flash_error'] = 'There is no diary';
            header('Location: /diaries');
            exit;
        }

        echo $this->twig->render('detailScreen.html.twig', [
            'diary' => $diary,
            'diary_owner_id' => $diary['user_id'],
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function showConfirm(): void
    {
        $_SESSION['tmp_title'] = $_POST['title'] ?? '';
        $_SESSION['tmp_date'] = $_POST['date'] ?? '';
        $_SESSION['tmp_contents'] = $_POST['contents'] ?? '';
        $_SESSION['tmp_is_private'] = isset($_POST['is_private']) ? true : false;

        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';
        $is_private = $_SESSION['tmp_is_private'];


        $errorMessages = $this->validator->validate($title, $date, $contents);

        if (!empty($errorMessages)) {

            echo $this->twig->render('formScreen.html.twig', [
                'title' => $title,
                'date' => $date,
                'contents' => $contents,
                'is_private' => $is_private,
                'error_messages' => $errorMessages,
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
            return;
        }

        echo $this->twig->render('confirmScreen.html.twig', [
            'title' => $title,
            'date' => $date,
            'contents' => $contents,
            'is_private' => $is_private,
            'csrf_token_value' => $_SESSION['csrf_token']
        ]);
    }

    public function insertDiary(): void
    {
        $title = $_SESSION['tmp_title'] ?? '';
        $date = $_SESSION['tmp_date'] ?? '';
        $contents = $_SESSION['tmp_contents'] ?? '';
        $is_private = $_SESSION['tmp_is_private'] ?? '';
        $user_id = $_SESSION['user_id'] ?? '';

        if ($title !== '' && $date !== '' && $contents !== '') {
            $this->diaryRepository->saveDiary($title, $date, $contents, $is_private, (int)$user_id);
        }

        unset($_SESSION['tmp_title'], $_SESSION['tmp_date'], $_SESSION['tmp_contents'], $_SESSION['tmp_is_private']);
        header('Location: /diaries');
        exit;
    }


    public function updateDiary(int $id): void
    {
        $user_id = (int)$_SESSION['user_id'];
        $diary = $this->diaryRepository->getDiaryById($id);

        if (!$diary || (int)$diary['user_id'] !== $user_id) {
            $_SESSION['flash_error'] = "You do not have permission to edit/delete this diary.";
            header('Location: /diaries');
            exit;
        }

        $diary_owner_id = $user_id;

        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $contents = $_POST['contents'] ?? '';
        $errorMessages = $this->validator->validate($title, $date, $contents);

        if (!empty($errorMessages)) {
            $diary = [
                'id' => $id,
                'title' => $title,
                'date' => $date,
                'contents' => $contents,
                'user_id' => $diary_owner_id
            ];

            echo $this->twig->render('detailScreen.html.twig', [
                'diary' => $diary,
                'error_messages' => $errorMessages,
                'diary_owner_id' => (int)$_POST['diary_owner_id'],
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
            return;
        }



        $this->diaryRepository->updateDiary($id, $title, $date, $contents);
        header('Location: /diaries');
        exit;
    }

    public function deleteDiary(int $id): void
    {
        $user_id = (int)$_SESSION['user_id'];
        $diary = $this->diaryRepository->getDiaryById($id);

        if (!$diary || (int)$diary['user_id'] !== $user_id) {
            $_SESSION['flash_error'] = "You do not have permission to edit/delete this diary.";
            header('Location: /diaries');
            exit;
        }

        $this->diaryRepository->deleteDiary($id);
        header('Location: /diaries');
        exit;
    }
}
