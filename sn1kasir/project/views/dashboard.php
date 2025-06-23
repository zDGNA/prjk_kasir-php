
<?php
require_once '../controllers/AuthController.php';
require_once '../controllers/ProductController.php';
require_once '../controllers/TransactionController.php';

$auth = new AuthController();
$auth->requireLogin();

$current_user = $auth->getCurrentUser();
$productController = new ProductController();
$transactionController = new TransactionController();

// Get statistics
$today = date('Y-m-d');
$dailySales = $transactionController->getDailySalesReport($today);
$lowStockProducts = $productController->getLowStock();
$bestSellingProducts = $transactionController->getBestSellingProducts(5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kasir</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', 'Fira Code', monospace, sans-serif;
        background: #1e1e2f;
        color: #d4d4d4;
        line-height: 1.6;
    }

        .navbar {
            background: linear-gradient(135deg, #2a2a45 0%, #3a2e5a 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
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
        color: #d4d4d4;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: background 0.3s;
    }

    .navbar-menu a:hover {
        background: #3a3a5c;
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

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

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .card {
        background: #2d2d40;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        overflow: hidden;
        color: #d4d4d4;
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
        background: #242438;
    }

    .card-header h3 {
        color: #ffffff;
        margin: 0;
    }

    .card-body {
        padding: 20px;
    }

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
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Sistem Kasir</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php"class="active">Dashboard</a></li>
                <li><a href="pos.php">Pembayaran</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
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
            <h1>Dashboard</h1>
            <p>Selamat datang di sistem kasir</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Penjualan Hari Ini</h3>
                <div class="value">Rp <?php echo number_format($dailySales['total_sales'] ?? 0, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card">
                <h3>Transaksi Hari Ini</h3>
                <div class="value"><?php echo $dailySales['total_transactions'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Produk Stok Rendah</h3>
                <div class="value"><?php echo count($lowStockProducts); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Pajak Hari Ini</h3>
                <div class="value">Rp <?php echo number_format($dailySales['total_tax'] ?? 0, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="content-grid">
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