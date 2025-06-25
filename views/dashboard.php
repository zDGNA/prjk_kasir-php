<?php
// Include controller dan model yang diperlukan
require_once '../controllers/AuthController.php';
require_once '../controllers/ProductController.php';
require_once '../controllers/TransactionController.php';

// Inisialisasi controller
$auth = new AuthController();
$auth->requireLogin();  // Pastikan user sudah login

// Ambil data user yang sedang login
$current_user = $auth->getCurrentUser();
$productController = new ProductController();
$transactionController = new TransactionController();

// Ambil statistik untuk dashboard
$today = date('Y-m-d');
$dailySales = $transactionController->getDailySalesReport($today);  // Laporan penjualan hari ini
$lowStockProducts = $productController->getLowStock();              // Produk dengan stok rendah
$bestSellingProducts = $transactionController->getBestSellingProducts(5);  // 5 produk terlaris
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kasir</title>
    <style>
    /* Reset CSS untuk konsistensi tampilan */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Styling body dengan tema dark */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #dcdcdc;
        line-height: 1.6;
    }

    /* Navbar dengan gradient background */
    .navbar {
        background: linear-gradient(135deg, #2a2a45 0%, #3a2e5a 100%);
        color: white;
        padding: 1rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    /* Container navbar dengan layout flexbox */
    .navbar-content {
        max-width: 100;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    /* Brand/logo sistem */
    .navbar-brand {
        font-size: 24px;
        font-weight: bold;
    }

    /* Menu navigasi */
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

    /* Efek hover dan active untuk menu */
    .navbar-menu a:hover,
    .navbar-menu a.active {
        background: rgba(255,255,255,0.2);
    }

    /* Informasi user di navbar */
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Container utama halaman */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Header halaman */
    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        color: #ffffff;
        margin-bottom: 10px;
    }

    /* Grid untuk kartu statistik */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    /* Kartu statistik individual */
    .stat-card {
        background: #2d2d40;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        border-left: 4px solid #8a9dfc;
    }

    .stat-card h3 {
        color: #a5a5d4;
        font-size: 14px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .stat-card .value {
        font-size: 28px;
        font-weight: bold;
        color: #ffffff;
    }

    /* Grid untuk konten utama */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Kartu konten */
    .card {
        background: #2d2d40;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        overflow: hidden;
        color: #d4d4d4;
    }

    /* Header kartu */
    .card-header {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
        background: #242438;
    }

    .card-header h3 {
        color: #ffffff;
        margin: 0;
    }

    /* Body kartu */
    .card-body {
        padding: 20px;
    }

    /* Item dalam list */
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #3b3b52;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    /* Badge untuk status */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-danger {
        background: #5c2b37;
        color: #ff6b81;
    }

    /* Styling tombol */
    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a6fd8;
    }

    /* Responsive design untuk tablet dan mobile */
    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .navbar-content {
            flex-direction: column;
            gap: 10px;
        }

        .navbar-menu {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>

</head>
<body>
    <!-- Navbar navigasi -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Sistem Kasir</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php"class="active">Dashboard</a></li>
                <li><a href="pos.php">Pembayaran</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
                <!-- Menu khusus admin -->
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

    <!-- Container utama -->
    <div class="container">
        <!-- Header halaman -->
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Selamat datang di sistem kasir</p>
        </div>

        <!-- Grid statistik utama -->
        <div class="stats-grid">
            <!-- Kartu penjualan hari ini -->
            <div class="stat-card">
                <h3>Penjualan Hari Ini</h3>
                <div class="value">Rp <?php echo number_format($dailySales['total_sales'] ?? 0, 0, ',', '.'); ?></div>
            </div>
            <!-- Kartu jumlah transaksi hari ini -->
            <div class="stat-card">
                <h3>Transaksi Hari Ini</h3>
                <div class="value"><?php echo $dailySales['total_transactions'] ?? 0; ?></div>
            </div>
            <!-- Kartu produk stok rendah -->
            <div class="stat-card">
                <h3>Produk Stok Rendah</h3>
                <div class="value"><?php echo count($lowStockProducts); ?></div>
            </div>
            <!-- Kartu total pajak hari ini -->
            <div class="stat-card">
                <h3>Total Pajak Hari Ini</h3>
                <div class="value">Rp <?php echo number_format($dailySales['total_tax'] ?? 0, 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- Grid konten detail -->
        <div class="content-grid">
            <!-- Kartu produk stok rendah -->
            <div class="card">
                <div class="card-header">
                    <h3>Produk Stok Rendah</h3>
                </div>
                <div class="card-body">
                    <?php if(empty($lowStockProducts)): ?>
                        <p>Tidak ada produk dengan stok rendah.</p>
                    <?php else: ?>
                        <?php foreach($lowStockProducts as $product): ?>
                        <div class="list-item">
                            <div>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                <small>Stok: <?php echo $product['stock']; ?> <?php echo $product['unit']; ?></small>
                            </div>
                            <span class="badge badge-danger">Stok Rendah</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kartu produk terlaris -->
            <div class="card">
                <div class="card-header">
                    <h3>Produk Terlaris</h3>
                </div>
                <div class="card-body">
                    <?php if(empty($bestSellingProducts)): ?>
                        <p>Belum ada data penjualan.</p>
                    <?php else: ?>
                        <?php foreach($bestSellingProducts as $product): ?>
                        <div class="list-item">
                            <div>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                <small>Terjual: <?php echo $product['total_sold']; ?> unit</small>
                            </div>
                            <div class="text-right">
                                <small>Rp <?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>