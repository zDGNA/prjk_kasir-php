<?php
/**
 * Halaman Point of Sale (POS) - Sistem Kasir
 * 
 * Halaman ini menangani:
 * - Pencarian produk untuk transaksi
 * - Manajemen keranjang belanja
 * - Proses pembayaran dan transaksi
 * - AJAX untuk pencarian produk dan proses transaksi
 * 
 * @author Tim Developer
 * @version 1.0
 */

// Tampilkan error hanya saat dibuka langsung via browser (untuk debugging)
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     ini_set('display_errors', 1);
//     ini_set('display_startup_errors', 1);
//     error_reporting(E_ALL);
//     echo "MULAI POS.PHP<br>";
// }

// Include controller dan model yang diperlukan
require_once '../controllers/AuthController.php';
require_once '../controllers/ProductController.php';
require_once '../controllers/TransactionController.php';

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     echo "AUTH CONTROLLER LOADED<br>";
// }

// Inisialisasi controller autentikasi
$auth = new AuthController();
$auth->requireLogin();  // Pastikan user sudah login

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     echo "AUTH OBJECT CREATED<br>";
//     echo "LOGIN CHECK PASSED<br>";
// }

// Ambil data user yang sedang login dan inisialisasi controller produk
$current_user = $auth->getCurrentUser();
$productController = new ProductController();

