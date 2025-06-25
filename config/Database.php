<?php

/**
 * Kelas Database untuk mengelola koneksi ke database MySQL
 * Menggunakan PDO untuk koneksi yang aman dan fleksibel
 */
class Database {
    // Konfigurasi database
    private $host = 'localhost';        // Host database
    private $db_name = 'kasir_db';      // Nama database
    private $username = 'root';         // Username database
    private $password = '';             // Password database
    private $connection;                // Objek koneksi PDO

    /**
     * Membuat koneksi ke database menggunakan PDO
     * @return PDO|null Objek koneksi PDO atau null jika gagal
     */
    public function connect() {
        $this->connection = null;

        try {
            // Membuat koneksi PDO dengan konfigurasi yang telah ditentukan
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Mengatur mode error PDO untuk menampilkan exception
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            // Menampilkan pesan error jika koneksi gagal
            echo "Connection error: " . $e->getMessage();
        }

        return $this->connection;
    }

    /**
     * Menutup koneksi database
     */
    public function disconnect() {
        $this->connection = null;
    }
}

?>