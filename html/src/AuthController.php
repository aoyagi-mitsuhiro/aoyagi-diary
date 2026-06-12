<?php

namespace Aoyagi\AoyagiDiary;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AuthController
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

    public function checkAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public function showLogin(): void
    {
        echo $this->twig->render(
            'loginScreen.html.twig',
            ['csrf_token_value' => $_SESSION['csrf_token']]
        );
    }

    public function showSignUp(): void
    {
        echo $this->twig->render(
            'signUpScreen.html.twig',
            ['csrf_token_value' => $_SESSION['csrf_token']]
        );
    }

    public function login(): void
    {
        // 1) POST データと CSRF トークンの検証
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $postToken = $_POST['_token'] ?? '';

        $errorMessages = $this->errorCheck($username,  $password, $postToken, 'login');

        if (!empty($errorMessages)) {
            $this->renderWithError($errorMessages, 'login');
            return;
        }

        // 2) DBから該当username持ってくる
        $user = $this->model->getUserByUsername($username);

        // 3) password_verify($inputPassword, $dbPassword) 暗証番号 check
        if (!$user || !password_verify($password, $user['password'])) {
            $this->renderWithError(['id or pw is not collect.'], 'login');
            return;
        }

        // need test
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: /diaries');
        exit;
    }

    public function signUp(): void
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $postToken = $_POST['_token'];

        $errorMessages = $this->errorCheck($username,  $password, $postToken, 'signup');

        if (!empty($errorMessages)) {
            $this->renderWithError($errorMessages, 'signup');
            return;
        }

        try {
            $this->model->signup($username, $password);
            header('Location: /login');
            exit;
        } catch (\PDOException $e) {
            error_log('Locatio: /login');
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $this->renderWithError(['id is deplicated'], 'signup');
            } else {
                $this->renderWithError(['sign error.' . $e->getMessage()], 'signup');
            }
        }
    }

    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['username']);
        header('Location: /login');
    }

    public function renderWithError(array $errorMessages, string $screen)
    {
        if ($screen === 'login') {
            echo $this->twig->render('loginScreen.html.twig', [
                'error_messages' => $errorMessages,
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
        } else if ($screen === 'signup') {
            echo $this->twig->render('signUpScreen.html.twig', [
                'error_messages' => $errorMessages,
                'csrf_token_value' => $_SESSION['csrf_token']
            ]);
        }
    }

    private function errorCheck(string $username, string $password, string $postToken, string $screen): array
    {
        $errorMessages = [];

        if (empty($postToken) || $postToken !== ($_SESSION['csrf_token'])) {
            $errorMessages[] = 'invalid access. (CSRF Token Error)';
        }

        if (trim($username) === '' || trim($password) === '') {
            $errorMessages[] = 'id, pw are required.';
        }

        if (strlen($username) > 50) {
            $errorMessages[] = 'length of id should be less than 50.';
        }
        if (strlen($password) > 255) {
            $errorMessages[] = 'length of pw should be less than 255.';
        }

        return $errorMessages;
    }
}
