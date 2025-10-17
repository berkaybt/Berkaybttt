<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;

class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function loginForm(): string
    {
        ob_start();
        include __DIR__ . '/../views/pages/login.php';
        return (string) ob_get_clean();
    }

    public function registerForm(): string
    {
        ob_start();
        include __DIR__ . '/../views/pages/register.php';
        return (string) ob_get_clean();
    }

    public function login(): string
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->users->getByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Geçersiz e-posta veya şifre';
            header('Location: ' . base_url('/login'));
            exit;
        }
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        header('Location: ' . base_url('/'));
        exit;
    }

    public function register(): string
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = 'Şifreler uyuşmuyor';
            header('Location: ' . base_url('/register'));
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->users->create($name, $email, $hash);

        $_SESSION['user'] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ];
        header('Location: ' . base_url('/'));
        exit;
    }

    public function logout(): string
    {
        unset($_SESSION['user']);
        header('Location: ' . base_url('/'));
        exit;
    }
}
