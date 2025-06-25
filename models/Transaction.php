<?php
require_once '../config/Database.php';

/**
 * Model Transaction untuk mengelola data transaksi penjualan
 * Menangani CRUD transaksi, laporan penjualan, dan statistik
 */
class Transaction {
    private $connection;
    private $table_name = "transactions";

    // Properties yang sesuai dengan kolom database
    public $id;
    public $transaction_code;
    public $user_id;
    public $transaction_date;
    public $subtotal;
    public $tax_amount = 0.00;
    public $discount_amount = 0.00;
    public $total_amount;
    public $payment_method = 'cash';
    public $payment_amount;
    public $change_amount = 0.00;
    public $notes;
    public $status = 'pending';
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
     * Generate kode transaksi unik berdasarkan timestamp dan random number
     * @return string Kode transaksi unik
     */
    private function generateTransactionCode() {
        $date = date('YmdHis');
        $random = rand(100, 999);
        return "TRX{$date}{$random}";
    }

    /**
     * Membuat transaksi baru
     * @return bool True jika berhasil, false jika gagal
     */
    public function create() {
        try {
            // Generate kode transaksi jika belum ada
            if (empty($this->transaction_code)) {
                $this->transaction_code = $this->generateTransactionCode();
            }

            $query = "INSERT INTO " . $this->table_name . " 
                (transaction_code, user_id, transaction_date, subtotal, tax_amount, discount_amount, total_amount, 
                 payment_method, payment_amount, change_amount, notes, status)
                VALUES
                (:transaction_code, :user_id, :transaction_date, :subtotal, :tax_amount, :discount_amount, :total_amount, 
                 :payment_method, :payment_amount, :change_amount, :notes, :status)";

            $stmt = $this->connection->prepare($query);

            // Sanitasi dan konversi data
            $this->transaction_code = htmlspecialchars(strip_tags($this->transaction_code));
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->transaction_date = htmlspecialchars(strip_tags($this->transaction_date));
            $this->subtotal = floatval($this->subtotal);
            $this->tax_amount = floatval($this->tax_amount);
            $this->discount_amount = floatval($this->discount_amount);
            $this->total_amount = floatval($this->total_amount);
            $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
            $this->payment_amount = floatval($this->payment_amount);
            $this->change_amount = floatval($this->change_amount);
            $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;
            $this->status = htmlspecialchars(strip_tags($this->status));

            // Bind parameter untuk prepared statement
            $stmt->bindParam(':transaction_code', $this->transaction_code);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':transaction_date', $this->transaction_date);
            $stmt->bindParam(':subtotal', $this->subtotal);
            $stmt->bindParam(':tax_amount', $this->tax_amount);
            $stmt->bindParam(':discount_amount', $this->discount_amount);
            $stmt->bindParam(':total_amount', $this->total_amount);
            $stmt->bindParam(':payment_method', $this->payment_method);
            $stmt->bindParam(':payment_amount', $this->payment_amount);
            $stmt->bindParam(':change_amount', $this->change_amount);
            $stmt->bindParam(':notes', $this->notes);
            $stmt->bindParam(':status', $this->status);

            if ($stmt->execute()) {
                // Simpan ID transaksi yang baru dibuat
                $this->id = $this->connection->lastInsertId();
                return true;
            }
            
            // Log error untuk debugging
            error_log("Transaction create error: " . print_r($stmt->errorInfo(), true));
            return false;
            
        } catch (Exception $e) {
            error_log("Transaction create exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Membaca semua transaksi dengan informasi user (JOIN)
     * @return PDOStatement Statement yang berisi semua transaksi
     */
    public function read() {
        $query = "SELECT t.*, u.full_name as user_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.user_id = u.id
                  ORDER BY t.created_at DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Membaca data transaksi berdasarkan ID dengan informasi user
     * @return bool True jika transaksi ditemukan, false jika tidak
     */
    public function readOne() {
        $query = "SELECT t.*, u.full_name as user_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.id = :id LIMIT 1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Set properties dengan data dari database
            $this->transaction_code = $row['transaction_code'];
            $this->user_id = $row['user_id'];
            $this->transaction_date = $row['transaction_date'];
            $this->subtotal = $row['subtotal'];
            $this->tax_amount = $row['tax_amount'];
            $this->discount_amount = $row['discount_amount'];
            $this->total_amount = $row['total_amount'];
            $this->payment_method = $row['payment_method'];
            $this->payment_amount = $row['payment_amount'];
            $this->change_amount = $row['change_amount'];
            $this->notes = $row['notes'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Update data transaksi
     * @return bool True jika berhasil, false jika gagal
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET subtotal=:subtotal, tax_amount=:tax_amount, discount_amount=:discount_amount, 
                      total_amount=:total_amount, payment_method=:payment_method, 
                      payment_amount=:payment_amount, change_amount=:change_amount, 
                      notes=:notes, status=:status 
                  WHERE id=:id";

        $stmt = $this->connection->prepare($query);

        // Bind parameter
        $stmt->bindParam(':subtotal', $this->subtotal);
        $stmt->bindParam(':tax_amount', $this->tax_amount);
        $stmt->bindParam(':discount_amount', $this->discount_amount);
        $stmt->bindParam(':total_amount', $this->total_amount);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':payment_amount', $this->payment_amount);
        $stmt->bindParam(':change_amount', $this->change_amount);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Hapus transaksi berdasarkan ID
     * @return bool True jika berhasil, false jika gagal
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    /**
     * Mendapatkan transaksi dalam rentang tanggal tertentu
     * @param string $start_date Tanggal mulai (Y-m-d)
     * @param string $end_date Tanggal akhir (Y-m-d)
     * @return PDOStatement Statement yang berisi transaksi dalam rentang tanggal
     */
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT t.*, u.full_name as user_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE DATE(t.transaction_date) BETWEEN :start_date AND :end_date
                  ORDER BY t.created_at DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Mendapatkan laporan penjualan harian
     * Menghitung total transaksi, penjualan, pajak, dan diskon untuk tanggal tertentu
     * @param string $date Tanggal laporan (Y-m-d)
     * @return array Data statistik penjualan harian
     */
    public function getDailySalesReport($date) {
        $query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(total_amount) as total_sales,
                    SUM(subtotal) as total_subtotal,
                    SUM(tax_amount) as total_tax,
                    SUM(discount_amount) as total_discount
                  FROM " . $this->table_name . " 
                  WHERE DATE(transaction_date) = :date AND status = 'completed'";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan pesan error terakhir dari database
     * @return string Pesan error
     */
    public function getLastError() {
        return $this->connection->errorInfo()[2];
    }
}
?>