<?php
require_once '../controllers/AuthController.php';
require_once '../controllers/TransactionController.php';

$auth = new AuthController();
$auth->requireLogin();

$current_user = $auth->getCurrentUser();
$transactionController = new TransactionController();

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Ambil transaksi
if ($start_date && $end_date) {
    $transactions = $transactionController->getByDateRange($start_date, $end_date);
} else {
    $transactions = $transactionController->index();
}

// Detail transaksi untuk AJAX & Print
$transactionDetails = null;
if (isset($_GET['view'])) {
    $transactionDetails = $transactionController->show($_GET['view']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Sistem Kasir</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #dcdcdc;
        line-height: 1.6;
    }

    .navbar {
        background: linear-gradient(135deg, #2a2a45 0%, #3a2e5a 100%);
        color: white;
        padding: 1rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .navbar-content {
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    .navbar-brand {
        font-size: 24px;
        font-weight: bold;
    }

    .navbar-menu {
        display: flex;
        list-style: none;
        gap: 20px;
    }

    .navbar-menu a {
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: background 0.3s;
    }

    .navbar-menu a:hover,
    .navbar-menu a.active {
        background: rgba(255,255,255,0.2);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        color: #ffffff;
        margin-bottom: 10px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a6fd8;
    }

    .btn-info {
        background: #17a2b8;
        color: white;
    }

    .btn-info:hover {
        background: #138496;
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .card,
    .stat-card {
        background: #2d2d40;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.4);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .card-header {
        padding: 20px;
        background: #252536;
        border-bottom: 1px solid #3a3a4f;
    }

    .card-header h3 {
        margin: 0;
        color: #ffffff;
    }

    .card-body {
        padding: 20px;
    }

    .filter-section {
        display: flex;
        gap: 15px;
        align-items: end;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #dcdcdc;
    }

    .form-control {
        padding: 8px 12px;
        border: 1px solid #444;
        border-radius: 5px;
        font-size: 14px;
        background: #1e1e2f;
        color: #eee;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        color: #dcdcdc;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #3a3a4f;
    }

    .table th {
        background: #2a2a3d;
        font-weight: 600;
        color: #fff;
    }

    .table tbody tr:hover {
        background: #32324a;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-success {
        background: #145c2a;
        color: #aef3c2;
    }

    .badge-warning {
        background: #4b3f00;
        color: #ffd666;
    }

    .badge-danger {
        background: #5a1e22;
        color: #f5aeb0;
    }

    .badge-info {
        background: #1b4f5f;
        color: #91eaf4;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
    }

    .modal-content {
        background: #2a2a3d;
        margin: 2% auto;
        padding: 0;
        border-radius: 10px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        color: #fff;
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h4 {
        margin: 0;
    }

    .close {
        font-size: 24px;
        cursor: pointer;
        color: #aaa;
    }

    .close:hover {
        color: #fff;
    }

    .modal-body {
        padding: 20px;
    }

    .transaction-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        padding: 15px;
        background: #252536;
        border-radius: 8px;
    }

    .info-label {
        font-weight: 600;
        color: #9da1b1;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 16px;
        color: #ffffff;
    }

    .summary-section {
        background: #252536;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .summary-row.total {
        font-weight: bold;
        font-size: 18px;
        border-top: 1px solid #3a3a4f;
        padding-top: 10px;
        margin-top: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #86b7fe;
        margin-bottom: 5px;
        padding-left: 8px;
    }

    .stat-label {
        color: #a8a8c4;
        font-size: 14px;
        padding-left: 8px;
    }

    /* Styling khusus untuk struk yang akan di-print */
    .receipt-content {
        display: none; /* Sembunyikan di layar normal */
    }

    /* CSS khusus untuk print - hanya tampilkan struk */
    @media print {
        /* Sembunyikan semua elemen halaman */
        body * {
            visibility: hidden;
        }

        /* Tampilkan hanya konten struk */
        .receipt-content,
        .receipt-content * {
            visibility: visible;
        }

        /* Posisikan struk di awal halaman */
        .receipt-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            display: block !important;
        }

        /* Reset styling untuk print */
        body {
            background: white !important;
            color: black !important;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }

        .receipt-content {
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-header h2 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .receipt-info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .receipt-info div {
            margin-bottom: 2px;
        }

        .receipt-items {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .receipt-summary {
            font-size: 11px;
        }

        .receipt-summary div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .receipt-total {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
    }

    @media (max-width: 768px) {
        .navbar-content {
            flex-direction: column;
            gap: 10px;
        }

        .navbar-menu {
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }

        .table-responsive {
            font-size: 12px;
        }

        .transaction-info {
            grid-template-columns: 1fr;
        }
    }
</style>

</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Sistem Kasir</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pos.php">Pembayaran</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="transactions.php" class="active">Transaksi</a></li>
                <?php if($current_user['role'] == 'admin'): ?>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Laporan</a></li>
                <?php endif; ?>
            </ul>
            <div class="user-info">
                <span>Halo, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                <a href="../controllers/AuthController.php?action=logout" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Riwayat Transaksi</h1>
            <p>Kelola dan pantau semua transaksi penjualan</p>
        </div>

        <!-- Statistics -->
        <?php
        $totalTransactions = count($transactions);
        $totalAmount = array_sum(array_column($transactions, 'total_amount'));
        $avgTransaction = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;
        $completedTransactions = count(array_filter($transactions, function($t) { return $t['status'] == 'completed'; }));
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalTransactions; ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($totalAmount, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Penjualan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($avgTransaction, 0, ',', '.'); ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completedTransactions; ?></div>
                <div class="stat-label">Transaksi Selesai</div>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="card">
            <div class="card-header">
                <h3>Daftar Transaksi</h3>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <form method="GET" class="filter-section">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #6c757d;">
                                    Tidak ada transaksi ditemukan
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($transaction['transaction_code']); ?></strong>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($transaction['transaction_date'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['user_name'] ?? 'N/A'); ?></td>
                                <td>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo ucfirst($transaction['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $transaction['status'] == 'completed' ? 'success' : 
                                            ($transaction['status'] == 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="viewTransaction(<?php echo $transaction['id']; ?>)">
                                        Detail
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="printReceipt(<?php echo $transaction['id']; ?>)">
                                        Print
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Detail Transaksi</h4>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Transaction details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Konten struk untuk print (tersembunyi di layar normal) -->
    <?php if ($transactionDetails): ?>
    <div class="receipt-content" id="receiptContent">
        <div class="receipt-header">
            <h2>TOKO SERBAGUNA</h2>
            <div>Jl. Contoh No. 123</div>
            <div>Telp: 021-12345678</div>
        </div>
        
        <div class="receipt-info">
            <div>No: <?php echo htmlspecialchars($transactionDetails['transaction_code']); ?></div>
            <div>Tanggal: <?php echo date('d/m/Y H:i', strtotime($transactionDetails['transaction_date'])); ?></div>
            <div>Kasir: <?php echo htmlspecialchars($transactionDetails['user_name'] ?? 'N/A'); ?></div>
        </div>

        <div class="receipt-items">
            <?php foreach ($transactionDetails['details'] as $detail): ?>
            <div class="receipt-item">
                <div>
                    <div><?php echo htmlspecialchars($detail['product_name']); ?></div>
                    <div><?php echo $detail['quantity']; ?> x Rp <?php echo number_format($detail['unit_price'], 0, ',', '.'); ?></div>
                </div>
                <div>Rp <?php echo number_format($detail['total_price'], 0, ',', '.'); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="receipt-summary">
            <div>
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($transactionDetails['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <div>
                <span>Pajak:</span>
                <span>Rp <?php echo number_format($transactionDetails['tax_amount'], 0, ',', '.'); ?></span>
            </div>
            <?php if ($transactionDetails['discount_amount'] > 0): ?>
            <div>
                <span>Diskon:</span>
                <span>Rp <?php echo number_format($transactionDetails['discount_amount'], 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-total">
                <span>TOTAL:</span>
                <span>Rp <?php echo number_format($transactionDetails['total_amount'], 0, ',', '.'); ?></span>
            </div>
            <div>
                <span>Bayar:</span>
                <span>Rp <?php echo number_format($transactionDetails['payment_amount'], 0, ',', '.'); ?></span>
            </div>
            <div>
                <span>Kembali:</span>
                <span>Rp <?php echo number_format($transactionDetails['change_amount'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="receipt-footer">
            <div>*** TERIMA KASIH ***</div>
            <div>Barang yang sudah dibeli</div>
            <div>tidak dapat dikembalikan</div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Fungsi untuk melihat detail transaksi dalam modal
        function viewTransaction(transactionId) {
            // Tampilkan modal
            document.getElementById('transactionModal').style.display = 'block';
            
            // Load detail transaksi via AJAX
            fetch(`transactions.php?view=${transactionId}`)
                .then(response => response.text())
                .then(html => {
                    // Parse HTML response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const details = doc.querySelector('#transactionDetailsContent');
                    
                    if (details) {
                        document.getElementById('transactionDetails').innerHTML = details.innerHTML;
                    } else {
                        // Fallback: redirect ke halaman view
                        window.location.href = `transactions.php?view=${transactionId}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback: redirect ke halaman view
                    window.location.href = `transactions.php?view=${transactionId}`;
                });
        }

        // Fungsi untuk print struk
        function printReceipt(transactionId) {
            // Redirect ke halaman dengan parameter view untuk load data transaksi
            window.location.href = `transactions.php?view=${transactionId}&print=true`;
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('transactionModal').style.display = 'none';
        }

        // Tutup modal ketika klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('transactionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Auto print jika ada parameter print=true
        <?php if (isset($_GET['print']) && $_GET['print'] === 'true' && $transactionDetails): ?>
        window.onload = function() {
            // Delay sedikit untuk memastikan konten sudah ter-load
            setTimeout(function() {
                window.print();
            }, 500);
        }
        <?php endif; ?>
    </script>

    <!-- Hidden transaction details content untuk AJAX loading -->
    <?php if ($transactionDetails): ?>
    <div id="transactionDetailsContent" style="display: none;">
        <div class="transaction-info">
            <div class="info-item">
                <div class="info-label">Kode Transaksi</div>
                <div class="info-value"><?php echo htmlspecialchars($transactionDetails['transaction_code']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal</div>
                <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($transactionDetails['transaction_date'])); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Kasir</div>
                <div class="info-value"><?php echo htmlspecialchars($transactionDetails['user_name'] ?? 'N/A'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Metode Pembayaran</div>
                <div class="info-value"><?php echo ucfirst($transactionDetails['payment_method']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="badge badge-<?php 
                        echo $transactionDetails['status'] == 'completed' ? 'success' : 
                            ($transactionDetails['status'] == 'pending' ? 'warning' : 'danger'); 
                    ?>">
                        <?php echo ucfirst($transactionDetails['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <h5>Item Transaksi</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactionDetails['details'] as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                        <td>Rp <?php echo number_format($detail['unit_price'], 0, ',', '.'); ?></td>
                        <td><?php echo $detail['quantity']; ?> <?php echo htmlspecialchars($detail['unit'] ?? 'pcs'); ?></td>
                        <td>Rp <?php echo number_format($detail['total_price'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp <?php echo number_format($transactionDetails['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Pajak:</span>
                <span>Rp <?php echo number_format($transactionDetails['tax_amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Diskon:</span>
                <span>Rp <?php echo number_format($transactionDetails['discount_amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>Rp <?php echo number_format($transactionDetails['total_amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Dibayar:</span>
                <span>Rp <?php echo number_format($transactionDetails['payment_amount'], 0, ',', '.'); ?></span>
            </div>
            <div class="summary-row">
                <span>Kembalian:</span>
                <span>Rp <?php echo number_format($transactionDetails['change_amount'], 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>