<?php

require_once '../models/Transaction.php';
require_once '../models/TransactionDetail.php';
require_once '../models/Product.php';
require_once '../controllers/AuthController.php';
require_once '../config/Database.php';

/**
 * Controller untuk mengelola transaksi penjualan
 * Menangani pembuatan transaksi, detail transaksi, dan laporan
 */
class TransactionController {
    private $transaction;
    private $transactionDetail;
    private $product;
    private $auth;
    private $conn;

    /**
     * Constructor - inisialisasi semua model dan koneksi database
     */
    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();  // Pastikan user sudah login

        // Buat koneksi database yang akan dibagi ke semua model
        $database = new Database();
        $this->conn = $database->connect();
        
        // Inisialisasi model dengan koneksi yang sama untuk transaksi database
        $this->transaction = new Transaction($this->conn);
        $this->transactionDetail = new TransactionDetail($this->conn);
        $this->product = new Product($this->conn);
    }

    /**
     * Memulai transaksi database
     */
    public function beginTransaction() {
        if ($this->conn) {
            $this->conn->beginTransaction();
        }
    }

    /**
     * Commit transaksi database
     */
    public function commit() {
        if ($this->conn) {
            $this->conn->commit();
        }
    }

    /**
     * Rollback transaksi database jika terjadi error
     */
    public function rollback() {
        if ($this->conn) {
            $this->conn->rollBack();
        }
    }

    /**
     * Mendapatkan semua transaksi
     * @return array Daftar semua transaksi
     */
    public function index() {
        $stmt = $this->transaction->read();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $transactions;
    }

    /**
     * Mendapatkan detail transaksi beserta item-itemnya
     * @param int $id ID transaksi
     * @return array|null Data transaksi lengkap atau null jika tidak ditemukan
     */
    public function show($id) {
        $this->transaction->id = $id;
        if($this->transaction->readOne()) {
            // Ambil data transaksi utama
            $transaction_data = [
                'id' => $this->transaction->id,
                'transaction_code' => $this->transaction->transaction_code,
                'user_id' => $this->transaction->user_id,
                'transaction_date' => $this->transaction->transaction_date,
                'subtotal' => $this->transaction->subtotal,
                'tax_amount' => $this->transaction->tax_amount,
                'discount_amount' => $this->transaction->discount_amount,
                'total_amount' => $this->transaction->total_amount,
                'payment_method' => $this->transaction->payment_method,
                'payment_amount' => $this->transaction->payment_amount,
                'change_amount' => $this->transaction->change_amount,
                'notes' => $this->transaction->notes,
                'status' => $this->transaction->status,
                'created_at' => $this->transaction->created_at,
                'updated_at' => $this->transaction->updated_at
            ];

            // Ambil detail item transaksi
            $stmt = $this->transactionDetail->readByTransaction($id);
            $transaction_data['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $transaction_data;
        }
        return null;
    }
    
    /**
     * Menyimpan transaksi baru beserta detail itemnya
     * Menggunakan database transaction untuk memastikan konsistensi data
     * @param array $data Data transaksi dan item-itemnya
     * @return array Hasil operasi (success/error dengan message)
     */
    public function store($data) {
        try {
            error_log("=== [STORE] Mulai transaksi ===");

            // Validasi data input
            if (!isset($data['items']) || empty($data['items'])) {
                throw new Exception("No items in transaction");
            }

            if (!isset($data['subtotal']) || !isset($data['total_amount'])) {
                throw new Exception("Missing required transaction amounts");
            }

            // Mulai transaksi database
            $this->beginTransaction();
            error_log("=== [STORE] Begin transaction");

            // Generate kode transaksi unik
            $transaction_code = "TRX" . date('YmdHis') . rand(100, 999);

            // Set data transaksi utama
            $this->transaction->user_id = $_SESSION['user_id'] ?? 0;
            $this->transaction->transaction_code = $transaction_code;
            $this->transaction->transaction_date = date('Y-m-d H:i:s');
            $this->transaction->subtotal = floatval($data['subtotal']);
            $this->transaction->tax_amount = isset($data['tax_amount']) ? floatval($data['tax_amount']) : 0.00;
            $this->transaction->discount_amount = isset($data['discount_amount']) ? floatval($data['discount_amount']) : 0.00;
            $this->transaction->total_amount = floatval($data['total_amount']);
            $this->transaction->payment_method = $data['payment_method'] ?? 'cash';
            $this->transaction->payment_amount = floatval($data['payment_amount']);
            $this->transaction->change_amount = isset($data['change_amount']) ? floatval($data['change_amount']) : 0.00;
            $this->transaction->notes = $data['notes'] ?? null;
            $this->transaction->status = 'completed';

            error_log("=== [STORE] Simpan transaksi utama");

            // Simpan transaksi utama
            if (!$this->transaction->create()) {
                throw new Exception("Failed to create transaction: " . $this->transaction->getLastError());
            }

            $transaction_id = $this->transaction->id;
            error_log("=== [STORE] Transaction ID: $transaction_id");

            if (!$transaction_id) {
                throw new Exception("Transaction ID not available after creation");
            }

            // Simpan setiap item transaksi dan update stok produk
            foreach ($data['items'] as $item) {
                error_log("=== [STORE] Simpan detail: " . json_encode($item));

                // Simpan detail transaksi
                $this->transactionDetail->transaction_id = $transaction_id;
                $this->transactionDetail->product_id = intval($item['product_id']);
                $this->transactionDetail->quantity = intval($item['quantity']);
                $this->transactionDetail->unit_price = floatval($item['unit_price']);
                $this->transactionDetail->total_price = floatval($item['total_price']);

                if (!$this->transactionDetail->create()) {
                    throw new Exception("Failed to create transaction detail for product ID: " . $item['product_id']);
                }

                // Update stok produk (kurangi stok sesuai quantity yang terjual)
                if (!$this->product->updateStock($item['product_id'], $item['quantity'])) {
                    throw new Exception("Failed to update stock for product ID: " . $item['product_id']);
                }
            }

            // Commit transaksi jika semua berhasil
            $this->commit();
            error_log("=== [STORE] Commit sukses");

            return [
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'transaction_code' => $transaction_code,
                'transaction_id' => $transaction_id
            ];
        } catch (Exception $e) {
            // Rollback jika terjadi error
            $this->rollback();
            error_log("Transaction error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: Transaction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mendapatkan laporan penjualan harian
     * @param string $date Tanggal dalam format Y-m-d
     * @return array Data laporan penjualan harian
     */
    public function getDailySalesReport($date) {
        return $this->transaction->getDailySalesReport($date);
    }

    /**
     * Mendapatkan transaksi dalam rentang tanggal tertentu
     * @param string $start_date Tanggal mulai
     * @param string $end_date Tanggal akhir
     * @return array Daftar transaksi dalam rentang tanggal
     */
    public function getByDateRange($start_date, $end_date) {
        $stmt = $this->transaction->getByDateRange($start_date, $end_date);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan produk terlaris berdasarkan jumlah penjualan
     * @param int $limit Jumlah produk yang akan ditampilkan
     * @return array Daftar produk terlaris
     */
    public function getBestSellingProducts($limit = 10) {
        $stmt = $this->transactionDetail->getBestSellingProducts($limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>