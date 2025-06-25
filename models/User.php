<?php

require_once '../config/Database.php';

/**
 * Model User untuk mengelola data pengguna sistem
 * Menangani autentikasi, CRUD user, dan manajemen role
 */
class User {
    private $connection;
    private $table_name = "users";

    // Properties yang sesuai dengan kolom database
    public $id;
    public $username;
    public $password;
    public $full_name;
    public $role;
    public $email;
    public $phone;
    public $status;
    public $created_at;
    public $updated_at;

    /**
     * Constructor - membuat koneksi database
     */
    public function __construct() {
        $database = new Database();
        $this->connection = $database->connect();
    }

    /**
     * Membuat user baru
     * @return bool True jika berhasil, false jika gagal
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, full_name=:full_name, 
                      role=:role, email=:email, phone=:phone, status=:status";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input untuk keamanan
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);  // Hash password
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameter untuk prepared statement
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Membaca semua data user
     * @return PDOStatement Statement yang berisi semua user
     */
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Membaca data user berdasarkan ID
     * @return bool True jika user ditemukan, false jika tidak
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set properties dengan data dari database
            $this->username = $row['username'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Update data user (tanpa mengubah password)
     * @return bool True jika berhasil, false jika gagal
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username=:username, full_name=:full_name, role=:role, 
                      email=:email, phone=:phone, status=:status 
                  WHERE id=:id";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameter
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Hapus user berdasarkan ID
     * @return bool True jika berhasil, false jika gagal
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Proses login user
     * Memverifikasi username dan password
     * @param string $username Username yang diinput
     * @param string $password Password yang diinput
     * @return bool True jika login berhasil, false jika gagal
     */
    public function login($username, $password) {
        $query = "SELECT id, username, password, full_name, role, status 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND status = 'active' LIMIT 0,1";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi password menggunakan password_verify untuk keamanan
        if($row && password_verify($password, $row['password'])) {
            // Set properties user yang berhasil login
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
}

?>