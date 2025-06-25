<?php

// Memulai session untuk menyimpan data login user
session_start();
require_once '../models/User.php';

/**
 * Controller untuk mengelola autentikasi dan otorisasi user
 * Menangani login, logout, dan pengecekan hak akses
 */
class AuthController {
    private $user;

    /**
     * Constructor - membuat instance User model
     */
    public function __construct() {
        $this->user = new User();
    }

    /**
     * Proses login user
     * @param string $username Username yang diinput
     * @param string $password Password yang diinput
     * @return array Hasil login (success/error dengan message)
     */
    public function login($username, $password) {
        // Cek kredensial user menggunakan model User
        if($this->user->login($username, $password)) {
            // Jika login berhasil, simpan data user ke session
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

    /**
     * Proses logout user
     * Menghapus semua data session dan redirect ke halaman login
     */
    public function logout() {
        session_start();
        session_destroy();  // Hapus semua data session
        header("Location: ../views/login.php");
        exit();
    }

    /**
     * Cek apakah user sudah login
     * @return bool True jika sudah login, false jika belum
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Memaksa user untuk login jika belum login
     * Redirect ke halaman login jika belum login
     */
    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            header("Location: ../views/login.php");
            exit();
        }
    }

    /**
     * Memaksa user memiliki role tertentu untuk mengakses halaman
     * @param string $role Role yang diperlukan (admin, cashier, manager)
     */
    public function requireRole($role) {
        $this->requireLogin();  // Pastikan user sudah login
        if($_SESSION['role'] !== $role) {
            // Jika role tidak sesuai, redirect ke dashboard
            header("Location: ../views/dashboard.php");
            exit();
        }
    }

    /**
     * Mendapatkan data user yang sedang login
     * @return array|null Data user atau null jika belum login
     */
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

// Handle request logout melalui GET parameter
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}
?>