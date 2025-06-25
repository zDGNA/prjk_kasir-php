<?php

require_once '../config/Database.php';

/**
 * Model Category untuk mengelola data kategori produk
 * Menangani CRUD kategori dan filter kategori aktif
 */
class Category {
    private $connection;
    private $table_name = "categories";

    // Properties yang sesuai dengan kolom database
    public $id;
    public $name;
    public $description;
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
     * Membuat kategori baru
     * @return bool True jika berhasil, false jika gagal
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, status=:status";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input untuk keamanan
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameter untuk prepared statement
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Membaca semua kategori diurutkan berdasarkan nama
     * @return PDOStatement Statement yang berisi semua kategori
     */
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Membaca data kategori berdasarkan ID
     * @return bool True jika kategori ditemukan, false jika tidak
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set properties dengan data dari database
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Update data kategori
     * @return bool True jika berhasil, false jika gagal
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, status=:status 
                  WHERE id=:id";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameter
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Hapus kategori berdasarkan ID
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
     * Mendapatkan kategori yang statusnya aktif saja
     * Digunakan untuk dropdown dan filter
     * @return PDOStatement Statement yang berisi kategori aktif
     */
    public function getActiveCategories() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

?>