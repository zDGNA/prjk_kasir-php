<?php
require_once '../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('admin'); // Only admin can access settings

$current_user = $auth->getCurrentUser();

// Handle settings update
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This would typically save to a settings table or config file
    $message = 'Pengaturan berhasil disimpan';
    $messageType = 'success';
}

// Default settings (would typically come from database)
$settings = [
    'store_name' => 'Toko ABC',
    'store_address' => 'Jl. Contoh No. 123, Jakarta',
    'store_phone' => '021-12345678',
    'store_email' => 'info@tokoabc.com',
    'tax_rate' => 10,
    'currency' => 'IDR',
    'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
    'low_stock_threshold' => 10
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Sistem Kasir</title>
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
            max-width: 800px;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .settings-section {
            margin-bottom: 30px;
        }

        .settings-section h4 {
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
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
            
            .form-grid {
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
                <li><a href="pos.php">Point of Sale</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="customers.php">Pelanggan</a></li>
                <li><a href="inventory.php">Inventori</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Laporan</a></li>
                <li><a href="settings.php" class="active">Pengaturan</a></li>
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
            <h1>Pengaturan Sistem</h1>
            <p>Konfigurasi pengaturan toko dan sistem</p>
        </div>

        <form method="POST">
            <!-- Store Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Informasi Toko</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Toko</label>
                            <input type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($settings['store_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="store_phone" class="form-control" value="<?php echo htmlspecialchars($settings['store_phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="store_email" class="form-control" value="<?php echo htmlspecialchars($settings['store_email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Toko</label>
                        <textarea name="store_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['store_address']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Pengaturan Sistem</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tarif Pajak (%)</label>
                            <input type="number" name="tax_rate" class="form-control" value="<?php echo $settings['tax_rate']; ?>" min="0" max="100" step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Mata Uang</label>
                            <select name="currency" class="form-control">
                                <option value="IDR" <?php echo $settings['currency'] == 'IDR' ? 'selected' : ''; ?>>Rupiah (IDR)</option>
                                <option value="USD" <?php echo $settings['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                <option value="EUR" <?php echo $settings['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batas Stok Rendah</label>
                            <input type="number" name="low_stock_threshold" class="form-control" value="<?php echo $settings['low_stock_threshold']; ?>" min="1">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Pengaturan Struk</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Footer Struk</label>
                        <textarea name="receipt_footer" class="form-control" rows="3" placeholder="Pesan yang akan ditampilkan di bagian bawah struk"><?php echo htmlspecialchars($settings['receipt_footer']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Backup & Security -->
            <div class="card">
                <div class="card-header">
                    <h3>Backup & Keamanan</h3>
                </div>
                <div class="card-body">
                    <div class="settings-section">
                        <h4>Backup Database</h4>
                        <p>Backup otomatis database dilakukan setiap hari pada pukul 02:00 WIB.</p>
                        <button type="button" class="btn btn-primary" onclick="alert('Fitur backup manual akan segera tersedia')">
                            Backup Manual Sekarang
                        </button>
                    </div>

                    <div class="settings-section">
                        <h4>Keamanan</h4>
                        <p>Sistem menggunakan enkripsi password dan session management yang aman.</p>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="force_password_change" value="1"> 
                                Paksa pengguna mengganti password setiap 90 hari
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="enable_two_factor" value="1"> 
                                Aktifkan autentikasi dua faktor (2FA)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn btn-success">
                    💾 Simpan Pengaturan
                </button>
            </div>