// Handle AJAX POST requests untuk operasi POS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            // Handle pencarian produk via AJAX
            case 'search_product':
                $keyword = $_POST['keyword'] ?? '';
                $products = $productController->search($keyword);
                echo json_encode($products);
                exit;

            // Handle proses transaksi via AJAX
            case 'process_transaction':
                try {
                    error_log("=== [DEBUG] Mulai Proses Transaksi ===");

                    // Inisialisasi controller transaksi
                    $transactionController = new TransactionController();
                    $data = json_decode($_POST['data'], true);

                    // Validasi data JSON
                    if (!$data) {
                        throw new Exception('Data kosong atau format JSON tidak valid.');
                    }

                    error_log("DATA TRANSAKSI: " . json_encode($data));

                    // Validasi item transaksi
                    if (!isset($data['items']) || empty($data['items'])) {
                        throw new Exception('Item transaksi kosong');
                    }

                    // Generate kode transaksi unik
                    $data['transaction_code'] = "TRX" . date('YmdHis') . rand(100, 999);
                    error_log("Kode transaksi: " . $data['transaction_code']);

                    // Simpan transaksi ke database
                    $result = $transactionController->store($data);
                    error_log("Simpan transaksi selesai");

                    // Validasi hasil penyimpanan
                    if (!is_array($result)) {
                        throw new Exception('store() tidak mengembalikan array');
                    }

                    echo json_encode($result);
                } catch (Exception $e) {
                    error_log("ERROR TRANSAKSI: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
                exit;
        }
    } catch (Exception $e) {
        error_log("POS Error luar: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error luar: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Sistem Kasir</title>
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
        max-width: 100%;
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

    /* Layout POS dengan grid 2 kolom */
    .pos-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        height: calc(100vh - 120px);
    }

    /* Panel kiri dan kanan */
    .left-panel, .right-panel {
        background: #2d2d40;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.4);
        display: flex;
        flex-direction: column;
    }

    /* Bagian pencarian produk */
    .search-section {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
    }

    /* Container untuk search box */
    .search-box {
        position: relative;
    }

    /* Input pencarian produk */
    .search-input {
        width: 100%;
        padding: 12px 40px 12px 16px;
        border: 2px solid #3b3b52;
        border-radius: 8px;
        font-size: 16px;
        background: #1e1e2f;
        color: #d4d4d4;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
    }

    /* Icon pencarian */
    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    /* Grid untuk menampilkan produk */
    .products-grid {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    /* Kartu produk individual */
    .product-card {
        border: 2px solid #3b3b52;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #1e1e2f;
    }

    .product-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Placeholder gambar produk */
    .product-image {
        width: 80px;
        height: 80px;
        background: #3b3b52;
        border-radius: 8px;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #aaa;
    }

    /* Nama produk */
    .product-name {
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 14px;
        color: #ffffff;
    }

    /* Harga produk */
    .product-price {
        color: #667eea;
        font-weight: bold;
        font-size: 16px;
    }

    /* Informasi stok produk */
    .product-stock {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    /* Header keranjang belanja */
    .cart-header {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
        background: #1e1e2f;
        border-radius: 10px 10px 0 0;
    }

    .cart-header h3 {
        margin: 0;
        color: #ffffff;
    }

    /* Area item keranjang */
    .cart-items {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #1e1e2f;
    }

    /* Item individual dalam keranjang */
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #3b3b52;
    }

    /* Informasi item */
    .item-info .item-name {
        font-weight: 600;
        color: #ffffff;
    }

    .item-price {
        color: #999;
        font-size: 14px;
    }

    /* Tombol quantity */
    .qty-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #3b3b52;
        background: #2d2d40;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d4d4d4;
    }

    .qty-btn:hover {
        background: #3a3a5c;
    }

    /* Input quantity */
    .qty-input {
        width: 50px;
        text-align: center;
        border: 1px solid #3b3b52;
        background: #1e1e2f;
        color: #fff;
        border-radius: 4px;
        padding: 5px;
    }

    /* Tombol hapus item */
    .remove-btn {
        color: #dc3545;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
    }

    /* Summary keranjang */
    .cart-summary {
        padding: 20px;
        border-top: 1px solid #3b3b52;
        background: #1e1e2f;
    }

    /* Baris summary */
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .summary-row.total {
        font-weight: bold;
        font-size: 18px;
        color: #ffffff;
        border-top: 1px solid #3b3b52;
        padding-top: 10px;
        margin-top: 10px;
    }

    /* Form control untuk input */
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #3b3b52;
        border-radius: 4px;
        font-size: 14px;
        background: #1e1e2f;
        color: #d4d4d4;
    }

    /* Styling tombol */
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
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
        width: 100%;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-success:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }

    /* Tampilan keranjang kosong */
    .empty-cart {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-cart-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    /* Responsive design untuk tablet */
    @media (max-width: 1024px) {
        .pos-layout {
            grid-template-columns: 1fr;
            height: auto;
        }

        .right-panel {
            order: -1;
            max-height: 400px;
        }
    }

    /* Responsive design untuk mobile */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pos.php"class="active">Pembayaran</a></li>
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

    <!-- Container utama POS -->
    <div class="container">
        <div class="pos-layout">
            <!-- Panel kiri: Pencarian dan daftar produk -->
            <div class="left-panel">
                <!-- Bagian pencarian produk -->
                <div class="search-section">
                    <div class="search-box">
                        <input type="text" class="search-input" id="productSearch" placeholder="Cari produk berdasarkan nama atau barcode...">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
                <!-- Grid produk -->
                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here via JavaScript -->
                </div>
            </div>

            <!-- Panel kanan: Keranjang belanja dan pembayaran -->
            <div class="right-panel">
                <!-- Header keranjang -->
                <div class="cart-header">
                    <h3>Keranjang Belanja</h3>
                </div>
                <!-- Daftar item keranjang -->
                <div class="cart-items" id="cartItems">
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang masih kosong</p>
                        <small>Pilih produk untuk memulai transaksi</small>
                    </div>
                </div>
                <!-- Summary dan pembayaran -->
                <div class="cart-summary" id="cartSummary" style="display: none;">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Pajak (10%):</span>
                        <span id="tax">Rp 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="total">Rp 0</span>
                    </div>
                    
                    <!-- Bagian pembayaran -->
                    <div class="payment-section">
                        <div class="form-group">
                            <label>Metode Pembayaran</label>
                            <select class="form-control" id="paymentMethod">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Bayar</label>
                            <input type="number" class="form-control" id="paymentAmount" placeholder="0" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Kembalian</label>
                            <input type="text" class="form-control" id="changeAmount" readonly>
                        </div>
                        <button class="btn btn-success" id="processBtn" onclick="processTransaction()">
                            Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variabel global untuk menyimpan data keranjang dan produk
        let cart = [];
        let products = [];

        // Load semua produk saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts('');
        });

        // Event listener untuk pencarian produk
        document.getElementById('productSearch').addEventListener('input', function() {
            const keyword = this.value;
            loadProducts(keyword);
        });

        // Event listener untuk kalkulasi kembalian
        document.getElementById('paymentAmount').addEventListener('input', function() {
            calculateChange();
        });

        /**
         * Memuat produk dari server via AJAX
         * @param {string} keyword - Kata kunci pencarian
         */
        function loadProducts(keyword) {
            const formData = new FormData();
            formData.append('action', 'search_product');
            formData.append('keyword', keyword);

            fetch('pos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                products = data;
                displayProducts(data);
            })
            .catch(error => {
                console.error('Error loading products:', error);
                alert('Gagal memuat produk: ' + error.message);
            });
        }

        /**
         * Menampilkan produk dalam grid
         * @param {Array} products - Array produk yang akan ditampilkan
         */
        function displayProducts(products) {
            const grid = document.getElementById('productsGrid');
            
            if (products.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #6c757d;">Tidak ada produk ditemukan</div>';
                return;
            }

            grid.innerHTML = products.map(product => `
                <div class="product-card" onclick="addToCart(${product.id})">
                    <div class="product-image">📦</div>
                    <div class="product-name">${escapeHtml(product.name)}</div>
                    <div class="product-price">Rp ${formatNumber(product.price)}</div>
                    <div class="product-stock">Stok: ${product.stock} ${escapeHtml(product.unit)}</div>
                </div>
            `).join('');
        }

        /**
         * Menambahkan produk ke keranjang
         * @param {number} productId - ID produk yang akan ditambahkan
         */
        function addToCart(productId) {
            const product = products.find(p => p.id == productId);
            if (!product || product.stock <= 0) {
                alert('Produk tidak tersedia atau stok habis');
                return;
            }

            const existingItem = cart.find(item => item.id == productId);
            if (existingItem) {
                // Jika produk sudah ada, tambah quantity
                if (existingItem.quantity < product.stock) {
                    existingItem.quantity++;
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    alert('Stok tidak mencukupi');
                    return;
                }
            } else {
                // Jika produk baru, tambahkan ke keranjang
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: 1,
                    total: parseFloat(product.price),
                    stock: product.stock
                });
            }

            updateCartDisplay();
        }

        /**
         * Update quantity item dalam keranjang
         * @param {number} productId - ID produk
         * @param {number} change - Perubahan quantity (+1 atau -1)
         */
        function updateQuantity(productId, change) {
            const item = cart.find(item => item.id == productId);
            if (!item) return;

            const newQuantity = item.quantity + change;
            if (newQuantity <= 0) {
                removeFromCart(productId);
                return;
            }

            if (newQuantity > item.stock) {
                alert('Stok tidak mencukupi');
                return;
            }

            item.quantity = newQuantity;
            item.total = item.quantity * item.price;
            updateCartDisplay();
        }

        /**
         * Hapus item dari keranjang
         * @param {number} productId - ID produk yang akan dihapus
         */
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id != productId);
            updateCartDisplay();
        }

        /**
         * Update tampilan keranjang belanja
         */
        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartSummary = document.getElementById('cartSummary');

            if (cart.length === 0) {
                // Tampilkan pesan keranjang kosong
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang masih kosong</p>
                        <small>Pilih produk untuk memulai transaksi</small>
                    </div>
                `;
                cartSummary.style.display = 'none';
                return;
            }

            // Tampilkan item dalam keranjang
            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${escapeHtml(item.name)}</div>
                        <div class="item-price">Rp ${formatNumber(item.price)} x ${item.quantity}</div>
                    </div>
                    <div class="item-controls">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <input type="number" class="qty-input" value="${item.quantity}" 
                               onchange="setQuantity(${item.id}, this.value)" min="1" max="${item.stock}">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <span class="remove-btn" onclick="removeFromCart(${item.id})">🗑️</span>
                    </div>
                </div>
            `).join('');

            // Hitung total
            const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            const tax = subtotal * 0.1;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = 'Rp ' + formatNumber(subtotal);
            document.getElementById('tax').textContent = 'Rp ' + formatNumber(tax);
            document.getElementById('total').textContent = 'Rp ' + formatNumber(total);

            cartSummary.style.display = 'block';
            calculateChange();
        }

        /**
         * Set quantity item secara langsung
         * @param {number} productId - ID produk
         * @param {string} quantity - Quantity baru
         */
        function setQuantity(productId, quantity) {
            const item = cart.find(item => item.id == productId);
            if (!item) return;

            quantity = parseInt(quantity);
            if (quantity <= 0) {
                removeFromCart(productId);
                return;
            }

            if (quantity > item.stock) {
                alert('Stok tidak mencukupi');
                return;
            }

            item.quantity = quantity;
            item.total = item.quantity * item.price;
            updateCartDisplay();
        }

        /**
         * Hitung kembalian berdasarkan jumlah bayar
         */
        function calculateChange() {
            const total = cart.reduce((sum, item) => sum + item.total, 0) * 1.1; // Include tax
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const change = paymentAmount - total;

            document.getElementById('changeAmount').value = change >= 0 ? 'Rp ' + formatNumber(change) : 'Rp 0';
        }

        /**
         * Proses transaksi pembayaran
         */
        function processTransaction() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong');
                return;
            }

            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            const tax = subtotal * 0.1;
            const total = subtotal + tax;

            if (paymentAmount < total) {
                alert('Jumlah pembayaran kurang');
                return;
            }

            // Disable button dan tampilkan loading
            const processBtn = document.getElementById('processBtn');
            processBtn.disabled = true;
            processBtn.textContent = 'Memproses...';
            processBtn.classList.add('loading');

            // Siapkan data transaksi
            const transactionData = {
                items: cart.map(item => ({
                    product_id: parseInt(item.id),
                    quantity: parseInt(item.quantity),
                    unit_price: parseFloat(item.price),
                    total_price: parseFloat(item.total)
                })),
                subtotal: parseFloat(subtotal),
                tax_amount: parseFloat(tax),
                total_amount: parseFloat(total),
                payment_method: document.getElementById('paymentMethod').value,
                payment_amount: parseFloat(paymentAmount),
                change_amount: parseFloat(paymentAmount - total)
            };

            console.log('Sending transaction data:', transactionData);

            // Kirim data transaksi via AJAX
            const formData = new FormData();
            formData.append('action', 'process_transaction');
            formData.append('data', JSON.stringify(transactionData));

            fetch('pos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                    if (data.success) {
                        alert('Transaksi berhasil! Kode: ' + (data.transaction_code || 'N/A'));
                        // Reset keranjang
                        cart = [];
                        updateCartDisplay();
                        document.getElementById('paymentAmount').value = '';
                        document.getElementById('changeAmount').value = '';
                        // Reload produk untuk update stok
                        loadProducts('');
                    } else {
                        alert('Error: ' + (data.message || 'Transaksi gagal'));
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    alert('Error parsing response: ' + e.message);
                }
            })
            .catch(error => {
                console.error('Error processing transaction:', error);
                alert('Terjadi kesalahan saat memproses transaksi: ' + error.message);
            })
            .finally(() => {
                // Re-enable button
                processBtn.disabled = false;
                processBtn.textContent = 'Proses Transaksi';
                processBtn.classList.remove('loading');
            });
        }

        /**
         * Format angka dengan pemisah ribuan
         * @param {number} number - Angka yang akan diformat
         * @returns {string} Angka yang sudah diformat
         */
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        /**
         * Escape HTML untuk mencegah XSS
         * @param {string} text - Text yang akan di-escape
         * @returns {string} Text yang sudah di-escape
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>