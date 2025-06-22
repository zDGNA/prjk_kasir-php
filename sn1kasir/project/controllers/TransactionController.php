<?php

require_once '../models/Transaction.php';
require_once '../models/TransactionDetail.php';
require_once '../models/Product.php';
require_once '../controllers/AuthController.php';
require_once '../config/Database.php';

class TransactionController {
    private $transaction;
    private $transactionDetail;
    private $product;
    private $auth;
    private $conn;

    public function __construct() {
        $this->transaction = new Transaction();
        $this->transactionDetail = new TransactionDetail();
        $this->product = new Product();
        $this->auth = new AuthController();
        $this->auth->requireLogin();

        $database = new Database();
        $this->conn = $database->connect();
    }

    public function beginTransaction() {
        if ($this->conn) {
            $this->conn->beginTransaction();
        }
    }

    public function commit() {
        if ($this->conn) {
            $this->conn->commit();
        }
    }

    public function rollback() {
        if ($this->conn) {
            $this->conn->rollBack();
        }
    }

    public function index() {
        $stmt = $this->transaction->read();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $transactions;
    }

    public function show($id) {
        $this->transaction->id = $id;
        if($this->transaction->readOne()) {
            $transaction_data = [
                'id' => $this->transaction->id,
                'transaction_code' => $this->transaction->transaction_code,
                // 'customer_id' => $this->transaction->customer_id,
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

            $stmt = $this->transactionDetail->readByTransaction($id);
            $transaction_data['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $transaction_data;
            
        }
        return null;

        
    }
    
public function store($data) {
    try {
        $this->beginTransaction();

        // Set data transaksi
        $this->transaction->user_id = $_SESSION['user_id'];
        $this->transaction->transaction_code = $data['transaction_code'];
        $this->transaction->transaction_date = date('Y-m-d H:i:s');
        $this->transaction->subtotal = $data['subtotal'];
        $this->transaction->tax_amount = $data['tax_amount']  ?? 0.00;;
        $this->transaction->discount_amount = isset($data['discount_amount']) && $data['discount_amount'] !== '' ? $data['discount_amount'] : 0.00;
        $this->transaction->total_amount = $data['total_amount'];
        $this->transaction->payment_method = $data['payment_method'];
        $this->transaction->payment_amount = $data['payment_amount'];
        $this->transaction->change_amount = $data['change_amount']  ?? 0.00;;
        $this->transaction->notes = $data['notes'] ?? null;
        $this->transaction->status = 'completed';
        

        $this->transaction->create();
        $transaction_id = $this->conn->lastInsertId();

        foreach ($data['items'] as $item) {
            $this->transactionDetail->transaction_id = $transaction_id;
            $this->transactionDetail->product_id = $item['product_id'];
            $this->transactionDetail->quantity = $item['quantity'];
            $this->transactionDetail->unit_price = $item['unit_price'];
            $this->transactionDetail->total_price = $item['total_price'];
            $this->transactionDetail->create();

            $this->product->updateStock($item['product_id'], -$item['quantity']);
        }

        $this->commit();

        return [
            'success' => true,
            'message' => 'Transaksi berhasil disimpan.'
        ];
    } catch (Exception $e) {
        $this->rollback();
        return [
            'success' => false,
            'message' => 'Error: Transaction failed: ' . $e->getMessage()
        ];
    }
}


    public function getDailySalesReport($date) {
        return $this->transaction->getDailySalesReport($date);
    }

    public function getByDateRange($start_date, $end_date) {
        $stmt = $this->transaction->getByDateRange($start_date, $end_date);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBestSellingProducts($limit = 10) {
        $stmt = $this->transactionDetail->getBestSellingProducts($limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
