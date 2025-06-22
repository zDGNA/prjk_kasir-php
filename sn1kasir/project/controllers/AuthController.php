<?php

session_start();
require_once '../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login($username, $password) {
        if($this->user->login($username, $password)) {
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['username'] = $this->user->username;
            $_SESSION['full_name'] = $this->user->full_name;
            $_SESSION['role'] = $this->user->role;
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => '../views/dashboard.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            header("Location: ../views/login.php");
            exit();
        }
    }

    public function requireRole($role) {
        $this->requireLogin();
        if($_SESSION['role'] !== $role) {
            header("Location: ../views/dashboard.php");
            exit();
        }
    }

    public function getCurrentUser() {
        if($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
?>
