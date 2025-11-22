<?php
namespace App\Controllers;

class AuthController {

    public function showLoginForm() {
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login() {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        $envUser = $_SERVER['ADMIN_USERNAME'] ?? $_ENV['ADMIN_USERNAME'] ?? 'admin';
        $envPass = $_SERVER['ADMIN_PASSWORD'] ?? $_ENV['ADMIN_PASSWORD'] ?? '123456';

        if ($user === $envUser && $pass === $envPass) {
            $_SESSION['is_admin'] = true; 
            header('Location: ' . BASE_URL . '/admin');
            exit;
        } else {
            $error = "نام کاربری یا رمز عبور اشتباه است.";
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }

    public function logout() {
        session_destroy(); 
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}