<?php

require_once '../config/Database.php';

/**
 * Model Product untuk mengelola data produk
 * Menangani CRUD produk, pencarian, manajemen stok, dan filter produk
 */
class Product {
    private $connection;
    private $table_name = "products";

    // Properties yang sesuai dengan kolom database
    public $id;
    public $category_id;
    public $name;
    public $description;
    public $barcode;
    public $price;
    public $cost_price;
    public $stock;
    public $min_stock;
    public $unit;
    public $image;
    public $status;
    public $created_at;
    public $updated_at;

    /**
     * Constructor - membuat koneksi database
     * @param PDO|null $db Koneksi database opsional (untuk sharing koneksi)
     */
    public function __construct($db = null) {
        if ($db) {
            // Gunakan koneksi yang sudah ada (untuk transaksi database)
            $this->connection = $db;
        } else {
            // Buat koneksi baru
            $database = new Database();
            $this->connection = $database->connect();
        }
    }

    /**
     * Membuat produk baru
     * @return bool True jika berhasil, false jika gagal
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, description=:description, 
                      barcode=:barcode, price=:price, cost_price=:cost_price, 
                      stock=:stock, min_stock=:min_stock, unit=:unit, 
                      image=:image, status=:status";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input untuk keamanan
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->barcode = htmlspecialchars(strip_tags($this->barcode));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->cost_price = htmlspecialchars(strip_tags($this->cost_price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->min_stock = htmlspecialchars(strip_tags($this->min_stock));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameter untuk prepared statement
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":barcode", $this->barcode);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":cost_price", $this->cost_price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":min_stock", $this->min_stock);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Membaca semua produk dengan informasi kategori (JOIN)
     * @return PDOStatement Statement yang berisi semua produk
     */
    public function read() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Membaca data produk berdasarkan ID dengan informasi kategori
     * @return bool True jika produk ditemukan, false jika tidak
     */
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set properties dengan data dari database
            $this->category_id = $row['category_id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->barcode = $row['barcode'];
            $this->price = $row['price'];
            $this->cost_price = $row['cost_price'];
            $this->stock = $row['stock'];
            $this->min_stock = $row['min_stock'];
            $this->unit = $row['unit'];
            $this->image = $row['image'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Update data produk
     * @return bool True jika berhasil, false jika gagal
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, description=:description, 
                      barcode=:barcode, price=:price, cost_price=:cost_price, 
                      stock=:stock, min_stock=:min_stock, unit=:unit, 
                      image=:image, status=:status 
                  WHERE id=:id";

        $stmt = $this->connection->prepare($query);

        // Sanitasi input
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->barcode = htmlspecialchars(strip_tags($this->barcode));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->cost_price = htmlspecialchars(strip_tags($this->cost_price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->min_stock = htmlspecialchars(strip_tags($this->min_stock));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameter
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':barcode', $this->barcode);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':cost_price', $this->cost_price);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':min_stock', $this->min_stock);
        $stmt->bindParam(':unit', $this->unit);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Hapus produk berdasarkan ID
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
     * Mencari produk berdasarkan nama atau barcode
     * Digunakan untuk fitur pencarian di POS dan manajemen produk
     * @param string $keyword Kata kunci pencarian
     * @return PDOStatement Statement yang berisi hasil pencarian
     */
    public function search($keyword) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE (p.name LIKE :keyword OR p.barcode LIKE :keyword) 
                  AND p.status = 'active'
                  ORDER BY p.name ASC";
        
        $stmt = $this->connection->prepare($query);
        $keyword = htmlspecialchars(strip_tags($keyword));
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Update stok produk (mengurangi stok setelah penjualan)
     * Digunakan saat transaksi penjualan untuk mengurangi stok
     * @param int $product_id ID produk yang akan diupdate stoknya
     * @param int $quantity Jumlah yang akan dikurangi dari stok
     * @return bool True jika berhasil, false jika gagal
     */
    public function updateStock($product_id, $quantity) {
        $query = "UPDATE " . $this->table_name . " 
                SET stock = stock - :quantity 
                WHERE id = :id";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $product_id);

        return $stmt->execute();
    }

    /**
     * Mendapatkan produk dengan stok rendah (di bawah minimum)
     * Digunakan untuk peringatan stok dan dashboard
     * @return PDOStatement Statement yang berisi produk dengan stok rendah
     */
    public function getLowStockProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.stock <= p.min_stock AND p.status = 'active'
                  ORDER BY p.stock ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Mendapatkan produk berdasarkan kategori
     * @param int $category_id ID kategori
     * @return PDOStatement Statement yang berisi produk dalam kategori
     */
    public function getByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id AND p.status = 'active'
                  ORDER BY p.name ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Mendapatkan produk yang statusnya aktif saja
     * @return PDOStatement Statement yang berisi produk aktif
     */
    public function getActiveProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.status = 'active'
                  ORDER BY p.name ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

?>