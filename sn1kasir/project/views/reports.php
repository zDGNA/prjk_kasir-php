<?php
require_once '../controllers/AuthController.php';
require_once '../controllers/TransactionController.php';
require_once '../controllers/ProductController.php';

$auth = new AuthController();
$auth->requireRole('admin'); // Only admin can access reports

$current_user = $auth->getCurrentUser();
$transactionController = new TransactionController();
$productController = new ProductController();

// Get date range from query parameters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get report data
$transactions = $transactionController->getByDateRange($start_date, $end_date);
$bestSellingProducts = $transactionController->getBestSellingProducts(10);
$lowStockProducts = $productController->getLowStock();

// Calculate statistics
$totalTransactions = count($transactions);
$totalRevenue = array_sum(array_column($transactions, 'total_amount'));
$totalTax = array_sum(array_column($transactions, 'tax_amount'));
$totalDiscount = array_sum(array_column($transactions, 'discount_amount'));
$avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

// Group transactions by date for chart
$dailySales = [];
foreach ($transactions as $transaction) {
    $date = date('Y-m-d', strtotime($transaction['transaction_date']));
    if (!isset($dailySales[$date])) {
        $dailySales[$date] = 0;
    }
    $dailySales[$date] += $transaction['total_amount'];
}

// Payment method statistics
$paymentMethods = [];
foreach ($transactions as $transaction) {
    $method = $transaction['payment_method'];
    if (!isset($paymentMethods[$method])) {
        $paymentMethods[$method] = 0;
    }
    $paymentMethods[$method]++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Kasir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-content {
            max-width: 1200px;
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

        .navbar-menu a:hover, .navbar-menu a.active {
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
            color: #333;
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }

        .filter-section {
            display: flex;
            gap: 15px;
            align-items: end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
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
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            border: 2px dashed #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            border-radius: 8px;
        }

        .report-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .payment-method-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .method-name {
            font-weight: 500;
            text-transform: capitalize;
        }

        .method-count {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        @media print {
            .navbar, .filter-section, .btn {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
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
            
            .report-grid {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                font-size: 12px;
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
                <li><a href="pos.php">Point of Sale</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php" class="active">Laporan</a></li>
            </ul>
            <div class="user-info">
                <span>Halo, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                <a href="../controllers/AuthController.php?action=logout" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Laporan Penjualan</h1>
            <p>Analisis dan statistik penjualan toko</p>
        </div>

        <!-- Filter Section -->
        <div class="card">
            <div class="card-header">
                <h3>Filter Laporan</h3>
            </div>
            <div class="card-body">
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
                        <button type="submit" class="btn btn-primary">Generate Laporan</button>
                        <button type="button" class="btn btn-success" onclick="window.print()">Print Laporan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalTransactions; ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($avgTransaction, 0, ',', '.'); ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($totalTax, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pajak</div>
            </div>
        </div>

        <div class="report-grid">
            <!-- Sales Chart -->
            <div class="card">
                <div class="card-header">
                    <h3>Grafik Penjualan Harian</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            📊 Grafik Penjualan Harian<br>
                            <small>Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></small>
                        </div>
                    </div>
                    
                    <!-- Simple text-based chart data -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dailySales as $date => $amount): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($date)); ?></td>
                                    <td>Rp <?php echo number_format($amount, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card">
                <div class="card-header">
                    <h3>Metode Pembayaran</h3>
                </div>
                <div class="card-body">
                    <div class="payment-methods">
                        <?php foreach ($paymentMethods as $method => $count): ?>
                        <div class="payment-method-item">
                            <span class="method-name"><?php echo ucfirst($method); ?></span>
                            <span class="method-count"><?php echo $count; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Selling Products -->
        <div class="card">
            <div class="card-header">
                <h3>Produk Terlaris</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bestSellingProducts)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                    Tidak ada data produk terlaris
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($bestSellingProducts as $index => $product): ?>
                            <tr>
                                <td><strong>#<?php echo $index + 1; ?></strong></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $product['total_sold']; ?> unit</td>
                                <td>Rp <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card">
            <div class="card-header">
                <h3>Peringatan Stok Rendah</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lowStockProducts)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                    Semua produk memiliki stok yang cukup
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $product['stock']; ?> <?php echo htmlspecialchars($product['unit']); ?></td>
                                <td><?php echo $product['min_stock']; ?> <?php echo htmlspecialchars($product['unit']); ?></td>
                                <td>
                                    <span class="badge badge-danger">Stok Rendah</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header">
                <h3>Ringkasan Laporan</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h5>Periode Laporan</h5>
                        <p><?php echo date('d F Y', strtotime($start_date)); ?> - <?php echo date('d F Y', strtotime($end_date)); ?></p>
                        
                        <h5 style="margin-top: 20px;">Total Transaksi</h5>
                        <p><?php echo $totalTransactions; ?> transaksi</p>
                        
                        <h5 style="margin-top: 20px;">Pendapatan Kotor</h5>
                        <p>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
                    </div>
                    
                    <div>
                        <h5>Pajak Terkumpul</h5>
                        <p>Rp <?php echo number_format($totalTax, 0, ',', '.'); ?></p>
                        
                        <h5 style="margin-top: 20px;">Total Diskon</h5>
                        <p>Rp <?php echo number_format($totalDiscount, 0, ',', '.'); ?></p>
                        
                        <h5 style="margin-top: 20px;">Rata-rata per Transaksi</h5>
                        <p>Rp <?php echo number_format($avgTransaction, 0, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <p><strong>Laporan dibuat pada:</strong> <?php echo date('d F Y H:i:s'); ?></p>
                    <p><strong>Dibuat oleh:</strong> <?php echo htmlspecialchars($current_user['full_name']); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>