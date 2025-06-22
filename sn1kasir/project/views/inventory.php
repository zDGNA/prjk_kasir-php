<?php
require_once '../controllers/AuthController.php';
require_once '../controllers/ProductController.php';

$auth = new AuthController();
$auth->requireLogin();

$current_user = $auth->getCurrentUser();
$productController = new ProductController();

// Handle stock adjustment
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'adjust_stock') {
        // This would require a new method in ProductController
        $product_id = $_POST['product_id'];
        $adjustment = $_POST['adjustment'];
        $reason = $_POST['reason'];
        
        // For now, we'll just show a message
        $message = 'Fitur penyesuaian stok akan segera tersedia';
        $messageType = 'info';
    }
}

// Get all products
$products = $productController->index();
$lowStockProducts = $productController->getLowStock();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventori - Sistem Kasir</title>
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

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
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

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
        }

        .stock-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .stock-good {
            background: #28a745;
        }

        .stock-low {
            background: #ffc107;
        }

        .stock-critical {
            background: #dc3545;
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
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="customers.php">Pelanggan</a></li>
                <li><a href="inventory.php" class="active">Inventori</a></li>
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
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>Manajemen Inventori</h1>
            <p>Pantau dan kelola stok produk</p>
        </div>

        <!-- Statistics -->
        <?php
        $totalProducts = count($products);
        $totalValue = array_sum(array_column($products, 'stock')) * array_sum(array_column($products, 'cost_price'));
        $lowStockCount = count($lowStockProducts);
        $activeProducts = count(array_filter($products, function($p) { return $p['status'] == 'active'; }));
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalProducts; ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $activeProducts; ?></div>
                <div class="stat-label">Produk Aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $lowStockCount; ?></div>
                <div class="stat-label">Stok Rendah</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Rp <?php echo number_format($totalValue, 0, ',', '.'); ?></div>
                <div class="stat-label">Nilai Inventori</div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <?php if (!empty($lowStockProducts)): ?>
        <div class="card">
            <div class="card-header">
                <h3>⚠️ Peringatan Stok Rendah</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori'); ?></small>
                                </td>
                                <td>
                                    <span class="stock-indicator stock-<?php echo $product['stock'] == 0 ? 'critical' : 'low'; ?>"></span>
                                    <?php echo $product['stock']; ?> <?php echo htmlspecialchars($product['unit']); ?>
                                </td>
                                <td><?php echo $product['min_stock']; ?> <?php echo htmlspecialchars($product['unit']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $product['stock'] == 0 ? 'danger' : 'warning'; ?>">
                                        <?php echo $product['stock'] == 0 ? 'Habis' : 'Stok Rendah'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                                        Tambah Stok
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Products Inventory -->
        <div class="card">
            <div class="card-header">
                <h3>Daftar Inventori</h3>
            </div>
            <div class="card-body">
                <div class="search-box">
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari produk...">
                </div>
                
                <div class="table-responsive">
                    <table class="table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Nilai Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <?php 
                            $stockStatus = 'good';
                            if ($product['stock'] <= $product['min_stock']) {
                                $stockStatus = $product['stock'] == 0 ? 'critical' : 'low';
                            }
                            $stockValue = $product['stock'] * $product['cost_price'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if ($product['barcode']): ?>
                                    <br><small>Barcode: <?php echo htmlspecialchars($product['barcode']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori'); ?></td>
                                <td>
                                    <span class="stock-indicator stock-<?php echo $stockStatus; ?>"></span>
                                    <?php echo $product['stock']; ?> <?php echo htmlspecialchars($product['unit']); ?>
                                    <?php if ($product['stock'] <= $product['min_stock']): ?>
                                    <br><small class="text-danger">Min: <?php echo $product['min_stock']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?php echo number_format($product['cost_price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($stockValue, 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $product['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
</body>
</html>