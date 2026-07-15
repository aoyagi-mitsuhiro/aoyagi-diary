<?php

namespace Aoyagi\AoyagiDiary\controller;

use Twig\Environment;
use Aoyagi\AoyagiDiary\model\DiaryRepository;
use Aoyagi\AoyagiDiary\validator\DiaryValidator;
use Aoyagi\AoyagiDiary\service\DiaryDownloadInterface;

class DiaryController
{
    private DiaryRepository $diaryRepository;
    private Environment $twig;
    private DiaryValidator $validator;
    private DiaryDownloadInterface $downloadService;

    public function __construct(
        DiaryRepository $diaryRepository,
        Environment $twig,
        DiaryValidator $validator,
        DiaryDownloadInterface $downloadService,
    ) {
        $this->diaryRepository = $diaryRepository;
        $this->twig = $twig;
        $this->validator = $validator;
        $this->downloadService = $downloadService;
    }

    public function showHome(?string $errorMessages = null): void
    {
        $user_id = $_SESSION['user_id'];
        $diaries = $this->diaryRepository->getHomeDiaries((int)$user_id);

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
        $errorMessages = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $date = $_POST['date'] ?? '';
            $contents = $_POST['contents'] ?? '';
            $is_private = isset($_POST['is_private']);
            $errorMessages = $this->validator->validate($title, $date, $contents);
        } else {
            $title = $_SESSION['tmp_title'] ?? '';
            $date = $_SESSION['tmp_date'] ?? '';
            $contents = $_SESSION['tmp_contents'] ?? '';
            $is_private = $_SESSION['tmp_is_private'] ?? '';
        }

        echo $this->twig->render('formScreen.html.twig', [
            'title' => $title,
            'date' => $date,
            'contents' => $contents,
            'is_private' => $is_private,
            'csrf_token_value' => $_SESSION['csrf_token'],
            'error_messages' => $errorMessages,
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
        $diary = $this->diaryRepository->getDiaryById($id);
        $login_user_id = (int)$_SESSION['user_id'];
        $diary_owner_id = (int)$diary['user_id'];


        if (!$diary || $diary_owner_id !== $login_user_id) {
            $_SESSION['flash_error'] = "You do not have permission to edit/delete this diary.";
            header('Location: /diaries');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $contents = $_POST['contents'] ?? '';
        $errorMessages = $this->validator->validate($title, $date, $contents);

        if (!empty($errorMessages)) {
            $diary = [
                'id' => $id,
                'title' => $title,
                'date' => $date,
                'contents' => $contents
            ];

            echo $this->twig->render('detailScreen.html.twig', [
                'diary' => $diary,
                'error_messages' => $errorMessages,
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

    public function downloadDiaries(): void
    {
        $user_id = $_SESSION['user_id'];
        $diaries = $this->diaryRepository->getHomeDiaries((int)$user_id);
        $this->downloadService->downloadList($diaries);
    }


    // 
    // api
    // 
    public function apiDownloadDiaries(): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $diaries = $this->diaryRepository->getHomeDiaries((int)$user_id);
        $this->downloadService->downloadList($diaries);
    }

    public function apiGetDiaryList(): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $diaries = $this->diaryRepository->getHomeDiaries((int)$user_id);

        http_response_code(200);
        echo json_encode(['diaries' => $diaries]);
    }

    public function apiInsertDiary(): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $contents = $input['contents'] ?? '';
        $is_private = !empty($input['is_private']);

        $errorMessages = $this->validator->validate($title, $date, $contents);
        if (!empty($errorMessages)) {
            http_response_code(400);
            echo json_encode(['errors' => $errorMessages]);
            return;
        }

        $isDiarySaved = $this->diaryRepository->saveDiary($title, $date, $contents, $is_private, (int)$user_id);
        if (!$isDiarySaved) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save diary']);
            return;
        }

        http_response_code(201);
        echo json_encode(['message' => 'created']);
    }

    public function apiShowDetail(int $diaryId): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $diary = $this->diaryRepository->getDiaryById($diaryId);

        if (!$diary) {
            http_response_code(404);
            echo json_encode(['error' => 'Diary not found']);
            return;
        }
        if ($diary['is_private'] && (int)$diary['user_id'] !== (int)$user_id) {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to read this diary.']);
            return;
        }

        http_response_code(200);
        echo json_encode(['diary' => $diary]);
    }

    public function apiUpdateDiary(int $diaryId): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $diary = $this->diaryRepository->getDiaryById($diaryId);
        if (!$diary) {
            http_response_code(404);
            echo json_encode(['error' => 'Diary not found']);
            return;
        }
        if ((int)$diary['user_id'] !== (int)$user_id) {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to edit this diary.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $contents = $input['contents'] ?? '';

        $errorMessages = $this->validator->validate($title, $date, $contents);
        if (!empty($errorMessages)) {
            http_response_code(400);
            echo json_encode(['errors' => $errorMessages]);
            return;
        }

        $isDiaryUpdated = $this->diaryRepository->updateDiary($diaryId, $title, $date, $contents);
        if (!$isDiaryUpdated) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update diary']);
            return;
        }

        http_response_code(200);
        echo json_encode(['message' => 'updated']);
    }

    public function apiDeleteDiary(int $diaryId): void
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $diary = $this->diaryRepository->getDiaryById($diaryId);
        if (!$diary) {
            http_response_code(404);
            echo json_encode(['error' => 'Diary not found']);
            return;
        }
        if ((int)$diary['user_id'] !== (int)$user_id) {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to delete this diary.']);
            return;
        }

        $isDiaryDeleted = $this->diaryRepository->deleteDiary($diaryId);
        if (!$isDiaryDeleted) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete diary']);
            return;
        }

        http_response_code(200);
        echo json_encode(['message' => 'deleted']);
    }
}
