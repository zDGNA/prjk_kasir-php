<?php
require_once '../models/Transaction.php';
require_once '../models/TransactionDetail.php';
require_once '../config/Database.php';

$database = new Database();
$conn = $database->connect();

try {
    // Mulai transaksi database
    $conn->beginTransaction();

    // Buat objek Transaction dengan koneksi yang sama
    $transaction = new Transaction($conn);
    $transaction->transaction_code = "TRX" . time();
    $transaction->user_id = 1;
    $transaction->transaction_date = date('Y-m-d H:i:s');
    $transaction->subtotal = 10000;
    $transaction->tax_amount = 0.00;
    $transaction->discount_amount = 0.00;
    $transaction->total_amount = 10000;
    $transaction->payment_method = "cash";
    $transaction->payment_amount = 15000;
    $transaction->change_amount = 5000;
    $transaction->notes = "Transaksi test";
    $transaction->status = "completed";

    if (!$transaction->create()) {
        throw new Exception("Gagal menyimpan transaksi utama: " . $transaction->getLastError());
    }

    $transactionId = $transaction->id;
    if (!$transactionId) {
        throw new Exception("Transaction ID tidak tersedia setelah pembuatan.");
    }

    // Buat objek TransactionDetail dengan koneksi yang sama
    $detail = new TransactionDetail($conn);
    $detail->transaction_id = $transactionId;
    $detail->product_id = 1; // pastikan produk ini ada
    $detail->quantity = 2;
    $detail->unit_price = 5000;
    $detail->total_price = 10000;

    if (!$detail->create()) {
        throw new Exception("Gagal menyimpan detail transaksi.");
    }

    // Commit transaksi jika semua berhasil
    $conn->commit();

    echo "Transaksi dan detail berhasil disimpan.";

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
