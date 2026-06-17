<?php

namespace Aoyagi\AoyagiDiary\controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Aoyagi\AoyagiDiary\model\AuthRepository;
use Aoyagi\AoyagiDiary\validator\AuthValidator;
use Aoyagi\AoyagiDiary\database\Database;


class AuthController
{
    private AuthRepository $authRepository;
    private FilesystemLoader $loader;
    private Environment $twig;
    private AuthValidator $validator;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->authRepository = new AuthRepository($pdo);
        $this->loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($this->loader);
        $this->validator = new AuthValidator();
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
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $errorMessages = $this->validator->validate($username,  $password);

        if (!empty($errorMessages)) {
            $this->renderWithError($errorMessages, 'login');
            return;
        }

        // DBから該当username持ってくる
        $user = $this->authRepository->getUserByUsername($username);

        // password_verify($inputPassword, $dbPassword) 暗証番号 check
        if (!$user || !password_verify($password, $user['password'])) {
            $this->renderWithError(['id or pw is not collect.'], 'login');
            return;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: /diaries');
        exit;
    }

    public function signUp(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $errorMessages = $this->validator->validate($username,  $password);

        if (!empty($errorMessages)) {
            $this->renderWithError($errorMessages, 'signup');
            return;
        }

        try {
            $this->authRepository->signup($username, $password);
            header('Location: /login');
            exit;
        } catch (\PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                $this->renderWithError(['id is duplicated'], 'signup');
            } else {
                $this->renderWithError(['sign error.' . $e->getMessage()], 'signup');
            }
        }
    }

    public function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['username']);
        session_destroy();
        header('Location: /login');
    }

    public function renderWithError(array $errorMessages, string $screen): void
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
}
